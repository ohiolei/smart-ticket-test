<template>
    <div class="dashboard">
        <div class="dashboard__header">
            <h2>Analytics Dashboard</h2>
            <button class="btn btn-secondary" @click="refreshData">
                Refresh Data
            </button>
        </div>

        <div class="dashboard__content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card__icon">📋</div>
                    <div class="stat-card__content">
                        <h3>Total Tickets</h3>
                        <div class="stat-card__number">
                            {{ stats.total_tickets }}
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">🔓</div>
                    <div class="stat-card__content">
                        <h3>Open Tickets</h3>
                        <div class="stat-card__number">
                            {{ stats.open_tickets }}
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">⏳</div>
                    <div class="stat-card__content">
                        <h3>Pending Tickets</h3>
                        <div class="stat-card__number">
                            {{ stats.pending_tickets }}
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">✅</div>
                    <div class="stat-card__content">
                        <h3>Resolved Tickets</h3>
                        <div class="stat-card__number">
                            {{ stats.resolved_tickets }}
                        </div>
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
        </div>
    </div>
</template>

<script>
import axios from "axios";
import Chart from "chart.js/auto";

export default {
    name: "Dashboard",
    data() {
        return {
            stats: {
                total_tickets: 0,
                open_tickets: 0,
                pending_tickets: 0,
                resolved_tickets: 0,
            },
            categoryData: {},
            statusData: {},
        };
    },
    mounted() {
        this.refreshData();
    },
    beforeUnmount() {
        if (this.categoryChart) this.categoryChart.destroy();
        if (this.statusChart) this.statusChart.destroy();
    },
    methods: {
        async refreshData() {
            try {
                // Fetch main stats
                const statsRes = await axios.get("/api/tickets/stats");
                this.stats = statsRes.data;

                // Fetch category summary
                const categoryRes = await axios.get(
                    "/api/tickets/category-summary"
                );
                this.categoryData = categoryRes.data;

                // Fetch status summary
                const statusRes = await axios.get(
                    "/api/tickets/status-summary"
                );
                this.statusData = statusRes.data;

                this.$nextTick(() => {
                    this.renderCategoryChart();
                    this.renderStatusChart();
                });
            } catch (error) {
                console.error("Error loading dashboard data:", error);
            }
        },

        renderCategoryChart() {
            const ctx = this.$refs.categoryChart.getContext("2d");
            const categories = Object.keys(this.categoryData);
            const counts = Object.values(this.categoryData);

            const categoryColors = {
                bug: "#3498db",
                billing: "#9b59b6",
                account: "#e67e22",
                feature: "#95a5a6",
                other: "#e74c3c",
            };

            const backgroundColors = categories.map(
                (cat) => categoryColors[cat] || this.getRandomColor()
            );

            if (this.categoryChart) this.categoryChart.destroy();

            this.categoryChart = new Chart(ctx, {
                type: "pie",
                data: {
                    labels: categories.map(
                        (cat) => cat.charAt(0).toUpperCase() + cat.slice(1)
                    ),
                    datasets: [
                        {
                            data: counts,
                            backgroundColor: backgroundColors,
                            borderColor: "#fff",
                            borderWidth: 2,
                            hoverOffset: 12,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: { usePointStyle: true, font: { size: 12 } },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.label || "";
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce(
                                        (a, b) => a + b,
                                        0
                                    );
                                    const percentage = Math.round(
                                        (value / total) * 100
                                    );
                                    return `${label}: ${value} (${percentage}%)`;
                                },
                            },
                        },
                    },
                },
            });
        },

        renderStatusChart() {
            const ctx = this.$refs.statusChart.getContext("2d");
            const statuses = Object.keys(this.statusData);
            const counts = Object.values(this.statusData);

            const statusColors = {
                open: "#27ae60",
                in_progress: "#f39c12",
                closed: "#e74c3c",
            };

            const backgroundColors = statuses.map(
                (status) => statusColors[status] || this.getRandomColor()
            );

            if (this.statusChart) this.statusChart.destroy();

            this.statusChart = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: statuses.map((s) =>
                        s.replace("_", " ").toUpperCase()
                    ),
                    datasets: [
                        {
                            data: counts,
                            backgroundColor: backgroundColors,
                            borderColor: "#fff",
                            borderWidth: 2,
                            hoverOffset: 12,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: { usePointStyle: true, font: { size: 12 } },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.label || "";
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce(
                                        (a, b) => a + b,
                                        0
                                    );
                                    const percentage = Math.round(
                                        (value / total) * 100
                                    );
                                    return `${label}: ${value} (${percentage}%)`;
                                },
                            },
                        },
                    },
                },
            });
        },

        getRandomColor() {
            const letters = "0123456789ABCDEF";
            let color = "#";
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        },
    },
};
</script>

<style>
/* Keep your exact styles */
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

/* Responsive */
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
