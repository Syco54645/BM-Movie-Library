<?PHP
header('Content-type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'function.php';

/* ##################
 * # CHECK FIRST ID #
 */##################
$id_sql = 'SELECT id FROM ' . $table_name . ' LIMIT 1';
$id_result = mysql_query($id_sql);
if (!$id_result or mysql_num_rows($id_result) < 1) {
    header('Location: panel.php');
    die('Go to panel.php');
}

// Set id
if (!isset($_GET['id'])) {
    $id_assoc = mysql_fetch_array($id_result);
    $id = $id_assoc['id'];
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
$sort_mysql = array(1 => 'title ASC', 'year DESC', 'rating DESC');

/* ##########
 * # GENRES #
 */##########
$genre_sql = 'SELECT genre FROM ' . $table_name . ' ORDER by genre';
$genre_result = mysql_query($genre_sql);
$genre_array = array();
while ($genre_mysql_array = mysql_fetch_array($genre_result)) {
    foreach (explode(' / ', $genre_mysql_array['genre']) as $val) {
        if (!in_array($val, $genre_array) && strlen($val) > 2) {
            $genre_array[] = $val;
        }
    }
}
$genre_menu = '<div class="genre">' . ($genre == 'all' ? $lang['i_all'] : '<a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=all">' . $lang['i_all'] . '</a>') . '</div>';
$genre_sort = sort($genre_array);
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
$nav_sql = 'SELECT id, title, rating, year, genre, country FROM ' . $table_name . ' WHERE genre LIKE "%' . $genre_mysql . '%" AND title LIKE "%' . $search_mysql . '%" ORDER by ' . $sort_mysql[$sort];
$nav_result = mysql_query($nav_sql);
$row = mysql_num_rows($nav_result);
if ($per_page == 0) {
    $i_pages = 1;
    $nav = '';
} else {
    $i_pages = (ceil($row / $per_page));
    $nav =  ($page == 1 ? '<img src="img/first_g.png" alt="">' : '<a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=' . $genre . '&amp;page=1&amp;search=' . $search . '"><img class="opacity" src="img/first.png" title="' . $lang['i_first'] . '" alt=""></a>') . ' ' .
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
$list_sql = 'SELECT id, title, rating, year, plot, runtime, genre, country, director, v_codec, v_aspect, v_width, v_height, a_codec, a_channels FROM ' . $table_name . ' WHERE genre LIKE "%' . $genre_mysql . '%" AND title LIKE "%' . $search_mysql . '%" ORDER by ' . $sort_mysql[$sort] . $limit_sql;
$list_result = mysql_query($list_sql);
$panel_list = '';
while ($list = mysql_fetch_array($list_result)) {
    if ($list['v_width'] > 1279) {
        $flag_vres = '<img src="img/hd.png" alt="">';
    } else {
        $flag_vres = '';
    }
    $panel_list.= '<tr><td><div id="' . $list['id'] . '" class="movie_title"><a href="index.php?sort=' . $sort . '&amp;id=' . $list['id'] . '&amp;genre=' . $genre . '&amp;page=' . $page . '&amp;search=' . $search . '" title="' . $list['title'] . '">' . $list['title'] . '</a></div></td><td>' . $list['year'] . '</td><td>' . $list['rating'] . '</td><td>' . $flag_vres . '</td></tr>';
}

/* #########
 * # MOVIE #
 */#########
$movie_sql = 'SELECT id, title, rating, year, plot, tagline, runtime, genre, country, director, v_codec, v_aspect, v_width, v_height, a_codec, a_channels, img_poster, img_fanart FROM ' . $table_name . ' WHERE id=' . $id;
$movie_result = mysql_query($movie_sql);
$movie = mysql_fetch_array($movie_result);

$poster = 'cache/' . $movie['id'] . '.jpg'; 
if ($movie['img_poster'] == 'no_poster') {
    $poster = 'img/d_poster.jpg';
} elseif (!file_exists('cache/' . $movie['id'] . '.jpg')) {
    gd_convert($movie['id'], $movie['img_poster'], '');
}

$fanart = 'cache/' . $movie['id'] . '-fanart.jpg';
if ($movie['img_fanart'] == 'no_fanart') {
    $fanart = 'img/d_fanart.jpg';
} elseif (!file_exists('cache/' . $movie['id'] . '-fanart.jpg')) {
    gd_convert($movie['id'], '', $movie['img_fanart']);
}

// video resolution
$i = 0;
foreach ($width_height as $key => $val) {
    if ($movie['v_width'] >= $key || $movie['v_height'] >= $val) {
        $img_flag_vres = '<img id="vres" src="img/flags/vres_' . $vres_array[$i] . '.png" alt="">';
    }
    $i++;
}

// video codec
if (isset($vtype[$movie['v_codec']])) {
    $img_flag_vtype = '<img id="vtype" src="img/flags/vcodec_' . $vtype[$movie['v_codec']] . '.png" alt="">';
} else {
    $img_flag_vtype = '<img id="vtype" src="img/flags/vcodec_defaultscreen.png" alt="">';
}

// audio codec 
if (isset($atype[$movie['a_codec']])) {
    $img_flag_atype = '<img id="atype" src="img/flags/acodec_' . $atype[$movie['a_codec']] . '.png" alt="">';
} else {
    $img_flag_atype = '<img id="atype" src="img/flags/acodec_defaultsound.png" alt="">';
}

// audio channel
if (isset($achan[$movie['a_channels']])) {
    $img_flag_achan = '<img id="achan" src="img/flags/achan_' . $achan[$movie['a_channels']] . '.png" alt="">';
} else {
    $img_flag_achan = '<img id="achan" src="img/flags/achan_defaultsound.png" alt="">';
}
$img_flag = $img_flag_vres . $img_flag_vtype . $img_flag_atype . $img_flag_achan;
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $movie["title"] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link type="text/css" href="css/style.css" rel="stylesheet" media="all" />
        <link type="text/css" href="css/jquery.jscrollpane.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.mousewheel.js"></script>
        <script type="text/javascript" src="js/jquery.jscrollpane.min.js"></script>
        <script type="text/javascript" src="js/jquery.index.js"></script>
    </head>
    <body>
        <img id="bg" src="<?PHP echo $fanart ?>" alt="<?PHP echo $movie["id"] ?>" />
        <header id="panel_top" class="jq_hide">
            <section id="title"><?PHP echo $movie['title'] ?></section>
            <section id="tagline"><?PHP echo $movie['tagline'] ?></section>
        </header>
        <table id="info" class="jq_hide">
            <tr>
                <td><?PHP echo $lang['i_year'] ?>:</td>
                <td><?PHP echo $movie["year"] ?></td>
            </tr>
            <tr>
                <td><?PHP echo $lang['i_genre'] ?>:</td>
                <td><?PHP echo $movie["genre"] ?></td>
            </tr>
            <tr>
                <td><?PHP echo $lang['i_rating'] ?>:</td>
                <td><?PHP echo $movie["rating"] ?></td>
            </tr>
            <tr>
                <td><?PHP echo $lang['i_country'] ?>:</td>
                <td><?PHP echo $movie['country'] ?></td>
            </tr>
            <tr>
                <td><?PHP echo $lang['i_runtime'] ?>:</td>
                <td><?PHP echo $movie["runtime"] . ' ' . $lang['i_minute'] ?></td>
            </tr>
            <tr>
                <td><?PHP echo $lang['i_director'] ?>:</td>
                <td><?PHP echo $movie["director"] ?></td>
            </tr>
        </table>
        <aside id="panel_right" class="jq_hide">
            <section id="panel_list" class="scroll">
                <img id="options" class="opacity" src="img/options.png" title="<?PHP echo $lang['i_options'] ?>" alt="" />
                <nav class="nav"><?PHP echo $nav ?></nav>
                <div class="bold"><?PHP echo $lang['i_list_title'] ?>:</div>
                <table id="list"><?PHP echo $panel_list ?></table>
                <nav class="nav"><?PHP echo $nav ?></nav>
            </section>
            <section id="panel_options">
                <img id="back" class="opacity" src="img/back.png" title="<?PHP echo $lang['i_back'] ?>" alt="" />
                <div class="bold"><?PHP echo $lang['i_search'] ?>:</div>
                <form method="get" action="index.php"><?PHP echo $search_text ?></form>
                <div class="bold"><?PHP echo $lang['i_sort'] ?>:</div>
                <?PHP echo $sort_menu ?>
                <div class="bold"><?PHP echo $lang['i_genre'] ?>:</div>
                <div id="genre_menu"><?PHP echo $genre_menu ?></div>
            </section>
        </aside>
        <footer id="panel_bottom" class="jq_hide">
            <section id="plot_text"><span id="plot"><?PHP echo $lang['i_plot'] ?>: </span><?PHP echo $movie['plot'] ?></section>
            <img id="poster" src="<?PHP echo $poster ?>" alt="" />
            <section id="img_flag"><?PHP echo $img_flag ?></section>
        </footer>
    </body>
</html>