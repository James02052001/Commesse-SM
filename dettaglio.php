<?php
include("common/connection.php");
include("common/popup.php");
require_once 'lib/phpqrcode/qrlib.php';

// Verifica se il numero della commessa è stato passato correttamente
if (!isset($_GET['Commessa'])) {
    header("Location: index.php?msg=Commessa mancante");
    exit;
}

global $conn;
$commessa = $_GET['Commessa'];

// Prepara la query con un parametro per il numero della commessa
$query = "SELECT * FROM `commessa` WHERE Commessa = ?";

// Prepara la dichiarazione
$stmt = $conn->prepare($query);

// Verifica se la preparazione è riuscita
if ($stmt === false) {
    die('Errore nella preparazione della query: ' . $conn->error);
}

// Associa il parametro (tipologia 's' per stringa) e esegui la query
$stmt->bind_param("s", $commessa); // 's' sta per stringa
$stmt->execute();

// Ottieni il risultato
$result = $stmt->get_result();

// Verifica se ci sono risultati
if ($result->num_rows === 0) {
    header("Location: index.php?msg=Commessa non trovata");
    exit;
}

//Prelevo i dati
$row = $result->fetch_assoc();
$cliente = $row['cliente'];
$motore = $row['motore'];
$directory = "data/$commessa/";

//Genero il QR
generaQR($directory);

// Funzione per elencare i file presenti nella directory
function elencaFile($directory)
{
    $files = scandir($directory);
    $files = array_diff($files, array('.', '..')); // Rimuove le directory "." e ".."
    return $files;
}

// Elenca i file esistenti nella cartella della commessa
$fileList = [];
if (is_dir($directory)) {
    $fileList = elencaFile($directory);
}

//Leggo l'elenco dei responsabili
$queryResp = "SELECT * FROM `responsabile`";
$resResp = $conn->query($queryResp);

function generaQR($tempDir)
{
    global $commessa;

    // Ottieni l'URL corrente
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $currentURL = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // Genera il QR Code
    if (!is_dir($tempDir)) {
        mkdir($tempDir); // Crea la directory per i QR Code se non esiste
    }
    $fileName = $tempDir . "qr_" . md5($commessa) . ".png";
    QRcode::png($currentURL, $fileName, QR_ECLEVEL_L, 5);

    return $fileName;
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>Sud Motori - Inserimento Commesse</title>
    <link rel="icon" href="img/LogoSm.png" type="image/png">
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dettaglio.css">
</head>

<body>
    <?php include("common/header.php") ?>

    <main class="p-2 d-flex text-center justify-content-center">
        <?php
        if (isset($_GET['msg']) && trim($_GET['msg']) != '')
            mostraPopup($_GET['msg'], "Esito");
        ?>
        <div class="row w-100 d-flex flex-row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="p-2 m-0">Modifica Commessa</h4>
                    </div>
                    <div class="card-body main d-flex justify-content flex-column justify-content-center">
                        <form class="mb-3 d-flex justify-content flex-column justify-content-center"
                            enctype="multipart/form-data" action="registra.php" method="post">

                            <div class="row mb-3">
                                <!-- Label con larghezza minima di 200px -->
                                <div class="col-12 col-md-4">
                                    <label for="Cliente" class="form-label w-100 mt-2">Nome Cliente</label>
                                </div>
                                <!-- Input con larghezza minima di 300px -->
                                <div class="col-12 col-md-8">
                                    <input type="text" class="form-control mb-2" name="Cliente" id="Cliente" max="255"
                                        value="<?= isset($cliente) ? htmlspecialchars($cliente) : '' ?>" required />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 col-md-4">
                                    <label for="Commessa" class="form-label w-100 mt-2">Numero Commessa</label>
                                </div>
                                <div class="col-12 col-md-8">
                                    <input type="number" class="form-control mb-2" name="Commessa" id="Commessa" min="0"
                                        value="<?= isset($commessa) ? htmlspecialchars($commessa) : '' ?>" required />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 col-md-4">
                                    <label for="Motore" class="form-label w-100 mt-2">Numero Motore</label>
                                </div>
                                <div class="col-12 col-md-8">
                                    <input type="text" class="form-control mb-2" name="Motore" id="Motore" max="255"
                                        value="<?= isset($motore) ? htmlspecialchars($motore) : '' ?>" required />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 col-md-4">
                                    <label for="Responsabile" class="form-label w-100 mt-2">Responsabile</label>
                                </div>
                                <div class="col-12 col-md-8">
                                    <select class="form-control mb-2" name="Responsabile" id="Responsabile">
                                        <option value="Nessuno" <?= ($row['id_responsabile'] == '') ? 'selected' : ''; ?>>Nessuno</option>
                                        <!-- Ciclo i responsabili per scriverli nella selezione -->
                                        <?php while ($rowResp = $resResp->fetch_assoc()): ?>
                                            <option value="<?= $rowResp['id']?>" <?= ($row['id_responsabile'] == $rowResp['id']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($rowResp['nome']) . ' ' . htmlspecialchars($rowResp['cognome']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 col-md-4">
                                    <label for="DataInizio" class="form-label w-100 mt-2">Data Inizio</label>
                                </div>
                                <div class="col-12 col-md-8">
                                    <input type="date" class="form-control mb-2" name="DataInizio" id="DataInizio"
                                        value="<?= isset($row['data_inizio']) ? htmlspecialchars($row['data_inizio']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 col-md-4">
                                    <label for="DataFine" class="form-label w-100 mt-2">Data Fine</label>
                                </div>
                                <div class="col-12 col-md-8">
                                    <input type="date" class="form-control mb-2" name="DataFine" id="DataFine"
                                        value="<?= isset($row['data_fine']) ? htmlspecialchars($row['data_fine']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <input class="btn w-100 mt-2" type="submit" value="Elimina" name="action" onclick="return confirm('Sei sicuro di voler eliminare questa commessa?')">
                                </div>
                                <div class="col-md-6">
                                    <input class="btn w-100 mt-2" type="submit" value="Modifica" name="action">
                                </div>
                            </div>
                        </form>

                        <!-- Aggiunta nuovi file -->
                        <h3>Aggiungi nuovi file</h3>
                        <form action="gestione_file.php" method="post" enctype="multipart/form-data" class="mb-5"
                            onsubmit="return validateFileSize();">
                            <input type="hidden" name="commessa" value="<?php echo $commessa; ?>">
                            <div class="mb-3">
                                <label for="file" class="form-label">Seleziona i file da caricare</label>
                                <input type="file" class="form-control" name="file[]" id="file" multiple
                                    accept="image/*,video/*">
                                <small class="text-muted">La dimensione massima consentita per ogni file è di 10
                                    MB.</small>
                            </div>
                            <button type="submit" class="btn btn-success">Aggiungi File</button>
                        </form>
                    </div>
                </div>


            </div>
            <div class="col-md-6 d-flex justify-content-between">
                <?php if (!empty($fileList)): ?>
                    <div class="row w-100">
                        <?php foreach ($fileList as $file): ?>
                            <div class="col-12 col-sm-6 col-md-6 mb-3">
                                <div class="card">
                                    <?php
                                    $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
                                    if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                        <!-- Visualizza l'immagine con dimensioni fisse -->
                                        <a href="<?= $directory . $file; ?>">
                                            <img src="<?php echo $directory . $file; ?>"
                                                alt="<?php echo htmlspecialchars($file); ?>" class="card-img-top img-fluid"
                                                data-bs-toggle="modal" data-bs-target="#mediaModal-<?php echo $file; ?>"
                                                style="cursor: pointer; width: 100%; height: 300px; object-fit: cover;">
                                        </a>
                                    <?php elseif (in_array($fileExtension, ['mp4', 'webm', 'avi'])): ?>
                                        <!-- Visualizza il video -->
                                        <video class="card-img-top img-fluid" controls data-bs-toggle="modal"
                                            data-bs-target="#mediaModal-<?php echo $file; ?>"
                                            style="cursor: pointer; width: 100%; height: 300px; object-fit: cover;">
                                            <source src="<?php echo $directory . $file; ?>"
                                                type="video/<?php echo $fileExtension; ?>">
                                            Il tuo browser non supporta il formato video.
                                        </video>
                                    <?php else: ?>
                                        <!-- Se il file non è né immagine né video, visualizza il nome -->
                                        <div class="card-body">
                                            <p class="card-text text-truncate" title="<?php echo htmlspecialchars($file); ?>">
                                                <?php echo htmlspecialchars($file); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Form di eliminazione -->
                                    <div class="card-body text-center">
                                        <form action="gestione_file.php" method="post"
                                            onsubmit="return confirmDelete('<?php echo htmlspecialchars($file); ?>');">
                                            <input type="hidden" name="commessa" value="<?php echo $commessa; ?>">
                                            <input type="hidden" name="elimina_file" value="<?php echo $file; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm w-100" style="min-width:auto;"
                                                <?= str_contains($file, "qr_") ? "disabled" : "" ?>>Elimina</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modale per l'immagine o il video -->
                            <div class="modal fade" id="mediaModal-<?php echo $file; ?>" tabindex="-1"
                                aria-labelledby="mediaModalLabel-<?php echo $file; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="mediaModalLabel-<?php echo $file; ?>">Media:
                                                <?php echo htmlspecialchars($file); ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                                <img src="<?php echo $directory . $file; ?>"
                                                    alt="<?php echo htmlspecialchars($file); ?>" class="img-fluid w-100">
                                            <?php elseif (in_array($fileExtension, ['mp4', 'webm', 'avi'])): ?>
                                                <video class="img-fluid w-100" controls>
                                                    <source src="<?php echo $directory . $file; ?>"
                                                        type="video/<?php echo $fileExtension; ?>">
                                                    Il tuo browser non supporta il formato video.
                                                </video>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>

                    </div>
                <?php else: ?>
                    <p>Nessun file presente per questa commessa.</p>
                <?php endif; ?>

            </div>


        </div>
    </main>

    <?php include("common/footer.php") ?>

    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>

    <script src="js/script.js"></script>
    <script>
        // Funzione per confermare l'eliminazione
        function confirmDelete(fileName) {
            return confirm("Sei sicuro di voler eliminare il file " + fileName + "?");
        }

        // Mostra il messaggio se presente
        <?php if (isset($_GET['msg'])): ?>
            document.getElementById("message").style.display = "block";
            document.getElementById("message-text").innerText = "<?php echo htmlspecialchars($_GET['msg']); ?>";
            setTimeout(function () {
                document.getElementById("message").style.display = "none";
            }, 5000); // Nascondi il messaggio dopo 5 secondi
        <?php endif; ?>
    </script>
</body>

</html>