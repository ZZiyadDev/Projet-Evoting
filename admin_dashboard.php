<?php
// File: admin_dashboard.php
include('includes/auth_session.php');
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php"); // Redirect non-admins
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">🗳️ Vote Électronique - Admin</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Tableau de Bord Admin</h2>
            <p class="lead">Bienvenue, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>
        <hr>

        <h4>Gestion de l'Élection</h4>
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-people-fill fs-1 text-primary"></i>
                        <h5 class="card-title mt-2">Gérer les Partis</h5>
                        <p class="card-text">Ajoutez, modifiez ou supprimez les partis politiques du système.</p>
                        <a href="admin_parties.php" class="btn btn-primary">Aller aux Partis</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-geo-alt-fill fs-1 text-primary"></i>
                        <h5 class="card-title mt-2">Gérer les Circonscriptions</h5>
                        <p class="card-text">Définissez les circonscriptions électorales pour l'élection.</p>
                        <a href="admin_districts.php" class="btn btn-primary">Aller aux Circonscriptions</a>
                    </div>
                </div>
            </div>
             <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-person-plus-fill fs-1 text-primary"></i>
                        <h5 class="card-title mt-2">Créer un Candidat</h5>
                        <p class="card-text">Créez des comptes pour les nouveaux candidats se présentant à l'élection.</p>
                        <a href="create_candidate.php" class="btn btn-primary">Créer Maintenant</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-list-ol fs-1 text-primary"></i>
                        <h5 class="card-title mt-2">Gérer les Candidats</h5>
                        <p class="card-text">Définissez le rang des candidats sur les listes de leur parti pour chaque circonscription.</p>
                        <a href="admin_candidates.php" class="btn btn-primary">Gérer les Rangs</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-bar-chart-line-fill fs-1 text-info"></i>
                        <h5 class="card-title mt-2">Voir les Résultats</h5>
                        <p class="card-text">Consultez le décompte des votes et les résultats des élections.</p>
                        <a href="results.php" class="btn btn-primary">Voir les Résultats</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>