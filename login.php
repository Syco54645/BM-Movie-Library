<?php
session_start();
require 'config.php';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('location:index.php');
}

// Login panel
elseif (!isset($_POST['pass'])) {
    $content_output = '<form action="login.php" method="POST">' . $lang['l_panel_pass'] . ':<br/><input class="l_pass" name="pass" type="password" /><br/><input class="l_pass" name="submit" type="submit" value="' . $lang['l_panel_login'] . '" /></form>';
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
        <title><?PHP echo $lang['l_html_login'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link type="text/css" href="css/style.css" rel="stylesheet" media="all" />
    </head>
    <body>
        <div id="l_login">
            <div class="l_box_title"><?PHP echo $lang['l_html_login'] ?></div>
            <div class="l_box_content"><?PHP echo $content_output ?></div>
        </div>
    </body>
</html>