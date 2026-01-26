<script setup lang="js">
import apiClient from '@/apiClient';
import AppLayout from '@/layouts/AppLayout.vue';
import showNotification from '@/notification';
import { dashboard } from '@/routes';
// import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { DollarSign, ArrowBigUp, ArrowBigDown, DollarSignIcon } from 'lucide-vue-next';
import { ref, onMounted, computed, watch } from 'vue';
import { Chart as highcharts } from 'highcharts-vue';
import { dateEs } from '@/dateEs';

const page = usePage();
const user = computed(() => page.props.auth.user);

const breadcrumbs = [
    {
        title: `Estadísticas - Sucursal ${user.value.store.name}`,
        href: dashboard().url,
    },
];

const chartOptions = ref({
    chart: {
        type: 'column',
    },
    title: {
        text: ''
    },
    xAxis: {
        type: 'date',
        categories: '',
        title: {
            text: 'Día del mes'
        },
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Monto vendido'
        },
    },
    tooltip: {
        formatter: function () {
            return `
            <span style="font-size:10px">${this.point.key}</span><br>
            <b>${this.point.y.toLocaleString('es-MX', {
                style: 'currency',
                currency: 'MXN'
            })}</b>
            `;
        },
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        },
        series: {
            dataLabels: {
                enabled: true,
                formatter: function () {
                    return this.y ? formatCurrency(this.y) : '';
                }
            }
        }
    },
    credits: {
        enabled: false
    },
    series: []
});

const chartYear = ref({
    chart: {
        type: 'column',
    },
    title: {
        text: ''
    },
    xAxis: {
        type: 'date',
        categories: '',
        title: {
            text: 'Mes'
        },
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Monto vendido'
        },
    },
    tooltip: {
        formatter: function () {
            return `
            <span style="font-size:10px">${this.point.key}</span><br>
            <b>${this.point.y.toLocaleString('es-MX', {
                style: 'currency',
                currency: 'MXN'
            })}</b>
            `;
        },
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        },
        series: {
            dataLabels: {
                enabled: true,
                formatter: function () {
                    return this.y ? formatCurrency(this.y) : '';
                }
            }
        }
    },
    credits: {
        enabled: false
    },
    series: []
});
const currentDate  = ref(new Date().toISOString().slice(0, 10));
const currentYear  = ref(new Date().getFullYear());
const currentMonth = ref(new Date().getMonth() + 1);
const years        = ref([new Date().getFullYear() - 1, new Date().getFullYear()]);
const months       = ref([
    { value: 1, label: 'Enero' },
    { value: 2, label: 'Febrero' },
    { value: 3, label: 'Marzo' },
    { value: 4, label: 'Abril' },
    { value: 5, label: 'Mayo' },
    { value: 6, label: 'Junio' },
    { value: 7, label: 'Julio' },
    { value: 8, label: 'Agosto' },
    { value: 9, label: 'Septiembre' },
    { value: 10, label: 'Octubre' },
    { value: 11, label: 'Noviembre' },
    { value: 12, label: 'Diciembre' },
]);
const search       = ref({
    year: new Date().getFullYear(),
    month: new Date().getMonth() + 1,
    currentYear: new Date().getFullYear()
});
const income        = ref(0);
const expenses      = ref(0);
const profits       = ref(0);

onMounted(() => {
    getStatistics();
});

const getStatistics = async ()=> {
    income.value   = 0;
    expenses.value = 0;
    profits.value  = 0;
    const response = await apiClient('user/statistics', 'GET', {
        year: search.value.year,
        month: search.value.month,
        currentYear: search.value.currentYear
    });
    chart(response.data.sales);
    setChartYear(response.data.salesYear);
    animateValue(income, response.data.totalSales, 2000);
    animateValue(expenses, response.data.expenses, 2000);
    animateValue(profits, (response.data.totalSales - response.data.expenses), 2000);
};

const chart = (sales)=> {
    const dates = getDaysInMonth(currentMonth.value, currentYear.value);

    chartOptions.value.xAxis.categories = dates;
    chartOptions.value.series = [];
    chartOptions.value.series.push({
        name: 'Ingresos',
        data: Object.values(sales),
        colorByPoint: false,
        color: '#00c951'
    });
}

const setChartYear = (sales)=> {
    const dates = [];

    months.value.forEach(m => {
        dates.push(`${m.value < 10 ? '0'+m.value : m.value}/${search.value.currentYear}`);
    });

    chartYear.value.xAxis.categories = dates;
    chartYear.value.series = [];
    chartYear.value.series.push({
        name: 'Ingresos',
        data: Object.values(sales),
        colorByPoint: false,
        color: '#00c951'
    });
}

const getDaysInMonth = (month, year)=> {
    const days = [];
    const totalDays = new Date(year, month, 0).getDate();

    for (let day = 1; day <= totalDays; day++) {
        const dd   = String(day).padStart(2, '0');
        const mm   = String(month).padStart(2, '0');
        const yyyy = String(year);
        days.push(`${dd}/${mm}/${yyyy}`);
    }

    return days;
};

const formatCurrency = (value)=> {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const animateValue = (refValue, end, duration = 1000) => {
    const start = refValue.value;
    const startTime = performance.now();

    const step = (now) => {
    const progress = Math.min((now - startTime) / duration, 1);
    refValue.value = Number(
        (start + (end - start) * progress).toFixed(2)
    );

    if (progress < 1) {
        requestAnimationFrame(step);
    }
    };

    requestAnimationFrame(step);
};
</script>

<template>
    <Head title="Estadísticas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <el-row :gutter="20">
                <el-col :span="24" class="mb-5">
                    <el-card class="my-card">
                        <el-row>
                            <el-col :span="24" class="text-center mb-5">
                                <p class="text-black bold text-2xl">{{ dateEs(currentDate, ' de ', 0, true) }}</p>
                            </el-col>
                            <el-col :span="8" class="text-center">
                                <p class="text-black mb-3">Ventas en efectivo</p>
                                <p class="text-gray-700 text-3xl bold">$502.50</p>
                            </el-col>
                            <el-col :span="8" class="text-center">
                                <p class="text-balck mb-3">Ventas con tarjeta</p>
                                <p class="text-gray-700 text-3xl bold">$0.00</p>
                            </el-col>
                            <el-col :span="8" class="text-center">
                                <p class="text-balck mb-3">Total vendido</p>
                                <p class="text-gray-700 text-3xl bold">$0.00</p>
                            </el-col>
                        </el-row>
                    </el-card>
                </el-col>
                <el-col :span="18">
                    <el-card class="my-card">
                        <!-- <span class="text-xl bold text-black"> -->
                            <el-form-item>
                                <template #label>
                                    <span class="text-xl bold text-black">Ventas mensuales</span>
                                </template>
                                <el-select v-model="search.month" class="w-20" @change="getStatistics">
                                    <el-option v-for="m in months" :key="m.value" :value="m.value" :label="m.label" />
                                </el-select>
                                <el-select v-model="search.year" class="w-10 ml-3" @change="getStatistics">
                                    <el-option v-for="y in years" :key="y" :value="y" :label="y" />
                                </el-select>
                            </el-form-item>
                        <!-- </span> -->
                        <el-divider />
                        <highcharts
                            :options="chartOptions"
                        />
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-row>
                        <el-col :span="24" class="mb-3">
                            <el-card class="my-card height-card text-center pr">
                                <p class="text-black bold text-xl mb-4">Ingresos</p>
                                <p class="text-green-500 bold text-3xl mb-3">{{ formatCurrency(income) }}</p>
                                <p class="text-black">{{ months[search.month - 1].label }} de {{ search.year }}</p>
                                <ArrowBigUp class="inline-block pa text-green-500" :size="45" style="top: 60px; right: 25px;" />
                            </el-card>
                        </el-col>
                        <el-col :span="24" class="mb-3">
                            <el-card class="my-card height-card text-center pr">
                                <p class="text-black bold text-xl mb-4">Egresos</p>
                                <p class="text-red-500 bold text-3xl mb-3">{{ formatCurrency(expenses) }}</p>
                                <p class="text-black">{{ months[search.month - 1].label }} de {{ search.year }}</p>
                                <ArrowBigDown class="inline-block pa text-red-500" :size="45" style="top: 60px; right: 25px;" />
                            </el-card>
                        </el-col>
                        <el-col :span="24" class="mb-3">
                            <el-card class="my-card height-card text-center pr">
                                <p class="text-black bold text-xl mb-4">Balance</p>
                                <p class="text-blue-500 bold text-3xl mb-3">{{ formatCurrency(profits) }}</p>
                                <p class="text-black">{{ months[search.month - 1].label }} de {{ search.year }}</p>
                                <DollarSignIcon class="inline-block pa text-blue-500" :size="35" style="top: 60px; right: 25px;" />
                            </el-card>
                        </el-col>
                    </el-row>
                </el-col>
                <el-col :span="16" class="mt-5">
                    <el-card class="my-card">
                        <!-- <span class="text-xl bold text-black"> -->
                            <el-form-item>
                                <template #label>
                                    <span class="text-xl bold text-black">Ventas anuales</span>
                                </template>
                                <el-select v-model="search.currentYear" class="w-10 ml-3" @change="getStatistics">
                                    <el-option v-for="y in years" :key="y" :value="y" :label="y" />
                                </el-select>
                            </el-form-item>
                        <!-- </span> -->
                        <el-divider />
                        <highcharts
                            :options="chartYear"
                        />
                    </el-card>
                </el-col>
            </el-row>
        </div>
    </AppLayout>
</template>

<style scoped>
.my-card {
    border-width: 5px 1px 1px;
    border-style: solid;
    border-color: rgb(6, 120, 183);
    border-image: initial;
    border-radius: 1rem;
}

.height-card {
    height: 168px;
}
</style>