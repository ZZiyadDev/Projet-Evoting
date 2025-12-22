<?php
// File: edit_profile.php
session_start();
require('includes/db.php');

// 1. Security Check: Only Candidates allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'candidate') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 2. Handle Form Submission
if (isset($_POST['update_profile'])) {
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // A. Handle Image Upload
    $photo_sql_part = ""; // We only update photo if a new one is selected
    
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/";
        // Create unique name to prevent overwriting: "candidate_ID_filename"
        $target_file = $target_dir . "candidate_" . $user_id . "_" . basename($_FILES["photo"]["name"]);
        
        // Move the file from temporary storage to our folder
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $photo_sql_part = ", photo_path='$target_file'";
        } else {
            $message = "Error uploading photo.";
        }
    }

    // B. Update Database
    $sql = "UPDATE candidates SET description='$description' $photo_sql_part WHERE user_id='$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating database: " . mysqli_error($conn);
    }
}

// 3. Fetch current data to show in the form
$query = "SELECT * FROM candidates WHERE user_id='$user_id'";
$result = mysqli_query($conn, $query);
$current_data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .form-box { max-width: 500px; padding: 20px; border: 1px solid #ccc; }
        textarea { width: 100%; height: 150px; }
        .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Edit Your Campaign Profile ✍️</h1>
    <a href="cand_dashboard.php">← Back to Dashboard</a>
    <hr>

    <?php if($message) echo "<p class='success'>$message</p>"; ?>

    <div class="form-box">
        <form method="post" enctype="multipart/form-data">
            
            <label><strong>Campaign Manifesto (Description):</strong></label><br>
            <textarea name="description" placeholder="Write your goals here..."><?php echo $current_data['description']; ?></textarea>
            <br><br>

            <label><strong>Upload Photo:</strong></label><br>
            <?php 
                if(!empty($current_data['photo_path'])) {
                    echo "<img src='" . $current_data['photo_path'] . "' width='100'><br>";
                    echo "<small>Current photo above. Upload new one to change.</small><br>";
                }
            ?>
            <input type="file" name="photo" accept="image/*">
            <br><br>

            <button type="submit" name="update_profile">Save Changes</button>
        </form>
    </div>
</body>
</html>