<?php

// Vérification de la session de connexion
session_start();




if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: login.php");
    exit();
}
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "facturation");
if ($conn->connect_error) die("Échec de connexion : " . $conn->connect_error);

// Ajouter ou modifier un client
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];

    if (isset($_POST['id']) && $_POST['id'] != '') {
        // Modifier
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE clients SET nom=?, email=?, telephone=?, adresse=? WHERE id=?");
        $stmt->bind_param("ssssi", $nom, $email, $telephone, $adresse, $id);
        $stmt->execute();
    } else {
        // Ajouter
        $stmt = $conn->prepare("INSERT INTO clients (nom, email, telephone, adresse) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nom, $email, $telephone, $adresse);
        $stmt->execute();
    }

    header("Location: clients.php");
    exit;
}

// Supprimer
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM clients WHERE id=$id");
    header("Location: clients.php");
    exit;
}

// Pré-remplissage pour modification
$editClient = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = $conn->query("SELECT * FROM clients WHERE id=$id");
    $editClient = $res->fetch_assoc();
}

// Recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchQuery = $search != '' ? "WHERE nom LIKE '%$search%'" : '';
$clients = $conn->query("SELECT * FROM clients $searchQuery ORDER BY nom");
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Clients</title>
    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        h1, h2, h3 {
            text-align: center;
            color: #2c3e50;
        }
        form {
            margin-bottom: 20px;
        }
        input, textarea {
            margin: 5px;
            padding: 8px;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            padding: 10px 15px;
            margin: 5px 0;
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .actions a {
            margin: 0 5px;
        }
        nav {
            margin-bottom: 20px;
        }
        nav a {
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
            color: #3498db;
        }
        nav a:hover {
            text-decoration: underline;
        }
    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="clients.php">Clients</a>
            <a href="factures.php">Factures</a>
        </div>
        <div>
            <a href="logout.php" style="color: red;">Déconnexion</a>
        </div>
    </div>
</nav>

<h1>Bienvenue dans l'application de gestion de facturation</h1>

<div class="container">
    <h2><?= $editClient ? "Modifier le Client" : "Ajouter un Nouveau Client" ?></h2>
    <form method="post">
        <input type="hidden" name="id" value="<?= $editClient['id'] ?? '' ?>">
        <input type="text" name="nom" placeholder="Nom" required value="<?= $editClient['nom'] ?? '' ?>">
        <input type="email" name="email" placeholder="Email" required value="<?= $editClient['email'] ?? '' ?>">
        <input type="text" name="telephone" placeholder="Téléphone" required value="<?= $editClient['telephone'] ?? '' ?>">
        <textarea name="adresse" placeholder="Adresse" required><?= $editClient['adresse'] ?? '' ?></textarea>
        <button type="submit"><?= $editClient ? "Mettre à jour" : "Ajouter" ?></button>
    </form>

    <h3>Liste des Clients</h3>
    <form method="get">
        <input type="text" name="search" placeholder="Rechercher par nom" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Rechercher</button>
    </form>

    <table>
        <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Adresse</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $clients->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['nom']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['telephone']) ?></td>
            <td><?= htmlspecialchars($row['adresse']) ?></td>
            <td class="actions">
                <a href="?edit=<?= $row['id'] ?>">Modifier</a>
                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Supprimer ce client ?')">Supprimer</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>











