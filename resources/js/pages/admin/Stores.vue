<script setup lang="js">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import apiClient from '@/apiClient';
import { ref, onMounted } from 'vue';
import { Plus, Pen, Eye, Trash2 } from 'lucide-vue-next';
import CreateEditStore from './modals/CreateEditStore.vue';
import showNotification from '@/notification';

const breadcrumbs = [
    {
        title: 'Sucursales',
        href: dashboard().url,
    },
];

const createEditStoreRef = ref(null);
const stores = ref([]);

onMounted(() => {
    getStores();
});

const getStores = async ()=> {
    const response = await apiClient('admin/stores');
    stores.value = response.data;
};

const openModal = (store = null)=> {
    createEditStoreRef.value?.showModal(store);
};

const statusStore = async (store)=> {
    store.status = !store.status;
    const msj    = store.status ? 'activó' : 'desactivó';
    const response = await apiClient('admin/store', 'PUT', store);
    if (response.error) {
        showNotification(response.msj, 'error');
        return
    }
    getStores();
    showNotification(`La sucursal se ${msj} correctamente.`);
};

const deleteStore = async (id)=> {
    const response = await apiClient(`admin/store/${id}`, 'DELETE');
    if (response.error) {
        showNotification(response.msj, 'error');
        return
    }
    getStores();
    showNotification(response.msj);
};
</script>

<template>
    <Head title="Sucursales" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <el-card>
                <el-table :data="stores" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-black bold">
                    <el-table-column prop="id" label="#" width="50" align="center" />
                    <el-table-column prop="name" label="Sucursal" />
                    <el-table-column prop="address" label="Dirección" />
                    <el-table-column prop="status" label="Estatus" width="90" align="center">
                        <template #default="scope">
                            <span class="bold" :class="scope.row.status ? 'text-green-500' : 'text-red-500'">{{ scope.row.status ? 'Activa' : 'Inactiva' }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column width="180" align="center">
                        <template #header>
                        <el-tooltip content="Nueva sucursal" placement="top">
                            <el-button type="primary" class="!p-1" @click="openModal()">
                                <Plus size="20" />
                            </el-button>
                        </el-tooltip>
                    </template>
                    <template #default="scope">
                        <el-button-group>
                            <el-tooltip content="Editar sucursal" placement="top">
                                <el-button type="primary" class="!p-1" @click="openModal(scope.row)">
                                    <Pen size="20" />
                                </el-button>
                            </el-tooltip>
                            <el-tooltip :content="scope.row.status ? 'Desactivar sucursal' : 'Activar sucursal'" placement="top">
                                <el-button
                                    :type="scope.row.status ? 'warning' : 'success'"
                                    class="!p-1"
                                    @click="statusStore(scope.row)"
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
                                title="¿Seguro que deseas eliminar esta sucursal?"
                                placement="left"
                                @confirm="deleteStore(scope.row.id)"
                            >
                                <template #reference>
                                    <span>
                                        <el-tooltip content="Eliminar sucursal" placement="top">
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
            <CreateEditStore ref="createEditStoreRef" :get-parent-stores="getStores"/>
        </div>
    </AppLayout>
</template>
