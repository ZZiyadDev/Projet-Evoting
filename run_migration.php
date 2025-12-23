<?php
require('includes/db.php');

// Check if description column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM political_parties LIKE 'description'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE `political_parties` ADD `description` TEXT DEFAULT NULL;";
    if (mysqli_query($conn, $sql)) {
        echo "Migration successful: Description column added.";
    } else {
        echo "Migration failed: " . mysqli_error($conn);
    }
} else {
    echo "Migration skipped: Description column already exists.";
}
?>