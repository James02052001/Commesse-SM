<?php
include("common/check_session.php");
include "common/connection.php";

// Verifica che la commessa sia presente nella richiesta
if (isset($_POST['Commessa']) && isset($_POST['Anno'])) {
    $commessa = $_POST['Commessa'];
    $anno = $_POST['Anno'];
    $directory = "data/$anno/$commessa/";

    // Se la directory non esiste, la crea
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    // Gestione eliminazione file
    if (isset($_POST['elimina_file'])) {
        $file = $_POST['elimina_file'];
        $filePath = $directory . $file;

        // Verifica che il file esista prima di eliminarlo
        if (file_exists($filePath)) {
            unlink($filePath); // Elimina il file
            $message = "File $file eliminato correttamente.";
        } else {
            $message = "Errore: il file $file non esiste.";
        }
        // Reindirizza alla pagina di dettaglio
        header("Location: dettaglio.php?Anno=$anno&Commessa=$commessa&msg=" . urlencode($message));
        exit; // Assicurati di interrompere l'esecuzione
    }

    // Gestione aggiunta file
    if (isset($_FILES['files'])) {
        $newFiles = $_FILES['files'];
        $fileCount = count($newFiles['name']);

        if ($fileCount <= 0 || $newFiles['tmp_name'][0] == ''){
            $message = "Nessun file caricato!";
            header("Location: dettaglio.php?Anno=$anno&Commessa=$commessa&msg=" . urlencode($message));
            exit();
        }

        // Trova il numero più alto già presente nei file della directory
        $existingFiles = scandir($directory);
        $existingFiles = array_diff($existingFiles, array('.', '..')); // Rimuove le directory speciali

        $maxIndex = 0;
        foreach ($existingFiles as $existingFile) {
            $filenameWithoutExt = pathinfo($existingFile, PATHINFO_FILENAME);
            if (is_numeric($filenameWithoutExt) && (int) $filenameWithoutExt > $maxIndex) {
                $maxIndex = (int) $filenameWithoutExt;
            }
        }

        // Caricamento di ciascun file
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

    // Gestione download di 1 file specifico
    if (isset($_POST['scarica_file'])) {
        $file = $_POST['scarica_file'];
        $filePath = $directory . $file;

        // Verifica che il file esista prima di scaricarlo
        if (file_exists($filePath)) {
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
    
    //Gestione download di tutti i file in un archivio ZIP
    if (isset($_POST['scarica_tutti'])) {
        $files = scandir($directory);
        $files = array_diff($files, array('.', '..')); // Rimuove le directory speciali

        if (empty($files)) {
            $message = "Nessun file da scaricare.";
            header("Location: dettaglio.php?Anno=$anno&Commessa=$commessa&msg=" . urlencode($message));
            exit;
        }

        $zipFileName = "files_commessa_$commessa.zip";
        $zipFilePath = sys_get_temp_dir() . "/" . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            $message = "Errore nella creazione dell'archivio ZIP.";
            header("Location: dettaglio.php?Anno=$anno&Commessa=$commessa&msg=" . urlencode($message));
            exit;
        }

        foreach ($files as $file) {
            $filePath = $directory . $file;
            if (file_exists($filePath) && !preg_match('/^qr_/', $file)) {
                $zip->addFile($filePath, $file);
            }
        }
        $zip->close();

        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zipFilePath));
        readfile($zipFilePath);
        unlink($zipFilePath); // Elimina il file ZIP temporaneo
        exit; // Assicurati di interrompere l'esecuzione
    }
} else {
    echo "Errore: ID Commessa o Anno mancante.";
    exit;
}
?>