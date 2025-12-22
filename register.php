<?php
// File: register.php
session_start();
require('includes/db.php');

$message = "";

if (isset($_POST['register'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); 

    // Check if username already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $message = "<div class='alert alert-danger'>Username already taken. Please choose another.</div>";
    } else {
        // Insert new VOTER
        $sql = "INSERT INTO users (username, password, full_name, role) VALUES ('$username', '$password', '$fullname', 'voter')";
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-success'>Account created! <a href='index.php'>Login here</a></div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Voter Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>🗳️ Voter Sign Up</h4>
                    </div>
                    <div class="card-body">
                        
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