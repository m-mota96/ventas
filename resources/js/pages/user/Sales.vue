<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head, usePage } from '@inertiajs/vue3';
import apiClient from '@/apiClient';
import { ref, onMounted, computed } from 'vue';
import { ReceiptText, ListCollapse, X, Trash2 } from 'lucide-vue-next';
import DetailSale from './modals/DetailSale.vue';
import showNotification from '@/notification';
import { dateEs } from '@/dateEs';
import { time12H } from '@/time12H';
import Swal from 'sweetalert2';
const page = usePage();
const user = computed(() => page.props.auth.user);

const breadcrumbs = [
    {
        title: `Ventas - Sucursal ${user.value.store.name}`,
        href: dashboard().url,
    },
];

const detailSaleRef = ref(null);
const sales         = ref([]);
const pagination    = ref({
    currentPage: 1,
    pageSize: 20,
    total: 0
});

onMounted(() => {
    getSales();
});

const getSales = async ()=> {
    const response = await apiClient('user/sales', 'GET', {
        pagination: pagination.value,
    });
    pagination.value.total = response.data.totalRows;
    sales.value            = response.data.sales;
};

const openModal = (inventories = null)=> {
    detailSaleRef.value?.showModal(inventories);
};

const statusSale = async (id, status)=> {
    Swal.fire({
        title: 'Cancelar',
        text: 'Por favor, indica el motivo de la cancelación',
        input: 'textarea',
        inputPlaceholder: 'Escribe el motivo aquí...',
        inputAttributes: {
            'aria-label': 'Motivo de cancelación'
        },
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cerrar',
        confirmButtonColor: "#3085d6",
        reverseButtons: true,
        preConfirm: (motivo) => {
            if (!motivo || motivo.trim() === '') {
            Swal.showValidationMessage(
                'Debes ingresar un motivo de cancelación'
            )
            }
            return motivo
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const response = await apiClient('user/sale', 'PUT', {id, status, observations: result.value});
            if (response.error) {
                showNotification(response.msj, 'error');
                return
            }
            getSales();
            showNotification(`La venta se canceló correctamente.`);
        }
    });
};

const formatCurrency = (value)=> {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const colorStatus = (status) => {
    switch (status) {
        case 2: // Cancelada
            return 'text-red-500';
        default:
            return 'text-green-500';
    }
};

const handleSizeChange = (val) => {
    getSales();
}
const handleCurrentChange = (val) => {
    getSales();
}
</script>

<template>
    <Head title="Ventas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <el-card>
                <el-table :data="sales" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-black bold">
                    <el-table-column prop="id" label="#" width="50" align="center" />
                    <el-table-column label="Monto total" align="center">
                        <template #default="scope">
                            {{ formatCurrency(scope.row.total) }}
                        </template>
                    </el-table-column>
                    <el-table-column label="Efectico" align="center">
                        <template #default="scope">
                            {{ formatCurrency(scope.row.cash) }}
                        </template>
                    </el-table-column>
                    <el-table-column label="Tarjeta" align="center">
                        <template #default="scope">
                            {{ formatCurrency(scope.row.card) }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="payment_method.payment_method" label="Método de pago"/>
                    <el-table-column label="Cantidad de productos vendidos" align="center">
                        <template #default="scope">
                            {{ scope.row.inventories.length }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="created_by.name" label="Responsable"/>
                    <el-table-column label="Fecha y hora" align="center">
                        <template #default="scope">
                            {{ dateEs(scope.row.created_at, '/', 1) }}<br>{{ time12H(scope.row.created_at) }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="observations" label="Motivo de cancelación"/>
                    <el-table-column prop="updated_by.name">
                        <template #header>
                            Usuario<br>que<br>canceló
                        </template>
                    </el-table-column>
                    <el-table-column label="Fecha y hora de cancelación" align="center">
                        <template #default="scope">
                            <span v-if="scope.row.status_id === 2">{{ dateEs(scope.row.updated_at, '/', 1) }}<br>{{ time12H(scope.row.updated_at) }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="Estatus" align="center">
                        <template #default="scope">
                            <b :class="colorStatus(scope.row.status_id)">{{ scope.row.status.name }}</b>
                        </template>
                    </el-table-column>
                    <el-table-column label="Acciones" width="180" align="center">
                        <template #default="scope">
                            <el-button-group>
                                <el-tooltip content="Detalles de venta" placement="top">
                                    <el-button type="primary" class="!p-1" @click="openModal(scope.row.inventories)">
                                        <ListCollapse :size="20" />
                                    </el-button>
                                </el-tooltip>
                                <el-popconfirm
                                    v-if="scope.row.status_id === 1"
                                    class="box-item"
                                    confirm-button-text="Si"
                                    cancel-button-text="No"
                                    :hide-icon="true"
                                    confirm-button-type="danger"
                                    cancel-button-type="primary"
                                    :width="200"
                                    title="¿Seguro que deseas cancelar esta venta?"
                                    placement="left"
                                    @confirm="statusSale(scope.row.id, 2)"
                                >
                                    <template #reference>
                                        <span>
                                            <el-tooltip content="Cancelar venta" placement="top">
                                                <el-button type="danger" class="!p-1" style="border-top-left-radius: 0px; border-bottom-left-radius: 0px;">
                                                    <X size="20" />
                                                </el-button>
                                            </el-tooltip>
                                        </span>
                                    </template>
                                </el-popconfirm>
                            </el-button-group>
                        </template>
                    </el-table-column>
                </el-table>
                <el-pagination
                    class="mt-3 custom-pager"
                    v-model:current-page="pagination.currentPage"
                    v-model:page-size="pagination.pageSize"
                    :page-sizes="[20, 40, 60, 80, 100]"
                    layout="total, sizes, prev, pager, next"
                    :total="pagination.total"
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                />
            </el-card>
            <DetailSale ref="detailSaleRef"/>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-pager :deep(.el-select) {
    width: 150px !important;
}
</style>