<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

   

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap JS -->
    <script src="bootstrap/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>

<?php
function Nav_Bar($links) {
?>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top bg-white shadow-sm">
        <div class="container-fluid">

            <!-- LOGO -->
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="#">
                <i class="fas fa-shield-alt fa-2x me-3"></i>
                SecuScan
            </a>

            <!-- MOBILE TOGGLER -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- NAV CONTENT -->
            <div class="collapse navbar-collapse" id="mainNav">

                <!-- LINKS -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php
                    foreach ($links as $name => $url) {
                        $is_active = (basename($_SERVER["PHP_SELF"]) === basename($url)) ? "active fw-bold" : "";
                        echo '
                        <li class="nav-item">
                            <a class="nav-link ' . $is_active . '" href="' . htmlspecialchars($url) . '">
                                ' . htmlspecialchars($name) . '
                            </a>
                        </li>';
                    }
                    ?>
                </ul>

                <!-- USER DROPDOWN -->
                <?php if (isset($_SESSION["username"])): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i>
                            <?= htmlspecialchars($_SESSION["username"]) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
              
                        </ul>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </nav>

    <!-- SPACER (because navbar is fixed-top) -->
    <div style="height:10px;"></div>

<?php
}
?>

</body>
</html>
