 /*******Application web de gestion de facturation*******/

-------------------------------------
INSTRUCTIONS D’INSTALLATION
-------------------------------------

1. Installer un serveur local comme XAMPP  qui supporte PHP et MySQL.

2. Copier le dossier du projet dans le répertoire "htdocs" de XAMPP :
   Exemple : C:\xampp\htdocs\gestionDesFacturations

3. Ouvrir phpMyAdmin depuis http://localhost/phpmyadmin

4. Créer une base de données appelée"facturation" 

5. Importer le fichier de base de données fourni (facturation.sql) dans phpMyAdmin.

6. Ouvrir vs code et ouvrir le dossier"gestionDesFacturations" contenant les fichiers et faire configurer le code de configuration contenant les identifiants MySQL :
   - Hôte : localhost
   - Utilisateur : root
   - Mot de passe :  vide par défaut.

7. Lancer l'application dans le navigateur à l’adresse :
   http://localhost/gestionDesFacturations/login.php


-------------------------------------
DESCRIPTION DES FONCTIONNALITÉS
-------------------------------------


- Authentification utilisateur (login).
- Tableau de bord avec résumé des clients et factures.
- Gestion des clients : ajout, modification, suppression, Lister les clients.
- Création de factures liées à des clients: Créer une facture , Voir les factures d'un client , Modifier une facture , Supprimer une facture , Changer le statut d'une facture.
- Ajout de plusieurs produits ou services dans une facture.
- Enregistrer les modifications
- Calcul automatique du total de la facture.
- Visualisation des factures avec statut (payée / non payée).
- Modification et suppression des factures.
- Aperçu imprimable de chaque facture.

-------------------------------------






