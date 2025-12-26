<?php
// File: results.php
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

// Fetch all electoral districts for the dropdown selector
$districts_result = mysqli_query($conn, "SELECT * FROM electoral_districts ORDER BY name ASC");

// Fetch national vote totals for threshold check
$national_votes = [];
$national_total = 0;
$national_sql = "SELECT pp.id AS party_id, pp.name AS party_name, SUM(pv.vote_count) AS total_votes
                 FROM party_votes pv
                 JOIN political_parties pp ON pv.party_id = pp.id
                 GROUP BY pp.id
                 ORDER BY total_votes DESC";
$national_query = mysqli_query($conn, $national_sql);
while ($row = mysqli_fetch_assoc($national_query)) {
    $national_votes[$row['party_id']] = $row['total_votes'];
    $national_total += $row['total_votes'];
}

// Determine qualified parties (3% threshold)
define('NATIONAL_THRESHOLD_PERCENT', 3);
$qualified_parties = [];
foreach ($national_votes as $party_id => $votes) {
    $percentage = ($national_total > 0) ? ($votes / $national_total) * 100 : 0;
    if ($percentage >= NATIONAL_THRESHOLD_PERCENT) {
        $qualified_parties[] = $party_id;
    }
}

// Determine which district to show results for
$selected_district_id = 0;
if (isset($_GET['district_id'])) {
    $selected_district_id = (int)$_GET['district_id'];
} elseif (mysqli_num_rows($districts_result) > 0) {
    // Default to the first district if none is selected
    $first_district = mysqli_fetch_assoc($districts_result);
    $selected_district_id = $first_district['id'];
    // Reset pointer to use the full result set later
    mysqli_data_seek($districts_result, 0);
}

// Fetch results for the selected district
$results = [];
$total_votes = 0;
$district_seats = 0;
if ($selected_district_id > 0) {
    // Get district seats using a prepared statement
    $stmt = mysqli_prepare($conn, "SELECT available_seats FROM electoral_districts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $selected_district_id);
    mysqli_stmt_execute($stmt);
    $district_result = mysqli_stmt_get_result($stmt);
    $district_data = mysqli_fetch_assoc($district_result);
    $district_seats = $district_data['available_seats'] ?? 0;
    mysqli_stmt_close($stmt);

    // Fetch results using a prepared statement
    $results_sql = "SELECT pp.id AS party_id, pp.name AS party_name, pp.logo_path, pp.description, pv.vote_count
                    FROM party_votes pv
                    JOIN political_parties pp ON pv.party_id = pp.id
                    WHERE pv.district_id = ?
                    ORDER BY pv.vote_count DESC";
    $stmt = mysqli_prepare($conn, $results_sql);
    mysqli_stmt_bind_param($stmt, "i", $selected_district_id);
    mysqli_stmt_execute($stmt);
    $results_query = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($results_query)) {
        $results[] = $row;
        $total_votes += $row['vote_count'];
    }
    mysqli_stmt_close($stmt);

    // Allocate seats for qualified parties only
    $party_votes_for_allocation = [];
    foreach ($results as $res) {
        if (in_array($res['party_id'], $qualified_parties)) {
            $party_votes_for_allocation[$res['party_id']] = $res['vote_count'];
        }
    }
    $allocated_seats = allocate_seats($party_votes_for_allocation, $district_seats);

    // Add seats to results
    foreach ($results as &$res) {
        $res['seats'] = $allocated_seats[$res['party_id']] ?? 0;
    }
    unset($res); // Unset reference from loop

    // Fetch the winning candidates based on seat allocation and rank
    $winning_candidates = [];
    if (!empty($allocated_seats)) {
        foreach ($allocated_seats as $party_id => $num_seats) {
            if ($num_seats > 0) {
                $stmt = mysqli_prepare($conn, "SELECT u.full_name, p.name AS party_name 
                                               FROM candidates c
                                               JOIN users u ON c.user_id = u.id
                                               JOIN political_parties p ON c.party_id = p.id
                                               WHERE c.party_id = ? AND c.district_id = ?
                                               ORDER BY c.rank ASC
                                               LIMIT ?");
                // Bind parameters: party_id, district_id, num_seats
                mysqli_stmt_bind_param($stmt, "iii", $party_id, $selected_district_id, $num_seats);
                mysqli_stmt_execute($stmt);
                $winners_result = mysqli_stmt_get_result($stmt);
                while ($winner = mysqli_fetch_assoc($winners_result)) {
                    $winning_candidates[] = $winner;
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Election Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">📊 Election Results</a>
            <div class="collapse navbar-collapse">
                 <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="national_results.php">National Results</a></li>
                    <li class="nav-item"><span class="navbar-text me-3">Logged in as: <?php echo htmlspecialchars($_SESSION['name']); ?></span></li>
                    <li class="nav-item"><a class="btn btn-outline-light" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="alert alert-info">
            <strong>Moroccan Election Simulation:</strong> Results include proportional seat allocation using the Largest Remainder Method (Hare Quota). Only parties with ≥<?php echo NATIONAL_THRESHOLD_PERCENT; ?>% national vote qualify for seats.
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <h4>Select Electoral District</h4>
                <form method="get" id="district_form">
                    <select name="district_id" class="form-select" onchange="document.getElementById('district_form').submit();">
                        <option value="">-- Choose a District --</option>
                        <?php 
                        while ($district = mysqli_fetch_assoc($districts_result)) {
                            $selected = ($district['id'] == $selected_district_id) ? 'selected' : '';
                            echo "<option value='{$district['id']}' {$selected}>" . htmlspecialchars($district['name']) . "</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>
        </div>

        <?php if ($selected_district_id > 0): ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>Results for <?php
                                mysqli_data_seek($districts_result, 0); // Reset pointer
                                while($d = mysqli_fetch_assoc($districts_result)) {
                                    if ($d['id'] == $selected_district_id) echo htmlspecialchars($d['name']);
                                }
                            ?></h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Party</th>
                                        <th>Description</th>
                                        <th>Votes</th>
                                        <th>Percentage</th>
                                        <th>Seats Won</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($results)): ?>
                                        <?php $rank = 1; foreach ($results as $res): ?>
                                            <tr>
                                                <td><b>#<?php echo $rank++; ?></b></td>
                                                <td>
                                                    <?php if ($res['logo_path']): ?>
                                                        <img src="<?php echo htmlspecialchars($res['logo_path']); ?>" alt="Logo" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($res['party_name']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($res['description'] ?? 'N/A'); ?></td>
                                                <td><h5><?php echo $res['vote_count']; ?></h5></td>
                                                <td>
                                                    <?php 
                                                    $percentage = ($total_votes > 0) ? ($res['vote_count'] / $total_votes) * 100 : 0;
                                                    echo number_format($percentage, 2) . '%';
                                                    ?>
                                                </td>
                                                <td><strong><?php echo $res['seats']; ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center">No votes recorded in this district yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                     <div class="card">
                         <div class="card-header">
                             <h5>District Summary</h5>
                         </div>
                         <div class="card-body text-center">
                             <p class="card-text">Total Votes Cast</p>
                             <h3 class="card-title"><?php echo $total_votes; ?></h3>
                             <hr>
                             <p class="card-text">Available Seats</p>
                             <h4 class="card-title"><?php echo $district_seats; ?></h4>
                             <hr>
                             <p class="card-text">Seats Allocated</p>
                             <h4 class="card-title text-primary"><?php echo array_sum(array_column($results, 'seats')); ?></h4>
                             <small class="text-muted">Only qualified parties (≥<?php echo NATIONAL_THRESHOLD_PERCENT; ?>% national vote) can win seats.</small>
                         </div>
                     </div>

                     <div class="card mt-4">
                        <div class="card-header bg-success text-white">
                            <h5><i class="bi bi-trophy-fill"></i> Elected Candidates</h5>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php if (!empty($winning_candidates)): ?>
                                <?php foreach ($winning_candidates as $winner): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <strong><?php echo htmlspecialchars($winner['full_name']); ?></strong>
                                        <span class="badge rounded-pill bg-primary"><?php echo htmlspecialchars($winner['party_name']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item text-muted">No seats allocated yet in this district.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Please select a district to view the results.</div>
        <?php endif; ?>

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