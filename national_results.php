<?php
// File: national_results.php
session_start();
require('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Function to allocate seats proportionally using Largest Remainder Method (Hare Quota)
function allocate_seats($party_votes, $total_seats) {
    $allocated_seats = [];
    $total_votes = array_sum($party_votes);
    if ($total_votes == 0 || $total_seats == 0) return $allocated_seats;

    $quota = $total_votes / $total_seats;
    $remainders = [];

    // First, assign seats for full quotas
    foreach ($party_votes as $party_id => $votes) {
        $seats = floor($votes / $quota);
        $allocated_seats[$party_id] = $seats;
        $remainders[$party_id] = $votes - ($seats * $quota);
    }

    // Distribute remaining seats to parties with highest remainders
    $remaining_seats = $total_seats - array_sum($allocated_seats);
    arsort($remainders); // Sort by remainder descending
    $top_parties = array_keys(array_slice($remainders, 0, $remaining_seats, true));

    foreach ($top_parties as $party_id) {
        $allocated_seats[$party_id]++;
    }

    return $allocated_seats;
}

// Fetch national vote totals
$national_results = [];
$national_total = 0;
$national_sql = "SELECT pp.id AS party_id, pp.name AS party_name, pp.logo_path, pp.description, SUM(pv.vote_count) AS total_votes
                 FROM party_votes pv
                 JOIN political_parties pp ON pv.party_id = pp.id
                 GROUP BY pp.id
                 ORDER BY total_votes DESC";
$national_query = mysqli_query($conn, $national_sql);
while ($row = mysqli_fetch_assoc($national_query)) {
    $national_results[] = $row;
    $national_total += $row['total_votes'];
}

// Determine qualified parties (6% threshold)
$qualified_parties = [];
foreach ($national_results as $res) {
    $percentage = ($national_total > 0) ? ($res['total_votes'] / $national_total) * 100 : 0;
    $res['percentage'] = $percentage;
    if ($percentage >= 6) {
        $qualified_parties[] = $res['party_id'];
    }
}

// Calculate total seats across all districts
$total_seats_national = 0;
$seats_sql = "SELECT SUM(available_seats) AS total_seats FROM electoral_districts";
$seats_result = mysqli_query($conn, $seats_sql);
$seats_data = mysqli_fetch_assoc($seats_result);
$total_seats_national = $seats_data['total_seats'];

// Allocate national seats proportionally for qualified parties
$party_votes_national = [];
foreach ($national_results as $res) {
    if (in_array($res['party_id'], $qualified_parties)) {
        $party_votes_national[$res['party_id']] = $res['total_votes'];
    }
}
$national_allocated_seats = allocate_seats($party_votes_national, $total_seats_national);

// Add seats to results
foreach ($national_results as &$res) {
    $res['seats'] = $national_allocated_seats[$res['party_id']] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>National Election Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">🇲🇦 National Election Results</a>
            <div class="collapse navbar-collapse">
                 <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><span class="navbar-text me-3">Logged in as: <?php echo htmlspecialchars($_SESSION['name']); ?></span></li>
                    <li class="nav-item"><a class="btn btn-outline-light" href="results.php">District Results</a></li>
                    <li class="nav-item"><a class="btn btn-outline-light" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="alert alert-info">
            <strong>Moroccan Election Simulation:</strong> National results with 6% threshold and proportional seat allocation (Largest Remainder Method). Total seats: <?php echo $total_seats_national; ?>.
        </div>

        <div class="card">
            <div class="card-header">
                <h5>National Vote Totals & Seat Allocation</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Rank</th>
                            <th>Party</th>
                            <th>Description</th>
                            <th>Total Votes</th>
                            <th>National %</th>
                            <th>Qualified?</th>
                            <th>Seats Won</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($national_results)): ?>
                            <?php $rank = 1; foreach ($national_results as $res): ?>
                                <tr>
                                    <td><b>#<?php echo $rank++; ?></b></td>
                                    <td>
                                        <?php if ($res['logo_path']): ?>
                                            <img src="<?php echo htmlspecialchars($res['logo_path']); ?>" alt="Logo" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($res['party_name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($res['description'] ?? 'N/A'); ?></td>
                                    <td><h5><?php echo $res['total_votes']; ?></h5></td>
                                    <td><?php echo number_format($res['percentage'], 2) . '%'; ?></td>
                                    <td><?php echo in_array($res['party_id'], $qualified_parties) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'; ?></td>
                                    <td><strong><?php echo $res['seats']; ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No votes recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <?php if($_SESSION['role'] == 'admin'): ?>
                <a href="admin_dashboard.php">← Back to Admin Dashboard</a>
            <?php elseif($_SESSION['role'] == 'voter'): ?>
                 <a href="vote.php">← Back to Voting Page</a>
            <?php else: ?>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>