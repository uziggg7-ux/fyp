/**
 * admin_charts.js - Chart.js implementations for Admin Dashboard
 */
document.addEventListener('DOMContentLoaded', function() {
    const ctxMood = document.getElementById('moodChart');
    const ctxUser = document.getElementById('userGrowthChart');
    const ctxMethod = document.getElementById('methodChart');

    const accentRed = '#FF0000';
    const accentRedLight = 'rgba(255, 0, 0, 0.2)';
    const textMuted = '#888888';

    // 1. Mood Distribution Chart (Bar)
    if (ctxMood) {
        new Chart(ctxMood, {
            type: 'bar',
            data: {
                labels: moodChartLabels,
                datasets: [{
                    label: 'Detections',
                    data: moodChartData,
                    backgroundColor: accentRedLight,
                    borderColor: accentRed,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#1a1a1a' },
                        ticks: { color: textMuted }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textMuted }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 2. User Growth Chart (Line)
    if (ctxUser) {
        new Chart(ctxUser, {
            type: 'line',
            data: {
                labels: userChartLabels,
                datasets: [{
                    label: 'New Users',
                    data: userChartData,
                    fill: true,
                    backgroundColor: 'rgba(255, 0, 0, 0.05)',
                    borderColor: accentRed,
                    tension: 0.4,
                    pointBackgroundColor: accentRed,
                    pointBorderColor: '#000',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#1a1a1a' },
                        ticks: { color: textMuted, stepSize: 1 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textMuted }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 3. Detection Methods Chart (Doughnut)
    if (ctxMethod) {
        new Chart(ctxMethod, {
            type: 'doughnut',
            data: {
                labels: methodChartLabels,
                datasets: [{
                    data: methodChartData,
                    backgroundColor: [
                        '#FF0000', // Red
                        '#950101', // Dark Red
                        '#3D0000', // Maroon
                        '#1a1a1a'  // Grey
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textMuted,
                            padding: 20,
                            font: { size: 10 }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
});
