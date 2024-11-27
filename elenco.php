<?php
include("common/connection.php");
include("common/popup.php");



global $conn;
// Numero di commesse per pagina
$commessePerPagina = 8;

// Calcola la pagina corrente e assicurati che sia un numero valido
$paginaCorrente = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$paginaCorrente = max($paginaCorrente, 1); // Assicurati che la pagina non sia inferiore a 1
$offset = ($paginaCorrente - 1) * $commessePerPagina;

// Base della query
$query = "SELECT commessa.*, responsabile.nome, responsabile.cognome FROM commessa "
    . "LEFT OUTER JOIN responsabile ON commessa.id_responsabile = responsabile.id WHERE 1=1";
// Array per bind dei parametri
$where = "";
$params = [];
$types = "";

if (!empty($_GET['cliente'])) {
    $where .= " AND LOWER(REPLACE(REPLACE(commessa.cliente, ' ', ''), '.', '')) LIKE ?";
    $params[] = "%" . strtolower(str_replace([' ', '.'], '', $_GET['cliente'])) . "%";
    $types .= "s"; // Stringa
}

// Filtra per Numero Commessa
if (!empty($_GET['commessa'])) {
    $where .= " AND commessa.commessa LIKE ?";
    $params[] = "%" . $_GET['commessa'] . "%";
    $types .= "s"; // Intero
}

// Filtra per Motore
if (!empty($_GET['motore'])) {
    $where .= " AND commessa.motore LIKE ?";
    $params[] = "%" . $_GET['motore'] . "%";
    $types .= "s"; // Stringa
}

// Filtra per Responsabile
if (!empty($_GET['responsabile']) && $_GET['responsabile'] !== 'Nessuno') {
    $where .= " AND commessa.id_responsabile = ?";
    $params[] = $_GET['responsabile'];
    $types .= "i"; // Intero
}

// Filtra per Data Inizio
if (!empty($_GET['data_inizio'])) {
    $where .= " AND commessa.data >= ?";
    $params[] = $_GET['data_inizio'];
    $types .= "s"; // Data come stringa
}

// Filtra per Data Fine
if (!empty($_GET['data_fine'])) {
    $where .= " AND commessa.data <= ?";
    $params[] = $_GET['data_fine'];
    $types .= "s"; // Data come stringa
}

// Aggiungi limite e offset
$query .= $where . " LIMIT ? OFFSET ?";
$params[] = $commessePerPagina;
$params[] = $offset;
$types .= "ii"; // Due interi per il limite e l'offset

// Prepara ed esegui la query
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resComm = $stmt->get_result();

//Leggo l'elenco dei responsabili
$queryResp = "SELECT * FROM `responsabile`";
$resResp = $conn->query($queryResp);
?>

<!doctype html>
<html lang="en">

<head>
    <title>Sud Motori - Elenco Commesse</title>
    <link rel="icon" href="img/LogoSm.png" type="image/png">
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />


    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/elenco.css">
</head>


<body>

    <?php include("common/header.php") ?>

    <main class="d-flex flex-column p-4">
        <?php
        if (isset($_GET['msg']) && trim($_GET['msg']) != '')
            mostraPopup($_GET['msg'], "Esito");
        ?>
        <!-- Sticky Form -->
        <div id="stickyForm" class="container position-fixed end-0 p-3 bg-light border rounded shadow"
            style="top: 50px; max-width: 500px;">
            <button class="close-btn btn btn-link p-0" id="closeForm"
                style="font-size: 1.5rem; position: absolute; top: 10px; right: 15px;">&times;</button>
            <h5 class="mb-4">Filtri</h5>
            <form action="elenco.php" method="get">
                <!-- Nome Cliente -->
                <div class="row mb-3">
                    <div class="col-4">
                        <label for="cliente" class="form-label">Nome Cliente</label>
                    </div>
                    <div class="col-8">
                        <input type="text" id="cliente" name="cliente" class="form-control" max="255"
                            value="<?= isset($_GET['cliente']) ? htmlspecialchars($_GET['cliente']) : '' ?>" />
                    </div>
                </div>
                <!-- Num. Commessa -->
                <div class="row mb-3">
                    <div class="col-4">
                        <label for="commessa" class="form-label">Num. Commessa</label>
                    </div>
                    <div class="col-8">
                        <input type="number" id="commessa" name="commessa" class="form-control" min="1"
                            value="<?= isset($_GET['commessa']) ? htmlspecialchars($_GET['commessa']) : '' ?>" />
                    </div>
                </div>
                <!-- Motore -->
                <div class="row mb-3">
                    <div class="col-4">
                        <label for="motore" class="form-label">Motore</label>
                    </div>
                    <div class="col-8">
                        <input type="text" id="motore" name="motore" class="form-control"
                            value="<?= isset($_GET['motore']) ? htmlspecialchars($_GET['motore']) : '' ?>" />
                    </div>
                </div>
                <!-- Responsabile -->
                <div class="row mb-3">
                    <div class="col-4">
                        <label for="responsabile" class="form-label">Responsabile</label>
                    </div>
                    <div class="col-8">
                        <select id="responsabile" name="responsabile" class="form-select">
                            <option value="Nessuno" <?php echo (isset($_GET['responsabile']) && $_GET['responsabile'] == 'Nessuno') ? 'selected' : ''; ?>>Nessuno</option>
                            <!-- Ciclo i responsabili per scriverli nella selezione -->
                            <?php while ($rowResp = $resResp->fetch_assoc()): ?>
                                <option value="<?= $rowResp['id'] ?>" <?php
                                  // Verifica se l'ID del responsabile è uguale al valore passato tramite GET
                                  echo (isset($_GET['responsabile']) && $_GET['responsabile'] == $rowResp['id']) ? 'selected' : '';
                                  ?>>
                                    <?= htmlspecialchars($rowResp['nome']) . ' ' . htmlspecialchars($rowResp['cognome']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <!-- Data Inizio -->
                <div class="row mb-3">
                    <div class="col-4">
                        <label for="data-inizio" class="form-label">Data Inizio</label>
                    </div>
                    <div class="col-8">
                        <input type="date" id="data-inizio" name="data_inizio" class="form-control"
                            value="<?= isset($_GET['data_inizio']) ? htmlspecialchars($_GET['data_inizio']) : '' ?>" />
                    </div>
                </div>
                <!-- Data Fine -->
                <div class="row mb-3">
                    <div class="col-4">
                        <label for="data-fine" class="form-label">Data Fine</label>
                    </div>
                    <div class="col-8">
                        <input type="date" id="data-fine" name="data_fine" class="form-control"
                            value="<?= isset($_GET['data_fine']) ? htmlspecialchars($_GET['data_fine']) : '' ?>" />
                    </div>
                </div>
                <!-- Pulsante -->
                <button type="submit" class="btn btn-primary w-100">Ricerca</button>
            </form>
        </div>



        <!-- Round Button -->
        <div id="toggleButton" class="search-btn">
            <i class="bi bi-search"></i> <!-- Bootstrap Icon -->
        </div>

        <div class="row">
            <?php while ($rowComm = $resComm->fetch_assoc()): ?>
                <div class="col-md-3 mb-4"> <!-- Modifica da col-md-2 a col-md-3 -->
                    <a href="dettaglio.php?Commessa=<?= $rowComm['commessa'] ?>"
                        class="card custom-card text-decoration-none">
                        <div id="carousel-<?= $rowComm['commessa'] ?>" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php
                                // Recupera i file dalla cartella della commessa
                                $cartellaMedia = 'data/' . $rowComm['commessa'];

                                // Verifica se la cartella esiste e contiene immagini o video
                                if (is_dir($cartellaMedia)) {
                                    $mediaFiles = glob($cartellaMedia . '/*.{jpg,jpeg,png,gif,mp4,webm,avi}', GLOB_BRACE);
                                } else {
                                    $mediaFiles = []; // Se la cartella non esiste, metti un array vuoto
                                }

                                // Se non ci sono file media, usa il placeholder
                                if (empty($mediaFiles)) {
                                    $mediaFiles = ['img/logosm.png']; // Imposta l'immagine di fallback
                                }

                                foreach ($mediaFiles as $index => $mediaFile):
                                    $activeClass = $index == 0 ? 'active' : '';
                                    $fileExtension = pathinfo($mediaFile, PATHINFO_EXTENSION);

                                    // Controlla se è un'immagine o un video
                                    if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                        <div class="carousel-item <?php echo $activeClass; ?>">
                                            <img src="<?php echo $mediaFile; ?>" class="d-block w-100 custom-img"
                                                alt="Immagine della commessa">
                                        </div>
                                    <?php elseif (in_array($fileExtension, ['mp4', 'webm', 'avi'])): ?>
                                        <div class="carousel-item <?php echo $activeClass; ?>">
                                            <video class="d-block w-100 custom-video" controls>
                                                <source src="<?php echo $mediaFile; ?>" type="video/<?php echo $fileExtension; ?>">
                                                Il tuo browser non supporta la riproduzione video.
                                            </video>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button"
                                data-bs-target="#carousel-<?= $rowComm['commessa'] ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button"
                                data-bs-target="#carousel-<?= $rowComm['commessa'] ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Commessa: #<?= $rowComm['commessa'] ?></h5>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th scope="row">Cliente</th>
                                        <td><?= $rowComm['cliente'] ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Motore</th>
                                        <td><?= $rowComm['motore'] ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Responsabile</th>
                                        <td><?= $rowComm['nome'] . ' ' . $rowComm['cognome'] ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Data Inizio</th>
                                        <td><?= $rowComm['data_inizio'] ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Data Fine</th>
                                        <td><?= $rowComm['data_fine'] ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>

        </div>
    </main>

    <!-- Paginazione -->
    <nav aria-label="Paginazione" class="d-flex text-center justify-content-center">
        <ul class="pagination">
            <?php
            //tolgo il filtro del limit
            array_splice($params, -2);
            $types = substr($types, 0, -2);

            // Query per contare il numero totale di commesse con i filtri applicati
            $countQuery = "SELECT COUNT(*) as total FROM commessa WHERE 1=1" . $where;

            // Prepara la query per il conteggio totale
            $stmtCount = $conn->prepare($countQuery);

            // Lega i parametri della query di conteggio
            if (!empty($types)) {
                $stmtCount->bind_param($types, ...$params);
            }

            // Esegui la query di conteggio
            $stmtCount->execute();
            $resultCount = $stmtCount->get_result();
            $totalRows = $resultCount->fetch_assoc()['total'];

            // Calcola il numero totale di pagine
            $totalPages = ceil($totalRows / $commessePerPagina);

            // Pagine da mostrare
            $startPage = max(1, $paginaCorrente - 2);  // Prima pagina che deve essere mostrata
            $endPage = min($totalPages, $paginaCorrente + 2);  // Ultima pagina che deve essere mostrata
            
            // Costruisci la stringa di query con i filtri attuali
            $queryParams = '';
            foreach ($_GET as $key => $value) {
                // Aggiungi ogni parametro GET (eccetto 'pagina') alla query
                if ($key != 'pagina') {
                    $queryParams .= '&' . $key . '=' . urlencode($value);
                }
            }

            // Link per la pagina precedente
            if ($paginaCorrente > 1) {
                echo '<li class="page-item"><a class="page-link" href="?pagina=' . ($paginaCorrente - 1) . $queryParams . '">&laquo;</a></li>';
            }

            // Pagine precedenti alla pagina corrente
            for ($i = $startPage; $i < $paginaCorrente; $i++) {
                echo '<li class="page-item"><a class="page-link" href="?pagina=' . $i . $queryParams . '">' . $i . '</a></li>';
            }

            // Pagina corrente
            echo '<li class="page-item active"><span class="page-link">' . $paginaCorrente . '</span></li>';

            // Pagine successive alla pagina corrente
            for ($i = $paginaCorrente + 1; $i <= $endPage; $i++) {
                echo '<li class="page-item"><a class="page-link" href="?pagina=' . $i . $queryParams . '">' . $i . '</a></li>';
            }

            // Link per la pagina successiva
            if ($paginaCorrente < $totalPages) {
                echo '<li class="page-item"><a class="page-link" href="?pagina=' . ($paginaCorrente + 1) . $queryParams . '">&raquo;</a></li>';
            }
            ?>
        </ul>
    </nav>

    </main>

    <?php include("common/footer.php") ?>

    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>

    <script>
        // JavaScript per gestire l'apertura e la chiusura del form
        const toggleButton = document.getElementById('toggleButton');
        const stickyForm = document.getElementById('stickyForm');
        const closeForm = document.getElementById('closeForm');

        toggleButton.addEventListener('click', () => {
            stickyForm.style.display = 'block'; // Mostra il form
            toggleButton.style.display = 'none'; // Nasconde il pulsante
        });

        closeForm.addEventListener('click', () => {
            stickyForm.style.display = 'none'; // Nasconde il form
            toggleButton.style.display = 'flex'; // Ripristina il pulsante
        });
    </script>
</body>

</html>