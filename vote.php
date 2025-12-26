<?php
// File: vote.php
include('includes/auth_session.php');
if ($_SESSION['role'] !== 'voter') {
    header("Location: index.php");
    exit();
}
require('includes/db.php');

$user_id = $_SESSION['user_id'];

// Get user's voting status and district info using a prepared statement
$stmt = mysqli_prepare($conn, "SELECT u.has_voted, u.district_id, d.name AS district_name 
                             FROM users u 
                             LEFT JOIN electoral_districts d ON u.district_id = d.id
                             WHERE u.id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($stmt);

$has_voted = $user_data['has_voted'] ?? null;
$district_id = $user_data['district_id'] ?? null;
$district_name = $user_data['district_name'] ?? null;

$parties = [];
if (!$has_voted && !empty($district_id)) {
    // Fetch parties using a prepared statement
    $party_sql = "SELECT DISTINCT pp.id, pp.name, pp.logo_path, pp.description
                  FROM political_parties pp
                  JOIN candidates c ON pp.id = c.party_id
                  WHERE c.district_id = ?";
    $stmt = mysqli_prepare($conn, $party_sql);
    mysqli_stmt_bind_param($stmt, "i", $district_id);
    mysqli_stmt_execute($stmt);
    $parties_result = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($parties_result)) {
        $parties[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Voting Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">🗳️ E-Voting Portal</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span></li>
                    <li class="nav-item"><a class="btn btn-outline-light" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <?php
        // Display status message from session if it exists
        if (isset($_SESSION['vote_status_message'])) {
            $message_type = $_SESSION['vote_status_type'] ?? 'info';
            echo "<div class='alert alert-{$message_type} alert-dismissible fade show' role='alert'>";
            echo $_SESSION['vote_status_message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo "</div>";
            // Unset the session variables so the message doesn't linger
            unset($_SESSION['vote_status_message']);
            unset($_SESSION['vote_status_type']);
        }
        ?>

        <?php if ($has_voted == 1): ?>
        
            <div class="text-center p-5 bg-white rounded shadow">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h2 class="mt-3">You have already voted.</h2>
                <p class="lead">Thank you for your participation in the election.</p>
                <a href="results.php" class="btn btn-primary mt-3">View Live Results</a>
            </div>

        <?php elseif (empty($district_id)): ?>
             <div class="alert alert-warning text-center">
                Your user account is not assigned to an electoral district. Please contact an administrator.
            </div>
        <?php else: ?>

            <div class="text-center mb-4">
                <h2>Your Electoral District: <span class="text-primary"><?php echo htmlspecialchars($district_name); ?></span></h2>
                <p class="lead">Please cast your vote for one of the following political parties.</p>
            </div>

            <div class="alert alert-secondary text-center">
                Unsure who to vote for? <a href="campaigns.php" class="alert-link">Click here to read about the candidates</a> running in your district.
            </div>
            
            <div class="row g-4 justify-content-center">
            <?php if (!empty($parties)): ?>
                <?php foreach ($parties as $party): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 text-center party-card">
                            <div class="card-body">
                                <?php 
                                $logo = !empty($party['logo_path']) ? htmlspecialchars($party['logo_path']) : 'https://via.placeholder.com/80?text=Logo';
                                ?>
                                <img src="<?php echo $logo; ?>" alt="<?php echo htmlspecialchars($party['name']); ?> Logo" class="party-logo my-3">
                                <h5 class="card-title"><?php echo htmlspecialchars($party['name']); ?></h5>
                                <?php if (!empty($party['description'])): ?>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($party['description']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="submit_vote.php?party_id=<?php echo $party['id']; ?>" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to vote for <?php echo htmlspecialchars($party['name']); ?>?');">
                                    VOTE
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
             <?php else: ?>
                <div class="alert alert-info text-center">
                    There are currently no political parties registered to run in your district.
                </div>
            <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>