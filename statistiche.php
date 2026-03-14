<?php
include("common/check_session.php");
include("common/connection.php");
include("common/popup.php");

// Esegui la query
$query = "
    SELECT r.nome, 
           COUNT(CASE WHEN c.id_responsabile_mont IS NOT NULL THEN 1 END) AS motori_montati,
           COUNT(CASE WHEN c.id_responsabile_smont IS NOT NULL THEN 1 END) AS motori_smontati
    FROM responsabile r
    LEFT JOIN commessa c ON r.id = c.id_responsabile_mont OR r.id = c.id_responsabile_smont
    GROUP BY r.nome
";

$result = $conn->query($query);

$responsabili = [];
$motoriMontati = [];
$motoriSmontati = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
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

    <main class="p-2 d-flex text-center justify-content-center">
        <div class="container">
            <h2>Resoconto Responsabili</h2>
            <div class="row">
                <?php foreach ($responsabili as $index => $responsabile) { ?>
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h4 class="my-0 fw-normal"><?php echo $responsabile; ?></h4>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li>Motori Montati: <?php echo $motoriMontati[$index]; ?> - Motori Smontati: <?php echo $motoriSmontati[$index]; ?></li>
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
                                            },
                                            scales: {
                                                y: {
                                                    beginAtZero: true
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