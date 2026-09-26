




<?php
$conn = new mysqli("localhost", "root", "", "facturation");
if ($conn->connect_error) die("Erreur DB : " . $conn->connect_error);


// 💡 Fonction pour formater les montants en Ouguiya
function format_ouguiya($montant) {
    return number_format($montant, 2) . ' MRU';
}


// Ajouter une facture
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ajouter_facture'])) {
    $id_client = $_POST['id_client'];
    $date = $_POST['date_facture'];
    $statut = $_POST['statut'];
    $descriptions = $_POST['description'];
    $quantites = $_POST['quantite'];
    $prix = $_POST['prix'];

    $sous_total = 0;
    for ($i = 0; $i < count($descriptions); $i++) {
        $sous_total += $quantites[$i] * $prix[$i];
    }
    $tva = $sous_total * 0.2;
    $total = $sous_total + $tva;

    $stmt = $conn->prepare("INSERT INTO factures (id_client, date_facture, total, statut) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $id_client, $date, $total, $statut);
    $stmt->execute();
    $id_facture = $conn->insert_id;

    $stmt2 = $conn->prepare("INSERT INTO facture_details (id_facture, description_service, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
    for ($i = 0; $i < count($descriptions); $i++) {
        $stmt2->bind_param("isid", $id_facture, $descriptions[$i], $quantites[$i], $prix[$i]);
        $stmt2->execute();
    }

    header("Location: factures.php");
    exit;
}

// Modifier une facture
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['modifier_facture'])) {
    $id_facture = $_POST['id_facture'];
    $id_client = $_POST['id_client'];
    $date = $_POST['date_facture'];
    $statut = $_POST['statut'];
    $descriptions = $_POST['description'];
    $quantites = $_POST['quantite'];
    $prix = $_POST['prix'];

    $sous_total = 0;
    for ($i = 0; $i < count($descriptions); $i++) {
        $sous_total += $quantites[$i] * $prix[$i];
    }
    $tva = $sous_total * 0.2;
    $total = $sous_total + $tva;

    $stmt = $conn->prepare("UPDATE factures SET id_client=?, date_facture=?, total=?, statut=? WHERE id=?");
    $stmt->bind_param("isdsi", $id_client, $date, $total, $statut, $id_facture);
    $stmt->execute();

    $conn->query("DELETE FROM facture_details WHERE id_facture=$id_facture");
    $stmt2 = $conn->prepare("INSERT INTO facture_details (id_facture, description_service, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
    for ($i = 0; $i < count($descriptions); $i++) {
        $stmt2->bind_param("isid", $id_facture, $descriptions[$i], $quantites[$i], $prix[$i]);
        $stmt2->execute();
    }

    header("Location: factures.php");
    exit;
}

// Supprimer une facture
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM facture_details WHERE id_facture=$id");
    $conn->query("DELETE FROM factures WHERE id=$id");
    header("Location: factures.php");
    exit;
}

// Préparation modification
$modif = false;
if (isset($_GET['edit'])) {
    $modif = true;
    $id_edit = $_GET['edit'];
    $facture_edit = $conn->query("SELECT * FROM factures WHERE id=$id_edit")->fetch_assoc();
    $details_edit = $conn->query("SELECT * FROM facture_details WHERE id_facture=$id_edit");
}

$clients = $conn->query("SELECT id, nom FROM clients");
$factures = $conn->query("SELECT f.*, c.nom FROM factures f JOIN clients c ON f.id_client = c.id");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Factures</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f4f4; }
        form, table { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        input, select { padding: 5px; margin: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border-bottom: 1px solid #ccc; }
        th { background: #3498db; color: white; }
        .actions a { margin: 0 5px; text-decoration: none; color: #3498db; }


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
</head>
<body>



<!-- Menu de navigation -->


<nav style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <a href="clients.php">Clients</a>
        <a href="factures.php">Factures</a>
    </div>
    <div>
        <a href="logout.php" style="color: red;">Déconnexion</a>
    </div>
</nav>

















<h2><?= $modif ? "Modifier une Facture" : "Créer une Nouvelle Facture" ?></h2>
<form method="post">
    <input type="hidden" name="<?= $modif ? 'modifier_facture' : 'ajouter_facture' ?>" value="1">
    <?php if ($modif): ?>
        <input type="hidden" name="id_facture" value="<?= $facture_edit['id'] ?>">
    <?php endif; ?>

    Client :
    <select name="id_client" required>
        <?php $clients->data_seek(0); while ($c = $clients->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= $modif && $c['id'] == $facture_edit['id_client'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nom']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    Date :
    <input type="date" name="date_facture" value="<?= $modif ? $facture_edit['date_facture'] : '' ?>" required>
    Statut :
    <select name="statut">
        <option value="non payée" <?= $modif && $facture_edit['statut'] == 'non payée' ? 'selected' : '' ?>>Non payée</option>
        <option value="payée" <?= $modif && $facture_edit['statut'] == 'payée' ? 'selected' : '' ?>>Payée</option>
    </select>

    <h4>Services / Produits :</h4>
    <div id="lignes">
        <?php if ($modif): ?>
            <?php while ($d = $details_edit->fetch_assoc()): ?>
                <div>
                    Description : <input type="text" name="description[]" value="<?= htmlspecialchars($d['description_service']) ?>" required>
                    Quantité : <input type="number" name="quantite[]" value="<?= $d['quantite'] ?>" min="1" required>
                    Prix unitaire : <input type="number" name="prix[]" step="0.01" value="<?= $d['prix_unitaire'] ?>" required>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div>
                Description : <input type="text" name="description[]" required>
                Quantité : <input type="number" name="quantite[]" min="1" value="1" required>
                Prix unitaire : <input type="number" step="0.01" name="prix[]" required>
            </div>
        <?php endif; ?>
    </div>

    <button type="button" onclick="ajouterLigne()">+ Ajouter une ligne</button><br><br>
    <button type="submit"><?= $modif ? 'Enregistrer les modifications' : 'Créer la facture' ?></button>
</form>

<h2>Liste des Factures</h2>
<table>
    <tr>
        <th>Client</th>
        <th>Date</th>
        <th>Total</th>
        <th>Statut</th>
        <th>Actions</th>
    </tr>
    <?php while ($f = $factures->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($f['nom']) ?></td>
        <td><?= $f['date_facture'] ?></td>
        <td><?= format_ouguiya($f['total']) ?></td>
        <td><?= $f['statut'] ?></td>
        <td class="actions">
            <a href="facture_apercu.php?id=<?= $f['id'] ?>" target="_blank">Aperçu</a>
            <a href="?edit=<?= $f['id'] ?>">Modifier</a>
            <a href="?delete=<?= $f['id'] ?>" onclick="return confirm('Supprimer cette facture ?')">Supprimer</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<script>
function ajouterLigne() {
    const div = document.createElement('div');
    div.innerHTML = 'Description : <input type="text" name="description[]" required> ' +
                    'Quantité : <input type="number" name="quantite[]" min="1" value="1" required> ' +
                    'Prix unitaire : <input type="number" step="0.01" name="prix[]" required>';
    document.getElementById('lignes').appendChild(div);
}
</script>

</body>
</html>
