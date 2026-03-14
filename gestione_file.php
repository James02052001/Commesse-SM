<?php
// Include il file per controllare la sessione utente
include("common/check_session.php");
// Include il file per la connessione al database
include "common/connection.php";

// Verifica che i parametri Commessa e Anno siano presenti nella richiesta POST
if (isset($_POST['Commessa']) && isset($_POST['Anno'])) {
    $commessa = $_POST['Commessa'];
    $anno = $_POST['Anno'];
    $directory = "data/$anno/$commessa/";

    // Se la directory non esiste, la crea con permessi 0777
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    // Determina l'azione da eseguire in base ai parametri ricevuti
    if (isset($_POST['elimina_file'])) {
        eliminaFile($directory, $anno, $commessa);
    } elseif (isset($_FILES['files'])) {
        aggiungiFile($directory, $anno, $commessa);
    } elseif (isset($_POST['scarica_file']) || isset($_GET['scarica_file'])) {
        scaricaFile($directory, $anno, $commessa);
    } elseif (isset($_POST['scarica_tutti'])) {
        scaricaTutti($directory, $anno, $commessa);
    } else {
        echo "Errore: Azione non riconosciuta.";
        exit;
    }
} else {
    echo "Errore: ID Commessa o Anno mancante.";
    exit;
}

// Funzione per eliminare un file
function eliminaFile($directory, $anno, $commessa) {
    $file = $_POST['elimina_file'];
    $filePath = $directory . $file;

    // Verifica che il file esista prima di eliminarlo
    if (file_exists($filePath)) {
        unlink($filePath); // Elimina il file
        $message = "File $file eliminato correttamente.";
    } else {
        $message = "Errore: il file $file non esiste.";
    }
    // Reindirizza alla pagina di dettaglio con un messaggio
    header("Location: dettaglio.php?Anno=$anno&Commessa=$commessa&msg=" . urlencode($message));
    exit; // Assicurati di interrompere l'esecuzione
}

// Funzione per aggiungere file
function aggiungiFile($directory, $anno, $commessa) {
    $newFiles = $_FILES['files'];
    $fileCount = count($newFiles['name']);

    if ($fileCount <= 0 || $newFiles['tmp_name'][0] == ''){
        $message = "Nessun file caricato!";
        header("Location: dettaglio.php?Anno=$anno&Commessa=$commessa&msg=" . urlencode($message));
        exit();
    }

    // Trova il numero più alto già presente nei file della directory per nomi progressivi
    $existingFiles = scandir($directory);
    $existingFiles = array_diff($existingFiles, array('.', '..')); // Rimuove le directory speciali

    $maxIndex = 0;
    foreach ($existingFiles as $existingFile) {
        $filenameWithoutExt = pathinfo($existingFile, PATHINFO_FILENAME);
        if (is_numeric($filenameWithoutExt) && (int) $filenameWithoutExt > $maxIndex) {
            $maxIndex = (int) $filenameWithoutExt;
        }
    }

    // Caricamento di ciascun file con nome progressivo
    for ($i = 0; $i < $fileCount; $i++) {
        if ($newFiles['error'][$i] == 0) {
            $fileExt = strtolower(pathinfo($newFiles['name'][$i], PATHINFO_EXTENSION));

            // Nome progressivo del file
            $newFileName = ($maxIndex + 1) . '.' . $fileExt;
            $maxIndex++;

            // Sposta il file caricato nella directory con il nuovo nome progressivo
            move_uploaded_file($newFiles['tmp_name'][$i], $directory . $newFileName);
        }
    }

    $message = "Nuovi file caricati con successo.";
    // Reindirizza alla pagina di dettaglio
    header("Location: dettaglio.php?Anno=$anno&Commessa=$commessa&msg=" . urlencode($message));
    exit; // Assicurati di interrompere l'esecuzione
}

// Funzione per scaricare un file specifico
function scaricaFile($directory, $anno, $commessa) {
    $file = isset($_POST['scarica_file']) ? $_POST['scarica_file'] : $_GET['scarica_file'];
    $filePath = $directory . $file;

    // Verifica che il file esista prima di scaricarlo
    if (file_exists($filePath)) {
        // Imposta gli header per il download del file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit; // Assicurati di interrompere l'esecuzione
    } else {
        $message = "Errore: il file $file non esiste.";
        header("Location: dettaglio.php?Anno=$anno&Commessa=$commessa&msg=" . urlencode($message));
        exit; // Assicurati di interrompere l'esecuzione
    }
}

// Funzione per scaricare tutti i file: mostra una pagina con link di download individuali
function scaricaTutti($directory, $anno, $commessa) {
    // Ottieni la lista dei file nella directory
    $files = scandir($directory);
    $files = array_diff($files, array('.', '..')); // Rimuove le directory speciali

    // Filtra i file escludendo il QR (che inizia con 'qr_')
    $files = array_filter($files, function($file) {
        return !preg_match('/^qr_/', $file);
    });

    if (empty($files)) {
        $message = "Nessun file da scaricare.";
        header("Location: dettaglio.php?Anno=$anno&Commessa=$commessa&msg=" . urlencode($message));
        exit;
    }

    // Crea una pagina HTML con link di download per ciascun file e auto-download
    echo '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scarica File Commessa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .file-link { display: block; margin: 10px 0; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; text-decoration: none; color: #007bff; font-size: 18px; text-align: center; }
        .file-link:hover { background: #e9ecef; }
        .downloading { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Scarica File per Commessa ' . htmlspecialchars($commessa) . '</h1>
        <p class="mb-4" id="status">Download automatico in corso... I file verranno scaricati uno alla volta.</p>';
    
    $index = 0;
    foreach ($files as $file) {
        $downloadUrl = 'gestione_file.php?scarica_file=' . urlencode($file) . '&Anno=' . urlencode($anno) . '&Commessa=' . urlencode($commessa);
        echo '<a href="' . $downloadUrl . '" class="file-link" id="link-' . $index . '" download="' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . '</a>';
        $index++;
    }
    
    echo '<br><a href="dettaglio.php?Anno=' . $anno . '&Commessa=' . $commessa . '" class="btn btn-secondary">Torna alla Commessa</a>
    </div>
    <script>
        var links = document.querySelectorAll(".file-link");
        var index = 0;
        var status = document.getElementById("status");
        
        function downloadNext() {
            if (index < links.length) {
                status.textContent = "Scaricando: " + links[index].textContent;
                links[index].classList.add("downloading");
                links[index].click();
                index++;
                setTimeout(downloadNext, 2000); // 2 secondi tra download
            } else {
                status.textContent = "Download completato!";
            }
        }
        
        // Avvia il download automatico dopo 1 secondo
        setTimeout(downloadNext, 1000);
    </script>
</body>
</html>';
    exit;
}
?>