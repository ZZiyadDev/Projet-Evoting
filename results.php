<?php
// File: results.php
session_start();
require('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch all electoral districts for the dropdown selector
$districts_result = mysqli_query($conn, "SELECT id, name FROM electoral_districts ORDER BY name ASC");

// Determine which district to show results for
$selected_district_id = 0;

// Priority: GET -> voter's district -> first district
if (isset($_GET['district_id']) && (int)$_GET['district_id'] > 0) {
    $selected_district_id = (int)$_GET['district_id'];
} elseif ($_SESSION['role'] === 'voter' && isset($_SESSION['district_id']) && (int)$_SESSION['district_id'] > 0) {
    $selected_district_id = (int)$_SESSION['district_id'];
} elseif (mysqli_num_rows($districts_result) > 0) {
    $first_district = mysqli_fetch_assoc($districts_result);
    $selected_district_id = (int)($first_district['id'] ?? 0);
    mysqli_data_seek($districts_result, 0); // reset pointer
}

// Fetch results for the selected district (votes + percentages only)
$results = [];
$total_votes = 0;
$district_name = "N/A";

if ($selected_district_id > 0) {
    // Get district name
    $dist_stmt = mysqli_prepare($conn, "SELECT name FROM electoral_districts WHERE id = ?");
    mysqli_stmt_bind_param($dist_stmt, "i", $selected_district_id);
    mysqli_stmt_execute($dist_stmt);
    $dist_res = mysqli_stmt_get_result($dist_stmt);
    if ($dist_data = mysqli_fetch_assoc($dist_res)) {
        $district_name = $dist_data['name'];
    }
    mysqli_stmt_close($dist_stmt);

    // ✅ Show all parties (even those with 0 votes in this district)
    $sql = "SELECT pp.name AS party_name, pp.logo_path, COALESCE(pv.vote_count, 0) AS vote_count
            FROM political_parties pp
            LEFT JOIN party_votes pv
                ON pv.party_id = pp.id AND pv.district_id = ?
            ORDER BY vote_count DESC, pp.name ASC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $selected_district_id);
    mysqli_stmt_execute($stmt);
    $results_query = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($results_query)) {
        $row['vote_count'] = (int)$row['vote_count'];
        $results[] = $row;
        $total_votes += $row['vote_count'];
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats des Élections</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">📊 Résultats des Élections</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="national_results.php">Résultats Nationaux</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light" href="logout.php">Déconnexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title mb-3">Sélectionnez la Circonscription Électorale</h4>
            <form method="get" id="district_form">
                <div class="input-group">
                    <select name="district_id" class="form-select" onchange="document.getElementById('district_form').submit();">
                        <option value="">-- Choisissez une circonscription --</option>
                        <?php
                        if (mysqli_num_rows($districts_result) > 0) {
                            mysqli_data_seek($districts_result, 0);
                            while ($district = mysqli_fetch_assoc($districts_result)) {
                                $selected = ((int)$district['id'] === (int)$selected_district_id) ? 'selected' : '';
                                echo "<option value='{$district['id']}' {$selected}>" . htmlspecialchars($district['name']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <button class="btn btn-primary" type="submit">Voir</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selected_district_id > 0): ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Résultats pour : <?php echo htmlspecialchars($district_name); ?></h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <p class="text-muted mb-1">Total des voix exprimées</p>
                    <h2 class="display-5 mb-0"><?php echo number_format($total_votes); ?></h2>
                </div>

                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                    <tr>
                        <th>Parti</th>
                        <th class="text-end">Voix</th>
                        <th class="text-end" style="width: 180px;">Pourcentage</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($results)): ?>
                        <?php foreach ($results as $res): ?>
                            <?php
                            $percentage = ($total_votes > 0) ? ($res['vote_count'] / $total_votes) * 100 : 0;
                            ?>
                            <tr>
                                <td>
                                    <?php if (!empty($res['logo_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($res['logo_path']); ?>"
                                             alt="Logo"
                                             class="img-thumbnail"
                                             style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($res['party_name']); ?>
                                </td>
                                <td class="text-end fw-semibold"><?php echo number_format($res['vote_count']); ?></td>
                                <td class="text-end">
                                    <div class="progress" style="height: 24px;">
                                        <div class="progress-bar bg-primary"
                                             role="progressbar"
                                             style="width: <?php echo $percentage; ?>%;"
                                             aria-valuenow="<?php echo $percentage; ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            <?php echo number_format($percentage, 1); ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center p-4">Aucun vote enregistré pour le moment.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Veuillez sélectionner une circonscription pour voir les résultats.</div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <?php if($_SESSION['role'] == 'admin'): ?>
            <a href="admin_dashboard.php" class="btn btn-secondary">← Retour au Tableau de Bord</a>
        <?php elseif($_SESSION['role'] == 'voter'): ?>
            <a href="vote.php" class="btn btn-secondary">← Retour à la Page de Vote</a>
        <?php else: ?>
            <a href="logout.php" class="btn btn-secondary">Déconnexion</a>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
