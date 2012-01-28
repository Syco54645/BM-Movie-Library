<?PHP
header('Content-type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'function.php';

/* ##################
 * # CHECK FIRST ID #
 */##################
$id_sql = 'SELECT ' . $col['id_movie'] . ' FROM movie LIMIT 1, 1';
$id_result = mysql_query($id_sql);
if (!$id_result or mysql_num_rows($id_result) < 1) {
    header('Location: panel.php');
    die('Go to panel.php');
}

// Set id
if (!isset($_GET['id'])) {
    $id_assoc = mysql_fetch_array($id_result);
    $id = $id_assoc[$col['id_movie']];
} else {
    $id = $_GET['id'];
}

/* ##########
 * # SEARCH #
 */##########
if ($search == '') {
    $search_text = '<input type="text" name="search" class="search"><input type="image" class="search_img opacity" src="img/search.png" title="' . $lang['i_search'] . '" alt="Search" />';
    $search_mysql = '%';
} else {
    $search_text = $lang['i_result'] . ': ' . $search . ' <a href="index.php"><img class="opacity" src="img/delete.png" title="' . $lang['i_search_del'] . '" alt=""></a>';
    $search_mysql = $search;
}

/* ########
 * # SORT #
 */########
$sort_array = array(1 => $lang['i_title'], $lang['i_year'], $lang['i_rating']);
$sort_menu = '';
foreach ($sort_array as $key => $val) {
    $sort_menu.= ($sort == $key ? ' ' . $val . ' ' : ' <a href="index.php?sort=' . $key . '&amp;id=' . $id . '&amp;genre=' . $genre . '">' . $val . '</a> ');
}
$sort_mysql = array(1 => $col['title'] . ' ASC', $col['year'] . ' DESC', $col['rating'] . ' DESC');

/* ##########
 * # GENRES #
 */##########
$genre_sql = 'SELECT ' . $col['genre'] . ' FROM movie ORDER by ' . $col['genre'];
$genre_result = mysql_query($genre_sql);
$genre_array = array();
while ($genre_mysql_array = mysql_fetch_array($genre_result)) {
    foreach (explode(' / ', $genre_mysql_array[$col['genre']]) as $val) {
        if (!in_array($val, $genre_array) && strlen($val) > 2) {
            $genre_array[] = $val;
        }
    }
}
$genre_menu = '<div class="genre">' . ($genre == 'all' ? $lang['i_all'] : '<a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=all">' . $lang['i_all'] . '</a>') . '</div>';
sort($genre_array);
$genre_mysql = '%';
foreach ($genre_array as $key => $val) {
    if ((string) $key === (string) $genre) {
        $genre_menu.= '<div class="genre">' . $val . '</div>';
        $genre_mysql = $val;
    } else {
        $genre_menu.= '<div class="genre"><a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=' . $key . '">' . $val . '</a></div>';
    }
}

/* #############
 * # PANEL NAV #
 */#############
$nav_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['title'] . ', ' . $col['rating'] . ', ' . $col['year'] . ', ' . $col['genre'] . ', ' . $col['country'] . ' FROM movie WHERE ' . $col['genre'] . ' LIKE "%' . $genre_mysql . '%" AND ' . $col['title'] . ' LIKE "%' . $search_mysql . '%" ORDER by ' . $sort_mysql[$sort];
$nav_result = mysql_query($nav_sql);
$row = mysql_num_rows($nav_result);
if ($per_page == 0) {
    $i_pages = 1;
    $nav = '';
} else {
    $i_pages = (ceil($row / $per_page));
    $nav = ($page == 1 ? '<img src="img/first_g.png" alt="">' : '<a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=' . $genre . '&amp;page=1&amp;search=' . $search . '"><img class="opacity" src="img/first.png" title="' . $lang['i_first'] . '" alt=""></a>') . ' ' .
            ($page == 1 ? '<img src="img/previous_g.png" alt="">' : '<a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=' . $genre . '&amp;page=' . ($page - 1) . '&amp;search=' . $search . '"><img class="opacity" src="img/previous.png" title="' . $lang['i_previous'] . '" alt=""></a>') . ' ' .
            $lang['i_page'] . ' ' . $page . ' / ' . $i_pages . ' ' .
            ($page == $i_pages ? '<img src="img/next_g.png" alt="">' : '<a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=' . $genre . '&amp;page=' . ($page + 1) . '&amp;search=' . $search . '"><img class="opacity" src="img/next.png" title="' . $lang['i_next'] . '" alt=""></a>') . ' ' .
            ($page == $i_pages ? '<img src="img/last_g.png" alt="">' : '<a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=' . $genre . '&amp;page=' . $i_pages . '&amp;search=' . $search . '"><img class="opacity" src="img/last.png" title="' . $lang['i_last'] . '" alt=""></a>');
}

/* ##############
 * # MOVIE LIST #
 */##############
if ($per_page == 0) {
    $limit_sql = '';
} else {
    $start = ($page - 1) * $per_page;
    $limit_sql = ' LIMIT ' . $start . ', ' . $per_page;
}
$list_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['title'] . ', ' . $col['rating'] . ', ' . $col['year'] . ', ' . $col['plot'] . ', ' . $col['runtime'] . ', ' . $col['genre'] . ', ' . $col['country'] . ', ' . $col['director'] . ' FROM movie WHERE ' . $col['genre'] . ' LIKE "%' . $genre_mysql . '%" AND ' . $col['title'] . ' LIKE "%' . $search_mysql . '%" ORDER by ' . $sort_mysql[$sort] . $limit_sql;
$list_result = mysql_query($list_sql);
$panel_list = '';
while ($list = mysql_fetch_array($list_result)) {
    $v_width_result = mysql_query('SELECT idFile, iStreamType, iVideoWidth FROM streamdetails WHERE idFile=' . $list[$col['id_movie']] . ' AND iStreamType=0');
    $v_width = mysql_fetch_array($v_width_result);
    if ($v_width['iVideoWidth'] > 1279) {
        $flag_vres = '<img src="img/hd.png" alt="">';
    } else {
        $flag_vres = '';
    }
    $panel_list.= '<tr><td><div id="' . $list[$col['id_movie']] . '" class="movie_title"><a href="index.php?sort=' . $sort . '&amp;id=' . $list[$col['id_movie']] . '&amp;genre=' . $genre . '&amp;page=' . $page . '&amp;search=' . $search . '" title="' . $list[$col['title']] . '">' . $list[$col['title']] . '</a></div></td><td>' . $list[$col['year']] . '</td><td>' . round($list[$col['rating']], 1) . '</td><td>' . $flag_vres . '</td></tr>';
}

/* #########
 * # MOVIE #
 */#########
$movie_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['id_file'] . ', ' . $col['title'] . ', ' . $col['rating'] . ', ' . $col['year'] . ', ' . $col['poster'] . ', ' . $col['plot'] . ', ' . $col['tagline'] . ', ' . $col['runtime'] . ', ' . $col['fanart'] . ', ' . $col['genre'] . ', ' . $col['country'] . ', ' . $col['director'] . ', ' . $col['originaltitle'] . ', ' . $col['trailer'] . ' FROM movie WHERE ' . $col['id_movie'] . '=' . $id;
$movie_result = mysql_query($movie_sql);
$movie = mysql_fetch_array($movie_result);

$movie_stream_v_sql = 'SELECT strVideoCodec, fVideoAspect, iVideoWidth, iVideoHeight FROM streamdetails WHERE idFile = ' . $movie[$col['id_file']] . ' AND iStreamType = 0';
$movie_stream_v_result = mysql_query($movie_stream_v_sql);
$movie_stream_v = mysql_fetch_array($movie_stream_v_result);

$movie_stream_a_sql = 'SELECT strAudioCodec, iAudioChannels FROM streamdetails WHERE idFile = ' . $movie[$col['id_file']] . ' AND iStreamType = 1';
$movie_stream_a_result = mysql_query($movie_stream_a_sql);
$movie_stream_a = mysql_fetch_array($movie_stream_a_result);

// Check poster and fanart
$poster = 'cache/' . $movie[$col['id_movie']] . '.jpg';
$fanart = 'cache/' . $movie[$col['id_movie']] . '-fanart.jpg';
if (!file_exists($poster) or !file_exists($fanart)) {
    preg_match_all('/<thumb.*?>(.*?)<\/thumb>/', $movie[$col['poster']], $poster_path);
    preg_match_all('/<thumb.*?>(.*?)<\/thumb>/', $movie[$col['fanart']], $fanart_path);
    $poster_path = (isset($poster_path[1]) ? $poster_path[1] : '');
    $fanart_path = (isset($fanart_path[1]) ? $fanart_path[1] : '');
    gd_convert($movie[$col['id_movie']], $poster_path, $fanart_path);
}
if (!file_exists($poster)) {
    $poster = 'img/d_poster.jpg';
}
if (!file_exists($fanart)) {
    $fanart = 'img/d_fanart.jpg';
}

// video resolution
$i = 0;
foreach ($width_height as $key => $val) {
    if ($movie_stream_v['iVideoWidth'] >= $key or $movie_stream_v['iVideoHeight'] >= $val) {
        $img_flag_vres = '<img id="vres" src="img/flags/vres_' . $vres_array[$i] . '.png" alt="">';
    }
    $i++;
}

// video codec
if (isset($vtype[$movie_stream_v['strVideoCodec']])) {
    $img_flag_vtype = '<img id="vtype" src="img/flags/vcodec_' . $vtype[$movie_stream_v['strVideoCodec']] . '.png" alt="">';
} else {
    $img_flag_vtype = '<img id="vtype" src="img/flags/vcodec_defaultscreen.png" alt="">';
}

// audio codec 
if (isset($atype[$movie_stream_a['strAudioCodec']])) {
    $img_flag_atype = '<img id="atype" src="img/flags/acodec_' . $atype[$movie_stream_a['strAudioCodec']] . '.png" alt="">';
} else {
    $img_flag_atype = '<img id="atype" src="img/flags/acodec_defaultsound.png" alt="">';
}

// audio channel
if (isset($achan[$movie_stream_a['iAudioChannels']])) {
    $img_flag_achan = '<img id="achan" src="img/flags/achan_' . $achan[$movie_stream_a['iAudioChannels']] . '.png" alt="">';
} else {
    $img_flag_achan = '<img id="achan" src="img/flags/achan_defaultsound.png" alt="">';
}
$img_flag = $img_flag_vres . $img_flag_vtype . $img_flag_atype . $img_flag_achan;

// trailer
$trailer = trailer($movie[$col['title']], $movie[$col['year']], $movie[$col['originaltitle']], $movie[$col['trailer']]);
if ($trailer !== '') {
    $trailer_thumb = '<img id="trailer_thumb" class="jq_hide" src="http://img.youtube.com/vi/' . $trailer . '/default.jpg"><img id="trailer_play" class="opacity jq_hide" src="img/trailer.png" alt="" />';
} else {
    $trailer_thumb = '';
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $movie[$col['title']] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link type="text/css" href="css/style.css" rel="stylesheet" media="all" />
        <link type="text/css" href="css/jquery.jscrollpane.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.mousewheel.js"></script>
        <script type="text/javascript" src="js/jquery.jscrollpane.min.js"></script>
        <script type="text/javascript" src="js/jquery.index.js"></script>
    </head>
    <body>
        <img id="bg" src="<?PHP echo $fanart ?>" alt="<?PHP echo $movie[$col['id_movie']] ?>" />
        <div id="panel_top" class="jq_hide">
            <div id="title"><?PHP echo $movie[$col['title']] ?></div>
            <div id="tagline"><?PHP echo $movie[$col['tagline']] ?></div>
        </div>
        <table id="info" class="jq_hide">
            <tr>
                <td><?PHP echo $lang['i_year'] ?>:</td>
                <td><?PHP echo $movie[$col['year']] ?></td>
            </tr>
            <tr>
                <td><?PHP echo $lang['i_genre'] ?>:</td>
                <td><?PHP echo $movie[$col['genre']] ?></td>
            </tr>
            <tr>
                <td><?PHP echo $lang['i_rating'] ?>:</td>
                <td><?PHP echo round($movie[$col['rating']], 1) ?></td>
            </tr>
            <tr>
                <td><?PHP echo $lang['i_country'] ?>:</td>
                <td><?PHP echo $movie[$col['country']] ?></td>
            </tr>
            <tr>
                <td><?PHP echo $lang['i_runtime'] ?>:</td>
                <td><?PHP echo $movie[$col['runtime']] . ' ' . $lang['i_minute'] ?></td>
            </tr>
            <tr>
                <td><?PHP echo $lang['i_director'] ?>:</td>
                <td><?PHP echo $movie[$col['director']] ?></td>
            </tr>
        </table>
        <div id="panel_right" class="jq_hide">
            <div id="panel_list" class="scroll">
                <img id="options" class="opacity" src="img/options.png" title="<?PHP echo $lang['i_options'] ?>" alt="" />
                <div class="nav"><?PHP echo $nav ?></div>
                <div class="bold"><?PHP echo $lang['i_list_title'] ?>:</div>
                <table id="list"><?PHP echo $panel_list ?></table>
                <nav class="nav"><?PHP echo $nav ?></nav>
            </div>
            <div id="panel_options">
                <img id="back" class="opacity" src="img/back.png" title="<?PHP echo $lang['i_back'] ?>" alt="" />
                <div class="bold"><?PHP echo $lang['i_search'] ?>:</div>
                <form method="get" action="index.php"><?PHP echo $search_text ?></form>
                <div class="bold"><?PHP echo $lang['i_sort'] ?>:</div>
                <?PHP echo $sort_menu ?>
                <div class="bold"><?PHP echo $lang['i_genre'] ?>:</div>
                <div id="genre_menu"><?PHP echo $genre_menu ?></div>
            </div>
        </div>
        <div id="panel_bottom" class="jq_hide">
            <div id="plot_text"><span id="plot"><?PHP echo $lang['i_plot'] ?>: </span><?PHP echo $movie[$col['plot']] ?></div>
            <img id="poster" src="<?PHP echo $poster ?>" alt="" />
            <div id="img_flag"><?PHP echo $img_flag ?></div>
        </div>
        <?PHP echo $trailer_thumb ?>
        <div id="trailer">
        <div id="mediaplayer"></div>
        <script type="text/javascript" src="jwplayer.js"></script>
        <script type="text/javascript">
            var id_yt='<?PHP echo $trailer ?>';
            if (id_yt !== '') {
                jwplayer("mediaplayer").setup({
                    flashplayer: "player.swf",
                    file: 'http://www.youtube.com/watch?v='+id_yt,
                    skin: 'glow.zip',
                    autostart: 'false',
                    stretching: 'fill',
                    controlbar: 'over',
                    width: '470',
                    height: '320'
                });
            }
        </script>
        </div>
    </body>
</html>
