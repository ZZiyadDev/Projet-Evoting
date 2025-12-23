<?php
// File: create_candidate.php
include('includes/auth_session.php');
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
require('includes/db.php');

$message = "";

// Fetch data for dropdowns
$parties_result = mysqli_query($conn, "SELECT * FROM political_parties ORDER BY name ASC");
$districts_result = mysqli_query($conn, "SELECT * FROM electoral_districts ORDER BY name ASC");

// Handle Form Submission
if (isset($_POST['add_candidate'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $party_id = (int)$_POST['party_id'];
    $district_id = (int)$_POST['district_id'];
    
    // Validate inputs
    if (empty($fullname) || empty($username) || empty($password) || empty($party_id) || empty($district_id)) {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // A. Insert into USERS table
        $sql_user = "INSERT INTO users (username, password, full_name, role, district_id) 
                     VALUES ('$username', '$hashed_password', '$fullname', 'candidate', '$district_id')";
        
        if ($conn->query($sql_user) === TRUE) {
            $new_user_id = $conn->insert_id;

            // B. Insert into CANDIDATES table
            $sql_cand = "INSERT INTO candidates (user_id, party_id, district_id) VALUES ('$new_user_id', '$party_id', '$district_id')";
            
            if ($conn->query($sql_cand) === TRUE) {
                $message = "<div class='alert alert-success'>Candidate '$fullname' created successfully!</div>";
            } else {
                // Clean up user if candidate creation fails
                $conn->query("DELETE FROM users WHERE id = $new_user_id");
                $message = "<div class='alert alert-danger'>Error creating candidate profile: " . $conn->error . "</div>";
            }
        } else {
            if ($conn->errno == 1062) { // Duplicate entry for username
                 $message = "<div class='alert alert-danger'>Error: This username is already taken.</div>";
            } else {
                 $message = "<div class='alert alert-danger'>Error creating user account: " . $conn->error . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Candidate</title>
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
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Create New Candidate Account</h4>
                    </div>
                    <div class="card-body">
                        <?php if($message) echo $message; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="fullname" class="form-control" required placeholder="e.g. Jamal Benkirane">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" required placeholder="e.g. jbenkirane">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <hr>
                             <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Political Party</label>
                                    <select name="party_id" class="form-select" required>
                                        <option value="">-- Select Party --</option>
                                        <?php while($p = mysqli_fetch_assoc($parties_result)) {
                                            echo "<option value='{$p['id']}'>".htmlspecialchars($p['name'])."</option>";
                                        } ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Electoral District</label>
                                     <select name="district_id" class="form-select" required>
                                        <option value="">-- Select District --</option>
                                        <?php while($d = mysqli_fetch_assoc($districts_result)) {
                                            echo "<option value='{$d['id']}'>".htmlspecialchars($d['name'])."</option>";
                                        } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="add_candidate" class="btn btn-success">Add Candidate</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>