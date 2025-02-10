<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Database connection
require_once 'config/db.php';

// Fetch some quick statistics (we'll implement these functions later)
$totalProducts = 0; // getTotalProducts();
$totalSales = 0; // getTotalSales();
$lowStock = 0; // getLowStockItems();
$monthlyRevenue = 0; // getMonthlyRevenue();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexInvent - Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
        }
        .sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background-color: #2c3e50;
            padding-top: 20px;
            color: white;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
        .sidebar-link {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar-link:hover {
            background-color: #34495e;
            color: #ecf0f1;
        }
        .card-dashboard {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="px-3 mb-4">
        <h4 class="text-center">NexInvent</h4>
    </div>
    <nav>
        <a href="index.php" class="sidebar-link active">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a href="inventory/index.php" class="sidebar-link">
            <i class="bi bi-box-seam me-2"></i> Inventory
        </a>
        <a href="products/index.php" class="sidebar-link">
            <i class="bi bi-cart3 me-2"></i> Products
        </a>
        <a href="sales/index.php" class="sidebar-link">
            <i class="bi bi-graph-up me-2"></i> Sales
        </a>
        <a href="purchases/index.php" class="sidebar-link">
            <i class="bi bi-bag me-2"></i> Purchases
        </a>
        <a href="suppliers/index.php" class="sidebar-link">
            <i class="bi bi-truck me-2"></i> Suppliers
        </a>
        <a href="employees/index.php" class="sidebar-link">
            <i class="bi bi-people me-2"></i> Employees
        </a>
        <a href="payroll/index.php" class="sidebar-link">
            <i class="bi bi-cash-stack me-2"></i> Payroll
        </a>
        <a href="reports/index.php" class="sidebar-link">
            <i class="bi bi-file-earmark-text me-2"></i> Reports
        </a>
        <a href="settings/index.php" class="sidebar-link">
            <i class="bi bi-gear me-2"></i> Settings
        </a>
    </nav>
</div>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard</h2>
            <div class="user-info">
                <span class="me-2"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span>
                <a href="../login/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card card-dashboard bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Products</h6>
                                <h3 class="mb-0"><?php echo number_format($totalProducts); ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-dashboard bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Monthly Revenue</h6>
                                <h3 class="mb-0">$<?php echo number_format($monthlyRevenue, 2); ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-dashboard bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Sales</h6>
                                <h3 class="mb-0"><?php echo number_format($totalSales); ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-dashboard bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Low Stock Items</h6>
                                <h3 class="mb-0"><?php echo number_format($lowStock); ?></h3>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities and Charts -->
        <div class="row">
            <div class="col-md-8">
                <div class="card card-dashboard">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Sales Overview</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-dashboard">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <!-- We'll populate this with PHP later -->
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">New Sale</h6>
                                    <small>3 mins ago</small>
                                </div>
                                <p class="mb-1">Sale #1234 was completed</p>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Low Stock Alert</h6>
                                    <small>1 hour ago</small>
                                </div>
                                <p class="mb-1">Product XYZ is running low</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Custom JS -->
<script>
// Sample chart data - we'll make this dynamic later
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Monthly Sales',
            data: [12, 19, 3, 5, 2, 3],
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>

</body>
</html>