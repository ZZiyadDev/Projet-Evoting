<?php
// File: create_candidate.php
session_start();
require('includes/db.php');

// 1. Security Check: Kick out if not Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// 2. Handle Form Submission
$message = "";
if (isset($_POST['add_candidate'])) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password']; 

    // A. Insert into USERS table
    $sql_user = "INSERT INTO users (username, password, full_name, role) 
                 VALUES ('$username', '$password', '$fullname', 'candidate')";
    
    if ($conn->query($sql_user) === TRUE) {
        // B. Get the ID of the new user we just created
        $new_user_id = $conn->insert_id;

        // C. Insert into CANDIDATES table (linked by ID)
        $sql_cand = "INSERT INTO candidates (user_id) VALUES ('$new_user_id')";
        
        if ($conn->query($sql_cand) === TRUE) {
            $message = "Candidate '$fullname' created successfully!";
        } else {
            $message = "Error creating candidate profile: " . $conn->error;
        }
    } else {
        $message = "Error creating user: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Candidate</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .form-box { border: 1px solid #ccc; padding: 20px; width: 300px; margin-top: 20px;}
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        input { width: 90%; padding: 8px; margin: 5px 0; }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <h1>Create New Candidate Account</h1>
    <a href="admin_dashboard.php">← Back to Admin Dashboard</a>
    <hr>

    <?php if($message) echo "<p class='success'>$message</p>"; ?>

    <div class="form-box">
        <form method="post">
            <label>Full Name:</label><br>
            <input type="text" name="fullname" required placeholder="e.g. Jane Doe"><br><br>

            <label>Username:</label><br>
            <input type="text" name="username" required placeholder="e.g. janedoe"><br><br>

            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>

            <button type="submit" name="add_candidate">Add Candidate</button>
        </form>
    </div>
</body>
</html>