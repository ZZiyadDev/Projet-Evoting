<?php
// File: index.php
session_start();
require('includes/db.php');

$error = "";

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; 

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['district_id'] = (int)$row['district_id'];

            if ($row['role'] == 'admin') header("Location: admin_dashboard.php");
            elseif ($row['role'] == 'candidate') header("Location: cand_dashboard.php");
            else header("Location: vote.php");
            exit(); // It's good practice to exit after a header redirect
        } else {
            $error = "<div class='alert alert-danger'>Invalid Username or Password</div>";
        }
    } else {
        $error = "<div class='alert alert-danger'>Invalid Username or Password</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>E-Voting Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">

    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- Left Side with Info -->
            <div class="col-md-6 login-info-side">
                <div class="login-info-content">
                    <h1 class="text-white">E-Voting System</h1>
                    <p class="text-white-50">Modern, Secure, and Transparent Elections.</p>
                </div>
            </div>

            <!-- Right Side with Form -->
            
            <div class="col-md-6 login-form-side">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d5/Coat_of_arms_of_Morocco.svg/2001px-Coat_of_arms_of_Morocco.svg.png" alt="Logo" class="login-logo mb-4">
                <div class="login-form-container">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <h3 class="text-center mb-4">Login</h3>
                            
                            <?php echo $error; ?>

                            <form method="post">
                                <div class="mb-4">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control form-control-lg" placeholder="Enter username" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter password" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="submit" class="btn btn-primary btn-lg">Login</button>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-center bg-white py-3">
                            <small>New Voter? <a href="register.php">Create an Account</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>