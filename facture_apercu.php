<?php
$conn = new mysqli("localhost", "root", "", "facturation");
if ($conn->connect_error) die("Erreur DB : " . $conn->connect_error);

// Fonction pour formater en Ouguiya
function format_ouguiya($montant) {
    return number_format($montant, 2) . ' MRU';
}












































































































































































// Vérifier si l'ID de la facture est passé dans l'URL
if (isset($_GET['id'])) {
    $id_facture = $_GET['id'];

    // Récupérer les informations de la facture
    $facture = $conn->query("SELECT f.*, c.nom AS client_nom, c.adresse FROM factures f JOIN clients c ON f.id_client = c.id WHERE f.id = $id_facture")->fetch_assoc();

    // Récupérer les détails des produits/services de la facture
    $details_facture = $conn->query("SELECT * FROM facture_details WHERE id_facture = $id_facture");
} else {
    echo "Facture non trouvée.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Aperçu de la Facture</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #3498db;
        }
        .facture-header, .facture-details {
            width: 100%;
            margin-bottom: 30px;
        }
        .facture-header div, .facture-details th, .facture-details td {
            padding: 10px;
        }
        .facture-header {
            border-bottom: 1px solid #3498db;
        }
        .facture-details {
            width: 100%;
            border-collapse: collapse;
        }
        .facture-details th {
            background-color: #3498db;
            color: white;
        }
        .facture-details td {
            border: 1px solid #ccc;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
        @media print {
            body {
                font-size: 14px;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>


<!-- Lien Déconnexion en haut à droite -->
<div class="no-print" style="text-align: right; margin-bottom: 10px;">
    <a href="logout.php" style="text-decoration: none; color: red; font-weight: bold;">Déconnexion</a>
</div>

















<h1>Aperçu de la Facture</h1>

<div class="facture-header">
    <div><strong>Facture N°:</strong> <?= $facture['id'] ?></div>
    <div><strong>Date:</strong> <?= date('d/m/Y', strtotime($facture['date_facture'])) ?></div>
    <div><strong>Client:</strong> <?= htmlspecialchars($facture['client_nom']) ?></div>
    <div><strong>Adresse:</strong> <?= htmlspecialchars($facture['adresse']) ?></div>
</div>

<table class="facture-details">
    <thead>
        <tr>
            <th>Description</th>
            <th>Quantité</th>
            <th>Prix Unitaire</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sous_total = 0;
        while ($detail = $details_facture->fetch_assoc()) {
            $total_ligne = $detail['quantite'] * $detail['prix_unitaire'];
            $sous_total += $total_ligne;
            ?>
            <tr>
                <td><?= htmlspecialchars($detail['description_service']) ?></td>
                <td><?= $detail['quantite'] ?></td>
                <td><?= format_ouguiya($detail['prix_unitaire']) ?></td>
                <td><?= format_ouguiya($total_ligne) ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<div class="total">
<div>Sous-total: <?= format_ouguiya($sous_total) ?></div>
<div>TVA (20%): <?= format_ouguiya($sous_total * 0.2) ?></div>
<div><strong>Total TTC: <?= format_ouguiya($sous_total * 1.2) ?></strong></div>
</div>

<div class="no-print" style="text-align: center; margin-top: 20px;">
    <button onclick="window.print()">Imprimer cette facture</button>
    <br><br>
    <a href="factures.php">Retour à la liste des factures</a>
</div>

</body>













</html>
