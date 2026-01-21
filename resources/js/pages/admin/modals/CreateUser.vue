<script setup lang="js">
import apiClient from '@/apiClient';
import showNotification from '@/notification';
import { ref, defineExpose } from 'vue';

const { getParentUsers, stores } = defineProps({
    getParentUsers: Function,
    stores: Array
});

const dialogVisible  = ref(false);
const title          = ref('');
const txtBtn         = ref('');
const loading        = ref(false);
const disabledInputs = ref(false);
const passwordReq    = ref(true);
const user           = ref({
    id: null,
    store_id: '',
    name: '',
    email: '',
    password: '',
    password_confirm: '',
    status: 1
});
const errors = ref({
    store: false,
    name: false,
    email: false,
    email_invalid: false,
    password: false,
    password_confirm: false,
    passwords: false,
});

const showModal = (_user = null)=> {
    resetErrors();
    title.value                 = _user ? 'Editar usuario' : 'Registrar usuario';
    txtBtn.value                = _user ? 'Guardar cambios' : 'Guardar';
    user.value.id               = null;
    user.value.store_id         = '';
    user.value.name             = '';
    user.value.email            = '';
    user.value.password         = '';
    user.value.password_confirm = '';
    user.value.status           = 1;
    disabledInputs.value        = false;
    passwordReq.value           = true;
    if (_user) {
        user.value.id               = _user.id;
        user.value.name             = _user.name;
        user.value.store_id         = _user.store_id;
        user.value.email            = _user.email;
        user.value.password         = _user.password;
        user.value.password_confirm = _user.password_confirm;
        user.value.status           = _user.status;
        disabledInputs.value        = true;
        passwordReq.value           = false;
    }
    dialogVisible.value = true;
};

const saveInfo = async ()=> {
    if (validate()) {
        const method = user.value.id ? 'PUT' : 'POST';
        loading.value = true;
        const response = await apiClient('admin/user', method, user.value);
        loading.value = false;
        if (response.error) {
            showNotification(response.msj , 'error');
            return
        }
        getParentUsers();
        showNotification(response.msj);
        dialogVisible.value = false;
    }
}

const validate = ()=> {
    resetErrors();
    let valid       = true;
    const mailRegex =  /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;
    if (!user.value.store_id) {
        errors.value.store = true;
        valid              = false;
    }
    if (!user.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    if (!user.value.email) {
        errors.value.email = true;
        valid             = false;
    }
    if (user.value.email) {
        if (!mailRegex.test(user.value.email)) {
            errors.value.email_invalid = true;
            valid                      = false;
        }
    }
    if (!user.value.password && passwordReq.value) {
        errors.value.password = true;
        valid             = false;
    }
    if (!user.value.password_confirm && passwordReq.value) {
        errors.value.password_confirm = true;
        valid             = false;
    }
    if (user.value.password && user.value.password_confirm) {
        if (user.value.password !== user.value.password_confirm) {
            errors.value.passwords = true;
            valid                  = false;
        }
    }
    return valid;
}

const resetErrors = ()=> {
    errors.value.store            = false;
    errors.value.name             = false;
    errors.value.email            = false;
    errors.value.email_invalid    = false;
    errors.value.password         = false;
    errors.value.password_confirm = false;
    errors.value.passwords        = false;
};

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
        <p class="text-black">Sucursal <span class="text-red-500">*</span></p>
        <el-form-item
            :error="errors.store ? ' ' : ''"
            class="!mb-0"
        >
            <el-select v-model="user.store_id" placeholder="Selecciona una sucursal" :disabled="disabledInputs">
                <el-option
                    v-for="item in stores"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                />
            </el-select>
        </el-form-item>
        <p class="mb-3 text-red-400 text-sm" v-if="errors.store">Selecciona un elemento de la lista.</p>
        <p class="text-black mt-3">Nombre <span class="text-red-500">*</span></p>
        <el-form-item
            :error="errors.name ? ' ' : ''"
            class="!mb-0"
        >
            <el-input
                v-model="user.name"
                name="name"
                autocomplete="name"
                clearable
            />
        </el-form-item>
        <p class="mb-3 text-red-400 text-sm" v-if="errors.name">El nombre del usuario es obligatorio.</p>
        <p class="text-black mt-3">Correo electrónico <span class="text-red-500">*</span></p>
        <el-form-item
            :error="errors.email || errors.email_invalid ? ' ' : ''"
            class="!mb-0"
        >
            <el-input
                v-model="user.email"
                name="email"
                autocomplete="email"
                clearable
                :disabled="disabledInputs"
            />
        </el-form-item>
        <p class="mb-3 text-red-400 text-sm" v-if="errors.email">El correo es obligatorio.</p>
        <p class="mb-3 text-red-400 text-sm" v-if="errors.email_invalid">Ingresa un correo válido.</p>
        <p class="text-black mt-3">Contraseña <span class="text-red-500" v-if="passwordReq">*</span></p>
        <el-form-item
            :error="errors.password || errors.passwords ? ' ' : ''"
            class="!mb-0"
        >
            <el-input
                type="password"
                v-model="user.password"
            />
        </el-form-item>
        <p class="mb-3 text-red-400 text-sm" v-if="errors.password">La contraseña es obligatoria.</p>
        <p class="mb-3 text-red-400 text-sm" v-if="errors.passwords">Las contraseñas no coinciden.</p>
        <p class="text-black mt-3">Confirmar contraseña <span class="text-red-500" v-if="passwordReq">*</span></p>
        <el-form-item
            :error="errors.password_confirm || errors.passwords ? ' ' : ''"
            class="!mb-0"
        >
            <el-input
                type="password"
                v-model="user.password_confirm"
            />
        </el-form-item>
        <p class="mb-3 text-red-400 text-sm" v-if="errors.password_confirm">Confirma la contraseña.</p>
        <p class="mb-3 text-red-400 text-sm" v-if="errors.passwords">Las contraseñas no coinciden.</p>
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