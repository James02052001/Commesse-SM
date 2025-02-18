<?php
include("common/check_session.php");
include("common/connection.php");
include("common/popup.php");

// Ottieni l'anno selezionato
$anno = isset($_GET['anno']) ? $_GET['anno'] : date('Y');

// Ottieni gli anni disponibili
$anniQuery = "SELECT DISTINCT c.anno FROM commessa c ORDER BY c.anno DESC";
$anniResult = $conn->query($anniQuery);
$anniDisponibili = [];
if ($anniResult->num_rows > 0) {
    while ($row = $anniResult->fetch_assoc()) {
        $anniDisponibili[] = $row['anno'];
    }
}

// Esegui la query
$query = "
    SELECT r.nome, 
           COUNT(CASE WHEN c.id_responsabile_mont IS NOT NULL THEN 1 END) AS motori_montati,
           COUNT(CASE WHEN c.id_responsabile_smont IS NOT NULL THEN 1 END) AS motori_smontati
    FROM responsabile r
    LEFT JOIN commessa c ON r.id = c.id_responsabile_mont OR r.id = c.id_responsabile_smont
    WHERE c.anno = $anno
    GROUP BY r.nome
";

$result = $conn->query($query);

$responsabili = [];
$motoriMontati = [];
$motoriSmontati = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $responsabili[] = $row['nome'];
        $motoriMontati[] = $row['motori_montati'];
        $motoriSmontati[] = $row['motori_smontati'];
    }
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include("common/header.php") ?>

    <main class="p-2 d-flex text-center justify-content-center position-relative">
        <div id="stickyForm" class="container position-fixed end-0 p-3 bg-light border rounded shadow"
            style="top: 50px; max-width: 500px; display: none;">
            <button class="close-btn btn btn-link p-0" id="closeForm"
                style="font-size: 1.5rem; position: absolute; top: 10px; right: 15px;">&times;</button>
            <h5 class="mb-4">Filtri</h5>
            <form method="GET" action="">
            <div class="row mb-3">
            <div class="col-4">
                        <label for="anno" class="form-label">Anno</label>
                    </div>
                    <div class="col-8">
                        <select class="form-control mb-2" id="anno" name="anno" onchange="this.form.submit()">
                            <?php foreach ($anniDisponibili as $annoDisponibile) { ?>
                                <option value="<?php echo $annoDisponibile; ?>" <?php if ($annoDisponibile == $anno)
                                       echo 'selected'; ?>><?php echo $annoDisponibile; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div id="toggleButton" class="search-btn">
            <i class="bi bi-search"></i> <!-- Bootstrap Icon -->
        </div>

        <div class="container">
            <h2>Resoconto Responsabili</h2>
            <div class="row">
                <?php foreach ($responsabili as $index => $responsabile) { ?>
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-secondary text-primary">
                                <h4 class="my-0 fw-normal"><?php echo $responsabile; ?></h4>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li>Motori Montati: <?php echo $motoriMontati[$index]; ?> - Motori Smontati:
                                        <?php echo $motoriSmontati[$index]; ?>
                                    </li>
                                    <li></li><canvas id="chart-<?php echo $index; ?>"></canvas></li>
                                </ul>
                                <script>
                                    var ctx = document.getElementById('chart-<?php echo $index; ?>').getContext('2d');
                                    var myChart = new Chart(ctx, {
                                        type: 'pie',
                                        data: {
                                            labels: ['Motori Montati', 'Motori Smontati'],
                                            datasets: [{
                                                label: '<?php echo $responsabile; ?>',
                                                data: [<?php echo $motoriMontati[$index]; ?>, <?php echo $motoriSmontati[$index]; ?>],
                                                backgroundColor: ['rgba(54, 162, 235, 0.2)', 'rgba(255, 99, 132, 0.2)'],
                                                borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 99, 132, 1)'],
                                                borderWidth: 1
                                            }]
                                        },
                                        options: {
                                            plugins: {
                                                legend: {
                                                    display: true,
                                                    position: 'top'
                                                }
                                            }
                                        }
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="col-md-12 mb-4">
                <h2 class="text-center">Motori Montati e Smontati da Ogni Responsabile</h2>
            </div>

            <div class="col-md-12 mb-4">
                <div class="row">
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card mb-4 shadow-sm">
                            <div
                                class="card-header bg-secondary text-primary d-flex justify-content-between align-items-center">
                                <h4 class="my-0 fw-normal">Totale Motori Montati</h4>
                                <button class="btn btn-light btn-sm" onclick="toggleChartType('Montati')">
                                    <i class="bi bi-arrow-repeat"></i> Cambia Grafico
                                </button>
                            </div>
                            <div class="card-body d-flex justify-content-center">
                                <canvas id="pieChartMontati"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card mb-4 shadow-sm">
                            <div
                                class="card-header bg-secondary text-primary d-flex justify-content-between align-items-center">
                                <h4 class="my-0 fw-normal">Totale Motori Smontati</h4>
                                <button class="btn btn-light btn-sm" onclick="toggleChartType('Smontati')">
                                    <i class="bi bi-arrow-repeat"></i> Cambia Grafico
                                </button>
                            </div>
                            <div class="card-body d-flex justify-content-center">
                                <canvas id="pieChartSmontati"></canvas>
                            </div>
                        </div>
                    </div>
                    <script>
                        var responsabili = <?php echo json_encode($responsabili); ?>;
                        var motoriMontati = <?php echo json_encode($motoriMontati); ?>;
                        var motoriSmontati = <?php echo json_encode($motoriSmontati); ?>;
                        var colors = [
                            'rgba(54, 162, 235, 0.2)', // Blue
                            'rgba(255, 99, 132, 0.2)', // Red
                            'rgba(255, 206, 86, 0.2)', // Yellow
                            'rgba(75, 192, 192, 0.2)', // Green
                            'rgba(153, 102, 255, 0.2)', // Purple
                            'rgba(255, 159, 64, 0.2)', // Orange
                            'rgba(199, 199, 199, 0.2)', // Grey
                            'rgba(255, 205, 86, 0.2)', // Light Yellow
                            'rgba(54, 162, 235, 0.2)', // Light Blue
                            'rgba(75, 192, 192, 0.2)', // Light Green
                            'rgba(255, 99, 132, 0.2)', // Light Red
                            'rgba(153, 102, 255, 0.2)', // Light Purple
                            'rgba(255, 159, 64, 0.2)', // Light Orange
                            'rgba(199, 199, 199, 0.2)', // Light Grey
                            'rgba(255, 205, 86, 0.2)', // Light Yellow
                            'rgba(54, 162, 235, 0.2)', // Light Blue
                            'rgba(75, 192, 192, 0.2)', // Light Green
                            'rgba(255, 99, 132, 0.2)', // Light Red
                            'rgba(153, 102, 255, 0.2)', // Light Purple
                            'rgba(255, 159, 64, 0.2)'  // Light Orange
                        ];
                        var borderColors = [
                            'rgba(54, 162, 235, 1)', // Blue
                            'rgba(255, 99, 132, 1)', // Red
                            'rgba(255, 206, 86, 1)', // Yellow
                            'rgba(75, 192, 192, 1)', // Green
                            'rgba(153, 102, 255, 1)', // Purple
                            'rgba(255, 159, 64, 1)', // Orange
                            'rgba(199, 199, 199, 1)', // Grey
                            'rgba(255, 205, 86, 1)', // Light Yellow
                            'rgba(54, 162, 235, 1)', // Light Blue
                            'rgba(75, 192, 192, 1)', // Light Green
                            'rgba(255, 99, 132, 1)', // Light Red
                            'rgba(153, 102, 255, 1)', // Light Purple
                            'rgba(255, 159, 64, 1)', // Light Orange
                            'rgba(199, 199, 199, 1)', // Light Grey
                            'rgba(255, 205, 86, 1)', // Light Yellow
                            'rgba(54, 162, 235, 1)', // Light Blue
                            'rgba(75, 192, 192, 1)', // Light Green
                            'rgba(255, 99, 132, 1)', // Light Red
                            'rgba(153, 102, 255, 1)', // Light Purple
                            'rgba(255, 159, 64, 1)'  // Light Orange
                        ];

                        var chartTypeMontati = 'pie';
                        var chartTypeSmontati = 'pie';

                        var ctxMontati = document.getElementById('pieChartMontati').getContext('2d');
                        var pieChartMontati = new Chart(ctxMontati, {
                            type: chartTypeMontati,
                            data: {
                                labels: responsabili,
                                datasets: [{
                                    label: 'Totale Motori Montati',
                                    data: motoriMontati,
                                    backgroundColor: colors,
                                    borderColor: borderColors,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    }
                                }
                            }
                        });

                        var ctxSmontati = document.getElementById('pieChartSmontati').getContext('2d');
                        var pieChartSmontati = new Chart(ctxSmontati, {
                            type: chartTypeSmontati,
                            data: {
                                labels: responsabili,
                                datasets: [{
                                    label: 'Totale Motori Smontati',
                                    data: motoriSmontati,
                                    backgroundColor: colors,
                                    borderColor: borderColors,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    }
                                }
                            }
                        });

                        function toggleChartType(chart) {
                            if (chart === 'Montati') {
                                chartTypeMontati = chartTypeMontati === 'pie' ? 'bar' : 'pie';
                                pieChartMontati.destroy();
                                pieChartMontati = new Chart(ctxMontati, {
                                    type: chartTypeMontati,
                                    data: {
                                        labels: responsabili,
                                        datasets: [{
                                            label: 'Totale Motori Montati',
                                            data: motoriMontati,
                                            backgroundColor: colors,
                                            borderColor: borderColors,
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'top'
                                            }
                                        },
                                        scales: chartTypeMontati === 'bar' ? {
                                            y: {
                                                beginAtZero: true
                                            }
                                        } : {}
                                    }
                                });
                            } else if (chart === 'Smontati') {
                                chartTypeSmontati = chartTypeSmontati === 'pie' ? 'bar' : 'pie';
                                pieChartSmontati.destroy();
                                pieChartSmontati = new Chart(ctxSmontati, {
                                    type: chartTypeSmontati,
                                    data: {
                                        labels: responsabili,
                                        datasets: [{
                                            label: 'Totale Motori Smontati',
                                            data: motoriSmontati,
                                            backgroundColor: colors,
                                            borderColor: borderColors,
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'top'
                                            }
                                        },
                                        scales: chartTypeSmontati === 'bar' ? {
                                            y: {
                                                beginAtZero: true
                                            }
                                        } : {}
                                    }
                                });
                            }
                        }

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
                </div>
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
</body>

</html>