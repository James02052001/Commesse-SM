<?php
include "common/connection.php";

$action = $_POST['action'];
$cliente = "";
$commessa = "";
$motore = "";
$responsabile = "";
$dataInizio = "";
$dataFine = "";
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
    case "Ricerca":
        Ricerca($pagina, $msg);
        break;

    default:
        unset($_POST);
        header("Location:index.php?msg=valori mancanti");
        break;
}

if ($pagina != "")
    header("Location:$pagina?msg=$msg");


function Salva(&$pagina, &$msg)
{
    global $conn;
    global $cliente, $commessa, $motore, $responsabile, $dataInizio, $dataFine, $files;

    Getdata();

    // Controlliamo se la commessa esiste già
    $query = "SELECT * FROM `commessa` WHERE Commessa = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        $msg = 'Errore nella preparazione della query di selezione: ' . $conn->error;
        $pagina = "index.php";
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
        $pagina = "index.php";
        $stmt->close();
        return;
    }

    // Prepariamo la query per l'inserimento
    $query = "INSERT INTO `commessa` (`Commessa`, `cliente`, `motore`, `id_responsabile`, `data_inizio`, `data_fine`) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    // Controllo se la query di inserimento è stata preparata correttamente
    if (!$stmt) {
        $msg = 'Errore nella preparazione della query di inserimento: ' . $conn->error;
        $pagina = "index.php";
        return;
    }

    // Lega i parametri alla query
    $stmt->bind_param("sssiss", $commessa, $cliente, $motore, $responsabile, $dataInizio, $dataFine);

    // Esegui l'inserimento
    $result = $stmt->execute();

    if ($result) {
        $directory = "data/$commessa/";

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

        $pagina = "index.php";
    } else {
        $msg = 'Errore nel salvataggio: ' . $stmt->error;
        $pagina = "index.php";
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
    global $cliente, $commessa, $motore, $responsabile, $dataInizio, $dataFine, $files;
    $cliente = isset($_POST['Cliente']) ? $_POST['Cliente'] : "";
    $commessa = isset($_POST['Commessa']) ? $_POST['Commessa'] : "";
    $motore = isset($_POST['Motore']) ? $_POST['Motore'] : "";
    $responsabile = isset($_POST['Responsabile']) ? $_POST['Responsabile'] : "";

    $dataInizio = isset($_POST['DataInizio']) ? $_POST['DataInizio'] : "";
    $dataFine = isset($_POST['DataFine']) ? $_POST['DataFine'] : "";

    $files = isset($_FILES["files"]) ? $_FILES["files"] : "NULL";
}

function Modifica(&$pagina, &$msg)
{
    global $conn;
    global $cliente, $commessa, $motore, $responsabile, $dataInizio, $dataFine, $files;

    Getdata();

    // Preparare la query di UPDATE
    $query = "UPDATE `commessa` SET `cliente` = ?, `motore` = ?, `id_responsabile` = ?, `data_inizio` = ?, `data_fine` = ? WHERE `Commessa` = ?";
    $stmt = $conn->prepare($query);

    // Controllo se la query è stata preparata correttamente
    if (!$stmt) {
        $msg = 'Errore nella composizione della query: ' . $conn->error;
        $pagina = "index.php";
        return;
    }

    // Lega i parametri alla query
    $stmt->bind_param("ssisss", $cliente, $motore, $responsabile, $dataInizio, $dataFine, $commessa);

    // Esegui l'UPDATE
    $result = $stmt->execute();

    // Controllo se l'esecuzione è andata a buon fine
    if ($result) {
        // Gestione dei file, se necessario come nel caso della funzione Salva
        $msg = 'Modifica effettuata';
        $pagina = "index.php";
    } else {
        $msg = 'Errore nella modifica: ' . $stmt->error;
        $pagina = "index.php";
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
        $pagina = "index.php";
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
        $pagina = "index.php";
    } else {
        // Se c'è stato un errore
        $msg = 'Errore nell\'eliminazione: ' . $stmt->error;
        $pagina = "index.php";
    }

    // Chiudiamo il prepared statement
    $stmt->close();

    return;
}

function Ricerca(&$pagina, &$msg)
{
    global $conn;
    global $cliente, $commessa, $motore, $responsabile, $dataInizio, $dataFine, $files;

    // Recuperiamo i dati dal form
    Getdata();

    // Creiamo una query dinamica per gestire più criteri di ricerca
    $query = "SELECT * FROM `commessa` WHERE 1=1";

    // Aggiungiamo i criteri di ricerca solo se vengono forniti
    if (!empty($commessa)) {
        $query .= " AND `Commessa` LIKE '%$commessa%'";
    }
    if (!empty($cliente)) {
        $query .= " AND `cliente` LIKE '%$cliente%'";
    }
    if (!empty($motore)) {
        $query .= " AND `motore` LIKE '%$motore%'";
    }

    // Eseguiamo la query
    $result = $conn->query($query);

    // Se troviamo risultati, li formattiamo in una tabella HTML
    if ($result->num_rows > 0) {
        $msg = 'Risultati trovati:';
        echo "<table border='1'>
                <tr>
                    <th>Commessa</th>
                    <th>Cliente</th>
                    <th>Motore</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['Commessa']}</td>
                    <td>{$row['cliente']}</td>
                    <td>{$row['motore']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        // Nessun risultato trovato
        $msg = 'Nessun risultato trovato per i criteri di ricerca forniti.';
    }

    $pagina = "index.php"; // Puoi reindirizzare a una pagina di risultati, se necessario
}







?>