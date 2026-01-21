<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import apiClient from '@/apiClient';
import { ref, onMounted } from 'vue';
import { Plus, Pen, Eye, Trash2 } from 'lucide-vue-next';
import CreateInventory from './modals/CreateInventory.vue';
import showNotification from '@/notification';
import { dateEs } from '@/dateEs';

const breadcrumbs = [
    {
        title: 'Inventario',
        href: dashboard().url,
    },
];

const { products, references } = defineProps({
    products: {
        type: Array,
        required: true
    },
    references: {
        type: Array,
        required: true
    }
});

const createInventoryRef = ref(null);
const inventories = ref([]);

onMounted(() => {
    getInventory();
});

const getInventory = async ()=> {
    const response = await apiClient('user/inventories');
    inventories.value = response.data;
};

const openModal = (product = null)=> {
    createInventoryRef.value?.showModal(product);
};

const typeInventory = (_type)=> {
    return _type === 'input' ? 'Ingreso' : 'Egreso';
};

const deleteInventory = async (id)=> {
    const response = await apiClient(`user/inventory/${id}`, 'DELETE');
    if (response.error) {
        showNotification(response.msj, 'error');
        return
    }
    getInventory();
    showNotification(response.msj);
};
</script>

<template>
    <Head title="Inventario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <el-card>
                <el-table :data="inventories" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-black bold">
                    <el-table-column prop="id" label="#" width="50" align="center" />
                    <el-table-column label="Producto">
                        <template #default="scope">
                            {{ scope.row.product.name }} {{ scope.row.product.content }} {{ scope.row.product.abreviation }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="quantity" label="Cantidad" align="center" />
                    <el-table-column label="Tipo de movimiento" align="center">
                        <template #default="scope">
                            <span :class="scope.row.type === 'input' ? 'text-green-500' : 'text-red-500'">{{ typeInventory(scope.row.type) }}</span>
                        </template>
                    </el-table-column>
                    <!-- <el-table-column label="Referencia" align="center">
                        <template #default="scope">
                            {{ typeReference(scope.row.reference) }}
                        </template>
                    </el-table-column> -->
                    <el-table-column prop="reference.name" label="Referencia" />
                    <el-table-column prop="description" label="Descripción" />
                    <el-table-column prop="batch" label="Lote" />
                    <el-table-column label="Fecha de caducidad">
                        <template #default="scope">
                            <span v-if="scope.row.expiration_date">{{ dateEs(scope.row.expiration_date, '/', 1) }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="Fecha">
                        <template #default="scope">
                            {{ dateEs(scope.row.created_at, '/', 1) }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="created_by.name" label="Usuario que registró" />
                    <el-table-column width="120" align="center">
                        <template #header>
                            <el-tooltip content="Nuevo registro" placement="top">
                                <el-button type="primary" class="!p-1" @click="openModal()">
                                    <Plus size="20" />
                                </el-button>
                            </el-tooltip>
                        </template>
                        <template #default="scope">
                            <el-button-group>
                                <!-- <el-tooltip content="Editar producto" placement="top">
                                    <el-button type="primary" class="!p-1" @click="openModal(scope.row)">
                                        <Pen size="20" />
                                    </el-button>
                                </el-tooltip> -->
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
                                    v-if="scope.row.type === 'input'"
                                    class="box-item"
                                    confirm-button-text="Eliminar"
                                    cancel-button-text="Cancelar"
                                    :hide-icon="true"
                                    confirm-button-type="danger"
                                    cancel-button-type="primary"
                                    :width="200"
                                    title="¿Seguro que deseas eliminar este registro?"
                                    placement="left"
                                    @confirm="deleteInventory(scope.row.id)"
                                >
                                    <template #reference>
                                        <span>
                                            <el-tooltip content="Eliminar registro" placement="left">
                                                <el-button type="danger" class="!p-1">
                                                    <Trash2 size="20" />
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
            <CreateInventory ref="createInventoryRef" :get-parent-inventory="getInventory" :products="products" :references="references"/>
        </div>
    </AppLayout>
</template>
