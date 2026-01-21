<script setup lang="js">
import apiClient from '@/apiClient';
import showNotification from '@/notification';
import { ref, defineExpose } from 'vue';

const { getParentProducts, allProducts } = defineProps({
    getParentProducts: Function,
    allProducts: Array
});

const dialogVisible = ref(false);
const title         = ref('');
const txtBtn        = ref('');
const loading       = ref(false);
const product       = ref({
    id: null,
    newProduct: true,
    name: '',
    bar_code: '',
    content: '',
    abreviation: '',
    description: '',
    price: 0,
    batch: '',
    expiration_date: '',
    discount: '',
    status: 1
});
const errors = ref({
    name: false,
    bar_code: false,
    price: false
});

const showModal = (_product = null)=> {
    resetErrors();
    title.value  = _product ? 'Editar producto' : 'Registrar producto';
    txtBtn.value = _product ? 'Guardar cambios' : 'Guardar';
    product.value.id              = null;
    product.value.newProduct      = true;
    product.value.name            = '';
    product.value.bar_code        = '';
    product.value.content         = '';
    product.value.abreviation     = '';
    product.value.description     = '';
    product.value.price           = 0;
    product.value.batch           = '';
    product.value.expiration_date = '';
    product.value.discount        = '';
    product.value.status          = 1;
    if (_product) {
        product.value.id              = _product.id;
        product.value.newProduct      = false;
        product.value.name            = _product.name;
        product.value.bar_code        = _product.bar_code;
        product.value.content         = _product.content;
        product.value.abreviation     = _product.abreviation;
        product.value.description     = _product.description;
        product.value.price           = _product.product_store.price;
        product.value.batch           = _product.product_store.batch;
        product.value.expiration_date = _product.product_store.expiration_date;
        product.value.discount        = _product.product_store.discount;
        product.value.status          = _product.product_store.status;
    }
    dialogVisible.value = true;
};

const saveInfo = async ()=> {
    if (validate()) {
        const method = (product.value.id || !product.value.newProduct) ? 'PUT' : 'POST';
        loading.value = true;
        const response = await apiClient('user/product', method, product.value);
        loading.value = false;
        if (response.error) {
            showNotification(response.msj, 'error');
            return
        }
        getParentProducts();
        showNotification(response.msj);
        dialogVisible.value = false;
    }
}

const validate = ()=> {
    resetErrors();
    let valid = true;
    if (!product.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    if (!product.value.bar_code) {
        errors.value.bar_code = true;
        valid                 = false;
    }
    if (!product.value.price) {
        errors.value.price = true;
        valid              = false;
    }
    return valid;
}

const resetErrors = ()=> {
    errors.value.name     = false;
    errors.value.bar_code = false;
    errors.value.price    = false;
}

const handleSelect = (_product)=> {
    console.log(_product);
    product.value.id          = _product.id;
    product.value.name        = _product.name;
    product.value.bar_code    = _product.bar_code;
    product.value.price       = parseFloat(_product.product_store.price);
    product.value.content     = _product.content;
    product.value.abreviation = _product.abreviation;
    product.value.discount    = _product.discount;
    product.value.description = _product.description;
};

const querySearch = (queryString, cb) => {
    if (queryString.length < 3) {
        cb([]);
        return;
    }

    const results = allProducts
    .filter(createFilter(queryString))
    .map(product => ({
        ...product,
        value: `${product.name} ${product.content} ${product.abreviation}`
    }));

    cb(results);
};

const createFilter = (queryString) => {
    const search = queryString.toLowerCase();

    return (product) => {
        return product.name.toLowerCase().includes(search);
    };
};

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        :title="title"
        width="800"
        style="margin-top: 5vh;"
    >
        <el-row :gutter="20">
            <el-col :span="12" class="pt-3">
                <p class="text-black">Código de barras/Clave <span class="text-red-500">*</span></p>
                <el-form-item
                    :error="errors.bar_code ? ' ' : ''"
                    class="!mb-0"
                >
                    <el-input v-model="product.bar_code" clearable placeholder="Escanea el código de barras para buscar" />
                </el-form-item>
                <p class="text-red-400 text-sm" v-if="errors.bar_code">El código de barras es obligatorio.</p>
            </el-col>
            <el-col :span="12" class="pt-3">
                <p class="text-black">Nombre del producto <span class="text-red-500">*</span></p>
                <el-form-item
                    :error="errors.name ? ' ' : ''"
                    class="!mb-0"
                >
                    <!-- <el-input v-model="product.name" placeholder="Ej. Vaso desechable número 10" name="name_product" autocomplete="name_product" clearable /> -->
                    <el-autocomplete
                        v-model="product.name"
                        :fetch-suggestions="querySearch"
                        :trigger-on-focus="false"
                        clearable
                        placeholder="Escribe el nombre del producto para buscar"
                        @select="handleSelect"
                    />
                </el-form-item>
                <p class="text-red-400 text-sm" v-if="errors.name">El nombre del producto es obligatorio.</p>
            </el-col>
            <el-col :span="12" class="pt-3">
                <p class="text-black">Precio unitario <span class="text-red-500">*</span></p>
                <el-form-item
                    :error="errors.price ? ' ' : ''"
                    class="!mb-0"
                >
                    <el-input-number
                        v-model="product.price"
                        :precision="2"
                        :step="0.01"
                        :min="0"
                        :controls="false"
                        style="width: 100%;"
                    >
                        <template #prefix>$</template>
                    </el-input-number>
                </el-form-item>
                <p class="text-red-400 text-sm" v-if="errors.price">El precio es obligatorio.</p>
            </el-col>
            <el-col :span="12" class="pt-3">
                <p class="text-black">Contenido</p>
                <!-- <el-input v-model="product.capacity" placeholder="Ej. VDES10" clearable /> -->
                <el-input
                    v-model="product.content"
                    class="input-with-select"
                >
                    <template #append>
                        <el-select v-model="product.abreviation" placeholder="Elige una opción" style="width: 150px" clearable>
                        <el-option label="Kg (kilogramos)" value="Kg." />
                        <el-option label="gr (gramos)" value="gr." />
                        <el-option label="L (litros)" value="L." />
                        <el-option label="ml (mililitros)" value="ml." />
                        <el-option label="pzas (piezas)" value="pzas." />
                        </el-select>
                    </template>
                </el-input>
            </el-col>
            <el-col :span="12" class="pt-3">
                <p class="text-black">Descuento</p>
                <el-input v-model="product.discount" clearable />
            </el-col>
            <el-col :span="24" class="pt-3">
                <p class="text-black">Descripción del producto</p>
                <el-mention
                    v-model="product.description"
                    type="textarea"
                    :rows="5"
                />
            </el-col>
            <el-col :span="25" class="pt-4 pb-4">
                <p class="text-black text-base">
                    <b class="text-red-500">NOTA:</b>
                    Si el producto no cuenta con código de barras ingresa una clave única que 
                    no exista en tus registros y que puedas identificar o recordar fácilmente.
                </p>
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
            <el-button @click="dialogVisible = false">Cancelar</el-button>
            <el-button type="primary" @click="saveInfo()" :loading="loading">
                {{ txtBtn }}
            </el-button>
            </div>
        </template>
    </el-dialog>
</template>