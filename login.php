<?php
session_start();
include "common/connection.php";

$username = $_POST["Username"];
$password = $_POST["Password"];
$row = null; $msg = "";

if (ControlloCredenziali($username, $password)) {
    $_SESSION['username'] = $username;
    $_SESSION['Nome'] = $row['Nome'];

    header("Location:inserimento.php");
    exit();
} 

header("Location:index.php?msg=$msg");


function ControlloCredenziali($user, $pass){
    global $conn, $msg , $row;
    
    // Controlliamo se l'utente esiste
    $query = "SELECT * FROM `utente` WHERE username = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        $msg = 'Errore nella preparazione della query di selezione: ' . $conn->error;
        return false;
    }

    // Lega il parametro
    $stmt->bind_param("s", $user);

    // Eseguiamo la query di selezione
    $stmt->execute();
    $result = $stmt->get_result();

    //Controllo se l'utente esiste
    if ($result->num_rows == 0) {
        $msg = 'Nome utente inesistente!';
        return false;
    }

    //Controllo se la password è corretta
    $row = $result->fetch_assoc();
    if ($row["password"] != $pass) {
        $msg = 'Password errata!';
        return false;
    }

    $msg = '';
    return true;
}


?>