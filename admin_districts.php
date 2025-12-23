<?php
// File: admin_districts.php
include('includes/auth_session.php');
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php"); // Redirect non-admins
    exit();
}
require('includes/db.php');

$message = "";

// Handle form submission for adding a new district
if (isset($_POST['add_district'])) {
    $district_name = mysqli_real_escape_string($conn, $_POST['district_name']);
    $seats = (int)$_POST['seats'];

    if (!empty($district_name) && $seats > 0) {
        $sql = "INSERT INTO electoral_districts (name, available_seats) VALUES ('$district_name', '$seats')";
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-success'>Electoral district added successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error adding district: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>District name cannot be empty and seats must be a positive number.</div>";
    }
}

// Fetch all electoral districts to display
$districts_result = mysqli_query($conn, "SELECT * FROM electoral_districts ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Electoral Districts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <h4>Add New Electoral District</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="district_name" class="form-label">District Name</label>
                                <input type="text" class="form-control" id="district_name" name="district_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="seats" class="form-label">Available Seats</label>
                                <input type="number" class="form-control" id="seats" name="seats" min="1" value="1" required>
                            </div>
                            <button type="submit" name="add_district" class="btn btn-primary">Add District</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Existing Electoral Districts</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Seats</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($districts_result) > 0): ?>
                                    <?php while($district = mysqli_fetch_assoc($districts_result)): ?>
                                        <tr>
                                            <td><?php echo $district['id']; ?></td>
                                            <td><?php echo htmlspecialchars($district['name']); ?></td>
                                            <td><?php echo $district['available_seats']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No electoral districts found.</td>
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