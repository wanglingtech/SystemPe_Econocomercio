<template>
    <div class="chart-line-container" ref="container">
        <canvas ref="canvas" style="position:absolute;top:0;left:0;"></canvas>
    </div>
</template>
<style>
    .chart-line-container {
        position: relative;
        height: 220px;
        width: 100%;
        overflow: hidden;
    }
</style>
<script>
    import Chart from 'chart.js';
    export default {
        props: ['allData'],
        data() {
            return {
                chart: null,
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    animation: { duration: 0 },
                    legend: { display: false },
                    elements: {
                        line: { tension: 0.4, borderWidth: 2, fill: true },
                        point: { radius: 0, hitRadius: 10 }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: { display: false, drawBorder: false },
                            ticks: { fontColor: '#94a3b8', fontSize: 11 }
                        }],
                        yAxes: [{
                            gridLines: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                            ticks: { fontColor: '#94a3b8', fontSize: 11, beginAtZero: true }
                        }]
                    }
                }
            }
        },
        watch: {
            allData() {
                this.$nextTick(() => { this.createChart(); });
            }
        },
        methods: {
            createChart() {
                if (this.chart) {
                    this.chart.destroy();
                    this.chart = null;
                }
                const container = this.$refs.container;
                const canvas = this.$refs.canvas;
                const w = container.offsetWidth;
                const h = 220;
                canvas.width = w;
                canvas.height = h;
                canvas.style.width = w + 'px';
                canvas.style.height = h + 'px';

                const colors = [
                    { border: 'rgba(244,63,94,1)', bg: 'rgba(244,63,94,0.15)' },
                    { border: 'rgba(66,133,244,1)', bg: 'rgba(66,133,244,0.15)' },
                    { border: 'rgba(124,58,237,1)', bg: 'rgba(124,58,237,0.10)' },
                ];
                const datasets = this.allData.datasets.map((ds, i) => {
                    const c = colors[i] || colors[0];
                    return {
                        ...ds,
                        borderColor: c.border,
                        backgroundColor: c.bg,
                        pointRadius: 0,
                        borderWidth: 2,
                        fill: true,
                        lineTension: 0.4,
                    };
                });
                this.chart = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: { labels: this.allData.labels, datasets },
                    options: this.options,
                });
            }
        },
        beforeDestroy() {
            if (this.chart) { this.chart.destroy(); }
        }
    }
</script>