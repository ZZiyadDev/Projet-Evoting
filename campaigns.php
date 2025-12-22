<?php
// File: campaigns.php
session_start();
require('includes/db.php');

// Allow Voters, Admins, and Candidates to view this
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Candidate Campaigns</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f9f9f9; }
        .campaign-card {
            background: white;
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            align-items: start;
        }
        .campaign-card img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 20px;
            border: 1px solid #ccc;
        }
        .content { max-width: 800px; }
        h2 { margin-top: 0; color: #333; }
        .manifesto { color: #555; line-height: 1.6; white-space: pre-wrap; } /* pre-wrap keeps formatting */
        .back-btn { 
            display: inline-block; padding: 10px 20px; background: #333; 
            color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px;
        }
        .back-btn:hover { background: #555; }
    </style>
</head>
<body>
    
    <a href="vote.php" class="back-btn">← Back to Voting</a>

    <h1>📢 Candidate Campaigns</h1>
    <p>Read about the candidates before making your choice.</p>
    <hr>

    <?php
    // Fetch candidates + user info
    $sql = "SELECT users.full_name, candidates.description, candidates.photo_path 
            FROM candidates 
            JOIN users ON candidates.user_id = users.id";
    
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Default image if missing
            $photo = !empty($row['photo_path']) ? $row['photo_path'] : 'https://via.placeholder.com/150';
            // Default text if missing
            $text = !empty($row['description']) ? $row['description'] : "This candidate has not posted a campaign manifesto yet.";

            echo "<div class='campaign-card'>";
            echo "<img src='$photo' alt='Candidate Photo'>";
            echo "<div class='content'>";
            echo "<h2>" . $row['full_name'] . "</h2>";
            echo "<div class='manifesto'>" . $text . "</div>";
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<p>No candidates found.</p>";
    }
    ?>

</body>
</html>