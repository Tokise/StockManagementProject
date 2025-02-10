<?php
session_start();
require_once '../../config/db.php';
require_once '../../includes/permissions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login/index.php");
    exit();
}

// Check if user has permission to manage products
requirePermission('manage_products');

// Fetch all categories with product counts
$sql = "
    SELECT c.*, 
           COUNT(p.product_id) as product_count,
           COALESCE(u1.full_name, 'System') as created_by_name,
           COALESCE(u2.full_name, '') as updated_by_name
    FROM categories c
    LEFT JOIN products p ON c.category_id = p.category_id
    LEFT JOIN users u1 ON c.created_by = u1.user_id
    LEFT JOIN users u2 ON c.updated_by = u2.user_id
    GROUP BY c.category_id, c.name, c.description, c.created_at, c.created_by, c.updated_at, c.updated_by,
             u1.full_name, u2.full_name
    ORDER BY c.name
";
$categories = fetchAll($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexInvent - Categories Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>

<?php include '../../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Categories Management</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../index.php">Products</a></li>
                        <li class="breadcrumb-item active">Categories</li>
                    </ol>
                </nav>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-lg"></i> Add Category
            </button>
        </div>

        <!-- Categories Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="categoriesTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                                    <td>
                                        <a href="../index.php?category=<?php echo $category['category_id']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <?php echo $category['product_count']; ?> products
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($category['created_by_name']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($category['created_at'])); ?></td>
                                    <td>
                                        <?php 
                                        if ($category['updated_at']) {
                                            echo date('Y-m-d H:i', strtotime($category['updated_at']));
                                            echo '<br><small class="text-muted">by ' . 
                                                 htmlspecialchars($category['updated_by_name']) . '</small>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="editCategory(<?php echo $category['category_id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($category['product_count'] == 0): ?>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteCategory(<?php echo $category['category_id']; ?>)">
                                            <i class="bi bi-trash"></i>
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">Please enter category name</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCategory()">Save Category</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm" class="needs-validation" novalidate>
                    <input type="hidden" id="edit_category_id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                        <div class="invalid-feedback">Please enter category name</div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateCategory()">Update Category</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#categoriesTable').DataTable({
        "order": [[0, "asc"]],
        "pageLength": 25
    });
});

function saveCategory() {
    const form = document.getElementById('addCategoryForm');
    if (form.checkValidity()) {
        const formData = {
            name: $('#name').val(),
            description: $('#description').val()
        };

        $.ajax({
            url: 'ajax/save_category.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Category saved successfully'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(response.error || 'Failed to save category');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.error || 'Failed to save category'
                });
            }
        });
    }
    form.classList.add('was-validated');
}

function editCategory(categoryId) {
    $.get('ajax/get_category.php', { id: categoryId }, function(response) {
        if (response.success) {
            const category = response.data;
            $('#edit_category_id').val(category.category_id);
            $('#edit_name').val(category.name);
            $('#edit_description').val(category.description);
            $('#editCategoryModal').modal('show');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.error || 'Failed to load category details'
            });
        }
    });
}

function updateCategory() {
    const form = document.getElementById('editCategoryForm');
    if (form.checkValidity()) {
        const formData = {
            category_id: $('#edit_category_id').val(),
            name: $('#edit_name').val(),
            description: $('#edit_description').val()
        };

        $.ajax({
            url: 'ajax/update_category.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Category updated successfully'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(response.error || 'Failed to update category');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.error || 'Failed to update category'
                });
            }
        });
    }
    form.classList.add('was-validated');
}

function deleteCategory(categoryId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax/delete_category.php',
                method: 'POST',
                data: { category_id: categoryId },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Category deleted successfully'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(response.error || 'Failed to delete category');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.error || 'Failed to delete category'
                    });
                }
            });
        }
    });
}
</script>

</body>
</html> 