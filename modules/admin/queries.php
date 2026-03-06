<?php
require_once __DIR__ . '/../../includes/header.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: " . base_url('index.php'));
    exit;
}

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        $query_id = (int)$_POST['query_id'];
        $new_status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE Queries SET status = ? WHERE query_id = ?");
        $stmt->execute([$new_status, $query_id]);
    }
}

// Fetch Queries
$stmt = $pdo->query("SELECT * FROM Queries ORDER BY created_at DESC");
$queries = $stmt->fetchAll();
?>

<div class="container mt-5 pt-5 pb-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="list-group shadow-sm border-0">
                <a href="index.php" class="list-group-item list-group-item-action"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="products.php" class="list-group-item list-group-item-action"><i class="bi bi-box me-2"></i> Products & Inventory</a>
                <a href="orders.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i> Manage Orders</a>
                <a href="users.php" class="list-group-item list-group-item-action"><i class="bi bi-people me-2"></i> User Management</a>
                <a href="tutorials.php" class="list-group-item list-group-item-action"><i class="bi bi-youtube me-2"></i> Manage Tutorials</a>
                <a href="reviews.php" class="list-group-item list-group-item-action"><i class="bi bi-star-fill me-2"></i> Manage Reviews</a>
                <a href="promos.php" class="list-group-item list-group-item-action"><i class="bi bi-tags-fill me-2"></i> Promo Codes</a>
                <a href="queries.php" class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom"><i class="bi bi-headset me-2"></i> Customer Queries</a>
                <a href="feedbacks.php" class="list-group-item list-group-item-action"><i class="bi bi-lightbulb me-2"></i> Feedbacks</a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header border-bottom-0 pt-4 pb-0">
                    <h3 class="mb-0 text-primary-custom fw-bold"><i class="bi bi-headset me-2"></i>Customer Queries</h3>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($queries)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-envelope-open text-white opacity-25 d-block mb-3" style="font-size: 4rem;"></i>
                            <p class="text-white opacity-50">No customer queries found in the system.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light text-white opacity-75 small text-uppercase tracking-wider">
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($queries as $q): ?>
                                    <tr>
                                        <td class="text-white opacity-75 small"><?php echo date('M j, Y g:i A', strtotime($q['created_at'])); ?></td>
                                        <td>
                                            <div class="fw-bold text-white"><?php echo sanitize_html($q['name']); ?></div>
                                            <div class="small text-secondary-custom"><?php echo sanitize_html($q['email']); ?></div>
                                        </td>
                                        <td class="text-white">
                                            <span class="badge bg-dark border border-secondary"><?php echo sanitize_html($q['subject']); ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $badge_class = 'bg-warning text-dark';
                                            if ($q['status'] === 'Reviewed') $badge_class = 'bg-info text-dark';
                                            if ($q['status'] === 'Resolved') $badge_class = 'bg-success';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo sanitize_html($q['status']); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#viewQuery<?php echo $q['query_id']; ?>">
                                                Read
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Query Modals -->
<?php if (!empty($queries)): ?>
    <?php foreach ($queries as $q): ?>
    <div class="modal fade" id="viewQuery<?php echo $q['query_id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-panel">
                <div class="modal-header border-secondary border-opacity-50">
                    <h5 class="modal-title fw-bold text-primary-custom"><i class="bi bi-envelope-fill me-2"></i>Inquiry #<?php echo $q['query_id']; ?>: <?php echo sanitize_html($q['subject']); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-white">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <span class="small opacity-50 d-block text-uppercase tracking-wider">From</span>
                            <span class="fw-bold"><?php echo sanitize_html($q['name']); ?></span>
                            (<a href="mailto:<?php echo sanitize_html($q['email']); ?>" class="text-secondary-custom"><?php echo sanitize_html($q['email']); ?></a>)
                        </div>
                        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                            <span class="small opacity-50 d-block text-uppercase tracking-wider">Date Received</span>
                            <span class="fw-bold"><?php echo date('F j, Y, g:i a', strtotime($q['created_at'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="bg-dark p-4 rounded-3 border border-secondary mb-4 opacity-75">
                        <p class="mb-0" style="white-space: pre-wrap;"><?php echo sanitize_html($q['message']); ?></p>
                    </div>
                    
                    <form method="POST" action="queries.php" class="d-flex align-items-center">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="query_id" value="<?php echo $q['query_id']; ?>">
                        
                        <span class="me-3 small text-uppercase tracking-wider opacity-50">Update Status:</span>
                        <select name="status" class="form-select w-auto bg-dark border-secondary text-white shadow-none me-2">
                            <option value="Pending" <?php echo $q['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Reviewed" <?php echo $q['status'] === 'Reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                            <option value="Resolved" <?php echo $q['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                        <button type="submit" class="btn btn-secondary btn-sm fw-bold px-3">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
