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
        formatter: function() {
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
                formatter: function() {
                    return this.y ? formatCurrency(this.y) : '';
                }
            }
        }
    },
    credits: {
        enabled: false
    },
    accessibility: {
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
        formatter: function() {
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
                formatter: function() {
                    return this.y ? formatCurrency(this.y) : '';
                }
            }
        }
    },
    credits: {
        enabled: false
    },
    accessibility: {
        enabled: false
    },
    series: []
});

const chartPie = ref({
    chart: {
        type: 'pie',
        zooming: {
            type: 'xy'
        },
        panning: {
            enabled: true,
            type: 'xy'
        },
        panKey: 'shift'
    },
    title: {
        text: ''
    },
    tooltip: {
        formatter: function() {
            const value = this.point.realValue ?? this.y;

            const sign = value < 0 ? '-' : '';
            
            return `
            <span style="font-size:10px">${this.point.key}</span><br>
            <b>${sign}${this.point.y.toLocaleString('es-MX', {
                style: 'currency',
                currency: 'MXN'
            })}</b>
            `;
        },
        shared: true,
        useHTML: true
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: [{
                enabled: true,
                distance: 20,
                // formatter: function() {
                //     return this.point.y ? formatCurrency(this.point.y) : '';
                // }
            }, {
                enabled: true,
                distance: -50,
                formatter: function () {
                    const value = this.point.realValue ?? this.y;

                    const formatted = new Intl.NumberFormat('es-MX', {
                        style: 'currency',
                        currency: 'MXN'
                    }).format(Math.abs(value));

                    return value < 0 ? `-${formatted}` : formatted;
                },
                style: {
                    fontSize: '1.2em',
                    textOutline: 'none',
                    opacity: 0.7
                },
                filter: {
                    operator: '>',
                    property: 'y',
                    value: 10
                }
            }]
        }
    },
    credits: {
        enabled: false
    },
    accessibility: {
        enabled: false
    },
    series: [
        {
            name: 'Monto',
            colorByPoint: true,
            data: []
        }
    ]
});

const currentDate  = ref(
    new Date().toLocaleDateString('en-CA')
);
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
const search = ref({
    year: new Date().getFullYear(),
    month: new Date().getMonth() + 1,
    currentYear: new Date().getFullYear()
});
const income    = ref(0);
const expenses  = ref(0);
const profits   = ref(0);
const salesCard = ref(0);
const salesCash = ref(0);

onMounted(() => {
    getStatistics();
});

const getStatistics = async (type = 'all')=> {
    if (type === 'month' || type === 'all') {
        income.value               = 0;
        expenses.value             = 0;
        profits.value              = 0;
        chartOptions.value.series  = [];
    }
    if (type === 'year' || type === 'all') {
        chartYear.value.series        = [];
        chartPie.value.series[0].data = [];
    }
    const response = await apiClient('user/statistics', 'GET', {
        year: search.value.year,
        month: search.value.month,
        currentYear: search.value.currentYear
    });
    if (type === 'month' || type === 'all') {
        chart(response.data.sales, response.data.arrayExpenses);
        animateValue(income, response.data.totalSales, 2000);
        animateValue(expenses, response.data.expenses, 2000);
        animateValue(profits, (response.data.totalSales - response.data.expenses), 2000);
    }
    if (type === 'year' || type === 'all') {
        setChartYear(response.data.salesYear);
        setChartPie(response.data.salesForYear, response.data.expensesForYear);
    }
    salesCash.value = response.data.salesCash;
    salesCard.value = response.data.salesCard;
};

const chart = (sales, expenses)=> {
    const dates = getDaysInMonth(search.value.month, search.value.year);

    chartOptions.value.xAxis.categories = dates;
    chartOptions.value.series.push({
        name: 'Ingresos',
        data: Object.values(sales),
        colorByPoint: false,
        color: '#00c951'
    });
    chartOptions.value.series.push({
        name: 'Egresos',
        data: Object.values(expenses),
        colorByPoint: false,
        color: '#ff6900'
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

const setChartPie = (sales, expenses)=> {
    const balance = sales - expenses;
    const color   = balance < 0 ? '#fb2c36' : '#2b7fff';
    chartPie.value.series[0].data = [
        { name: 'Ventas', y: sales, color: '#00c951' },
        { name: 'Gastos', y: expenses, color: '#ff6900' },
        { name: 'Balance', y: Math.abs(balance), realValue: balance, color, sliced: true, selected: true }
    ];
};

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
                                <p class="text-gray-700 text-3xl bold">{{ formatCurrency(salesCash) }}</p>
                            </el-col>
                            <el-col :span="8" class="text-center">
                                <p class="text-balck mb-3">Ventas con tarjeta</p>
                                <p class="text-gray-700 text-3xl bold">{{ formatCurrency(salesCard) }}</p>
                            </el-col>
                            <el-col :span="8" class="text-center">
                                <p class="text-balck mb-3">Total vendido</p>
                                <p class="text-gray-700 text-3xl bold">{{ formatCurrency((salesCash + salesCard)) }}</p>
                            </el-col>
                        </el-row>
                    </el-card>
                </el-col>
                <el-col :span="18">
                    <el-card class="my-card">
                        <!-- <span class="text-xl bold text-black"> -->
                            <el-form-item>
                                <template #label>
                                    <span class="text-xl bold text-black">Resumen mensual</span>
                                </template>
                                <el-select v-model="search.month" class="w-20" @change="getStatistics('month')">
                                    <el-option v-for="m in months" :key="m.value" :value="m.value" :label="m.label" />
                                </el-select>
                                <el-select v-model="search.year" class="w-15 ml-3" @change="getStatistics('month')">
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
                                <p class="text-orange-500 bold text-3xl mb-3">{{ formatCurrency(expenses) }}</p>
                                <p class="text-black">{{ months[search.month - 1].label }} de {{ search.year }}</p>
                                <ArrowBigDown class="inline-block pa text-orange-500" :size="45" style="top: 60px; right: 25px;" />
                            </el-card>
                        </el-col>
                        <el-col :span="24" class="mb-3">
                            <el-card class="my-card height-card text-center pr">
                                <p class="text-black bold text-xl mb-4">Balance</p>
                                <p
                                    :class="(profits < 0) ? 'text-red-500' : 'text-blue-500'"
                                    class="bold text-3xl mb-3"
                                >
                                    {{ formatCurrency(profits) }}
                                </p>
                                <p class="text-black">{{ months[search.month - 1].label }} de {{ search.year }}</p>
                                <DollarSignIcon
                                    :class="(profits < 0) ? 'text-red-500' : 'text-blue-500'"
                                    class="inline-block pa"
                                    :size="35"
                                    style="top: 60px; right: 25px;"
                                />
                            </el-card>
                        </el-col>
                    </el-row>
                </el-col>
                <el-col :span="16" class="mt-5">
                    <el-card class="my-card">
                        <!-- <span class="text-xl bold text-black"> -->
                            <el-form-item>
                                <template #label>
                                    <span class="text-xl bold text-black">Resumen anual</span>
                                </template>
                                <el-select v-model="search.currentYear" class="w-15 ml-3" @change="getStatistics('year')">
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
                <el-col :span="8" class="mt-5">
                    <el-card class="my-card pt-9" style="height: 525px;">
                        <highcharts :options="chartPie" />
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