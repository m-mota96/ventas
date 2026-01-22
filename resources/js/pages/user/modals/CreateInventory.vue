<script setup lang="js">
import apiClient from '@/apiClient';
import showNotification from '@/notification';
import { ref, defineExpose } from 'vue';

const { getParentInventory, products } = defineProps({
    getParentInventory: Function,
    products: Array,
    references: Array,
});

const dialogVisible = ref(false);
const title         = ref('');
const txtBtn        = ref('');
const loading       = ref(false);
const inventory     = ref({
    id: null,
    product_id: null,
    name: '',
    bar_code: '',
    type: '',
    reference: '',
    batch: '',
    expiration_date: '',
    quantity: 1,
    description: '',
    status: 1
});
const errors = ref({
    name: false,
    bar_code: false,
    type: false,
    reference: false,
    quantity: false,
});

const showModal = (_inventory = null)=> {
    resetErrors();
    title.value  = _inventory ? 'Editar inventario' : 'Registrar inventario';
    txtBtn.value = _inventory ? 'Guardar cambios' : 'Guardar';
    inventory.value.id              = null;
    inventory.value.product_id      = null;
    inventory.value.name            = '';
    inventory.value.bar_code        = '';
    inventory.value.type            = '';
    inventory.value.reference       = '';
    inventory.value.batch           = '';
    inventory.value.expiration_date = '';
    inventory.value.quantity        = 1;
    inventory.value.description     = '';
    inventory.value.status          = 1;
    dialogVisible.value = true;
};

const saveInfo = async ()=> {
    if (validate()) {
        const method = inventory.value.id ? 'PUT' : 'POST';
        loading.value = true;
        const response = await apiClient('user/inventory', method, inventory.value);
        loading.value = false;
        if (response.error) {
            showNotification(response.msj, 'error');
            return
        }
        getParentInventory();
        showNotification(response.msj);
        dialogVisible.value = false;
    }
}

const validate = ()=> {
    resetErrors();
    let valid = true;
    if (!inventory.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    if (!inventory.value.bar_code) {
        errors.value.bar_code = true;
        valid                 = false;
    }
    if (!inventory.value.type) {
        errors.value.type = true;
        valid             = false;
    }
    if (!inventory.value.reference) {
        errors.value.reference = true;
        valid                  = false;
    }
    if (!inventory.value.quantity) {
        errors.value.quantity = true;
        valid                 = false;
    }
    return valid;
}

const resetErrors = ()=> {
    errors.value.name      = false;
    errors.value.bar_code  = false;
    errors.value.type      = false;
    errors.value.reference = false;
    errors.value.quantity  = false;
}

const handleSelect = (_product)=> {
    inventory.value.product_id = _product.id;
    inventory.value.bar_code   = _product.bar_code;
};

const querySearch = (queryString, cb) => {
    if (queryString.length < 3) {
        cb([]);
        return;
    }

    const results = products
    .filter(createFilter(queryString))
    .map(product => ({
        ...product,
        value: `${product.name} ${product.content ? product.content : ''} ${product.abreviation ? product.abreviation : ''}`
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
                <p class="text-black">Código de barras <span class="text-red-500">*</span></p>
                <el-form-item
                    :error="errors.bar_code ? ' ' : ''"
                    class="!mb-0"
                >
                    <el-input v-model="inventory.bar_code" clearable placeholder="Escribe o escanea el código de barras para buscar" />
                </el-form-item>
                <p class="text-red-400 text-sm" v-if="errors.bar_code">El código de barras es obligatorio.</p>
            </el-col>
            <el-col :span="12" class="pt-3">
                <p class="text-black">Nombre del producto <span class="text-red-500">*</span></p>
                <el-form-item
                    :error="errors.name ? ' ' : ''"
                    class="!mb-0"
                >
                    <el-autocomplete
                        v-model="inventory.name"
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
                <p class="text-black">Tipo de movimiento <span class="text-red-500">*</span></p>
                <el-form-item
                    :error="errors.type ? ' ' : ''"
                    class="!mb-0"
                >
                    <el-select v-model="inventory.type" placeholder="Selecciona una opción">
                        <el-option
                            :key="0"
                            label="Ingreso"
                            value="input"
                        />
                        <el-option
                            :key="1"
                            label="Egreso"
                            value="output"
                        />
                    </el-select>
                </el-form-item>
                <p class="text-red-400 text-sm" v-if="errors.type">El tipo es obligatorio.</p>
            </el-col>
            <el-col :span="12" class="pt-3">
                <p class="text-black">Referencia <span class="text-red-500">*</span></p>
                <el-form-item
                    :error="errors.reference ? ' ' : ''"
                    class="!mb-0"
                >
                    <el-select v-model="inventory.reference" placeholder="Selecciona una opción">
                        <el-option
                            v-for="r in references"
                            :key="r.id"
                            :label="r.name"
                            :value="r.id"
                        />
                    </el-select>
                </el-form-item>
                <p class="text-red-400 text-sm" v-if="errors.reference">La referencia es obligatoria.</p>
            </el-col>
            <el-col :span="12" class="pt-3">
                <p class="text-black">Lote</p>
                <el-input v-model="inventory.batch" clearable />
            </el-col>
            <el-col :span="12" class="pt-3">
                <p class="text-black">Fecha de caducidad</p>
                <el-date-picker
                    style="width: 100%;"
                    v-model="inventory.expiration_date"
                    type="date"
                    placeholder="Selecciona la fecha"
                    clearable
                    format="DD/MM/YYYY"
                    value-format="YYYY-MM-DD"
                />
            </el-col>
            <el-col :span="12" class="pt-3">
                <p class="text-black">Cantidad <span class="text-red-500">*</span></p>
                <el-form-item
                    :error="errors.quantity ? ' ' : ''"
                    class="!mb-0"
                >
                    <el-input-number
                        class="w-100"
                        v-model="inventory.quantity"
                        :precision="3"
                        :step="0.001"
                        :min="0.001"
                        :controls="false"
                    />
                </el-form-item>
                <p class="text-red-400 text-sm" v-if="errors.quantity">La cantidad es obligatoria.</p>
            </el-col>
            <el-col :span="24" class="pt-3">
                <p class="text-black">Describe el movimiento</p>
                <el-mention
                    v-model="inventory.description"
                    type="textarea"
                    :rows="5"
                />
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