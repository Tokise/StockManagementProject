<?php
// Get current page for active menu highlighting
$current_page = basename(dirname($_SERVER['PHP_SELF']));
?>

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
    z-index: 1000;
    display: flex;
    flex-direction: column;
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
.sidebar-link:hover, .sidebar-link.active {
    background-color: #34495e;
    color: #ecf0f1;
    text-decoration: none;
}
.sidebar-brand {
    padding: 15px 20px;
    font-size: 1.5rem;
    font-weight: bold;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 15px;
}
.sidebar-user {
    padding: 15px 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
    margin-top: auto;
    background-color: rgba(0,0,0,0.2);
}
.nav-section {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.nav-header {
    padding: 10px 20px;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: rgba(255,255,255,0.5);
}
.sidebar-content {
    flex: 1;
    overflow-y: auto;
    -ms-overflow-style: none;  /* Hide scrollbar for IE and Edge */
    scrollbar-width: none;     /* Hide scrollbar for Firefox */
}

/* Hide scrollbar for Chrome, Safari and Opera */
.sidebar-content::-webkit-scrollbar {
    display: none;
}
.logout-section {
    padding: 15px 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
    margin-top: 15px;
}
</style>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-box-seam"></i> NexInvent
    </div>
    
    <div class="sidebar-content">
        <div class="nav-section">
            <div class="nav-header">Main Navigation</div>
            <nav>
                <a href="../index.php" class="sidebar-link <?php echo $current_page == 'modules' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="../inventory/index.php" class="sidebar-link <?php echo $current_page == 'inventory' ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam me-2"></i> Inventory
                </a>
                <a href="../products/index.php" class="sidebar-link <?php echo $current_page == 'products' ? 'active' : ''; ?>">
                    <i class="bi bi-cart3 me-2"></i> Products
                </a>
                <a href="../sales/index.php" class="sidebar-link <?php echo $current_page == 'sales' ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up me-2"></i> Sales
                </a>
                <a href="../purchases/index.php" class="sidebar-link <?php echo $current_page == 'purchases' ? 'active' : ''; ?>">
                    <i class="bi bi-bag me-2"></i> Purchases
                </a>
            </nav>
        </div>

        <div class="nav-section">
            <div class="nav-header">Management</div>
            <nav>
                <a href="../suppliers/index.php" class="sidebar-link <?php echo $current_page == 'suppliers' ? 'active' : ''; ?>">
                    <i class="bi bi-truck me-2"></i> Suppliers
                </a>
                <a href="../employees/index.php" class="sidebar-link <?php echo $current_page == 'employees' ? 'active' : ''; ?>">
                    <i class="bi bi-people me-2"></i> Employees
                </a>
                <a href="../payroll/index.php" class="sidebar-link <?php echo $current_page == 'payroll' ? 'active' : ''; ?>">
                    <i class="bi bi-cash-stack me-2"></i> Payroll
                </a>
            </nav>
        </div>

        <div class="nav-section">
            <div class="nav-header">Reports & Settings</div>
            <nav>
                <a href="../reports/index.php" class="sidebar-link <?php echo $current_page == 'reports' ? 'active' : ''; ?>">
                    <i class="bi bi-file-earmark-text me-2"></i> Reports
                </a>
                <a href="../settings/index.php" class="sidebar-link <?php echo $current_page == 'settings' ? 'active' : ''; ?>">
                    <i class="bi bi-gear me-2"></i> Settings
                </a>
            </nav>
        </div>
    </div>

    <div class="logout-section">
        <a href="../../login/logout.php" class="btn btn-outline-light w-100">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div> 