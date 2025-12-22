<?php
// File: cand_dashboard.php
session_start();

// Security Check: Kick out if not Candidate
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'candidate') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<body>
    <h1>Candidate Dashboard</h1>
    <p>Welcome, Candidate <?php echo $_SESSION['name']; ?></p>
    <hr>
    
    <h3>Your Menu (From Diagram):</h3>
     <ul>
      <li><a href="edit_profile.php">Edit Profile (Upload Photo)</a></li>
      <li><a href="results.php">View Statistics / Results</a></li> </ul>
     </ul>

    <a href="logout.php">Logout</a>
</body>
</html>