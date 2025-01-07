<?php
include("common/check_session.php");
include "common/connection.php";

$action = $_POST['action'];

$anno = ""; $commessa = "";
$cliente = ""; $motore = "";
$respMont = ""; $respSmont = "";
$dataInizio = ""; $dataFine = "";
$dataInizioMont = ""; $dataFineMont = "";
$dataInizioSmont = ""; $dataFineSmont = "";
$files = "";

$pagina = "";
$msg = "";

switch ($action) {
    case "Salva":
        Salva($pagina, $msg);
        break;
    case "Modifica":
        Modifica($pagina, $msg);
        break;
    case "Elimina":
        Elimina($pagina, $msg);
        break;
    default:
        unset($_POST);
        header("Location:inserimento.php?msg=valori mancanti");
        break;
}

if ($pagina != "") {
    // Aggiungi 'msg' alla URL esistente
    $redirectUrl = strpos($pagina, '?') === false ? "$pagina?msg=$msg" : "$pagina&msg=$msg";
    header("Location: $redirectUrl");
    exit; // Termina lo script per assicurarti che il redirect avvenga immediatamente
}


function Salva(&$pagina, &$msg)
{
    global $conn;
    global $anno, $commessa, $motore, $cliente, $respMont, $respSmont, $dataInizio, $dataFine, $dataInizioMont, $dataFineMont, $dataInizioSmont, $dataFineSmont, $files;

    Getdata();

    // Controlliamo se la commessa esiste già
    $query = "SELECT * FROM `commessa` WHERE Commessa = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        $msg = 'Errore nella preparazione della query di selezione: ' . $conn->error;
        $pagina = "inserimento.php";
        return;
    }

    // Lega il parametro
    $stmt->bind_param("s", $commessa);

    // Eseguiamo la query di selezione
    $stmt->execute();
    $result = $stmt->get_result();
    $nResult = $result->num_rows;

    // Controlla se la commessa esiste già
    if ($nResult > 0) {
        $msg = 'Commessa già esistente';
        $pagina = "inserimento.php";
        $stmt->close();
        return;
    }

    // Prepariamo la query per l'inserimento
    $query = "INSERT INTO `commessa` (`Anno`, `Commessa`, `cliente`, `motore`, `id_responsabile_mont`, `id_responsabile_smont`, `data_inizio`, `data_fine`, `data_inizio_mont`, `data_fine_mont`, `data_inizio_smont`, `data_fine_smont`) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    // Controllo se la query di inserimento è stata preparata correttamente
    if (!$stmt) {
        $msg = 'Errore nella preparazione della query di inserimento: ' . $conn->error;
        $pagina = "inserimento.php";
        return;
    }

    // Lega i parametri alla query
    $stmt->bind_param("isssiissssss",$anno, $commessa, $cliente, $motore, $respMont, $respSmont, $dataInizio, $dataFine, $dataInizioMont, $dataFineMont, $dataInizioSmont, $dataFineSmont);

    // Esegui l'inserimento
    $result = $stmt->execute();

    if ($result) {
        $directory = "data/$anno/$commessa/";

        // Se la directory esiste, cancellala prima di crearne una nuova
        if (is_dir($directory)) {
            deleteDirectory($directory); // Funzione per eliminare la directory e i file al suo interno
        }

        // Crea la directory
        mkdir($directory, 0777, true);

        // Gestione dei file
        if (isset($files["name"]) && is_array($files["name"]) && count($files["name"]) > 0) {
            // Sposta i file caricati nella nuova directory
            for ($i = 0; $i < count($files["name"]); $i++) {
                $fileExt = strtolower(pathinfo($files["name"][$i], PATHINFO_EXTENSION));
                move_uploaded_file($files["tmp_name"][$i], $directory . "$i.$fileExt");
            }
            $msg = 'Salvataggio effettuato correttamente!';
        } else {
            $msg = 'Salvataggio effettuato! Ma non è stato caricato nessun file!';
        }

        $pagina = "inserimento.php";
    } else {
        $msg = 'Errore nel salvataggio: ' . $stmt->error;
        $pagina = "inserimento.php";
    }

    // Chiudi il prepared statement
    $stmt->close();

    return;
}

// Funzione per cancellare una directory e tutti i file al suo interno
function deleteDirectory($dir)
{
    if (!is_dir($dir)) {
        return;
    }

    // Scandisci la directory per trovare tutti i file
    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
        $filePath = "$dir/$file";
        if (is_dir($filePath)) {
            deleteDirectory($filePath); // Chiamata ricorsiva per le sotto-directory
        } else {
            unlink($filePath); // Cancella il file
        }
    }

    rmdir($dir); // Cancella la directory vuota
}

function Getdata()
{
    global $anno, $commessa, $motore, $cliente, $respMont, $respSmont, $dataInizio, $dataFine, $dataInizioMont, $dataFineMont, $dataInizioSmont, $dataFineSmont, $files;
    $anno = isset($_POST['Anno']) ? $_POST['Anno'] : "";
    $commessa = isset($_POST['Commessa']) ? $_POST['Commessa'] : "";
    $cliente = isset($_POST['Cliente']) ? $_POST['Cliente'] : "";    
    $motore = isset($_POST['Motore']) ? $_POST['Motore'] : "";

    $respMont = isset($_POST['RespMont']) ? $_POST['RespMont'] : "";
    $respSmont = isset($_POST['RespSmont']) ? $_POST['RespSmont'] : "";

    $dataInizio = isset($_POST['DataInizio']) ? $_POST['DataInizio'] : "";
    $dataFine = isset($_POST['DataFine']) ? $_POST['DataFine'] : "";
    $dataInizioMont = isset($_POST['DataInizioMont']) ? $_POST['DataInizioMont'] : "";
    $dataFineMont = isset($_POST['DataFineMont']) ? $_POST['DataFineMont'] : "";
    $dataInizioSmont = isset($_POST['DataInizioSmont']) ? $_POST['DataInizioSmont'] : "";
    $dataFineSmont = isset($_POST['DataFineSmont']) ? $_POST['DataFineSmont'] : "";

    $files = isset($_FILES["files"]) ? $_FILES["files"] : "NULL";
}

function Modifica(&$pagina, &$msg)
{
    global $conn;
    global $anno, $commessa, $cliente, $motore, $respMont, $respSmont, $dataInizio, $dataFine, $dataInizioMont, $dataFineMont, $dataInizioSmont, $dataFineSmont, $files;

    Getdata();

    // Preparare la query di UPDATE con tutte le colonne aggiornabili
    $query = "UPDATE `commessa` 
              SET `cliente` = ?
              , `motore` = ?
              , `id_responsabile_mont` = ?
              , `id_responsabile_smont` = ?
              , `data_inizio` = ?
              , `data_fine` = ?
              , `data_inizio_mont` = ?
              , `data_fine_mont` = ?
              , `data_inizio_smont` = ?
              , `data_fine_smont` = ?
              WHERE `anno` = ? AND `commessa` = ?";
    $stmt = $conn->prepare($query);

    // Controllo se la query è stata preparata correttamente
    if (!$stmt) {
        $msg = 'Errore nella composizione della query: ' . $conn->error;
        $pagina = "dettaglio.php?Anno=".$anno."&Commessa=".$commessa;
        return;
    }

    // Lega i parametri alla query
    $stmt->bind_param("ssiissssssis", $cliente, $motore, $respMont, $respSmont, $dataInizio, $dataFine, $dataInizioMont, $dataFineMont, $dataInizioSmont, $dataFineSmont, $anno, $commessa);

    // Esegui l'UPDATE
    $result = $stmt->execute();

    // Controllo se l'esecuzione è andata a buon fine
    if ($result) {
        // Gestione dei file, se necessario come nel caso della funzione Salva
        $msg = 'Modifica effettuata';
        $pagina = "dettaglio.php?Anno=".$anno."&Commessa=".$commessa;
    } else {
        $msg = 'Errore nella modifica: ' . $stmt->error;
        $pagina = "dettaglio.php?Anno=".$anno."&Commessa=".$commessa;
    }

    // Chiudi il prepared statement
    $stmt->close();

    return;
}

function Elimina(&$pagina, &$msg)
{
    global $conn;
    global $commessa;

    // Recuperiamo i dati dal form (assumiamo che la funzione Getdata() assegni il valore a $commessa)
    Getdata();

    // Prepariamo la query con il segnaposto per il parametro
    $query = "DELETE FROM `commessa` WHERE `Commessa` = ?";

    // Prepara la query
    $stmt = $conn->prepare($query);

    // Verifica se la query è stata preparata correttamente
    if (!$stmt) {
        $msg = 'Errore nella preparazione della query: ' . $conn->error;
        $pagina = "inserimento.php";
        return;
    }

    // Associa il parametro (in questo caso, una stringa per il valore di $commessa)
    $stmt->bind_param("s", $commessa);

    // Esegui la query
    $result = $stmt->execute();

    // Verifica il risultato dell'esecuzione
    if ($result) {
        // Se la query è andata a buon fine
        $msg = 'Eliminazione effettuata';
        $pagina = "inserimento.php";
    } else {
        // Se c'è stato un errore
        $msg = 'Errore nell\'eliminazione: ' . $stmt->error;
        $pagina = "inserimento.php";
    }

    // Chiudiamo il prepared statement
    $stmt->close();

    return;
}

function debugQuery($query, $params) {
    foreach ($params as &$param) {
        if (is_string($param)) {
            $param = "'" . $param . "'";
        } elseif (is_null($param)) {
            $param = "NULL";
        }
    }
    return vsprintf(str_replace("?", "%s", $query), $params);
}

?>