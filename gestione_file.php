<?php
include "common/connection.php";

// Verifica che la commessa sia presente nella richiesta
if (isset($_POST['commessa'])) {
    $commessa = $_POST['commessa'];
    $directory = "data/$commessa/";

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
        header("Location: dettaglio.php?Commessa=$commessa&msg=" . urlencode($message));
        exit; // Assicurati di interrompere l'esecuzione
    }

    // Gestione aggiunta file
    if (isset($_FILES['new_files'])) {
        $newFiles = $_FILES['new_files'];
        $fileCount = count($newFiles['name']);

        // Trova il numero più alto già presente nei file della directory
        $existingFiles = scandir($directory);
        $existingFiles = array_diff($existingFiles, array('.', '..')); // Rimuove le directory speciali

        $maxIndex = 0;
        foreach ($existingFiles as $existingFile) {
            $filenameWithoutExt = pathinfo($existingFile, PATHINFO_FILENAME);
            if (is_numeric($filenameWithoutExt) && (int)$filenameWithoutExt > $maxIndex) {
                $maxIndex = (int)$filenameWithoutExt;
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
        header("Location: dettaglio.php?Commessa=$commessa&msg=" . urlencode($message));
        exit; // Assicurati di interrompere l'esecuzione
    }
} else {
    echo "Errore: ID Commessa mancante.";
    exit;
}
?>
