<?php
// File: national_results.php
session_start();
require('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 1) Fetch national vote totals per party (including parties with 0 votes)
$national_results = [];
$national_total_votes = 0;

$sql = "SELECT 
            pp.id AS party_id,
            pp.name AS party_name,
            pp.logo_path,
            pp.description,
            COALESCE(SUM(pv.vote_count), 0) AS total_votes
        FROM political_parties pp
        LEFT JOIN party_votes pv ON pv.party_id = pp.id
        GROUP BY pp.id
        ORDER BY total_votes DESC, pp.name ASC";

$query = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($query)) {
    $row['total_votes'] = (int)$row['total_votes'];
    $national_results[] = $row;
    $national_total_votes += $row['total_votes'];
}

// 2) Add percentage for display
foreach ($national_results as &$res) {
    $res['percentage'] = ($national_total_votes > 0)
        ? ($res['total_votes'] / $national_total_votes) * 100
        : 0;
}
unset($res);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats des Élections Nationales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">🇲🇦 Résultats des Élections Nationales</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <li class="nav-item">
                    <span class="navbar-text text-white-50 me-2">
                        Connecté en tant que: <?php echo htmlspecialchars($_SESSION['name']); ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light" href="results.php">Résultats par Circonscription</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light" href="logout.php">Déconnexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">

    <div class="alert alert-info">
        <strong>Simulation (version simplifiée) :</strong>
        résultats nationaux basés sur <strong>le total des votes</strong> et <strong>les pourcentages</strong> par parti.
        (Pas de calcul de sièges dans cette version.)
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Totaux nationaux (votes et pourcentages)</h5>
        </div>
        <div class="card-body">

            <div class="text-center mb-4">
                <p class="text-muted mb-1">Total des voix exprimées (national)</p>
                <h2 class="display-5 mb-0"><?php echo number_format($national_total_votes, 0, ',', ' '); ?></h2>
            </div>

            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                <tr>
                    <th>Rang</th>
                    <th>Parti</th>
                    <th>Description</th>
                    <th class="text-end">Total des votes</th>
                    <th class="text-end" style="width: 180px;">% National</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($national_results)): ?>
                    <?php $rank = 1; foreach ($national_results as $res): ?>
                        <tr>
                            <td><b>#<?php echo $rank++; ?></b></td>
                            <td>
                                <?php if (!empty($res['logo_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($res['logo_path']); ?>"
                                         alt="Logo"
                                         style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;"
                                         class="img-thumbnail">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($res['party_name']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($res['description'] ?? 'N/A'); ?></td>
                            <td class="text-end fw-semibold">
                                <?php echo number_format($res['total_votes'], 0, ',', ' '); ?>
                            </td>
                            <td class="text-end">
                                <div class="progress" style="height: 24px;">
                                    <div class="progress-bar bg-primary"
                                         role="progressbar"
                                         style="width: <?php echo $res['percentage']; ?>%;"
                                         aria-valuenow="<?php echo $res['percentage']; ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                        <?php echo number_format($res['percentage'], 1, ',', ' '); ?>%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Aucun vote enregistré pour le moment.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>

    <div class="mt-4 text-center">
        <?php if($_SESSION['role'] == 'admin'): ?>
            <a href="admin_dashboard.php" class="btn btn-secondary">← Retour au tableau de bord</a>
        <?php elseif($_SESSION['role'] == 'voter'): ?>
            <a href="vote.php" class="btn btn-secondary">← Retour à la page de vote</a>
        <?php else: ?>
            <a href="logout.php" class="btn btn-secondary">Déconnexion</a>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
