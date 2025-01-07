<?php $pageName = basename($_SERVER['PHP_SELF']); ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<header class="p-2">
    <!-- place navbar here -->
    <nav class="rounded-3 navbar navbar-expand-sm navbar-dark p-2">

        <a class="navbar-brand" href="inserimento.php"><img src="img/logoSm.png" class="ps-2 img-fluid rounded-top" alt="" />
        </a>
        <button class="navbar-toggler d-lg-none me-2" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapsibleNavId" aria-controls="collapsibleNavId" aria-expanded="false"
            aria-label="Toggle navigation"><i class="bi bi-list"></i></button>

        <div class="collapse navbar-collapse" id="collapsibleNavId">
            <ul class="navbar-nav me-auto mt-2 mt-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-center rounded-3 <?= $pageName == 'inserimento.php' ? 'active' : '' ?>"
                        href="inserimento.php" aria-current="page"> Inserimento <span
                            class="visually-hidden">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-3 <?= $pageName == 'elenco.php' ? 'active' : '' ?>" href="elenco.php"
                        aria-current="page"> Elenco <span class="visually-hidden">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-3 <?= $pageName == 'statistiche.php' ? 'active' : '' ?>" href="statistiche.php"
                        aria-current="page"> Statistiche <span class="visually-hidden">(current)</span></a>
                </li>
            </ul>
        </div>
        <div class="welcome d-flex justify-content-between align-items-center">
            <button class="btn m-2" onclick="location.href='inserimento.php'"><strong class="d-inline">Benvenuto <?= $_SESSION['Nome'] ?></strong></button>
            <button class="btn w-auto" style="min-width:auto;" onclick="location.href='index.php?action=logout'">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </nav>


</header>