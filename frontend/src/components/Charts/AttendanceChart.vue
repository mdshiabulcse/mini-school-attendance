<!-- src/components/Charts/AttendanceChart.vue -->
<template>
  <div class="attendance-chart-container">
    <canvas ref="chartCanvas" :height="height"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  chartData: {
    type: Array,
    default: () => []
  },
  height: {
    type: Number,
    default: 300
  }
})

const chartCanvas = ref(null)
let chartInstance = null

const createChart = () => {
  if (chartInstance) {
    chartInstance.destroy()
  }

  if (!chartCanvas.value || !props.chartData.length) return

  const ctx = chartCanvas.value.getContext('2d')

  const labels = props.chartData.map(item => {
    const date = new Date(item.date)
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
  })

  const datasets = [
    {
      label: 'Present',
      data: props.chartData.map(item => item.present),
      borderColor: '#4CAF50',
      backgroundColor: 'rgba(76, 175, 80, 0.1)',
      borderWidth: 2,
      tension: 0.4,
      fill: true,
      pointBackgroundColor: '#4CAF50',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6
    },
    {
      label: 'Absent',
      data: props.chartData.map(item => item.absent),
      borderColor: '#F44336',
      backgroundColor: 'rgba(244, 67, 54, 0.1)',
      borderWidth: 2,
      tension: 0.4,
      fill: true,
      pointBackgroundColor: '#F44336',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6
    },
    {
      label: 'Late',
      data: props.chartData.map(item => item.late),
      borderColor: '#FF9800',
      backgroundColor: 'rgba(255, 152, 0, 0.1)',
      borderWidth: 2,
      tension: 0.4,
      fill: true,
      pointBackgroundColor: '#FF9800',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6
    }
  ]

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          labels: {
            usePointStyle: true,
            padding: 20,
            font: {
              family: "'Inter', sans-serif",
              size: 12,
              weight: '500'
            }
          }
        },
        tooltip: {
          mode: 'index',
          intersect: false,
          backgroundColor: 'rgba(255, 255, 255, 0.95)',
          titleColor: '#333',
          bodyColor: '#666',
          borderColor: 'rgba(0, 0, 0, 0.1)',
          borderWidth: 1,
          padding: 12,
          boxPadding: 6,
          usePointStyle: true,
          callbacks: {
            label: function(context) {
              return `${context.dataset.label}: ${context.parsed.y} students`
            }
          }
        }
      },
      interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
      },
      scales: {
        x: {
          display: true,
          grid: {
            display: false
          },
          ticks: {
            font: {
              family: "'Inter', sans-serif",
              size: 11
            },
            color: 'rgba(0, 0, 0, 0.6)'
          },
          title: {
            display: true,
            text: 'Date',
            font: {
              family: "'Inter', sans-serif",
              size: 12,
              weight: '600'
            },
            color: 'rgba(0, 0, 0, 0.7)',
            padding: { top: 10, bottom: 0 }
          }
        },
        y: {
          display: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.05)',
            drawBorder: false
          },
          ticks: {
            font: {
              family: "'Inter', sans-serif",
              size: 11
            },
            color: 'rgba(0, 0, 0, 0.6)',
            padding: 8
          },
          title: {
            display: true,
            text: 'Number of Students',
            font: {
              family: "'Inter', sans-serif",
              size: 12,
              weight: '600'
            },
            color: 'rgba(0, 0, 0, 0.7)',
            padding: { top: 0, bottom: 10 }
          },
          beginAtZero: true
        }
      }
    }
  })
}

watch(() => props.chartData, () => {
  if (props.chartData.length > 0) {
    createChart()
  }
})

onMounted(() => {
  if (props.chartData.length > 0) {
    createChart()
  }
})

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy()
  }
})
</script>

<style scoped>
.attendance-chart-container {
  position: relative;
  width: 100%;
  height: 100%;
}
</style>
