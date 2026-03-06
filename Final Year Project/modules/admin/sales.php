<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

// Fetch Categories for Filter Dropdown
$cat_stmt = $pdo->query("SELECT * FROM Categories ORDER BY category_name ASC");
$categories = $cat_stmt->fetchAll();

// Process Filters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // Default: 1st of current month
$end_date   = $_GET['end_date'] ?? date('Y-m-d');
$cat_id     = $_GET['category_id'] ?? '';

// Build Base Query (Only Valid Orders)
$sql = "
    SELECT
        oi.*,
        o.created_at,
        o.order_id,
        p.product_name,
        c.category_name
    FROM Order_Items oi
    JOIN Orders o ON oi.order_id = o.order_id
    JOIN Products p ON oi.product_id = p.product_id
    JOIN Categories c ON p.category_id = c.category_id
    WHERE o.status IN ('confirmed', 'shipped', 'delivered')
";
$params = [];

// Apply Date Filters
if ($start_date) {
    $sql .= " AND DATE(o.created_at) >= ?";
    $params[] = $start_date;
}
if ($end_date) {
    $sql .= " AND DATE(o.created_at) <= ?";
    $params[] = $end_date;
}

// Apply Category Filter
if ($cat_id !== '') {
    $sql .= " AND p.category_id = ?";
    $params[] = $cat_id;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll();

// Calculate KPIs
$total_revenue = 0;
$total_units = 0;
$unique_orders = [];

foreach ($sales as $sale) {
    $total_revenue += $sale['subtotal'];
    $total_units += $sale['quantity'];
    $unique_orders[$sale['order_id']] = true;
}
$total_orders = count($unique_orders);

// Handle Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    
    // Overall KPIs
    fputcsv($output, ['Sales KPIs']);
    fputcsv($output, ['Total Revenue', 'Units Sold', 'Valid Orders']);
    fputcsv($output, [$total_revenue, $total_units, $total_orders]);
    fputcsv($output, []);
    
    // Filter Info
    fputcsv($output, ['Active Filters']);
    fputcsv($output, ['Start Date', 'End Date', 'Category ID']);
    fputcsv($output, [$start_date, $end_date, $cat_id ? $cat_id : 'All']);
    fputcsv($output, []);
    
    // Itemized Sales
    fputcsv($output, ['Itemized Transactions']);
    fputcsv($output, ['Date', 'Order ID', 'Product', 'Category', 'Quantity', 'Subtotal']);
    foreach($sales as $s) {
        $date = date('Y-m-d', strtotime($s['created_at']));
        fputcsv($output, [$date, $s['order_id'], $s['product_name'], $s['category_name'], $s['quantity'], $s['subtotal']]);
    }
    
    fclose($output);
    exit();
}

// Render HTML
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row pt-4 pb-5">
    <!-- Sidebar -->
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm border-0">
            <a href="index.php" class="list-group-item list-group-item-action"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="sales.php" class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom fw-bold"><i class="bi bi-graph-up-arrow me-2"></i> Sales Report</a>
            <a href="products.php" class="list-group-item list-group-item-action"><i class="bi bi-box me-2"></i> Products & Inventory</a>
            <a href="orders.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i> Manage Orders</a>
            <a href="users.php" class="list-group-item list-group-item-action"><i class="bi bi-people me-2"></i> User Management</a>
            <a href="tutorials.php" class="list-group-item list-group-item-action"><i class="bi bi-youtube me-2"></i> Manage Tutorials</a>
            <a href="reviews.php" class="list-group-item list-group-item-action"><i class="bi bi-star-fill me-2"></i> Manage Reviews</a>
            <a href="promos.php" class="list-group-item list-group-item-action"><i class="bi bi-tags-fill me-2"></i> Promo Codes</a>
        </div>
    </div>

    <!-- Main Content Panel -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
            <h2 class="text-primary-custom fw-bold m-0"><i class="bi bi-graph-up-arrow me-2"></i>Sales Analytics</h2>
            <div>
                <?php
                    // Build query string for export to retain filters
                    $export_params = $_GET;
                    $export_params['export'] = 'csv';
                    $export_qs = http_build_query($export_params);
                ?>
                <a href="sales.php?<?php echo $export_qs; ?>" class="btn btn-outline-success btn-sm me-2"><i class="bi bi-file-earmark-excel me-1"></i> Export Data</a>
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print Report</button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card bg-success text-white h-100 border-0 shadow-sm glass-panel" style="background: rgba(25, 135, 84, 0.85) !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white opacity-75 fw-bold text-uppercase mb-1">Total Revenue</h6>
                                <h3 class="mb-0 fw-bold">&#8377;<?php echo number_format($total_revenue, 2); ?></h3>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                                <i class="bi bi-cash-coin fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card bg-primary-custom text-white h-100 border-0 shadow-sm glass-panel">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white opacity-75 fw-bold text-uppercase mb-1">Units Sold</h6>
                                <h3 class="mb-0 fw-bold"><?php echo number_format($total_units); ?></h3>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                                <i class="bi bi-box-fill fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-dark text-white h-100 border-0 shadow-sm glass-panel">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white opacity-75 fw-bold text-uppercase mb-1">Valid Orders</h6>
                                <h3 class="mb-0 fw-bold"><?php echo number_format($total_orders); ?></h3>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                                <i class="bi bi-receipt fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card shadow-sm border-0 mb-4 glass-panel bg-dark">
            <div class="card-body">
                <form method="GET" action="sales.php" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-white opacity-75 small fw-bold">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo sanitize_html($start_date); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white opacity-75 small fw-bold">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo sanitize_html($end_date); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white opacity-75 small fw-bold">Product Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $c): ?>
                                <option value="<?php echo $c['category_id']; ?>" <?php echo $cat_id == $c['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitize_html($c['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sales Data Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom pt-4 pb-3">
                <h5 class="fw-bold mb-0 text-white"><i class="bi bi-table me-2"></i>Itemized Transaction Report</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-white opacity-75 small text-uppercase tracking-wider sticky-top">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Order ID</th>
                                <th>Product / Category</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end pe-4">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($sales) > 0): ?>
                                <?php foreach($sales as $s): ?>
                                <tr>
                                    <td class="ps-4 text-white opacity-75 small"><?php echo date('M j, Y', strtotime($s['created_at'])); ?></td>
                                    <td class="fw-bold"><a href="orders.php?view=<?php echo $s['order_id']; ?>" class="text-primary-custom text-decoration-none hover-secondary">#<?php echo str_pad($s['order_id'], 6, '0', STR_PAD_LEFT); ?></a></td>
                                    <td>
                                        <div class="text-white fw-bold"><?php echo sanitize_html($s['product_name']); ?></div>
                                        <div class="text-white opacity-50 small"><?php echo sanitize_html($s['category_name']); ?></div>
                                    </td>
                                    <td class="text-center text-white"><?php echo $s['quantity']; ?></td>
                                    <td class="text-end pe-4 fw-bold text-secondary-custom">&#8377;<?php echo number_format($s['subtotal'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="bi bi-clipboard-x fs-1 text-white opacity-25 d-block mb-3"></i>
                                        <p class="text-white opacity-75 fw-bold">No sales data found matching these filters.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .col-md-9, .col-md-9 * { visibility: visible; }
    .col-md-9 { position: absolute; left: 0; top: 0; width: 100%; }
    .btn, .list-group, form { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
