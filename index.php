<?php
include("common/connection.php");
include("common/popup.php");

global $conn;
//Leggo l'elenco dei responsabili
$queryResp = "SELECT * FROM `responsabile`";
$resResp = $conn->query($queryResp);

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
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <?php include("common/header.php") ?>

    <main class="p-2 d-flex text-center justify-content-center">
        <?php
        if (isset($_GET['msg']) && trim($_GET['msg']) != '')
            mostraPopup($_GET['msg'], "Esito");
        ?>
        <div class="card">
            <div class="card-header">
                <h4 class="p-2 m-0">Registrazione Commessa</h4>
            </div>
            <div class="card-body d-flex justify-content flex-column justify-content-center">
                <form class="mb-3 d-flex justify-content flex-column justify-content-center"
                    enctype="multipart/form-data" action="registra.php" method="post"
                    onsubmit="return validateFileSize();">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="Cliente" class="form-label mt-2">Nome Cliente</label>
                            <input type="text" class="form-control mb-2" name="Cliente" id="Cliente" max="255"
                                required />
                        </div>

                        <div class="col-md-6">
                            <label for="Commessa" class="form-label mt-2">Numero Commessa</label>
                            <input type="number" class="form-control mb-2" name="Commessa" id="Commessa" min="0"
                                required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="Motore" class="form-label mt-2">Numero Motore</label>
                            <input type="text" class="form-control mb-2" name="Motore" id="Motore" max="255" required />
                        </div>

                        <div class="col-md-6">
                            <label for="Responsabile" class="form-label mt-2">Responsabile</label>
                            <select class="form-control mb-2" name="Responsabile" id="Responsabile">
                                <option selected> Nessuno </option>
                                <?php while ($rowResp = $resResp->fetch_assoc()): ?>
                                    <option value="<?= $rowResp['id'] ?>">
                                        <?= $rowResp['nome'] . ' ' . $rowResp['cognome'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="DataInizio" class="form-label mt-2">Data Inizio</label>
                            <input type="date" class="form-control mb-2" name="DataInizio" id="DataInizio" />
                        </div>

                        <div class="col-md-6">
                            <label for="DataFine" class="form-label mt-2">Data Fine</label>
                            <input type="date" class="form-control mb-2" name="DataFine" id="DataFine" />
                        </div>
                    </div>

                    <div class="row mb-3 ps-2 pe-2">
                        <label for="file" class="form-label mt-2">Allega i file</label>
                        <input type="file" class="form-control mb-2" name="files[]" id="file" multiple />
                        <small class="text-muted">La dimensione massima consentita per ogni file è di 10 MB.</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <button class="btn w-100 mt-2" onclick="location.reload();">Svuota</button>
                        </div>
                        <div class="col-md-6">
                            <input class="btn w-100 mt-2" type="submit" value="Salva" name="action">
                        </div>
                    </div>
                </form>
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
</body>

</html>