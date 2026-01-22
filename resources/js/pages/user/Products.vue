<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import apiClient from '@/apiClient';
import { ref, onMounted } from 'vue';
import { Plus, Pen, Eye, Trash2 } from 'lucide-vue-next';
import CreateEditProduct from './modals/CreateEditProduct.vue';
import showNotification from '@/notification';

const breadcrumbs = [
    {
        title: 'Productos',
        href: dashboard().url,
    },
];

const { allProducts } = defineProps({
    allProducts: {
        type: Array,
        required: true
    },
});

const createEditProductRef = ref(null);
const products = ref([]);

onMounted(() => {
    getProducts();
});

const getProducts = async ()=> {
    const response = await apiClient('user/products');
    products.value = response.data;
};

const openModal = (product = null)=> {
    createEditProductRef.value?.showModal(product);
};

const statusProduct = async (product)=> {
    const prod = {
        id: product.id,
        name: product.name,
        bar_code: product.bar_code,
        sku: product.sku,
        description: product.description,
        price: product.product_store.price,
        batch: product.product_store.batch,
        expiration_date: product.product_store.expiration_date,
        discount: product.product_store.discount,
        status: !product.product_store.status,
    };
    const msj      = prod.status ? 'activó' : 'desactivó';
    const response = await apiClient('user/product', 'PUT', prod);
    if (response.error) {
        showNotification(response.msj, 'error');
        return
    }
    getProducts();
    showNotification(`El producto se ${msj} correctamente.`);
};

const deleteProduct = async (id)=> {
    const response = await apiClient(`user/product/${id}`, 'DELETE');
    if (response.error) {
        showNotification(response.msj, 'error');
        return
    }
    getProducts();
    showNotification(response.msj);
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
</script>

<template>
    <Head title="Productos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <el-card>
                <el-table :data="products" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-black bold">
                    <el-table-column prop="id" label="#" width="50" align="center" />
                    <el-table-column prop="bar_code" label="Código de barras" />
                    <el-table-column prop="name" label="Nombre del producto" />
                    <el-table-column label="Contenido (gr/ml)">
                        <template #default="scope">
                            {{ scope.row.content }} {{ scope.row.abreviation }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="description" label="Descripción" />
                    <el-table-column prop="product_store.price" label="Precio">
                        <template #default="scope">
                            {{ formatCurrency(scope.row.product_store.price) }}
                        </template>
                    </el-table-column>
                    <el-table-column label="Precio mayoreo" align="center">
                        <template #default="scope">
                            {{ scope.row.product_store.discounted_price ? formatCurrency(scope.row.product_store.discounted_price) : '0.00' }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="product_store.discount" label="Precio especial" align="center">
                        <template #default="scope">
                            {{ scope.row.product_store.special_price ? formatCurrency(scope.row.product_store.special_price) : '0.00' }}
                        </template>
                    </el-table-column>
                    <el-table-column label="Cantidad disponible" align="center">
                        <template #default="scope">
                            <!-- {{ (scope.row.inputs - scope.row.outputs).toFixed(3) }} -->
                            {{ parseQuantity(scope.row.type_sale, (scope.row.inputs - scope.row.outputs)) }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="Estatus" width="90" align="center">
                        <template #default="scope">
                            <span class="bold" :class="scope.row.product_store.status ? 'text-green-500' : 'text-red-500'">{{ scope.row.product_store.status ? 'Activo' : 'Inactivo' }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column width="180" align="center">
                        <template #header>
                            <el-tooltip content="Nuevo producto" placement="top">
                                <el-button type="primary" class="!p-1" @click="openModal()">
                                    <Plus size="20" />
                                </el-button>
                            </el-tooltip>
                        </template>
                        <template #default="scope">
                            <el-button-group>
                                <el-tooltip content="Editar producto" placement="top">
                                    <el-button type="primary" class="!p-1" @click="openModal(scope.row)">
                                        <Pen size="20" />
                                    </el-button>
                                </el-tooltip>
                                <el-tooltip :content="scope.row.product_store.status ? 'Desactivar producto' : 'Activar producto'" placement="top">
                                    <el-button
                                        :type="scope.row.product_store.status ? 'warning' : 'success'"
                                        class="!p-1"
                                        @click="statusProduct(scope.row)"
                                    >
                                        <Eye size="20" />
                                    </el-button>
                                </el-tooltip>
                                <el-popconfirm
                                    class="box-item"
                                    confirm-button-text="Eliminar"
                                    cancel-button-text="Cancelar"
                                    :hide-icon="true"
                                    confirm-button-type="danger"
                                    cancel-button-type="primary"
                                    :width="200"
                                    title="¿Seguro que deseas eliminar este producto?"
                                    placement="left"
                                    @confirm="deleteProduct(scope.row.id)"
                                >
                                    <template #reference>
                                        <span>
                                            <el-tooltip content="Eliminar producto" placement="top">
                                                <el-button type="danger" class="!p-1" style="border-top-left-radius: 0px; border-bottom-left-radius: 0px;">
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
            <CreateEditProduct ref="createEditProductRef" :get-parent-products="getProducts" :all-products="allProducts"/>
        </div>
    </AppLayout>
</template>
