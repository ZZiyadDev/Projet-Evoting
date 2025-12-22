<?php
// File: index.php
session_start();
require('includes/db.php');

$error = "";

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; 

    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['name'] = $row['full_name'];

        if ($row['role'] == 'admin') header("Location: admin_dashboard.php");
        elseif ($row['role'] == 'candidate') header("Location: cand_dashboard.php");
        else header("Location: vote.php");
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
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm mt-5">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Secure Login 🔒</h3>
                        
                        <?php echo $error; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="submit" class="btn btn-success">Login</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center bg-white">
                        <small>New Voter? <a href="register.php">Create an Account</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>