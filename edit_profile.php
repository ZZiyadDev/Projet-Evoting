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
$message_type = "success";

// 2. Handle Form Submission
if (isset($_POST['update_profile'])) {
    $description = $_POST['description'];
    $photo_path_to_update = null;
    $upload_error = false;
    
    // A. Handle Image Upload
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/";
        // Create unique name to prevent overwriting: "candidate_ID_filename"
        $target_file = $target_dir . "candidate_" . $user_id . "_" . basename($_FILES["photo"]["name"]);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                $photo_path_to_update = $target_file;
            } else {
                $upload_error = true;
            }
        } else {
            $upload_error = true;
            $message = "Invalid file type. Only JPG, JPEG, PNG, GIF, WEBP allowed.";
            $message_type = "danger";
        }
    }

    // B. Update Database with Prepared Statement
    if (!$upload_error) {
        if ($photo_path_to_update !== null) {
            $sql = "UPDATE candidates SET description = ?, photo_path = ? WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $description, $photo_path_to_update, $user_id);
        } else {
            $sql = "UPDATE candidates SET description = ? WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $description, $user_id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Profile updated successfully!";
            $message_type = "success";
            if ($upload_error) {
                 $message = "Profile description updated, but there was an error uploading the photo.";
                 $message_type = "warning";
            }
        } else {
            $message = "Error updating database: " . mysqli_stmt_error($stmt);
            $message_type = "danger";
        }
        mysqli_stmt_close($stmt);
    }
}

// 3. Fetch current data to show in the form
$stmt = mysqli_prepare($conn, "SELECT * FROM candidates WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$current_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="cand_dashboard.php">🗳️ Candidate Dashboard</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <a href="cand_dashboard.php">← Back to Dashboard</a>
        <hr>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Your Campaign Profile ✍️</h4>
                    </div>
                    <div class="card-body">
                        <?php if($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="description" class="form-label"><strong>Campaign Manifesto (Description):</strong></label>
                                <textarea id="description" name="description" class="form-control" rows="8" placeholder="Write your goals, platform, and promises to the voters here..."><?php echo htmlspecialchars($current_data['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="photo" class="form-label"><strong>Upload Photo:</strong></label><br>
                                <?php 
                                    if(!empty($current_data['photo_path'])) {
                                        echo "<img src='" . htmlspecialchars($current_data['photo_path']) . "' class='img-fluid rounded mb-2' style='max-width: 200px;'><br>";
                                        echo "<small class='text-muted'>Current photo is shown above. Upload a new file to replace it.</small><br>";
                                    }
                                ?>
                                <input type="file" id="photo" name="photo" class="form-control mt-2" accept="image/*">
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>