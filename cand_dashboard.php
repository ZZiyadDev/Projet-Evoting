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
<head>
    <title>Candidate Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
        <div class="d-flex justify-content-between align-items-center">
            <h2>Welcome, Candidate <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
        </div>
        <hr>

        <h4>Your Menu</h4>
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-pencil-square fs-1 text-primary"></i>
                        <h5 class="card-title mt-2">Edit Profile</h5>
                        <p class="card-text">Update your campaign manifesto and upload a new photo.</p>
                        <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-bar-chart-line-fill fs-1 text-info"></i>
                        <h5 class="card-title mt-2">View Results</h5>
                        <p class="card-text">See the current vote counts and election results.</p>
                        <a href="results.php" class="btn btn-info">View Results</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>