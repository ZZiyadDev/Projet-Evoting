<?php
// File: admin_dashboard.php
include('includes/auth_session.php');
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php"); // Redirect non-admins
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">🗳️ E-Voting Admin</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Admin Dashboard</h2>
            <p class="lead">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>
        <hr>

        <h4>Election Management</h4>
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-people-fill fs-1 text-primary"></i>
                        <h5 class="card-title mt-2">Manage Parties</h5>
                        <p class="card-text">Add, edit, or remove political parties from the system.</p>
                        <a href="admin_parties.php" class="btn btn-primary">Go to Parties</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-geo-alt-fill fs-1 text-primary"></i>
                        <h5 class="card-title mt-2">Manage Districts</h5>
                        <p class="card-text">Define the electoral districts for the election.</p>
                        <a href="admin_districts.php" class="btn btn-primary">Go to Districts</a>
                    </div>
                </div>
            </div>
             <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-person-plus-fill fs-1 text-primary"></i>
                        <h5 class="card-title mt-2">Create Candidate</h5>
                        <p class="card-text">Create user accounts for new candidates running in the election.</p>
                        <a href="create_candidate.php" class="btn btn-primary">Create Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-list-ol fs-1 text-primary"></i>
                        <h5 class="card-title mt-2">Manage Candidates</h5>
                        <p class="card-text">Set the rank of candidates within their party lists for each district.</p>
                        <a href="admin_candidates.php" class="btn btn-primary">Manage Ranks</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-bar-chart-line-fill fs-1 text-info"></i>
                        <h5 class="card-title mt-2">View Results</h5>
                        <p class="card-text">See the current vote counts and election results.</p>
                        <a href="results.php" class="btn btn-primary">View Results</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>