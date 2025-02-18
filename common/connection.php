<?php
    include "config.php";
    $conn = new mysqli($db_Host, $db_User, $db_Pass, $db_Name);
    if($conn->connect_error){
        die("hai cappellato ".$conn->connect_error);
    }

    /**
     * Scrive un log nella tabella `log`.
     *
     * @param mysqli $conn Connessione al database.
     * @param string|null $description Descrizione dell'operazione.
     * @param string|null $query Testo della query eseguita.
     * @param int|null $userId ID dell'utente (opzionale). Se non fornito, viene prelevato dalla sessione.
     */
    function writeLog($conn, $description = null, $query = null, $userId = null) {
        // Usa l'ID utente dalla sessione
        $userId = isset($_SESSION['IdUtente']) ? $_SESSION['IdUtente'] : "NULL";
        // Escapa i valori per evitare SQL injection
        $description = $conn->real_escape_string($description);
        $query = $conn->real_escape_string($query);
        // Ottieni la data e l'ora attuali
        $currentDateTime = date('Y-m-d H:i:s');
        // Costruisci la query di inserimento
        $sql = "INSERT INTO log (id_utente, operazione, query, data) VALUES ($userId, '$description', '$query', '$currentDateTime')";
        // Esegui la query e verifica il risultato
        if ($conn->query($sql) === TRUE) {
            echo "Log written successfully";
        } else {
            echo "Error writing log: " . $conn->error;
        }
    }

    /**
     * Compone la query finita data la query con i `?` e i parametri.
     *
     * @param string $query La query con i `?`.
     * @param array $params I parametri della query.
     * @return string La query finita.
     */
    function composeQuery($query, $params) {
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