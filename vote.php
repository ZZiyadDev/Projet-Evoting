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
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Page de vote</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-light">

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="#">🗳️ Portail E-Voting</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVote"
              aria-controls="navbarVote" aria-expanded="false" aria-label="Menu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarVote">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
          <li class="nav-item">
            <span class="navbar-text text-white me-lg-3">
              Bienvenue, <?php echo htmlspecialchars($_SESSION['name']); ?>
            </span>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-light" href="logout.php">Déconnexion</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">

    <?php
    if (isset($_SESSION['vote_status_message'])) {
      $message_type = $_SESSION['vote_status_type'] ?? 'info';
      echo "<div class='alert alert-{$message_type} alert-dismissible fade show' role='alert'>";
      echo $_SESSION['vote_status_message'];
      echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>';
      echo "</div>";
      unset($_SESSION['vote_status_message'], $_SESSION['vote_status_type']);
    }
    ?>

    <?php if ($has_voted == 1): ?>

      <div class="text-center p-5 bg-white rounded shadow">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        <h2 class="mt-3">Vous avez déjà voté.</h2>
        <p class="lead">Merci pour votre participation à l’élection.</p>
        <a href="results.php" class="btn btn-primary mt-3">Voir les résultats en direct</a>
      </div>

    <?php elseif (empty($district_id)): ?>

      <div class="alert alert-warning text-center">
        Votre compte n’est pas associé à une circonscription électorale. Veuillez contacter un administrateur.
      </div>

    <?php else: ?>

      <div class="text-center mb-4">
        <h2>Votre circonscription : <span class="text-primary"><?php echo htmlspecialchars($district_name); ?></span></h2>
        <p class="lead">Veuillez voter pour l’un des partis politiques suivants.</p>
      </div>

      <div class="alert alert-secondary text-center">
        Vous hésitez ? <a href="campaigns.php" class="alert-link">Cliquez ici</a> pour lire les programmes des candidats de votre circonscription.
      </div>

      <div class="row g-4 justify-content-center">
        <?php if (!empty($parties)): ?>
          <?php foreach ($parties as $party): ?>

            <?php
              // Chemin logo sécurisé + fallback
              $fallback = "https://via.placeholder.com/80?text=Logo";
              $logo = ltrim($party['logo_path'], '/');
              $logo = htmlspecialchars($logo);
              $partyName = htmlspecialchars($party['name']);
            ?>

            <div class="col-12 col-md-4 col-lg-3">
              <div class="card h-100 text-center party-card">
                <div class="card-body">
                  <img
                    src="<?php echo $logo; ?>"
                    alt="Logo <?php echo $partyName; ?>"
                    class="party-logo my-3"
                    onerror="this.onerror=null;this.src='<?php echo $fallback; ?>';"
                  >
                  <h5 class="card-title"><?php echo $partyName; ?></h5>

                  <?php if (!empty($party['description'])): ?>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($party['description']); ?></p>
                  <?php endif; ?>
                </div>

                <div class="card-footer bg-white border-0">
                  <a
                    href="submit_vote.php?party_id=<?php echo (int)$party['id']; ?>"
                    class="btn btn-success w-100"
                    onclick="return confirm('Confirmez-vous votre vote pour <?php echo $partyName; ?> ?');"
                  >
                    Voter
                  </a>
                </div>
              </div>
            </div>

          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert alert-info text-center">
            Aucun parti politique n’est actuellement enregistré dans votre circonscription.
          </div>
        <?php endif; ?>
      </div>

    <?php endif; ?>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
