<?php
// File: submit_vote.php
include('includes/auth_session.php');
if ($_SESSION['role'] !== 'voter') {
    header("Location: index.php");
    exit();
}
require('includes/db.php');

$message_type = 'danger'; // Default message type
$message = 'An unknown error occurred.';

// Check if a party was selected
if (isset($_GET['party_id'])) {
    $party_id = (int)$_GET['party_id'];
    $user_id = $_SESSION['user_id'];

    // Get user's voting status and district
    $user_sql = "SELECT has_voted, district_id FROM users WHERE id = '$user_id'";
    $user_result = mysqli_query($conn, $user_sql);
    $user_data = mysqli_fetch_assoc($user_result);

    if ($user_data && $user_data['has_voted'] == 0 && !empty($user_data['district_id'])) {
        $district_id = $user_data['district_id'];

        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            // Use INSERT ... ON DUPLICATE KEY UPDATE to handle the vote count
            $vote_sql = "INSERT INTO party_votes (party_id, district_id, vote_count) 
                         VALUES ('$party_id', '$district_id', 1)
                         ON DUPLICATE KEY UPDATE vote_count = vote_count + 1";
            
            if (!mysqli_query($conn, $vote_sql)) {
                throw new Exception(mysqli_error($conn));
            }

            // Mark user as having voted
            $mark_voted_sql = "UPDATE users SET has_voted = 1 WHERE id = '$user_id'";
            if (!mysqli_query($conn, $mark_voted_sql)) {
                 throw new Exception(mysqli_error($conn));
            }

            // If all queries were successful, commit the transaction
            mysqli_commit($conn);
            $message_type = 'success';
            $message = 'Your vote has been cast successfully! Thank you for participating.';

        } catch (Exception $e) {
            // An error occurred, rollback the transaction
            mysqli_rollback($conn);
            $message = 'A database error occurred. Your vote was not saved. Please try again.';
        }

    } elseif ($user_data && $user_data['has_voted'] == 1) {
        $message = 'You have already cast your vote.';
    } else {
        $message = 'Invalid user data or district information missing.';
    }
} else {
    $message = 'No party selected. Please go back and choose a party to vote for.';
}

// Store the message in a session variable and redirect to a status page
$_SESSION['vote_status_message'] = $message;
$_SESSION['vote_status_type'] = $message_type;

// Redirect to vote.php which will now display the status
header("Location: vote.php");
exit();
?>
