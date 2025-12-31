<?php
// File: admin_candidates.php
include('includes/auth_session.php');
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
require('includes/db.php');

$message = "";

// Handle Rank Update
if (isset($_POST['update_rank'])) {
    $candidate_id = (int)$_POST['candidate_id'];
    $new_rank = (int)$_POST['rank'];

    if ($candidate_id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE candidates SET rank = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $new_rank, $candidate_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "<div class='alert alert-success'>Rank updated successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error updating rank: " . mysqli_stmt_error($stmt) . "</div>";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch all candidates with their details
$sql = "SELECT c.id, u.full_name, p.name AS party_name, d.name AS district_name, c.rank
        FROM candidates c
        JOIN users u ON c.user_id = u.id
        JOIN political_parties p ON c.party_id = p.id
        JOIN electoral_districts d ON c.district_id = d.id
        ORDER BY d.name, p.name, c.rank";
$candidates_result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Gérer les Candidats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">Admin Dashboard</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                     <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4>Manage Candidate Ranks</h4>
                <p class="mb-0">Set the rank of candidates within their party list for each district. Rank 1 is the highest.</p>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>District</th>
                            <th>Party</th>
                            <th>Candidate Name</th>
                            <th>Current Rank</th>
                            <th style="width: 150px;">Set New Rank</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($candidates_result) > 0): ?>
                            <?php while($cand = mysqli_fetch_assoc($candidates_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cand['district_name']); ?></td>
                                    <td><?php echo htmlspecialchars($cand['party_name']); ?></td>
                                    <td><?php echo htmlspecialchars($cand['full_name']); ?></td>
                                    <td><strong><?php echo $cand['rank']; ?></strong></td>
                                    <td>
                                        <form method="post" class="d-flex">
                                            <input type="hidden" name="candidate_id" value="<?php echo $cand['id']; ?>">
                                            <input type="number" name="rank" class="form-control form-control-sm me-2" value="<?php echo $cand['rank']; ?>" min="0">
                                            <button type="submit" name="update_rank" class="btn btn-sm btn-outline-primary">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No candidates found. <a href="create_candidate.php">Create one now</a>.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
