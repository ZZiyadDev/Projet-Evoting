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
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Campagnes des candidats</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css?v=1">
</head>

<body class="bg-light">

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="vote.php">🗳️ Portail E-Voting</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCampaigns"
              aria-controls="navbarCampaigns" aria-expanded="false" aria-label="Menu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarCampaigns">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="btn btn-outline-light" href="vote.php">
              <i class="bi bi-arrow-left"></i> Retour au vote
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">

    <div class="text-center mb-4">
      <h1 class="mb-2"><i class="bi bi-megaphone-fill"></i> Campagnes des candidats</h1>
      <p class="lead mb-0">
        Candidats de votre circonscription :
        <strong class="text-primary"><?php echo htmlspecialchars($district_name); ?></strong>
      </p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body d-flex align-items-center gap-2">
        <i class="bi bi-info-circle text-primary"></i>
        <div class="text-muted">
          Consultez les programmes/manifestes avant de voter.
        </div>
      </div>
    </div>

    <?php if (!empty($candidates)): ?>
      <div class="row g-4">
        <?php foreach ($candidates as $candidate): ?>

         <?php
$fallback = "https://via.placeholder.com/300x300?text=Aucune+photo";

$raw = trim((string)($candidate['photo_path'] ?? ''));
$raw = str_replace('\\', '/', $raw);

if ($raw === '') {
  $photo = $fallback;
} elseif (preg_match('~^https?://~i', $raw)) {
  $photo = $raw;                 // already absolute URL
} else {
  $photo = $raw;                 // keep RELATIVE: uploads/... (no leading /)
}

$photo = htmlspecialchars($photo);
$fullName = htmlspecialchars($candidate['full_name']);
$partyName = htmlspecialchars($candidate['party_name']);
$rank = (int)$candidate['rank'];
$desc = !empty($candidate['description'])
  ? htmlspecialchars($candidate['description'])
  : "Ce candidat n’a pas encore fourni de manifeste.";
?>


          <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
              <div class="row g-0">

                <div class="col-md-3 p-3 d-flex align-items-center justify-content-center bg-light">
                  <img
                    src="<?php echo $photo; ?>"
                    class="candidate-photo"
                    alt="Photo de <?php echo $fullName; ?>"
                    onerror="this.onerror=null;this.src='<?php echo $fallback; ?>';"
                  >
                </div>

                <div class="col-md-9">
                  <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
                      <div>
                        <h4 class="card-title mb-1"><?php echo $fullName; ?></h4>
                        <div class="text-muted">
                          <i class="bi bi-flag"></i>
                          <?php echo $partyName; ?>
                          <span class="mx-2">•</span>
                          <i class="bi bi-list-ol"></i>
                          Rang <?php echo $rank; ?>
                        </div>
                      </div>

                      <span class="badge rounded-pill text-bg-light border">
                        <i class="bi bi-geo-alt"></i>
                        Circonscription
                      </span>
                    </div>

                    <hr class="my-3">

                    <p class="card-text mb-0" style="white-space: pre-wrap;">
                      <?php echo $desc; ?>
                    </p>
                  </div>
                </div>

              </div>
            </div>
          </div>

        <?php endforeach; ?>
      </div>

    <?php elseif (!empty($district_id)): ?>
      <div class="alert alert-info text-center">
        Aucun candidat n’est encore enregistré dans votre circonscription.
      </div>
    <?php else: ?>
      <div class="alert alert-warning text-center">
        Votre compte n’est pas associé à une circonscription. Veuillez contacter un administrateur.
      </div>
    <?php endif; ?>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
