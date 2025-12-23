<?php
// File: debug_login.php
require('includes/db.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Debugger</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { padding: 20px; } </style>
</head>
<body>
    <div class="container">
        <h2>Login Debugger</h2>
        <p>Enter the credentials for the user you just registered.</p>

        <form method="post">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Run Debug</button>
        </form>

        <?php
        if (isset($_POST['username'])) {
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $password = $_POST['password'];

            echo "<div class='mt-4 p-3 bg-light border'>";
            echo "<h4>Debug Output:</h4>";

            // 1. Fetch user from DB
            $query = "SELECT * FROM users WHERE username='$username'";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                echo "<p>✅ User '<strong>" . htmlspecialchars($row['username']) . "</strong>' found in the database.</p>";
                
                // 2. Get the stored hash
                $stored_hash = $row['password'];
                echo "<p>🔍 Stored Hash: <pre>" . htmlspecialchars($stored_hash) . "</pre></p>";
                echo "<p>📏 Length of Stored Hash: <strong>" . strlen($stored_hash) . "</strong> characters.</p>";

                // 3. Verify the password against the hash
                echo "<p>Checking password_verify()...</p>";
                $is_password_correct = password_verify($password, $stored_hash);

                if ($is_password_correct) {
                    echo "<p class='text-success fw-bold'>✅ SUCCESS: The password is correct.</p>";
                    echo "<p>Login should be working. If you are still having issues, there might be a problem with session handling or redirects.</p>";
                } else {
                    echo "<p class='text-danger fw-bold'>❌ FAILURE: The provided password does not match the stored hash.</p>";
                    echo "<p>This is the most likely reason login is failing. Please ensure you are typing the correct password. If you are, the hash may have been saved incorrectly during registration.</p>";
                }

            } else {
                echo "<p class='text-danger fw-bold'>❌ FAILURE: No user found with the username '<strong>" . htmlspecialchars($username) . "</strong>'.</p>";
            }

            echo "</div>";
        }
        ?>
    </div>
</body>
</html>