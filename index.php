<?php
session_start();
if (isset($_GET['action']) && $_GET['action'] == 'logout')
    session_destroy();
else if (isset($_SESSION['username']))
    header("Location:inserimento.php");

include("common/popup.php");
?>

<!doctype html>
<html lang="en">

<head>
    <title>Title</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <header>
        <!-- place navbar here -->
    </header>
    <main class="p-2 d-flex text-center justify-content-center">
        <?php
        if (isset($_GET['msg']) && trim($_GET['msg']) != '')
            mostraPopup($_GET['msg'], "Esito");
        ?>
        <div class="card m-auto">
            <form class="mb-3 d-flex justify-content flex-column justify-content-center" action="login.php"
                method="post">
                <!-- Header della card -->
                <div class="card-header">
                    <h4 class="p-2 m-0"> Commesse SM</h4>
                </div>

                <!-- Body della card -->
                <div class="card-body">
                    <label for="Username" class="form-label mt-2">Username</label>
                    <input type="text" class="form-control mb-2" name="Username" id="Username" max="255" required />

                    <label for="Password" class="form-label mt-2">Password</label>
                    <div class="input-group mb-2">
                        <input type="password" class="form-control" name="Password" id="Password" max="255" required />
                        <button class="btn p-0 m-0" style="min-width:50px" type="button" id="togglePassword" tabindex="-1">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Footer della card -->
                <div class="card-footer text-muted">
                    <input class="btn w-100 mt-2" type="submit" value="Accedi" name="action">
                </div>
            </form>
        </div>

    </main>
    <footer>
        <!-- place footer here -->
    </footer>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>

    <!-- Script per il toggle della password -->
    <script>
        const passwordInput = document.getElementById('Password');
        const togglePasswordButton = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePasswordButton.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>