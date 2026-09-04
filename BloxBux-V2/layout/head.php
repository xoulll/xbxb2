<?php
if (false and !isset($_GET["lemmein"])) { //Change To True for Maintenance;
    include_once "error.php";
    exit();
}
//Stop Direct Access to the File
//Works only in PHP 5.0 and Up
if (get_included_files()[0] == __FILE__) {
    exit("<h1>Access Denied</h1>");
}

//Stop Including This File Twice
if (defined(strtoupper(basename(__FILE__, ".php")) . "_PHP")) {
    return True;
}
define(strtoupper(basename(__FILE__, ".php")) . "_PHP", True);

include_once "php/main.php";
include_once "php/session_handler.php";
include_once "php/roblox_handler.php";
include_once "php/giveaway_handler.php";

checkGiveaways()
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Deposit items and coinflip them on Xflips - sleek blue/black coinflip experience.">
    <meta name="keywords" content="mm2, xflips, coinflip, items, bet">
    <meta name="theme-color" content="#0b1220">
    <meta property="og:title" content="Xflips - Coinflip" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://xflips.example" />
    <meta property="og:image" content="https://xflips.example/img/favicon.png" />
    <meta property="og:description" content="Deposit items and coinflip them on Xflips - sleek blue/black coinflip experience." />

    <!-- Title -->
    <title>Xflips - Coinflip</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/sweetalert2.all.min.js"></script>
    <script src="js/textFit.min.js"></script>
    <script src="js/socket.io.min.js"></script>

    <!-- Links -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/xflips.css">
    <link rel="icon" href="img/favicon.png">
    <link rel="stylesheet" href="css/sweetalert2-dark.css">
    <link rel="stylesheet" href="css/coin.css">

    <!-- AmpDuel-like tweaks: include ampduel-specific UI script -->
    <script defer src="js/ampduel-ui.js"></script>

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-YOURTAGHERE"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-YOURTAGHERE');
    </script>
</head>

<body>
    <header>
        <div class="logo">
            <img src="img/favicon.png" alt="Xflips">
        </div>
        <nav>
            <button onclick="toggleMenu()" class="btn-primary mobile mobile-only">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" id="footer-sample-full" width="25" height="25" preserveAspectRatio="xMidYMid meet" viewBox="0 0 16 16">
                    <path fill="currentColor" fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"></path>
                </svg>
            </button>
            <?php if ($session) : ?>
                <!-- Logged In Only -->
                <button onclick='window.location.href = "inventory.php"' class="btn-primary <?php if ($isMobile) {
                                                                                            echo "mobile";
                                                                                        } ?>">
                    <?php
                    if (!$isMobile) {
                        echo "Inventory";
                    }
                    ?>
                </button>
                <img onclick="logOut()" class="clickable userthumb" src="<?php echo getUserThumbnail($session["user_id"]) ?>">
            <?php else : ?>
                <!-- Logged Out Only -->
                <button onclick='login()' class="btn-primary <?php if ($isMobile) {
                                                                    echo "mobile";
                                                                } ?>">
                    <?php
                    if (!$isMobile) {
                        echo "Login";
                    }
                    ?>
                </button>
            <?php endif; ?>
        </nav>
    </header>
