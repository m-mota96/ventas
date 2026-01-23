<script setup lang="js">
import { ref, defineExpose } from 'vue';

const dialogVisible = ref(false);
const inventories   = ref([]);

const showModal = (_inventories)=> {
    inventories.value   = _inventories;
    dialogVisible.value = true;
};

const parseQuantity = (type_sale, quantity)=> {
    return type_sale === 'pza' ? parseInt(quantity) : quantity;
};

const formatCurrency = (value)=> {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

defineExpose({
    showModal
})
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        title="Detalles de venta"
        width="1300"
        style="margin-top: 5vh;"
    >
        <el-table :data="inventories" stripe header-cell-class-name="text-black bold">
            <el-table-column label="#" width="80" align="center">
                <template #default="scope">
                    {{ scope.$index + 1 }}
                </template>
            </el-table-column>
            <el-table-column label="Producto">
                <template #default="scope">
                    {{ scope.row.product.name }} 
                    {{ scope.row.product.content ? scope.row.product.content : '' }}
                    {{ scope.row.product.abreviation ? scope.row.product.abreviation : '' }}
                </template>
            </el-table-column>
            <el-table-column label="Precio" width="180" align="center">
                <template #default="scope">
                    {{ formatCurrency(scope.row.price) }}
                </template>
            </el-table-column>
            <el-table-column label="Cantidad" width="180" align="center">
                <template #default="scope">
                    {{ parseQuantity(scope.row.product.type_sale, scope.row.quantity) }}
                </template>
            </el-table-column>
            <el-table-column label="Importe" width="180" align="center">
                <template #default="scope">
                    {{ formatCurrency(scope.row.price * scope.row.quantity) }}
                </template>
            </el-table-column>
        </el-table>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cerrar</el-button>
            </div>
        </template>
    </el-dialog>
</template>