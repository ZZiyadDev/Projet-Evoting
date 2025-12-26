<?php
// File: admin_parties.php
include('includes/auth_session.php');
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php"); // Redirect non-admins
    exit();
}
require('includes/db.php');

$message = "";

// Handle form submission for adding a new party
if (isset($_POST['add_party'])) {
    $party_name = $_POST['party_name'];
    $description = $_POST['description'];
    $logo_path = NULL;

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = basename($_FILES['logo']['name']);
        $file_path = $upload_dir . time() . '_' . $file_name; // Add timestamp to avoid conflicts
        $file_type = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        // Validate file type (images only)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $file_path)) {
                $logo_path = $file_path;
            } else {
                $message = "<div class='alert alert-danger'>Error uploading logo file. Check folder permissions.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Invalid file type. Only JPG, JPEG, PNG, GIF, JFIF allowed.</div>";
        }
    }

    // Basic validation
    if (!empty($party_name)) {
        $sql = "INSERT INTO political_parties (name, description, logo_path) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $party_name, $description, $logo_path);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "<div class='alert alert-success'>Political party added successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error adding party: " . mysqli_stmt_error($stmt) . "</div>";
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "<div class='alert alert-danger'>Party name cannot be empty.</div>";
    }
}

// Fetch all political parties to display
$parties_result = mysqli_query($conn, "SELECT * FROM political_parties ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Political Parties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">Admin Dashboard</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Add New Political Party</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="party_name" class="form-label">Party Name</label>
                                <input type="text" class="form-control" id="party_name" name="party_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Party Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Describe the party's platform, goals, or slogan..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="logo" class="form-label">Party Logo</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*,.jfif">
                                <small class="form-text text-muted">Upload JPG, JPEG, PNG, GIF, or JFIF (max 2MB).</small>
                            </div>
                            <button type="submit" name="add_party" class="btn btn-primary">Add Party</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Existing Political Parties</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Logo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($parties_result) > 0): ?>
                                    <?php while($party = mysqli_fetch_assoc($parties_result)): ?>
                                        <tr>
                                            <td><?php echo $party['id']; ?></td>
                                            <td><?php echo htmlspecialchars($party['name']); ?></td>
                                            <td><?php echo htmlspecialchars($party['description'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($party['logo_path']): ?>
                                                    <img src="<?php echo htmlspecialchars($party['logo_path']); ?>" alt="Logo" style="width: 100px; height: 100px; object-fit: cover;">
                                                <?php else: ?>
                                                    No Logo
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No political parties found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>