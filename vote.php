<?php
// File: vote.php
session_start();
require('includes/db.php');

// 1. Security Check: Kick out if not a Voter
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'voter') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// 2. Check Eligibility (Have they voted already?)
$query_check = "SELECT has_voted FROM users WHERE id='$user_id'";
$result_check = mysqli_query($conn, $query_check);
$row = mysqli_fetch_assoc($result_check);
$has_voted = $row['has_voted'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Voting Page</title>
    <style>
        .candidate-card {
            border: 1px solid #ccc;
            padding: 15px;
            margin: 10px;
            width: 200px;
            display: inline-block;
            text-align: center;
        }
        .voted-message { color: red; font-weight: bold; font-size: 1.2em; }
        img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; }
    </style>
</head>
<body>
    <h1>Election 2025</h1>
    <p>Welcome, <?php echo $user_name; ?> | <a href="logout.php">Logout</a></p>
    <hr>
    <p style="text-align:center;">
        <a href="campaigns.php" style="font-size: 1.2em; font-weight: bold; color: blue;">
            📖 Read Candidate Campaigns / Manifestos
        </a>
    </p>

    <?php if ($has_voted == 1): ?>
        
        <div class="voted-message">
            <p>You have already voted.</p>
            <p>Thank you for participating!</p>
        </div>

    <?php else: ?>

        <h3>Please select your candidate:</h3>
        
        <?php
        // Fetch candidates joining with users table to get names
        $sql = "SELECT candidates.id, users.full_name, candidates.description, candidates.photo_path 
                FROM candidates 
                JOIN users ON candidates.user_id = users.id";
        $candidates = mysqli_query($conn, $sql);

        while ($cand = mysqli_fetch_assoc($candidates)) {
            // Use a default image if none exists
            $photo = !empty($cand['photo_path']) ? $cand['photo_path'] : 'https://via.placeholder.com/100';
            
            echo "<div class='candidate-card'>";
            echo "<img src='$photo'><br>";
            echo "<h4>" . $cand['full_name'] . "</h4>";
            echo "<p>" . $cand['description'] . "</p>";
            // The Link to Vote
            echo "<a href='submit_vote.php?id=" . $cand['id'] . "'>
                    <button>VOTE</button>
                  </a>";
            echo "</div>";
        }
        ?>

    <?php endif; ?>

</body>
</html>