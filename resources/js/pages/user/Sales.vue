<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import apiClient from '@/apiClient';
import { ref, onMounted } from 'vue';
import { ReceiptText, ListCollapse, X, Trash2 } from 'lucide-vue-next';
// import CreateEditProduct from './modals/CreateEditProduct.vue';
import showNotification from '@/notification';
import { dateEs } from '@/dateEs';
import { time12H } from '@/time12H';

const breadcrumbs = [
    {
        title: 'Ventas',
        href: dashboard().url,
    },
];

// const { allProducts } = defineProps({
//     allProducts: {
//         type: Array,
//         required: true
//     },
// });

const createEditProductRef = ref(null);
const sales = ref([]);

onMounted(() => {
    getSales();
});

const getSales = async ()=> {
    const response = await apiClient('user/sales');
    sales.value    = response.data;
};

const openModal = (product = null)=> {
    // createEditProductRef.value?.showModal(product);
};

const statusSale = async (id, status)=> {
    const response = await apiClient('user/sale', 'PUT', {id, status});
    if (response.error) {
        showNotification(response.msj, 'error');
        return
    }
    getSales();
    showNotification(`La venta se canceló correctamente.`);
};

const deleteProduct = async (id)=> {
    // const response = await apiClient(`user/product/${id}`, 'DELETE');
    // if (response.error) {
    //     showNotification(response.msj, 'error');
    //     return
    // }
    // getSales();
    // showNotification(response.msj);
};

const parseQuantity = (type_sale, quantity)=> {
    return type_sale === 'pza' ? parseInt(quantity) : quantity.toFixed(3);
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
                    <el-table-column label="Fecha y hora de cancelación" align="center">
                        <template #default="scope">
                            <span v-if="scope.row.status_id === 2">{{ dateEs(scope.row.updated_at, '/', 1) }}<br>{{ time12H(scope.row.updated_at) }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="observations" label="Motivo de cancelación"/>
                    <el-table-column prop="updated_by.name">
                        <template #header>
                            Usuario<br>que<br>canceló
                        </template>
                    </el-table-column>
                    <el-table-column label="Estatus" align="center">
                        <template #default="scope">
                            <b :class="colorStatus(scope.row.status_id)">{{ scope.row.status.name }}</b>
                        </template>
                    </el-table-column>
                    <el-table-column label="Acciones" width="180" align="center">
                        <!-- <template #header>
                            <el-tooltip content="Nuevo producto" placement="top">
                                <el-button type="primary" class="!p-1" @click="openModal()">
                                    <Plus size="20" />
                                </el-button>
                            </el-tooltip>
                        </template> -->
                        <template #default="scope">
                            <el-button-group>
                                <el-tooltip content="Detalles de venta" placement="top">
                                    <el-button type="primary" class="!p-1">
                                        <ListCollapse :size="20" />
                                    </el-button>
                                </el-tooltip>
                                <!-- <el-tooltip :content="scope.row.product_store.status ? 'Desactivar producto' : 'Activar producto'" placement="top">
                                    <el-button
                                        :type="scope.row.product_store.status ? 'warning' : 'success'"
                                        class="!p-1"
                                        @click="statusProduct(scope.row)"
                                    >
                                        <Eye size="20" />
                                    </el-button>
                                </el-tooltip> -->
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
                                                <el-button type="warning" class="!p-1" style="border-top-left-radius: 0px; border-bottom-left-radius: 0px;">
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
            </el-card>
            <!-- <CreateEditProduct ref="createEditProductRef" :get-parent-products="getSales" :all-products="allProducts"/> -->
        </div>
    </AppLayout>
</template>
