<template>
    <div class="dashboard">
        <div class="dashboard__header">
            <h2>Analytics Dashboard</h2>
            <button class="btn btn-secondary" @click="refreshData">Refresh Data</button>
        </div>

        <div class="dashboard__content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card__icon">📋</div>
                    <div class="stat-card__content">
                        <h3>Total Tickets</h3>
                        <div class="stat-card__number">{{ stats.total_tickets }}</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card__icon">🔓</div>
                    <div class="stat-card__content">
                        <h3>Open Tickets</h3>
                        <div class="stat-card__number">{{ stats.open_tickets }}</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card__icon">⏳</div>
                    <div class="stat-card__content">
                        <h3>Pending Tickets</h3>
                        <div class="stat-card__number">{{ stats.pending_tickets }}</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card__icon">✅</div>
                    <div class="stat-card__content">
                        <h3>Resolved Tickets</h3>
                        <div class="stat-card__number">{{ stats.resolved_tickets }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-card__header">
                        <h3>Tickets by Category</h3>
                    </div>
                    <div class="chart-container">
                        <canvas ref="categoryChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card">
                    <div class="chart-card__header">
                        <h3>Tickets by Status</h3>
                    </div>
                    <div class="chart-container">
                        <canvas ref="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Sample Data Notice -->
            <div class="sample-notice">
                <p>📊 Displaying sample data for demonstration purposes</p>
            </div>
        </div>
    </div>
</template>

<script>
import Chart from 'chart.js/auto';

export default {
    name: 'Dashboard',
    data() {
        return {
            stats: {
                total_tickets: 42,
                open_tickets: 18,
                pending_tickets: 12,
                resolved_tickets: 12,
                category_stats: {
                    'bug': 15,
                    'billing': 8,
                    'account': 7,
                    'feature': 9,
                    'other': 3
                }
            }
        }
    },
    mounted() {
        // Render charts after a brief delay to ensure DOM is ready
        this.$nextTick(() => {
            this.renderCategoryChart();
            this.renderStatusChart();
        });
    },
    beforeUnmount() {
        // Clean up charts to prevent memory leaks
        if (this.categoryChart) {
            this.categoryChart.destroy();
        }
        if (this.statusChart) {
            this.statusChart.destroy();
        }
    },
    methods: {
        renderCategoryChart() {
            const ctx = this.$refs.categoryChart.getContext('2d');
            
            // Prepare data for the chart
            const categories = Object.keys(this.stats.category_stats || {});
            const counts = Object.values(this.stats.category_stats || {});
            
            // Define colors for each category
            const categoryColors = {
                'bug': '#3498db',
                'billing': '#9b59b6',
                'account': '#e67e22',
                'feature': '#95a5a6',
                'other': '#e74c3c'
            };
            
            const backgroundColors = categories.map(cat => 
                categoryColors[cat] || this.getRandomColor()
            );
            
            if (this.categoryChart) {
                this.categoryChart.destroy();
            }
            
            this.categoryChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: categories.map(cat => cat.charAt(0).toUpperCase() + cat.slice(1)),
                    datasets: [{
                        data: counts,
                        backgroundColor: backgroundColors,
                        borderColor: '#fff',
                        borderWidth: 2,
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        },
        
        renderStatusChart() {
            const ctx = this.$refs.statusChart.getContext('2d');
            
            const statusData = {
                'Open': this.stats.open_tickets,
                'in_progress': this.stats.pending_tickets,
                'closed': this.stats.resolved_tickets
            };
            
            const statusColors = {
                'Open': '#e74c3c',
                'in_progress': '#f39c12',
                'closed': '#27ae60'
            };
            
            const backgroundColors = Object.keys(statusData).map(status => 
                statusColors[status]
            );
            
            if (this.statusChart) {
                this.statusChart.destroy();
            }
            
            this.statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: backgroundColors,
                        borderColor: '#fff',
                        borderWidth: 2,
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        },
        
        getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        },
        
        refreshData() {
            // For dummy data, we'll just regenerate with slight variations
            this.stats = {
                total_tickets: Math.floor(Math.random() * 20) + 35, // Between 35-55
                open_tickets: Math.floor(Math.random() * 15) + 10,  // Between 10-25
                pending_tickets: Math.floor(Math.random() * 10) + 5, // Between 5-15
                resolved_tickets: Math.floor(Math.random() * 15) + 8, // Between 8-23
                category_stats: {
                    'bug': Math.floor(Math.random() * 8) + 10,
                    'billing': Math.floor(Math.random() * 5) + 5,
                    'account': Math.floor(Math.random() * 4) + 5,
                    'feature': Math.floor(Math.random() * 6) + 5,
                    'other': Math.floor(Math.random() * 3) + 1
                }
            };
            
            // Re-render charts with new data
            this.$nextTick(() => {
                this.renderCategoryChart();
                this.renderStatusChart();
            });
        }
    }
}
</script>

<style>
.dashboard__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
}

.stat-card__icon {
    font-size: 2.5rem;
    margin-right: 1.2rem;
    opacity: 0.8;
}

.stat-card__content h3 {
    margin: 0 0 0.5rem 0;
    font-size: 0.9rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.stat-card__number {
    font-size: 2.2rem;
    font-weight: bold;
    color: #2c3e50;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.chart-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    padding: 1.8rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.chart-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
}

.chart-card__header {
    margin-bottom: 1.5rem;
    text-align: center;
}

.chart-card__header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.3rem;
    font-weight: 600;
}

.chart-container {
    position: relative;
    height: 320px;
}

.sample-notice {
    text-align: center;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid #3498db;
}

.sample-notice p {
    margin: 0;
    color: #666;
    font-style: italic;
}

/* Responsive adjustments */
@media (max-width: 900px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard__header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .chart-container {
        height: 280px;
    }
}
</style>