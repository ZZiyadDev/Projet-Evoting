<?php
// File: register.php
session_start();
require('includes/db.php');

$message = "";

// Fetch electoral districts for the dropdown
$districts_result = mysqli_query($conn, "SELECT * FROM electoral_districts ORDER BY name ASC");


if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $district_id = (int)$_POST['district_id'];
    
    // Basic validation
    if (empty($district_id)) {
        $message = "<div class='alert alert-danger'>Please select your electoral district.</div>";
    } elseif (empty($fullname) || empty($username) || empty($password)) {
        $message = "<div class='alert alert-danger'>Please fill in all fields.</div>";
    }
    else {
        // Check if username already exists using a prepared statement
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $message = "<div class='alert alert-danger'>Username already taken. Please choose another.</div>";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            // Insert new VOTER using a prepared statement
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'voter';
            
            $sql = "INSERT INTO users (username, password, full_name, role, district_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $username, $hashed_password, $fullname, $role, $district_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "<div class='alert alert-success'>Account created! <a href='index.php'>Login here</a></div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . mysqli_stmt_error($stmt) . "</div>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Voter Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>🗳️ Voter Sign Up</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php echo $message; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="fullname" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="district_id" class="form-label">Electoral District</label>
                                <select class="form-select" id="district_id" name="district_id" required>
                                    <option value="">-- Select Your District --</option>
                                    <?php 
                                    if (mysqli_num_rows($districts_result) > 0) {
                                        while ($district = mysqli_fetch_assoc($districts_result)) {
                                            echo "<option value='" . $district['id'] . "'>" . htmlspecialchars($district['name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="register" class="btn btn-primary">Create Account</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        Already have an account? <a href="index.php">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>