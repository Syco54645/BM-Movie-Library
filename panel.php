<?PHP
session_start();
include 'config.php';
include 'function.php';

if (!isset($_SESSION['id']) or $_SESSION['id'] !== md5($_SERVER['REMOTE_ADDR']) . md5($panel_pass)) {
    header('location: login.php');
}

if (isset($_GET['option'])) {
    if ($_GET['option'] == 'del_all') {
        $content_output = delete_table($table_name, $lang);
    }
    if ($_GET['option'] == 'create_new') {
        $content_output = create_table($table_name, $lang);
    }
    if ($_GET['option'] == 'xml_file_info') {
        $content_output = xml_file_info($xml_file, $lang, $detect_encoding);
    }
    if ($_GET['option'] == 'nfo_file_info') {
        $content_output = nfo_file_info($table_name, $lang, $detect_encoding);
    }
    if ($_GET['option'] == 'xml_import') {
        $content_output = import_xml($xml_file, $table_name, $lang, $detect_encoding);
    }
    if ($_GET['option'] == 'clear_cache') {
        $content_output = clear_cache($lang);
    }
} else {
    $content_output = database_list($table_name, $lang, $detect_encoding);
}

/*#################
* LEFT PANEL START#
*/#################

// Check table in database
$sql_table = 'SHOW TABLES WHERE Tables_in_' . $mysql_database . ' = "' . $table_name . '"';
$result_table = mysql_query($sql_table);
if (!mysql_num_rows($result_table)) {
    $check_table = '<span class="red">' . $lang['p_tab_no_exists'] . '!</span> <a href="panel.php?option=create_new">' . $lang['p_tab_create'] . '</a>';
    $table_rows = '0';
} else {
    $check_table = '<span class="green">' . $table_name . '</span> <a href="panel.php">' . $lang['p_tab_show'] . '</a> <a href="panel.php?option=del_all" class="p_confirm" title="' . $lang['p_tab_confirm'] . '">' . $lang['p_tab_delete'] . '</a>';
    $sql_movies = 'SELECT id FROM ' . $table_name;
    $result_movies = mysql_query($sql_movies);
    $table_rows = '<span class="green">' . mysql_num_rows($result_movies) . '</span>';
}

// Find xml file
if (file_exists('export/' . $xml_file)) {
    $filesize = round(filesize('export/' . $xml_file) / 1048576, 2) . ' MB';
    $xml = simplexml_load_file('export/' . $xml_file);
    $i_movies = count($xml->movie);
    $check_xml = '<span class="green">' . $i_movies . '</span> ' . $filesize . ' <a href="panel.php?option=xml_file_info">' . $lang['p_xml_show'] . '</a> <a href="panel.php?option=xml_import">' . $lang['p_xml_import'] . '</a>';
} else {
    $check_xml = '<span class="orange">' . $lang['p_xml_not_found'] . '</span>';
}

// Find nfo files
$i_nfo = 0;
$dir = opendir('export/');
while (false !== ($file = readdir($dir))) {
    $info = pathinfo($file);
    if ($file !== "." && $file !== ".." && isset($info['extension']) && $info['extension'] == "nfo") {
        $i_nfo++;
    }
}
closedir();
$check_nfo = ($i_nfo == 0 ? '<span class="orange">' . $lang['p_nfo_not_found'] . '</span>' : '<span class="green">' . $i_nfo . '</span> <a href="panel.php?option=nfo_file_info">' . $lang['p_nfo_show'] . '</a>');

// Check gd library
if (!extension_loaded('gd')) {
    $check_gd = '<span class="red">ERROR</span>';
} else {
    $check_gd = '<span class="green">OK</span>';
}

// Check chmod
$chmod_files = array('export/', 'export/movies/', 'cache/');
$check_chmod = '';
foreach ($chmod_files as $val) {
    if (file_exists($val)) {
    $chmod = substr(decoct(fileperms($val)), 2);
    if ($chmod < 666) {
        $check_chmod.= $val . ' - ' . $chmod . ' <span class="red">' . $lang['p_chmod_change'] . '!</span><br/>';
    } else {
        $check_chmod.= $val . ' - ' . $chmod . ' <span class="green">OK</span><br/>';
    }
    } else {
        $check_chmod.= $val . ' - <span class="red">' . $lang['p_chmod_no_exists'] . '!</span><br/>';
    }
}

// Check cache
$i = 0;
$a = 0;
$check_cache = '';
$dir = opendir('cache/');
while (false !== ($file = readdir($dir))) {
    $info = pathinfo($file);
    if (preg_match('/fanart$/', $info['filename'])) {
        $i++;
    } elseif ($file !== "." && $file !== ".." && $info['extension'] == "jpg") {
        $a++;
    }
}
closedir($dir);
$check_cache.= $lang['p_cache_poster'] . ': ' . $a . '<br/>';
$check_cache.= $lang['p_cache_fanart'] . ': ' . $i . '<br/>';
$check_cache.= '<a href="panel.php?option=clear_cache" class="p_confirm" title="' . $lang['p_cache_confirm'] . '">' . $lang['p_cache_clear'] . '</a>';
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Panel admin</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link type="text/css" href="css/style.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
        <script type="text/javascript">
            $(window).load(function() {
                
                /* Check/uncheck all */
                $('span.select').click(function() {
                    $('input.check').attr('checked', true);
                });
                $('span.unselect').click(function() {
                    $('input.check').removeAttr('checked', true);
                });
                
                /* Confirm */
                var yes = "<?PHP echo $lang['p_jquery_yes'] ?>";
                var no = "<?PHP echo $lang['p_jquery_no'] ?>";
                $(".p_confirm").click(function(){
                    $("#p_confirm")
                    .fadeIn()
                    .html($(this).attr("title") + '<br/><br/><button onClick=\"location.href=\'' + $(this).attr("href") + '\'\">' + yes + '</button> <button> ' + no + ' </button>');
                    return false;
                });
                $("#p_confirm").click(function() {
                    $(this).fadeOut();
                });
                    
                /* Animate buttons */    
                $('.opacity').mouseenter(function(){
                    $(this).animate({
                        opacity: 0.5
                    }, 200 );
                });
                $('.opacity').mouseleave(function(){
                    $(this).animate({
                        opacity: 1
                    }, 200 );
                });
            });
        </script>
    </head>
    <body>
        <div id="p_all">
            <div id="p_panel_left">
                <div class="p_panel_title"><?PHP echo $lang['p_html_admin_panel'] ?></div>
                <div class="p_panel_box"><a href="index.php"><?PHP echo $lang['p_html_library'] ?></a> | <a href="panel.php"><?PHP echo $lang['p_html_admin'] ?></a> | <a href="login.php?logout=1"><?PHP echo $lang['p_html_logout'] ?></a></div>
                <div class="p_panel_title"><?PHP echo $lang['p_html_database'] ?></div>
                <div class="p_panel_box"><?PHP echo $lang['p_html_table'] ?>: <?PHP echo $check_table ?><br><?PHP echo $lang['p_html_movies'] ?>: <?PHP echo $table_rows ?></div>
                <div class="p_panel_title"><?PHP echo $lang['p_html_founded_files'] ?></div>
                <div class="p_panel_box">videodb.xml: <?PHP echo $check_xml ?><br><?PHP echo $lang['p_html_single_files'] ?>: <?PHP echo $check_nfo ?></div>
                <div class="p_panel_title"><?PHP echo $lang['p_html_gd_lib'] ?></div>
                <div class="p_panel_box"><?PHP echo $lang['p_html_gd_stat'] ?>: <?PHP echo $check_gd ?></div>
                <div class="p_panel_title"><?PHP echo $lang['p_html_chmod_stat'] ?></div>
                <div class="p_panel_box"><?PHP echo $check_chmod ?></div>
                <div class="p_panel_title"><?PHP echo $lang['p_html_cache'] ?></div>
                <div class="p_panel_box"><?PHP echo $check_cache ?></div>
            </div>
            <div id="p_panel_content"><?PHP echo $content_output ?></div>
        </div>
        <div id="p_confirm"></div>
    </body>
</html>