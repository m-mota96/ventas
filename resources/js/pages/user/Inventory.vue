<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import apiClient from '@/apiClient';
import { ref, onMounted, watch } from 'vue';
import { Plus, Pen, Trash2, FilterX  } from 'lucide-vue-next';
import CreateInventory from './modals/CreateInventory.vue';
import showNotification from '@/notification';
import { dateEs } from '@/dateEs';
import { rangeMonth } from '@/rangeMonth';

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

const range       = rangeMonth();
const inventories = ref([]);
const pagination  = ref({
    currentPage: 1,
    pageSize: 20,
    total: 0
});
const search = ref({
    product_name: '',
    quantity: '',
    type: [],
    reference: [1, 3],
    date: '',
    created_by: ''
});
const checkAll       = ref(false);
const indeterminate  = ref(false);
const checkAll2      = ref(false);
const indeterminate2 = ref(false);
const types = ref([
    {
        value: 'input',
        label: 'Ingreso'
    },
    {
        value: 'output',
        label: 'Egreso'
    }
]);

onMounted(() => {
    clearFilters();
    getInventory();
});

const getInventory = async ()=> {
    const response = await apiClient('user/inventories', 'GET', {
        pagination: pagination.value,
        search: search.value
    });
    pagination.value.total = response.data.totalRows;
    inventories.value      = response.data.inventories;
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

const clearFilters = ()=> {
    search.value.product_name = '';
    search.value.quantity     = '';
    search.value.type         = [];
    search.value.reference    = [1, 3];
    search.value.date         = [range.first, range.last];
    search.value.created_by   = '';
};

const parseQuantity = (type_sale, quantity)=> {
    return type_sale === 'pza' ? parseInt(quantity) : quantity;
};

const handleSizeChange = (val) => {
    getInventory();
};
const handleCurrentChange = (val) => {
    getInventory();
};

const handleCheckAll = (val) => {
    indeterminate.value = false
    if (val) {
        search.value.type = types.value.map((_) => _.value)
    } else {
        search.value.type = []
    }
    getInventory();
};

const handleCheckAll2 = (val) => {
    indeterminate2.value = false
    if (val) {
        search.value.reference = references.map((_) => _.value)
    } else {
        search.value.reference = []
    }
    getInventory();
};

watch(
    () => search.value.type,
    (val) => {
        if (!val || val.length === 0) {
            checkAll.value = false;
            indeterminate.value = false;
        } 
        else if (val.length === types.value.length) {
            checkAll.value = true;
            indeterminate.value = false;
        } 
        else {
            checkAll.value = false;
            indeterminate.value = true;
        }
    },
    { deep: true }
);

watch(
    () => search.value.reference,
    (val) => {
        if (!val || val.length === 0) {
            checkAll2.value = false;
            indeterminate2.value = false;
        } 
        else if (val.length === references.length) {
            checkAll2.value = true;
            indeterminate2.value = false;
        } 
        else {
            checkAll2.value = false;
            indeterminate2.value = true;
        }
    },
    { deep: true }
);
</script>

<template>
    <Head title="Inventario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <el-card>
                <el-row :gutter="20" class="mb-7">
                    <el-col :span="8" :offset="8" class="text-center mb-2">
                        <p class="bold text-lg">Búsquedas</p>
                    </el-col>
                    <el-col :span="8" class="mb-2 text-right">
                        <el-tooltip
                            class="box-item"
                            effect="dark"
                            content="Limpiar filtros"
                            placement="top"
                        >
                            <el-button size="small" @click="clearFilters">
                                <FilterX :size="18" class="text-black"/>
                            </el-button>
                        </el-tooltip>
                    </el-col>
                    <el-col :span="4">
                        <label for="searchProduct" class="text-sm text-black">Producto</label>
                        <el-input v-model="search.product_name" class="w-100" id="searchProduct" @input="getInventory" clearable />
                    </el-col>
                    <el-col :span="4">
                        <label for="searchQuantity" class="text-sm text-black">Cantidad</label>
                        <el-input v-model="search.quantity" class="w-100" id="searchQuantity" @input="getInventory" clearable />
                    </el-col>
                    <el-col :span="4">
                        <label for="searchType" class="text-sm text-black">Tipo de movimiento</label>
                        <el-select
                            v-model="search.type"
                            multiple
                            clearable
                            collapse-tags
                            placeholder="Elige una opción"
                            popper-class="custom-header"
                            :max-collapse-tags="1"
                            id="searchType"
                            @change="getInventory"
                        >
                            <template #header>
                            <el-checkbox
                                v-model="checkAll"
                                :indeterminate="indeterminate"
                                @change="handleCheckAll"
                            >
                                Seleccionar todo
                            </el-checkbox>
                            </template>
                            <el-option
                                v-for="item in types"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"
                            />
                        </el-select>
                    </el-col>
                    <el-col :span="4">
                        <label for="searchReference" class="text-sm text-black">Referencia</label>
                         <el-select
                            v-model="search.reference"
                            multiple
                            collapse-tags
                            placeholder="Elige una opción"
                            popper-class="custom-header"
                            :max-collapse-tags="1"
                            id="searchReference"
                            @change="getInventory"
                        >
                            <template #header>
                            <el-checkbox
                                v-model="checkAll2"
                                :indeterminate="indeterminate2"
                                @change="handleCheckAll2"
                            >
                                Seleccionar todo
                            </el-checkbox>
                            </template>
                            <el-option
                                v-for="item in references"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id"
                            />
                        </el-select>
                    </el-col>
                    <el-col :span="4">
                        <label class="text-sm text-black">Rango de fecha</label>
                        <el-date-picker
                            v-model="search.date"
                            type="daterange"
                            range-separator="A"
                            start-placeholder="Fecha incial"
                            end-placeholder="Fecha final"
                            class="w-100"
                            format="DD/MM/YYYY"
                            value-format="YYYY-MM-DD"
                            :clearable="false"
                            @change="getInventory"
                        />
                    </el-col>
                    <el-col :span="4">
                        <label for="searchUser" class="text-sm text-black">Usuario que registró</label>
                        <el-input v-model="search.created_by" class="w-100" id="searchUser" @input="getInventory" clearable />
                    </el-col>
                </el-row>
                <el-divider />
                <el-table :data="inventories" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-black bold">
                    <el-table-column prop="id" label="#" width="50" align="center" />
                    <el-table-column label="Producto">
                        <template #default="scope">
                            {{ scope.row.product.name }} {{ scope.row.product.content }} {{ scope.row.product.abreviation }}
                        </template>
                    </el-table-column>
                    <el-table-column label="Cantidad" align="center">
                        <template #default="scope">
                            {{ parseQuantity(scope.row.product.type_sale, scope.row.quantity) }}
                        </template>
                    </el-table-column>
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
                                    v-if="scope.row.type === 'input' || (scope.row.type === 'output' && scope.row.reference_id === 3)"
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
            <CreateInventory ref="createInventoryRef" :get-parent-inventory="getInventory" :products="products" :references="references"/>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-pager :deep(.el-select) {
    width: 150px !important;
}
.custom-header {
    .el-checkbox {
        display: flex;
        height: unset;
    }
}
</style>