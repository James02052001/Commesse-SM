<?php
    include "connection.php";

    $action=$_POST['action'];
    $cliente="";
    $commessa="";
    $motore="";
    $files="";

    $pagina = "";
    $msg = "";

    switch($action)
    {
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
    
    if($pagina!="")
        header("Location:$pagina?msg=$msg");
    

    function Salva(&$pagina, &$msg)
    {
        global $conn;
        global $cliente,$commessa,$motore,$files;

        Getdata();

        $query="SELECT * FROM `commessa` WHERE Commessa = '$commessa'";
        $result = $conn->query($query);
        $nResult=$result->num_rows;
        if($nResult>0)
        {
            $msg = 'Commessa già esistente';
            $pagina = "index.php";
            return; 
        }
        $query="INSERT INTO `commessa` (`Commessa`, `cliente`, `motore`) VALUES ('$commessa', '$cliente', '$motore')";
        $result = $conn->query($query);
        if($result === TRUE)
        {
            for($i=0;$i<count($files["name"]);$i++)
            {
                $fileExt = strtolower(pathinfo($files["name"][$i], PATHINFO_EXTENSION));
                mkdir("data/$commessa/",0777,true);
                move_uploaded_file($files["tmp_name"][$i],"data/$commessa/$i.$fileExt");
            }
            $msg = 'Salvataggio effettuato';
            $pagina = "index.php";
            return;
        }

        $msg = 'Errore nel salvataggio: '.$conn->error;
        $pagina = "index.php";
        return;
    }
    
    function Getdata()
    {
        global $cliente,$commessa,$motore,$files;
        $cliente=$_POST['Cliente'];
        $commessa=$_POST['Commessa'];
        $motore=$_POST['Motore'];
        $files=$_FILES["files"];

    }

    function Modifica(&$pagina, &$msg)
    {
        global $conn;
        global $cliente, $commessa, $motore, $files;

        Getdata();

        $query = "UPDATE `commessa` SET `cliente`='$cliente', `motore`='$motore' WHERE `Commessa`='$commessa'";
        $result = $conn->query($query);
        if ($result === TRUE) {
            // Gestione dei file, se necessario come nel caso della funzione Salva
            $msg = 'Modifica effettuata';
            $pagina = "index.php";
            return;
        }

        $msg = 'Errore nella modifica: ' . $conn->error;
        $pagina = "index.php";
        return;
    }

    function Elimina(&$pagina, &$msg)
    {
        global $conn;
        global $commessa;

        $query = "DELETE FROM `commessa` WHERE `Commessa`='$commessa'";
        $result = $conn->query($query);
        if ($result === TRUE) {
            // Rimozione dei file associati, se necessario
            $msg = 'Eliminazione effettuata';
            $pagina = "index.php";
            return;
        }

        $msg = 'Errore nell\'eliminazione: ' . $conn->error;
        $pagina = "index.php";
        return;
    }

    function Ricerca(&$pagina, &$msg)
{
    global $conn;
    global $cliente, $commessa, $motore;

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