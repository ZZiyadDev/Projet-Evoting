<?php
// File: admin_dashboard.php
session_start();

// Security Check: Kick out if not Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        ul { list-style-type: square; }
        li { margin: 10px 0; }
        a { text-decoration: none; color: #0000EE; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Admin Panel</h1>
    <p>Welcome, System Administrator | <a href="logout.php">Logout</a></p>
    <hr>

    <h3>Actions:</h3>
    <ul>
        <li><a href="results.php"><strong>View Election Results</strong></a></li>
        <li>Manage Election</li>
        <li><a href="create_candidate.php"><strong>Create Candidate Account</strong></a></li>
        <li>Import Voter List</li>
    </ul>
</body>
</html>