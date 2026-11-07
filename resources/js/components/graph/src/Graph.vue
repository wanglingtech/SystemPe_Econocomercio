<template>
    <div class="chart-container" style="position:relative;">
        <canvas ref="canvas"></canvas>
        <div class="chart-center-text" v-if="type === 'doughnut' || type === 'pie'">
            <span class="chart-center-value">
                <small class="chart-currency">S/</small>{{ centerAmount }}
            </span>
            <span class="chart-center-label">Total</span>
        </div>
    </div>
</template>
<style>
.chart-container {
    position: relative;
    margin: auto;
    height: 200px;
    width: 200px;
    max-width: 200px;
    max-height: 200px;
}
.chart-center-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
    width: 100%;
}
.chart-center-value {
    display: block;
    font-size: 17px;
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    line-height: 1.2;
}
.chart-currency {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    margin-right: 2px;
    vertical-align: middle;
}
.chart-center-label {
    display: block;
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
    margin-top: 2px;
}
</style>
<script>
import Chart from 'chart.js';
export default {
    props: ['type', 'allData'],
    data() {
        return {
            chart: null,
            centerAmount: '0.00',
            options: {
                maintainAspectRatio: false,
                legend: { display: false },
                cutoutPercentage: 72,
            },
        }
    },
    watch: {
        allData() {
            this.createChart();
        }
    },
    methods: {
        formatCompact(total) {
            if (total >= 1000000) return (total / 1000000).toFixed(1) + 'M';
            if (total >= 1000) return (total / 1000).toFixed(1) + 'k';
            return total.toFixed(2);
        },
        createChart() {
            if (this.chart) this.chart.destroy();

            const colors = [
                'rgba(99, 102, 241, 0.85)',
                'rgba(251, 191, 36, 0.85)',
                'rgba(244, 63, 94, 0.85)',
                'rgba(66, 133, 244, 0.85)',
            ];

            const datasets = this.allData.datasets.map(ds => ({
                ...ds,
                backgroundColor: colors,
                borderWidth: 0,
                hoverOffset: 4,
            }));

            if ((this.type === 'doughnut' || this.type === 'pie') && datasets[0] && datasets[0].data) {
                const total = datasets[0].data.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
                this.centerAmount = this.formatCompact(total);
            }

            this.chart = new Chart(this.$refs.canvas.getContext('2d'), {
                type: this.type,
                data: {
                    labels: this.allData.labels,
                    datasets: datasets,
                },
                options: this.options,
            });
        }
    }
}
</script>