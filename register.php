<?php
session_start();
$conn = new mysqli("localhost", "root", "", "facturation");
if ($conn->connect_error) die("Échec de la connexion : " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si le nom d'utilisateur existe déjà
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Nom d'utilisateur déjà utilisé.";
        } else {
            // Insérer le nouvel utilisateur
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $insert = $conn->prepare("INSERT INTO utilisateurs (username, password) VALUES (?, ?)");
            $insert->bind_param("ss", $username, $hash);
            if ($insert->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Erreur lors de l'enregistrement.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-box {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 300px;
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #2980b9;
        }
        .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>Créer un compte</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="password" name="confirm" placeholder="Confirmer le mot de passe" required>
            <button type="submit">S'inscrire</button>
        </form>
    </div>
</body>
</html>
