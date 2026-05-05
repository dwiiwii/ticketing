document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flatpickr for Date Selector
    const datePicker = flatpickr("#datePicker", {
        altInput: false,
        dateFormat: "l, jS F",
        defaultDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            document.getElementById('currentDate').textContent = dateStr;
        }
    });

    // Set initial date display
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', { 
        weekday: 'long', 
        day: 'numeric', 
        month: 'long' 
    });

    const ctx = document.getElementById('productivityChart').getContext('2d');
    
    // Gradient for the lines to make them look better (optional, but standard for modern UI)
    const blueGradient = ctx.createLinearGradient(0, 0, 0, 400);
    blueGradient.addColorStop(0, 'rgba(14, 165, 233, 0.2)');
    blueGradient.addColorStop(1, 'rgba(14, 165, 233, 0)');

    const purpleGradient = ctx.createLinearGradient(0, 0, 0, 400);
    purpleGradient.addColorStop(0, 'rgba(139, 92, 246, 0.2)');
    purpleGradient.addColorStop(1, 'rgba(139, 92, 246, 0)');

    const data = {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [
            {
                label: 'New Tickets',
                data: [12, 23, 18, 35, 31, 38, 25],
                borderColor: '#0ea5e9', // Blue
                backgroundColor: blueGradient,
                borderWidth: 2,
                pointBackgroundColor: '#0ea5e9',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4 // Smooth curves
            },
            {
                label: 'Resolved',
                data: [18, 11, 20, 14, 17, 13, 10],
                borderColor: '#8b5cf6', // Purple
                backgroundColor: purpleGradient,
                borderWidth: 2,
                pointBackgroundColor: '#8b5cf6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4 // Smooth curves
            }
        ]
    };

    const config = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // We built a custom legend in HTML
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            // Dummy logic to show '3h 10m' style tooltip
                            let val = context.raw;
                            let hours = Math.floor(val);
                            let mins = Math.floor((val - hours) * 60);
                            return `${hours}h ${mins}m`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    min: 0,
                    max: 4,
                    ticks: {
                        stepSize: 1,
                        color: '#94a3b8',
                        font: {
                            family: "'Inter', sans-serif",
                            size: 11
                        }
                    },
                    border: {
                        display: false
                    },
                    grid: {
                        color: '#f1f5f9',
                        drawTicks: false,
                    }
                },
                x: {
                    ticks: {
                        color: '#94a3b8',
                        font: {
                            family: "'Inter', sans-serif",
                            size: 11
                        }
                    },
                    border: {
                        display: false
                    },
                    grid: {
                        display: false,
                        drawTicks: false,
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
        }
    };

    new Chart(ctx, config);
});
