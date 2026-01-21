<script setup lang="js">
import apiClient from '@/apiClient';
import showNotification from '@/notification';
import { ref, defineExpose } from 'vue';

const { getParentStores } = defineProps({
    getParentStores: Function,
});

const dialogVisible = ref(false);
const title         = ref('');
const txtBtn        = ref('');
const loading       = ref(false);
const store         = ref({
    id: null,
    name: '',
    address: '',
    status: 1
});
const errors = ref({
    name: false
});

const showModal = (_store = null)=> {
    resetErrors();
    title.value  = _store ? 'Editar sucursal' : 'Registrar sucursal';
    txtBtn.value = _store ? 'Guardar cambios' : 'Guardar';
    store.value.id      = null;
    store.value.name    = '';
    store.value.address = '';
    store.value.status  = 1;
    if (_store) {
        store.value.id      = _store.id;
        store.value.name    = _store.name;
        store.value.address = _store.address;
        store.value.status  = _store.status;
    }
    dialogVisible.value = true;
};

const saveInfo = async ()=> {
    if (validate()) {
        const method = store.value.id ? 'PUT' : 'POST';
        loading.value = true;
        const response = await apiClient('admin/store', method, store.value);
        loading.value = false;
        if (response.error) {
            showNotification(response.msj, 'error');
            return
        }
        getParentStores();
        showNotification(response.msj);
        dialogVisible.value = false;
    }
}

const validate = ()=> {
    resetErrors();
    let valid = true;
    if (!store.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    return valid;
}

const resetErrors = ()=> {
    errors.value.name = false;
}

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        :title="title"
        width="500"
        style="margin-top: 5vh;"
    >
        <p class="text-black">Nombre <span class="text-red-500">*</span></p>
        <el-form-item
            :error="errors.name ? ' ' : ''"
            class="!mb-0"
        >
            <el-input v-model="store.name" clearable />
        </el-form-item>
        <p class="mb-3 text-red-400 text-sm" v-if="errors.name">El nombre de la sucursal es obligatorio.</p>
        <p class="text-black mt-3">Dirección</p>
        <el-mention
            v-model="store.address"
            type="textarea"
        />
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