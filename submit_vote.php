<?php
// File: submit_vote.php
session_start();
require('includes/db.php');

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'voter') {
    header("Location: index.php");
    exit();
}

// Get the candidate ID from the URL link
if (isset($_GET['id'])) {
    $candidate_id = $_GET['id'];
    $voter_id = $_SESSION['user_id'];

    // 1. Increment Candidate Vote Count
    $sql_vote = "UPDATE candidates SET vote_count = vote_count + 1 WHERE id='$candidate_id'";
    mysqli_query($conn, $sql_vote);

    // 2. Mark Voter as "Has Voted"
    $sql_mark = "UPDATE users SET has_voted = 1 WHERE id='$voter_id'";
    mysqli_query($conn, $sql_mark);

    // 3. Redirect back to vote page
    header("Location: vote.php");
} else {
    header("Location: vote.php");
}
?>