<?php
session_start();
include 'config.php';

// Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['id']);
    session_destroy();
    header('location:index.php');
}

// Login panel
if (!isset($_POST['pass'])) {
    $content_output = '<form action="login.php" method="POST">' . $lang['l_panel_pass'] . ':<br/><input id="l_pass" name="pass" type="password" /></form>';
} else {
    if ($_POST['pass'] === $panel_pass) {
        $_SESSION['id'] = md5($_SERVER['REMOTE_ADDR']) . md5($panel_pass);
        header('location: panel.php');
    } else {
        $content_output = $lang['l_panel_wrong'] . '. <a href="login.php">' . $lang['l_panel_again'] . '</a>';
    }
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Panel admin</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link type="text/css" href="css/style.css" rel="stylesheet" media="all" />
    </head>
    <body>
        <div id="l_login">
            <div class="l_panel_title"><?PHP echo $lang['l_html_login'] ?></div>
            <div class="l_panel_box"><?PHP echo $content_output ?></div>
        </div>
    </body>
</html>