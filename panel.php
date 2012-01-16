<?PHP
$time_start = microtime(true);
header('Content-type: text/html; charset=utf-8');
session_start();
require 'config.php';
require 'function.php';

if (!isset($_SESSION['id'])
        or $_SESSION['id'] !== md5($_SERVER['REMOTE_ADDR']) . md5($panel_pass)) {
    header('location: login.php');
}

/* ####################
 * # Sets script mode #
 */####################

$table_check_sql = 'SHOW TABLES LIKE "movie"';
$table_check_result = mysql_query($table_check_sql);
$check = mysql_num_rows($table_check_result);
if ($check == 0) {
    $mode = 2;
} else {
    $col_check_sql = 'SHOW COLUMNS FROM movie WHERE FIELD = "check"';
    $col_check_result = mysql_query($col_check_sql);
    $check = mysql_num_rows($col_check_result);
    if ($check == 0) {
        $mode = 1;
    } else {
        $mode = 2;
    }
}
if ($mode == 1) {
    $content_output = $lang['p_mode_safe'];
} else {
    $content_output = $lang['p_mode_normal'];
}

/* ##########################
 * # Sets available options #
 */##########################

if (isset($_GET['option'])) {
    switch ($_GET['option']) {
        case 'show_table':
            $content_output = database_list($col, $mode, $lang);
            break;
        case 'del_all':
            $content_output = ($mode == 1 ? $lang['p_mode_safe'] : delete_table($lang));
            break;
        case 'create_table':
            $content_output = ($mode == 1 ? $lang['p_mode_safe'] : create_table($col, $lang));
            break;
        case 'xml_file_info':
            $content_output = ($mode == 1 ? $lang['p_mode_safe'] : xml_file_info($xml_file, $lang));
            break;
        case 'nfo_file_info':
            $content_output = ($mode == 1 ? $lang['p_mode_safe'] : nfo_file_info($col, $lang));
            break;
        case 'xml_import':
            $content_output = ($mode == 1 ? $lang['p_mode_safe'] : import_xml($xml_file, $col, $lang));
            break;
        case 'synch':
            $content_output = ($mode == 1 ? $lang['p_mode_safe'] : synch_database($col, $mysql_database, $connect, $remote_connection, $lang));
            break;
        case 'delete_cache':
            $content_output = delete_cache($lang);
            break;
        case 'create_cache':
            $content_output = create_cache($col, $lang);
            break;
        case 'clear_cache':
            $content_output = clear_cache($col, $lang);
            break;
    }
}

/* ##############
 * # LEFT PANEL #
 */##############

/* ###########################
 * # Check table in database #
 */###########################

$sql_table = 'SHOW TABLES LIKE "movie"';
$result_table = mysql_query($sql_table);
if (!mysql_num_rows($result_table)) {
    $check_table = '<span class="red">' . $lang['p_tab_no_exists'] . '!</span> ' . ($mode == 1 ? '' : '<a href="panel.php?option=create_table">' . $lang['p_tab_create'] . '</a>');
    $table_rows = '0';
} else {
    $check_table = '<span class="green">movie</span> <a href="panel.php?option=show_table">' . $lang['p_tab_show'] . '</a> ' . ($mode == 1 ? '' : '<a href="panel.php?option=del_all" class="p_confirm" title="' . $lang['p_tab_confirm'] . '">' . $lang['p_tab_delete'] . '</a>');
    $sql_movies = 'SELECT COUNT(*) FROM movie';
    $result_movies = mysql_query($sql_movies);
    $table_rows = '<span class="green">' . mysql_result($result_movies, 0) . '</span>';
}

/* #################
 * # Find xml file #
 */#################

if ($mode == 1) {
    $check_xml = '';
} else {
    if (file_exists('export/' . $xml_file)) {
        $filesize = round(filesize('export/' . $xml_file) / 1048576, 2) . ' MB';
        $xml = @simplexml_load_file('export/' . $xml_file);
        if ($xml) {
            $i_movies = count($xml->movie);
            $check_xml = '<span class="green">' . $i_movies . '</span> ' . $filesize . ' <a href="panel.php?option=xml_file_info">' . $lang['p_xml_show'] . '</a> <a href="panel.php?option=xml_import">' . $lang['p_xml_import'] . '</a>';
        } else {
            $check_xml = '<span class="green">0</span> <a href="panel.php?option=xml_file_info">' . $lang['p_xml_show'] . '</a>';
        }
    } else {
        $check_xml = '<span class="orange">' . $lang['p_xml_not_found'] . '</span>';
    }
}

/* ##################
 * # Find nfo files #
 */##################

if ($mode == 1) {
    $check_nfo = '';
} else {
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
}

/* ###########################
 * # Check remote connection #
 */###########################

$connect_remote = @mysql_connect($remote_connection[0] . ':' . $remote_connection[1], $remote_connection[2], $remote_connection[3]);
$database_remote = @mysql_select_db($remote_connection[4], $connect_remote);
if (!$connect_remote) {
    $remote_conn_info = '<span class="orange">' . $lang['p_synch_no_conn'] . '</span>';
} elseif (!$database_remote) {
    $remote_conn_info = $lang['p_synch_conn_to'] . ': <span class="green">' . $mysql_host_remote . '</span><br /><span class="red">' . $lang['p_synch_cant_select'] . ': ' . $remote_connection[4] . '</span>';
} else {
    $remote_conn_info = $lang['p_synch_conn_to'] . ': <span class="green">' . $mysql_host_remote . '</span><br />';

    // Check id movie from remote
    $remote_sql = 'SELECT ' . $col['id_movie'] . ' FROM movie ORDER BY ' . $col['id_movie'];
    $remote_result = mysql_query($remote_sql, $connect_remote);
    $id_remote_assoc = array();
    while ($remote = mysql_fetch_assoc($remote_result)) {
        array_push($id_remote_assoc, $remote[$col['id_movie']]);
    }
    mysql_close();

    // Check id movie from local
    mysql_connect($mysql_host . ':' . $mysql_port, $mysql_login, $mysql_pass);
    mysql_select_db($mysql_database);
    $local_sql = 'SELECT ' . $col['id_movie'] . ' FROM movie ORDER BY ' . $col['id_movie'];
    $local_result = mysql_query($local_sql);
    $id_local_assoc = array();
    if ($local_result) {
        while ($local = mysql_fetch_assoc($local_result)) {
            array_push($id_local_assoc, $local[$col['id_movie']]);
        }
    }
    
    // Set movie to remove
    $i = 0;
    foreach ($id_local_assoc as $val) {
        if (!in_array($val, $id_remote_assoc)) {
            $i++;
        }
    }
    $movie_to_remove = $i;

    // Set movie to add
    $i = 0;
    foreach ($id_remote_assoc as $val) {
        if (!in_array($val, $id_local_assoc)) {
            $i++;
        }
    }
    $movie_to_add = $i;
    if ($movie_to_add == 0 && $movie_to_remove == 0) {
        $remote_conn_info.= '<span class="green">' . $lang['p_synch_database_synch'] . '</span>';
    } else {
        $remote_conn_info.= $lang['p_synch_movie_to_remove'] . ': <span class="orange">' . $movie_to_remove . '</span><br />' . $lang['p_synch_movie_to_add'] . ': <span class="orange">' . $movie_to_add . '</span><br /><a href="panel.php?option=synch">' . $lang['p_synch_synch'] . '</a>';
    }
}

/* ####################
 * # Check gd library #
 */####################

if (!extension_loaded('gd')) {
    $check_gd = '<span class="red">ERROR</span>';
} else {
    $check_gd = '<span class="green">OK</span>';
}

/* ###############
 * # Check chmod #
 */###############

if ($mode == 1) {
    $chmod_files = array('cache/');
} else {
    $chmod_files = array('export/', 'cache/');
}
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

/* ###############
 * # Check cache #
 */###############

$check_cache = '';
$dir = opendir('cache/');
$cache_poster_assoc = array();
$cache_fanart_assoc = array();
while (false !== ($file = readdir($dir))) {
    $info = pathinfo($file);
    if ($file !== "." && $file !== ".." && $info['extension'] == "jpg") {
        if (preg_match('/fanart$/', $info['filename'])) {
            array_push($cache_fanart_assoc, $info['filename']);
        } else {
            array_push($cache_poster_assoc, $info['filename']);
        }
    }
}
closedir($dir);
$check_cache.= $lang['p_cache_poster'] . ': ' . count($cache_poster_assoc) . '<br/>';
$check_cache.= $lang['p_cache_fanart'] . ': ' . count($cache_fanart_assoc) . '<br/>';
$check_cache.= '<a href="panel.php?option=delete_cache" class="p_confirm" title="' . $lang['p_cache_confirm'] . '">' . $lang['p_cache_delete'] . '</a>';
$create_cache = '<a href="panel.php?option=create_cache">' . $lang['p_cache_create'] . '</a>';
$clear_cache = '<a href="panel.php?option=clear_cache">' . $lang['p_cache_clear'] . '</a>';

/* #######################
 * # Script execute time #
 */#######################

$time_end = microtime(true);
$time = $time_end - $time_start;
$time = round($time, 2) . ' s';
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $lang['p_html_admin_panel'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link type="text/css" href="css/style.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.panel.js"></script>
    </head>
    <body>
        <div id="p_panel_left">
            <div class="p_box_title"><?PHP echo $lang['p_html_admin_panel'] ?></div>
            <div class="p_box_content"><a href="index.php"><?PHP echo $lang['p_html_library'] ?></a> | <a href="panel.php"><?PHP echo $lang['p_html_admin'] ?></a> | <a href="login.php?logout=1"><?PHP echo $lang['p_html_logout'] ?></a></div>
            <div class="p_box_title"><?PHP echo $lang['p_html_database'] ?></div>
            <div class="p_box_content"><?PHP echo $lang['p_html_table'] ?>: <?PHP echo $check_table ?><br><?PHP echo $lang['p_html_movies'] ?>: <?PHP echo $table_rows ?></div>
            <div class="p_box_title"><?PHP echo $lang['p_html_founded_files'] ?></div>
            <div class="p_box_content">videodb.xml: <?PHP echo $check_xml ?><br><?PHP echo $lang['p_html_single_files'] ?>: <?PHP echo $check_nfo ?></div>
            <div class="p_box_title"><?PHP echo $lang['p_html_remote_con'] ?></div>
            <div class="p_box_content"><?PHP echo $remote_conn_info ?></div>
            <div class="p_box_title"><?PHP echo $lang['p_html_gd_lib'] ?></div>
            <div class="p_box_content"><?PHP echo $lang['p_html_gd_stat'] ?>: <?PHP echo $check_gd ?></div>
            <div class="p_box_title"><?PHP echo $lang['p_html_chmod_stat'] ?></div>
            <div class="p_box_content"><?PHP echo $check_chmod ?></div>
            <div class="p_box_title"><?PHP echo $lang['p_html_cache'] ?></div>
            <div class="p_box_content"><?PHP echo $check_cache . ' | ' . $create_cache . ' | ' . $clear_cache ?></div>
            Script execute time: <?PHP echo $time ?>
        </div>
        <div id="p_panel_content"><?PHP echo $content_output ?></div>
        <div id="p_confirm"><button id="yes"><?PHP echo $lang['p_jquery_yes'] ?></button> <button id="no"><?PHP echo $lang['p_jquery_no'] ?></button></div>
    </body>
</html>