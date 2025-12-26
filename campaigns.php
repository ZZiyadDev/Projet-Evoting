<?php
// File: campaigns.php
include('includes/auth_session.php');
require('includes/db.php');

// Get the logged-in user's district ID and name
$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT u.district_id, d.name AS district_name FROM users u LEFT JOIN electoral_districts d ON u.district_id = d.id WHERE u.id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);
$district_id = $user_data['district_id'] ?? null;
$district_name = $user_data['district_name'] ?? 'Unknown';
mysqli_stmt_close($stmt);

// Fetch candidates for the user's district
$candidates = [];
if ($district_id) {
    $sql = "SELECT u.full_name, p.name as party_name, c.description, c.photo_path, c.rank 
            FROM candidates c
            JOIN users u ON c.user_id = u.id
            JOIN political_parties p ON c.party_id = p.id
            WHERE c.district_id = ?
            ORDER BY p.name, c.rank";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $district_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $candidates[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Candidate Campaigns</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="vote.php">🗳️ E-Voting Portal</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="btn btn-outline-light" href="vote.php">← Back to Voting</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="text-center mb-4">
            <h1><i class="bi bi-megaphone-fill"></i> Candidate Campaigns</h1>
            <p class="lead">Showing candidates for your electoral district: <strong class="text-primary"><?php echo htmlspecialchars($district_name); ?></strong></p>
        </div>
        <hr class="mb-4">

        <?php if (!empty($candidates)): ?>
            <div class="row g-4">
            <?php foreach ($candidates as $candidate): ?>
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="row g-0">
                            <div class="col-md-3 d-flex align-items-center justify-content-center p-3">
                                <?php 
                                $photo = !empty($candidate['photo_path']) ? htmlspecialchars($candidate['photo_path']) : 'https://via.placeholder.com/200?text=No+Photo';
                                ?>
                                <img src="<?php echo $photo; ?>" class="img-fluid rounded" alt="Candidate <?php echo htmlspecialchars($candidate['full_name']); ?>">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <h4 class="card-title"><?php echo htmlspecialchars($candidate['full_name']); ?></h4>
                                    <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($candidate['party_name']); ?> (Rank <?php echo $candidate['rank']; ?>)</h6>
                                    <hr>
                                    <p class="card-text" style="white-space: pre-wrap;"><?php echo !empty($candidate['description']) ? htmlspecialchars($candidate['description']) : 'This candidate has not provided a manifesto.'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php elseif ($district_id): ?>
            <div class="alert alert-info text-center">There are no candidates registered in your district yet.</div>
        <?php else: ?>
            <div class="alert alert-warning text-center">Your account is not assigned to a district. Please contact an administrator.</div>
        <?php endif; ?>
    </div>

</body>
</html>