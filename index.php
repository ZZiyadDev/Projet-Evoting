<?php
// File: index.php
session_start();
require('includes/db.php');

$error = "";

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['district_id'] = (int)$row['district_id'];

            if ($row['role'] == 'admin') header("Location: admin_dashboard.php");
            elseif ($row['role'] == 'candidate') header("Location: cand_dashboard.php");
            else header("Location: vote.php");
            exit();
        } else {
            $error = "<div class='alert alert-danger'>Nom d’utilisateur ou mot de passe incorrect.</div>";
        }
    } else {
        $error = "<div class='alert alert-danger'>Nom d’utilisateur ou mot de passe incorrect.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — E-Voting</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="assets/css/style.css?v=1" rel="stylesheet">
</head>

<body class="login-page">

    <div class="container-fluid min-vh-100">
        <div class="row min-vh-100">
            <!-- Côté gauche -->
            <div class="col-lg-6 login-info-side d-none d-lg-flex">
                <div class="login-info-content">
                    <h1 class="text-white">Système E-Voting</h1>
                    <p class="text-white-50 mb-0">Élections modernes, sécurisées et transparentes.</p>
                </div>
            </div>

            <!-- Côté droit -->
            <div class="col-12 col-lg-6 login-form-side">
                <img
                  src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d5/Coat_of_arms_of_Morocco.svg/2001px-Coat_of_arms_of_Morocco.svg.png"
                  alt="Royaume du Maroc"
                  class="login-logo mb-4"
                >

                <div class="login-form-container">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4 p-md-5">
                            <h3 class="text-center mb-1">Connexion</h3>
                            <p class="text-center text-muted mb-4">Accédez à votre espace de vote</p>

                            <?php echo $error; ?>

                            <form method="post" autocomplete="on">
                                <div class="mb-3">
                                    <label class="form-label">Nom d’utilisateur</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                        <input type="text" name="username" class="form-control" placeholder="Entrez votre nom d’utilisateur" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Mot de passe</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" class="form-control" placeholder="Entrez votre mot de passe" required>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" name="submit" class="btn btn-primary btn-lg">
                                        Se connecter
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="card-footer text-center bg-white py-3">
                            <small>
                                Nouvel électeur ? <a href="register.php">Créer un compte</a>
                            </small>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">© <?php echo date('Y'); ?> E-Voting</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
