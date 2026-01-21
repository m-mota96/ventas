<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import apiClient from '@/apiClient';
import { ref, onMounted } from 'vue';
import { Plus, Pen, Eye, Trash2 } from 'lucide-vue-next';
import CreateUser from './modals/CreateUser.vue';
import showNotification from '@/notification';

const breadcrumbs = [
    {
        title: 'Usuarios',
        href: dashboard().url,
    },
];

const { stores } = defineProps({
    stores: {
        type: Array,
        required: true
    }
});

const createUserRef = ref(null);
const users = ref([]);

onMounted(() => {
    getUsers();
});

const getUsers = async ()=> {
    const response = await apiClient('admin/users');
    users.value = response.data;
};

const openModal = (store = null)=> {
    createUserRef.value?.showModal(store);
};

const statusUser = async (user)=> {
    console.log(user);
    user.status    = !user.status;
    const msj      = user.status ? 'activó' : 'desactivó';
    const response = await apiClient('admin/user', 'PUT', user);
    if (response.error) {
        showNotification(response.msj, 'error');
        return
    }
    getUsers();
    showNotification(`El usuario se ${msj} correctamente.`);
};

const deleteUser = async (id)=> {
    const response = await apiClient(`admin/user/${id}`, 'DELETE');
    if (response.error) {
        showNotification(response.msj, 'error');
        return
    }
    getUsers();
    showNotification(response.msj);
};
</script>

<template>
    <Head title="Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <el-card>
                <el-table :data="users" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-black bold">
                    <el-table-column prop="id" label="#" width="50" align="center" />
                    <el-table-column prop="store.name" label="Sucursal" />
                    <el-table-column prop="name" label="Nombre" />
                    <el-table-column prop="email" label="Correo electrónico" />
                    <el-table-column prop="status" label="Estatus" width="90" align="center">
                        <template #default="scope">
                            <span class="bold" :class="scope.row.status ? 'text-green-500' : 'text-red-500'">{{ scope.row.status ? 'Activo' : 'Inactivo' }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column width="180" align="center">
                        <template #header>
                        <el-tooltip content="Nuevo usuario" placement="top">
                            <el-button type="primary" class="!p-1" @click="openModal()">
                                <Plus size="20" />
                            </el-button>
                        </el-tooltip>
                    </template>
                    <template #default="scope">
                        <el-button-group>
                            <el-tooltip content="Editar usuario" placement="top">
                                <el-button type="primary" class="!p-1" @click="openModal(scope.row)">
                                    <Pen size="20" />
                                </el-button>
                            </el-tooltip>
                            <el-tooltip :content="scope.row.status ? 'Desactivar usuario' : 'Activar usuario'" placement="top">
                                <el-button
                                    :type="scope.row.status ? 'warning' : 'success'"
                                    class="!p-1"
                                    @click="statusUser(scope.row)"
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
                                title="¿Seguro que deseas eliminar este usuario?"
                                placement="left"
                                @confirm="deleteUser(scope.row.id)"
                            >
                                <template #reference>
                                    <span>
                                        <el-tooltip content="Eliminar usuario" placement="top">
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
            <CreateUser ref="createUserRef" :get-parent-users="getUsers" :stores="stores"/>
        </div>
    </AppLayout>
</template>
