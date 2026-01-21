<script setup lang="js">
import apiClient from '@/apiClient';
import AppLayout from '@/layouts/AppLayout.vue';
import showNotification from '@/notification';
import { dashboard } from '@/routes';
// import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Search, Trash2, X, Check } from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const breadcrumbs = [
    {
        title: `Inicio - Sucursal ${user.value.store.name}`,
        href: dashboard().url,
    },
];

const { listProducts, paymentMethods } = defineProps({
    listProducts: {
        type: Array,
        required: true
    },
    paymentMethods: {
        type: Array,
        required: true
    },
});

const search = ref({
    bar_code: '',
    name: '',
    sku: ''
});

const sale = ref({
    // quantity: 1,
    paymentMethod: 0,
    subtotal: 0,
    discount: 0,
    total: 0,
    payTo: '',
    change: 0,
    cash: '',
    card: ''
});

const products     = ref([]);
const disablePayTo = ref(false);

onMounted(() => {
    paymentMethods.forEach(pm => {
        if (pm.default) {
            sale.value.paymentMethod = pm.id;
        }
    });
    if (sale.value.paymentMethod === 2) {
        disablePayTo.value = true;
    }
});

const registerSale = async ()=> {
    const response = await apiClient('user/registerSale', 'POST', {sale: sale.value, products: products.value});
    if (response.error) {
        showNotification(response.msj, 'error');
        return
    }
    showNotification(response.msj);
}

const calculateTotal = ()=> {
    let total = 0;
    products.value.forEach(p => {
        total = total + (p.quantity * p.price);
    });
    sale.value.subtotal = total;
    sale.value.total    = total;
};

const handleSelect = (_product)=> {
    products.value.push({
        id: _product.id,
        quantity: 1,
        name: _product.name,
        content: _product.content,
        abreviation: _product.abreviation,
        description: _product.description,
        price: _product.product_store.price,
        discount: _product.product_store.discount
    });
    calculateTotal();
    search.value.bar_code = '';
    search.value.name     = '';
    search.value.sku      = '';
};

const calculateChange = (_value)=> {
    sale.value.change = 0;
    if (_value > sale.value.total) {
        sale.value.change = _value - sale.value.total;
    }
};

const payToDisabled = (_value)=> {
    disablePayTo.value = false;
    sale.value.cash    = '';
    sale.value.card    = '';
    if (_value === 2) {
        disablePayTo.value = true;
        sale.value.payTo   = '';
        sale.value.change  = 0;
        sale.value.card    = sale.value.total;
    }
    if (_value === 3) {
        disablePayTo.value = true;
        sale.value.payTo   = '';
        sale.value.change  = 0;
        sale.value.card = (sale.value.total - sale.value.cash);
    }
};

const removeProduct = (id) => {
    products.value = products.value.filter(p => p.id !== id);
    calculateTotal();
};

const formatCurrency = (value)=> {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const querySearch = (queryString, cb) => {
    const results = listProducts
    .filter(createFilter(queryString))
    .filter(product => !products.value.some(p => p.id === product.id))
    .map(product => ({
      ...product,
      value: `${product.name} ${product.content} ${product.abreviation}`
    }));

    cb(results)
};

const createFilter = (queryString) => {
    return (product) => {
        return (
            product.name.toLowerCase().indexOf(queryString.toLowerCase()) === 0
        )
    }
};
</script>

<template>
    <Head title="Inicio" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <el-card class="my-card">
                <el-row :gutter="20">
                    <el-col :span="8">
                        <el-form-item>
                            <template #label>
                                <span class="text-black">Código de barras</span>
                            </template>
                            <el-input v-model="search.bar_code" clearable placeholder="Escanea el código para agregar productos" />
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item>
                            <template #label>
                                <span class="text-black">Producto</span>
                            </template>
                            <el-autocomplete
                                v-model="search.name"
                                :fetch-suggestions="querySearch"
                                :trigger-on-focus="false"
                                clearable
                                placeholder="Escribe el nombre del producto para buscar"
                                @select="handleSelect"
                            />
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item>
                            <template #label>
                                <span class="text-black">Sku</span>
                            </template>
                            <el-input v-model="search.sku" clearable placeholder="Escribe la clave para buscar" />
                        </el-form-item>
                    </el-col>
                    <el-col :span="24" class="mt-5">
                        <el-table
                            :data="products"
                            stripe
                            empty-text="No hay productos seleccionados para venta"
                            header-cell-class-name="text-black bold text-base !bg-blue-100"
                            row-class-name="text-black text-base"
                            style="border: 1px solid grey; height: 53vh !important; border-radius: 5px; width: 100%;"
                        >
                            <el-table-column>
                                <template #header>
                                    
                                </template>
                                <template #default="scope">
                                    <el-tooltip content="Quitar producto" placement="left">
                                        <el-button type="danger" size="small" @click="removeProduct(scope.row.id)"><Trash2 :size="15" /></el-button>
                                    </el-tooltip>
                                </template>
                            </el-table-column>
                            <el-table-column label="Producto" min-width="350">
                                <template #default="scope">
                                    {{ scope.row.name }} {{ scope.row.content }} {{ scope.row.abreviation }}
                                </template>
                            </el-table-column>
                            <el-table-column prop="description" label="Descripción" min-width="350" />
                            <el-table-column label="Precio" align="center" min-width="150">
                                <template #default="scope">
                                    {{ formatCurrency(scope.row.price) }}
                                </template>
                            </el-table-column>
                            <el-table-column prop="quantity" label="Cantidad" align="center" min-width="150">
                                <template #default="scope">
                                    <el-input-number
                                        v-model="products[scope.$index].quantity"
                                        size="small"
                                        :precision="0"
                                        :step="1"
                                        :min="1"
                                        @change="calculateTotal"
                                    />
                                </template>
                            </el-table-column>
                            <el-table-column label="Importe" align="center" min-width="150">
                                <template #default="scope">
                                    {{ formatCurrency(scope.row.price * scope.row.quantity) }}
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-col>
                    <el-col :span="24" class="mt-3">
                        <el-row :gutter="30">
                            <el-col :span="5">
                                <p class="text-2xl mb-1 relative">
                                    <span class="text-balck">Subtotal: </span>
                                    <span class="text-blue-600 absolute" style="right: 0;">{{ formatCurrency(sale.subtotal) }}</span>
                                </p>
                                <p class="text-2xl mb-1 relative">
                                    <span class="text-balck">Descuento: </span>
                                    <span class="text-blue-600 absolute" style="right: 0;">{{ formatCurrency(sale.discount) }}</span>
                                </p>
                                <p class="text-2xl mb-1 relative">
                                    <span class="text-balck bold">Total: </span>
                                    <span class="text-blue-600 bold absolute" style="right: 0;">{{ formatCurrency(sale.total) }}</span>
                                </p>
                                <p class="text-2xl mb-1">
                                    <el-form-item>
                                        <template #label>
                                            <span class="text-black">Pagó con</span>
                                        </template>
                                        <el-input v-model="sale.payTo" @input="calculateChange" clearable :disabled="disablePayTo" />
                                    </el-form-item>
                                </p>
                                <p class="text-2xl relative">
                                    <span class="text-balck bold">Cambio: </span>
                                    <span class="text-blue-600 absolute" style="right: 0;">{{ formatCurrency(sale.change) }}</span>
                                </p>
                            </el-col>
                            <el-col :span="5">
                                <p class="text-black text-lg">Método de pago</p>
                                <el-radio-group v-model="sale.paymentMethod" @change="payToDisabled">
                                    <div class="w-100" v-for="pm in paymentMethods" :key="pm.id">
                                        <el-radio :value="pm.id">{{ pm.payment_method }}</el-radio><br>
                                    </div>
                                </el-radio-group>
                            </el-col>
                            <el-col :span="4">
                                <div v-if="sale.paymentMethod === 3">
                                    <p class="text-black bold mt-2">¿Cuánto pagó en efectivo?</p>
                                    <el-input placeholder="Importe" v-model="sale.cash" @input="sale.card = sale.total - sale.cash" clearable />
                                </div>
                                <div v-if="sale.paymentMethod === 2 || sale.paymentMethod === 3">
                                    <p class="text-black bold mt-2 text-lg">Cargo a la tarjeta</p>
                                    <span class="text-blue-500 bold text-lg">{{ formatCurrency(sale.card) }}</span>
                                </div>
                            </el-col>
                            <el-col :span="10" class="text-right">
                                <el-button type="danger" plain class="w-25"><X :size="20" /> Cancelar venta</el-button>
                                <el-button type="success" plain class="w-25" @click="registerSale"><Check :size="20" /> Registrar venta</el-button>
                            </el-col>
                        </el-row>
                    </el-col>
                </el-row>
            </el-card>
        </div>
    </AppLayout>
</template>

<style scoped>
    .my-card {
        min-height: 86vh;
    }
    .mw-20 {
        min-width: 20% !important;
    }
    .mw-30 {
        min-width: 30% !important;
    }
    .el-form-item {
        margin-bottom: 0px !important;
    }
    .relative {
        position: relative;
    }
    .absolute {
        position: absolute;
    }
</style>
