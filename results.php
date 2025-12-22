<?php
// File: results.php
session_start();
require('includes/db.php');

// Security: Allow Admin AND Candidates to see results (or everyone, your choice)
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Election Results</title>
    <style>
        table { width: 50%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Election Results 📊</h1>
    <p>Logged in as: <?php echo $_SESSION['name']; ?> | <a href="logout.php">Logout</a></p>
    <hr>
    
    <h3>Live Vote Count:</h3>
    
    <table>
        <tr>
            <th>Rank</th>
            <th>Candidate Name</th>
            <th>Votes</th>
        </tr>

        <?php
        // Query: Get candidates and sort by votes (Highest first)
        $sql = "SELECT users.full_name, candidates.vote_count 
                FROM candidates 
                JOIN users ON candidates.user_id = users.id 
                ORDER BY vote_count DESC";
        
        $result = mysqli_query($conn, $sql);
        $rank = 1;

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>#" . $rank . "</td>";
                echo "<td>" . $row['full_name'] . "</td>";
                echo "<td><strong>" . $row['vote_count'] . "</strong></td>";
                echo "</tr>";
                $rank++;
            }
        } else {
            echo "<tr><td colspan='3'>No candidates found.</td></tr>";
        }
        ?>
    </table>

    <br>
    <?php if($_SESSION['role'] == 'admin'): ?>
        <a href="admin_dashboard.php">Back to Admin Panel</a>
    <?php elseif($_SESSION['role'] == 'candidate'): ?>
        <a href="cand_dashboard.php">Back to Dashboard</a>
    <?php else: ?>
        <a href="vote.php">Back to Home</a>
    <?php endif; ?>

</body>
</html>