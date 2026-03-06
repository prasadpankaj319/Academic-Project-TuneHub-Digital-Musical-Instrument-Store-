<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

$action = $_POST['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['product_name']);
            $cat_id = $_POST['category_id'];
            $desc = trim($_POST['description']);
            $brand = trim($_POST['brand']);
            $price = $_POST['price'];
            $stock = $_POST['stock_quantity'];
            $skill = $_POST['skill_level'];

            // Image Upload Handling
            $image_url_sql = "";
            $image_params = [];

            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['product_image'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array(strtolower($ext), $allowed)) {
                    $new_name = uniqid('prod_') . '.' . $ext;
                    $dest_dir = __DIR__ . '/../../assets/images/products/';

                    if (!is_dir($dest_dir)) {
                        mkdir($dest_dir, 0777, true);
                    }

                    if (move_uploaded_file($file['tmp_name'], $dest_dir . $new_name)) {
                        $image_url_sql = "assets/images/products/" . $new_name;
                    }
                }
            }

            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO Products (product_name, category_id, description, brand, price, stock_quantity, skill_level, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $cat_id, $desc, $brand, $price, $stock, $skill, $image_url_sql ?: 'assets/images/products/default.jpg']);
                $_SESSION['msg'] = "Product successfully created.";
            } else {
                $pid = $_POST['product_id'];
                if ($image_url_sql) {
                    $stmt = $pdo->prepare("UPDATE Products SET product_name=?, category_id=?, description=?, brand=?, price=?, stock_quantity=?, skill_level=?, image_url=? WHERE product_id=?");
                    $stmt->execute([$name, $cat_id, $desc, $brand, $price, $stock, $skill, $image_url_sql, $pid]);
                } else {
                    $stmt = $pdo->prepare("UPDATE Products SET product_name=?, category_id=?, description=?, brand=?, price=?, stock_quantity=?, skill_level=? WHERE product_id=?");
                    $stmt->execute([$name, $cat_id, $desc, $brand, $price, $stock, $skill, $pid]);
                }
                $_SESSION['msg'] = "Product successfully updated.";
            }
        } elseif ($action === 'archive') {
            $pid = $_POST['product_id'];
            $stmt = $pdo->prepare("UPDATE Products SET is_active = 0 WHERE product_id=?");
            $stmt->execute([$pid]);
            $_SESSION['msg'] = "Product is successfully archived and stripped from the Customer storefront.";
        } elseif ($action === 'unarchive') {
            $pid = $_POST['product_id'];
            $stmt = $pdo->prepare("UPDATE Products SET is_active = 1 WHERE product_id=?");
            $stmt->execute([$pid]);
            $_SESSION['msg'] = "Product unarchived. It is now live on the public storefront again.";
        }
        header("Location: products.php");
        exit;
    }
}

// Fetch lists
$products = $pdo->query("SELECT p.*, c.category_name FROM Products p JOIN Categories c ON p.category_id=c.category_id ORDER BY p.product_id DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM Categories")->fetchAll();

$edit_id = $_GET['edit'] ?? null;
$edit_prod = null;
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM Products WHERE product_id=?");
    $stmt->execute([$edit_id]);
    $edit_prod = $stmt->fetch();
}

// All backend logic complete. Render DOM.
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="row pt-4 pb-5">
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm border-0">
            <a href="index.php" class="list-group-item list-group-item-action"><i class="bi bi-speedometer2 me-2"></i>
                Dashboard</a>
            <a href="sales.php" class="list-group-item list-group-item-action"><i class="bi bi-graph-up-arrow me-2"></i>
                Sales Report</a>
            <a href="products.php"
                class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom fw-bold"><i
                    class="bi bi-box me-2"></i> Products & Inventory</a>
            <a href="orders.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i>
                Manage Orders</a>
            <a href="users.php" class="list-group-item list-group-item-action"><i class="bi bi-people me-2"></i> User
                Management</a>
            <a href="tutorials.php" class="list-group-item list-group-item-action"><i class="bi bi-youtube me-2"></i>
                Manage Tutorials</a>
            <a href="reviews.php" class="list-group-item list-group-item-action"><i class="bi bi-star-fill me-2"></i>
                Manage Reviews</a>
            <a href="promos.php" class="list-group-item list-group-item-action"><i class="bi bi-tags-fill me-2"></i>
                Promo Codes</a>
        </div>
    </div>

    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
            <h2 class="text-primary-custom fw-bold m-0"><i class="bi bi-box-seam me-2"></i>Inventory Management</h2>
            <?php if ($edit_prod): ?>
                <a href="products.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add
                    New</a>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-success d-flex align-items-center shadow-sm"><i
                    class="bi bi-check-circle-fill me-2 fs-5"></i><?php echo $_SESSION['msg'];
                    unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['err'])): ?>
            <div class="alert alert-danger d-flex align-items-center shadow-sm"><i
                    class="bi bi-exclamation-triangle-fill me-2 fs-5"></i><?php echo $_SESSION['err'];
                    unset($_SESSION['err']); ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-header border-bottom pt-3 pb-2">
                <h5 class="mb-0 fw-bold text-white">
                    <?php echo $edit_prod ? "<i class='bi bi-pencil-square text-primary-custom me-2'></i>Edit Product" : "<i class='bi bi-plus-circle-fill text-primary-custom me-2'></i>Add New Product"; ?>
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="products.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="<?php echo $edit_prod ? 'update' : 'create'; ?>">
                    <?php if ($edit_prod): ?>
                        <input type="hidden" name="product_id" value="<?php echo $edit_prod['product_id']; ?>">
                    <?php endif; ?>

                    <div class="row gy-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-white opacity-75 small">Product Name</label>
                            <input type="text" name="product_name" class="form-control"
                                value="<?php echo $edit_prod ? sanitize_html($edit_prod['product_name']) : ''; ?>"
                                placeholder="e.g. Fender Stratocaster" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-white opacity-75 small">Brand</label>
                            <input type="text" name="brand" class="form-control"
                                value="<?php echo $edit_prod ? sanitize_html($edit_prod['brand']) : ''; ?>"
                                placeholder="e.g. Fender" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-white opacity-75 small">Category</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?php echo $c['category_id'] ?>" <?php echo ($edit_prod && $edit_prod['category_id'] == $c['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo sanitize_html($c['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-white opacity-75 small">Price Details ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" step="0.01" name="price" class="form-control"
                                    value="<?php echo $edit_prod ? $edit_prod['price'] : ''; ?>" placeholder="0.00"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-white opacity-75 small">Stock Qty</label>
                            <input type="number" name="stock_quantity" class="form-control"
                                value="<?php echo $edit_prod ? $edit_prod['stock_quantity'] : '0'; ?>" min="0" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-white opacity-75 small">Target Skill Level</label>
                            <div class="d-flex gap-3">
                                <?php
                                $levels = ['Beginner', 'Intermediate', 'Advanced'];
                                $curr = $edit_prod ? $edit_prod['skill_level'] : 'Beginner';
                                foreach ($levels as $lvl):
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="skill_level"
                                            id="skill_<?php echo strtolower($lvl); ?>" value="<?php echo $lvl; ?>" <?php echo $curr == $lvl ? 'checked' : ''; ?>>
                                        <label class="form-check-label"
                                            for="skill_<?php echo strtolower($lvl); ?>"><?php echo $lvl; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-white opacity-75 small">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Detailed product description..."><?php echo $edit_prod ? sanitize_html($edit_prod['description']) : ''; ?></textarea>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold text-dark opacity-75 small">Product Image <span
                                    class="text-danger">*</span></label>
                            <div class="file-upload-zone shadow-sm">
                                <input type="file" name="product_image" id="product_image" accept="image/*" <?php echo $edit_prod ? '' : 'required'; ?>>
                                <div class="upload-placeholder">
                                    <i class="bi bi-cloud-arrow-up display-4 text-primary-custom mb-2 d-block"></i>
                                    <h5 class="text-dark fw-bold mb-1">Drag & Drop product image here</h5>
                                    <p class="text-dark opacity-75 small mb-0">or Click to Browse PC</p>
                                </div>
                                <img <?php echo $edit_prod && !empty($edit_prod['image_url']) ? 'src="../../' . $edit_prod['image_url'] . '"' : ''; ?> alt="Preview"
                                    class="upload-preview mt-3 mx-auto shadow-sm"
                                    style="<?php echo $edit_prod && !empty($edit_prod['image_url']) ? 'display:block;' : 'display:none;'; ?>">
                            </div>
                        </div>

                        <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                            <button class="btn btn-primary px-5 fw-bold" type="submit">
                                <i
                                    class="bi <?php echo $edit_prod ? 'bi-save' : 'bi-plus-lg'; ?> me-2"></i><?php echo $edit_prod ? 'Update Product' : 'Add Product'; ?>
                            </button>
                            <?php if ($edit_prod): ?>
                                <a href="products.php" class="btn btn-outline-secondary px-4 fw-bold">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Catalog List -->
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom pt-4 pb-3">
                <h5 class="fw-bold mb-0 text-white"><i class="bi bi-list-ul me-2"></i>Current Product Catalog</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-white opacity-75 small text-uppercase tracking-wider">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Name / Brand</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr class="<?php echo $p['is_active'] == 0 ? 'opacity-50' : ''; ?>">
                                    <td class="ps-4 text-white opacity-75">#<?php echo $p['product_id']; ?></td>
                                    <td>
                                        <div class="fw-bold text-white">
                                            <?php echo sanitize_html($p['product_name']); ?>
                                            <?php if ($p['is_active'] == 0): ?>
                                                <span class="badge bg-danger ms-2" style="font-size: 0.70rem;">ARCHIVED</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small text-white opacity-75"><?php echo sanitize_html($p['brand']); ?>
                                        </div>
                                    </td>
                                    <td><span
                                            class="badge border text-white"><?php echo sanitize_html($p['category_name']); ?></span>
                                    </td>
                                    <td class="fw-bold text-secondary-custom">
                                        &#8377;<?php echo number_format($p['price'], 2); ?></td>
                                    <td>
                                        <?php if ($p['stock_quantity'] == 0): ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php elseif ($p['stock_quantity'] <= 5): ?>
                                            <span class="badge bg-warning text-dark"><i
                                                    class="bi bi-exclamation-triangle-fill me-1"></i><?php echo $p['stock_quantity']; ?>
                                                left</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo $p['stock_quantity']; ?> in stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="products.php?edit=<?php echo $p['product_id']; ?>"
                                            class="btn btn-sm btn-outline-primary shadow-sm me-1"><i
                                                class="bi bi-pencil-fill"></i></a>
                                        <?php if ($p['is_active'] == 1): ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning shadow-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#archiveProductModal<?php echo $p['product_id']; ?>"
                                                title="Archive">
                                                <i class="bi bi-archive-fill"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-success shadow-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#unarchiveProductModal<?php echo $p['product_id']; ?>">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Custom TuneHub Delete Product Modals -->
<?php if (!empty($products)): ?>
    <?php foreach ($products as $p): ?>
        <div class="modal fade" id="archiveProductModal<?php echo $p['product_id']; ?>" tabindex="-1"
            aria-labelledby="archiveProductModalLabel<?php echo $p['product_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-panel" style="text-align: left;">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold text-warning"
                            id="archiveProductModalLabel<?php echo $p['product_id']; ?>">
                            <i class="bi bi-archive-fill me-2"></i>Archive Product Catalog Entry
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-white opacity-75 mb-3" style="font-size: 1.05rem;">Are you absolutely sure you want to
                            archive: <strong><?php echo sanitize_html($p['product_name']); ?></strong>?</p>
                        <div class="p-3 bg-warning bg-opacity-25 rounded border border-warning text-white small">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i><strong>Notice</strong><br>
                            This instrument will be archived and hidden from the public Storefront, but will be preserved in
                            your records.
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="products.php" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="archive">
                            <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                            <button type="submit" class="btn btn-warning fw-bold text-dark">Yes, Archive</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="unarchiveProductModal<?php echo $p['product_id']; ?>" tabindex="-1"
            aria-labelledby="unarchiveProductModalLabel<?php echo $p['product_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-panel" style="text-align: left;">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold text-success"
                            id="unarchiveProductModalLabel<?php echo $p['product_id']; ?>">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Restore Product Catalog Entry
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-white opacity-75 mb-3" style="font-size: 1.05rem;">Are you absolutely sure you want to
                            restore: <strong><?php echo sanitize_html($p['product_name']); ?></strong>?</p>
                        <div class="p-3 bg-success bg-opacity-25 rounded border border-success text-white small">
                            <i class="bi bi-check-circle-fill text-success me-2"></i><strong>Notice</strong><br>
                            This instrument will immediately become visible on the public Storefront and Search algorithms
                            again.
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="products.php" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="unarchive">
                            <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                            <button type="submit" class="btn btn-success fw-bold px-4">Yes, Unarchive Live</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>