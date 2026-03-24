<?php
session_start();

if (isset($_GET['set'])) {
    $_SESSION['test'] = 'bonjour';
    echo 'Session enregistrée !';
} else {
    var_dump($_SESSION);
}
?>