<?php

//la connexion à la base de données
//sans lui aucune page ne peut lire ou écrire des données 
//tous les autres fichiers l'incluent en premier

    $host   = 'localhost';
    $dbname = 'xibaar_yi';
    $user   = 'root';
    $password = '';

    try{
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $user,
            $password
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
?>


