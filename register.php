<?php
// File: register.php
session_start();
require('includes/db.php');

$message = "";

// Fetch electoral districts for the dropdown
$districts_result = mysqli_query($conn, "SELECT * FROM electoral_districts ORDER BY name ASC");


if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $district_id = (int)$_POST['district_id'];
    
    // Basic validation
    if (empty($district_id)) {
        $message = "<div class='alert alert-danger'>Veuillez sélectionner votre circonscription électorale.</div>";
    } elseif (empty($fullname) || empty($username) || empty($password)) {
        $message = "<div class='alert alert-danger'>Veuillez remplir tous les champs.</div>";
    }
    else {
        // Check if username already exists using a prepared statement
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $message = "<div class='alert alert-danger'>Nom d'utilisateur déjà pris. Veuillez en choisir un autre.</div>";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            // Insert new VOTER using a prepared statement
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'voter';
            
            $sql = "INSERT INTO users (username, password, full_name, role, district_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $username, $hashed_password, $fullname, $role, $district_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "<div class='alert alert-success'>Compte créé ! <a href='index.php'>Connectez-vous ici</a></div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . mysqli_stmt_error($stmt) . "</div>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription de l'électeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

    <div class="card auth-card">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-card">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Inscription de l'électeur</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php echo $message; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Nom complet</label>
                                <input type="text" name="fullname" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nom d'utilisateur</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="district_id" class="form-label">Circonscription électorale</label>
                                <select class="form-select" id="district_id" name="district_id" required>
                                    <option value="">-- Sélectionnez votre circonscription --</option>
                                    <?php 
                                    if (mysqli_num_rows($districts_result) > 0) {
                                        while ($district = mysqli_fetch_assoc($districts_result)) {
                                            echo "<option value='" . $district['id'] . "'>" . htmlspecialchars($district['name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="register" class="btn btn-primary">Créer un compte</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        Vous avez déjà un compte ? <a href="index.php">Connectez-vous ici</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>