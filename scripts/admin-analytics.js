document.addEventListener('DOMContentLoaded', function() {
    
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error("Chart.js is not loaded.");
        return;
    }

    // Access the data passed from PHP
    const data = dashboardData;

    // --- CHART 1: PIE CHART (Leave Types) ---
    const ctxPie = document.getElementById('leavePieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Sick Leave', 'Vacation'],
            datasets: [{
                data: [data.pie.sick, data.pie.vacation],
                backgroundColor: ['#ef5350', '#42a5f5'], // Red for Sick, Blue for Vacation
                hoverOffset: 4
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // --- CHART 2: BAR CHART (Absences) ---
    const ctxBar = document.getElementById('absenceBarChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: data.bar.labels,
            datasets: [{
                label: 'Avg Absences',
                data: data.bar.data,
                backgroundColor: '#7e57c2', // Purple
                borderRadius: 5,
                barPercentage: 0.7
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // --- CHART 3: LINE CHART (Forecast) ---
    const ctxLine = document.getElementById('forecastLineChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: data.line.labels,
            datasets: [
                {
                    label: 'Actual Leave Days',
                    data: data.line.history,
                    borderColor: '#42a5f5',
                    backgroundColor: '#42a5f5',
                    tension: 0.4,
                    fill: false,
                    pointRadius: 5
                },
                {
                    label: 'Forecasted Leave Days',
                    data: data.line.forecast,
                    borderColor: '#26a69a', // Green
                    backgroundColor: '#26a69a',
                    borderDash: [10, 5], // Dashed line effect
                    tension: 0.4,
                    fill: false,
                    pointRadius: 5,
                    pointStyle: 'rectRot'
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Leave Days' }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});