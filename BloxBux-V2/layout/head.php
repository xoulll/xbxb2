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
    <style>
        /* Hide offline chat indicator by default; will be shown only when online */
        #chatcount { display: none !important; }
        #chatindicator { display: none !important; }
    </style>
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
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" id="footer-sample-full" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 16 16">
                        <g transform="translate(1024 0) scale(-1 1)">
                            <path fill="currentColor" d="M1023.65 290.48c.464-23.664-5.904-78.848-77.84-98.064L223.394 47.794c-52.944 0-96 43.055-96 96v128.704l-32-.08c-52.752.223-95.632 43.15-95.632 95.9v668.32C-.6 955.808 41.776 998.176 94.528 998.4l823.68 3.264c65.12.16 138.368-44.928 143.872-134.016 5.504-89.088 8.96-233.6-..."/>
                        </g>
                    </svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" id="footer-sample-full" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 16 16">
                        <g transform="translate(1024 0) scale(-1 1)">
                            <path fill="currentColor" d="M532.528 661.408c-12.512 12.496-12.513 32.752-.001 45.248c6.256 6.256 14.432 9.376 22.624 9.376s16.368-3.12 22.624-9.376l189.008-194L577.7..."/>
                        </g>
                    </svg>
                    <?php
                    if (!$isMobile) {
                        echo "Login";
                    }
                    ?>
                </button>
            <?php endif; ?>
        </nav>
    </header>
    <div class="leftsidebar desktop-only">
        <div class="widthcontainer">
            <a href="./" class="item">
                <svg xmlns="http://www.w3.org/2000/svg" width="60px" viewBox="0 0 97.52 103.71">
                    <path xmlns="http://www.w3.org/2000/svg" d="M97.52,0a43.86,43.86,0,0,1-3.09,13.93c-5.75,14.2-21,24.76-26.88,31-3,3.19-6.27,6.1-9.19,9.35a7.81,7.81,0,0,0-2,4.43c-.12,2.39-1.19,..."/>
                </svg>
                Xflips
            </a>
        </div>
        <a href="https://discord.gg/bloxluck" class="item">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="60" height="60" aria-hidden="true" role="img" id="footer-sample-full" width="1em" height="1em" viewBox="0 0 24 24">
                <path fill="currentColor" d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515a.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0a12.64 12.64 0 0 0-.617-1.25..."/>
            </svg>
        </a>
    </div>
    <div class="rightsidebar desktop-only">
        <div class="widthcontainer" style="height:50px;font-size:24px;flex-direction:row;justify-content:center;">
            <svg id="chatindicator" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" id="footer-sample-full" width="25" height="25" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="11" fill="red"></circle>
            </svg>
            <p id="chatcount">Offline</p>
        </div>
        <div class="widthcontainer" id="chatcontainer">

        </div>
        <form id='chatform' class="widthcontainer" style="height:50px;gap:10px;flex-direction:row;justify-content:space-between;margin-block-end:0em;">
            <input type="text" class="chatbox" id="chatmsg">
            <button type='submit' class="btn-primary mobile" id='chatbtn' style="padding:10px;">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" id="footer-sample-full" width="25" height="25" preserveAspectRatio="xMidYMid meet" viewBox="0 0 16 16">
                    <path fill="currentColor" d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6...."/>
                </svg>
            </button>
        </form>
    </div>
    <div class="main">
