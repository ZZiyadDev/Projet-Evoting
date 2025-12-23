<?php
// File: vote.php
include('includes/auth_session.php');
if ($_SESSION['role'] !== 'voter') {
    header("Location: index.php");
    exit();
}
require('includes/db.php');

$user_id = $_SESSION['user_id'];

// Get user's voting status and district info
$user_sql = "SELECT u.has_voted, u.district_id, d.name AS district_name 
             FROM users u 
             LEFT JOIN electoral_districts d ON u.district_id = d.id
             WHERE u.id = '$user_id'";
$user_result = mysqli_query($conn, $user_sql);
$user_data = mysqli_fetch_assoc($user_result);
$has_voted = $user_data['has_voted'];
$district_id = $user_data['district_id'];
$district_name = $user_data['district_name'];

$parties = [];
if (!$has_voted && !empty($district_id)) {
    // Fetch parties that have at least one candidate in the user's district
    $party_sql = "SELECT DISTINCT pp.id, pp.name, pp.logo_path, pp.description
                  FROM political_parties pp
                  JOIN candidates c ON pp.id = c.party_id
                  WHERE c.district_id = '$district_id'";
    $parties_result = mysqli_query($conn, $party_sql);
    while($row = mysqli_fetch_assoc($parties_result)) {
        $parties[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Voting Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .party-card {
            transition: transform .2s, box-shadow .2s;
        }
        .party-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .party-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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