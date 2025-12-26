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
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $party_id = (int)$_POST['party_id'];
    $district_id = (int)$_POST['district_id'];
    $rank = (int)$_POST['rank'];
    
    // Validate inputs
    if (empty($fullname) || empty($username) || empty($password) || empty($party_id) || empty($district_id) || $rank <= 0) {
        $message = "<div class='alert alert-danger'>All fields are required, and rank must be a positive number.</div>";
    } else {
        mysqli_begin_transaction($conn);
        try {
            // Check if username exists
            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                throw new Exception("This username is already taken.");
            }
            mysqli_stmt_close($stmt);

            // A. Insert into USERS table
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'candidate';
            $sql_user = "INSERT INTO users (username, password, full_name, role, district_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql_user);
            mysqli_stmt_bind_param($stmt, "ssssi", $username, $hashed_password, $fullname, $role, $district_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating user account: " . mysqli_stmt_error($stmt));
            }
            
            $new_user_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            // B. Insert into CANDIDATES table
            $sql_cand = "INSERT INTO candidates (user_id, party_id, district_id, rank) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql_cand);
            mysqli_stmt_bind_param($stmt, "iiii", $new_user_id, $party_id, $district_id, $rank);
            if (!mysqli_stmt_execute($stmt)) {
                 throw new Exception("Error creating candidate profile: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);

            // If all good, commit
            mysqli_commit($conn);
            $message = "<div class='alert alert-success'>Candidate '" . htmlspecialchars($fullname) . "' created successfully!</div>";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $message = "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Candidate</title>
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
                             <div class="row align-items-end">
                                <div class="col-md-5 mb-3">
                                    <label class="form-label">Political Party</label>
                                    <select name="party_id" class="form-select" required>
                                        <option value="">-- Select Party --</option>
                                        <?php 
                                        mysqli_data_seek($parties_result, 0);
                                        while($p = mysqli_fetch_assoc($parties_result)) {
                                            echo "<option value='{$p['id']}'>".htmlspecialchars($p['name'])."</option>";
                                        } ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Electoral District</label>
                                     <select name="district_id" class="form-select" required>
                                        <option value="">-- Select District --</option>
                                        <?php 
                                        mysqli_data_seek($districts_result, 0);
                                        while($d = mysqli_fetch_assoc($districts_result)) {
                                            echo "<option value='{$d['id']}'>".htmlspecialchars($d['name'])."</option>";
                                        } ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">List Rank</label>
                                    <input type="number" name="rank" class="form-control" required value="1" min="1">
                                </div>
                            </div>
                            <div class="d-grid mt-3">
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