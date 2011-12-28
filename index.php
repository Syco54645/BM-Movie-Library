<?PHP
include 'config.php';
include 'function.php';

if ((!mysql_query('SELECT id FROM ' . $table_name . ' LIMIT 1')) or (mysql_num_rows(mysql_query('SELECT id FROM ' . $table_name . ' LIMIT 1')) < 1)) {
        header('Location: panel.php');
}

// sets the variables
if (!isset($_GET['id'])) {
    $id_result = mysql_query('SELECT id FROM ' . $table_name . ' LIMIT 1');
    $id_assoc = mysql_fetch_array($id_result);
    $id = $id_assoc['id'];
} else {
    $id = $_GET['id'];
}

/* ###############
 * ##Search START#
 */###############
if ($search == 1) {
    $search_text = '<form method="get" action="index.php"> <input type="text" name="search" class="search"><input type="image" class="search_img opacity" src="img/search.png" alt="Search"></form>';
    $search_mysql = '.*';
} else {
    $search_text = $lang['i_result'] . ': ' . $search . ' <a href="index.php"><img class="opacity" src="img/delete.png"></a>';
    $search_mysql = $search;
}
/* #############
 * ##Search END#
 */#############

/* #############
 * ##Sort START#
 */#############
$sort_array = array(1 => $lang['i_title'], $lang['i_year'], $lang['i_rating']);
$sort_menu = '';
foreach ($sort_array as $key => $val) {
    $sort_menu.='<div class="sort">' . ($sort == $key ? $val : '<a href="index.php?sort=' . $key . '&amp;id=' . $id . '&amp;genre=' . $genre . '">' . $val . '</a>') . '</div>';
}
$sort_mysql = array(1 => 'title ASC', 'year DESC', 'rating DESC');
/* #########
 * Sort END#
 */#########

/* #############
 * Genres START#
 */#############
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
$genre_mysql = '.*';
foreach ($genre_array as $key => $val) {
    if ((string) $key === (string) $genre) {
        $genre_menu.= '<div class="genre">' . $val . '</div>';
        $genre_mysql = $val;
    } else {
        $genre_menu.= '<div class="genre"><a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=' . $key . '">' . $val . '</a></div>';
    }
}
/* ###########
 * Genres END#
 */###########

/* ###########
 * #Nav START#
 */###########
$nav_sql = 'SELECT id, title, rating, year, genre, country FROM ' . $table_name . ' WHERE genre REGEXP "' . $genre_mysql . '" AND title REGEXP "' . $search_mysql . '" ORDER by ' . $sort_mysql[$sort];
$nav_result = mysql_query($nav_sql);
$row = mysql_num_rows($nav_result);
$i_pages = (ceil($row / $perPage));
$nav = '<div class="nav">';
$nav.= ($page == 1 ? '<img class="img_nav" src="img/first_g.png" alt="">' : '<a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=' . $genre . '&amp;page=1&amp;search=' . $search . '"><img class="img_nav opacity" src="img/first.png" alt=""></a>') . ' ' .
        ($page == 1 ? '<img class="img_nav" src="img/previous_g.png" alt="">' : '<a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=' . $genre . '&amp;page=' . ($page - 1) . '&amp;search=' . $search . '"><img class="img_nav opacity" src="img/previous.png" alt=""></a>') . ' ' .
        $lang['i_page'] . ' ' . $page . ' / ' . $i_pages . ' ' .
        ($page == $i_pages ? '<img class="img_nav" src="img/next_g.png" alt="">' : '<a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=' . $genre . '&amp;page=' . ($page + 1) . '&amp;search=' . $search . '"><img class="img_nav opacity" src="img/next.png" alt=""></a>') . ' ' .
        ($page == $i_pages ? '<img class="img_nav" src="img/last_g.png" alt="">' : '<a href="index.php?sort=' . $sort . '&amp;id=' . $id . '&amp;genre=' . $genre . '&amp;page=' . $i_pages . '&amp;search=' . $search . '"><img class="img_nav opacity" src="img/last.png" alt=""></a>');
$nav.='</div>';
if ($i_pages == 1) {
    $nav = '';
}
/* #########
 * #Nav END#
 */#########

/* ###########
 * List START#
 */###########
$start = ($page - 1) * $perPage;
$list_sql = 'SELECT id, title, rating, year, plot, runtime, genre, country, director, v_codec, v_aspect, v_width, v_height, a_codec, a_channels FROM ' . $table_name . ' WHERE genre REGEXP "' . $genre_mysql . '" AND title REGEXP "' . $search_mysql . '" ORDER by ' . $sort_mysql[$sort] . ' LIMIT ' . $start . ', ' . $perPage;
;
$list_result = mysql_query($list_sql);
$panel_list = '<table id="list">';
while ($list = mysql_fetch_array($list_result)) {
    if ($list['v_width'] > 1279) {
        $flag_vres = '<img src="img/hd.png" alt="">';
    } else {
        $flag_vres = '';
    }
    $panel_list.= '<tr><td><div id="' . $list['id'] . '" class="movie_title"><a href="index.php?sort=' . $sort . '&amp;id=' . $list['id'] . '&amp;genre=' . $genre . '&amp;page=' . $page . '&amp;search=' . $search . '" title="' . $list['title'] . '">' . $list['title'] . '</a></div></td><td>' . $list['year'] . '</td><td>' . $list['rating'] . '</td><td>' . $flag_vres . '</td></tr>';
}
$panel_list.= '</table>';
/* #########
 * List END#
 */#########

/* ############
 * Movie START#
 */############
$movie_sql = 'SELECT id, title, rating, year, plot, tagline, runtime, genre, country, director, v_codec, v_aspect, v_width, v_height, a_codec, a_channels, img_poster, img_fanart FROM ' . $table_name . ' WHERE id=' . $id;
$movie_result = mysql_query($movie_sql);
$movie = mysql_fetch_array($movie_result);

if (!file_exists('cache/' . $movie['id'] . '.jpg') or !file_exists('cache/' . $movie['id'] . '-fanart.jpg')) {
    gd_convert($movie['id'], $movie['img_poster'], $movie['img_fanart'], $detect_encoding);
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
/* ##########
 * Movie END#
 */##########
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $movie["title"] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link type="text/css" href="css/style.css" rel="stylesheet" media="all" />
        <link type="text/css" href="css/jquery.jscrollpane.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.mousewheel.js"></script>
        <script type="text/javascript" src="js/jquery.jscrollpane.min.js"></script>
        <script type="text/javascript">
            $(window).load(function() {

                /* Load all panel in order */
                $('#panel_right').fadeIn(1000);
                $('#panel_title').fadeIn(1000, function() {
                    $('#poster').fadeIn(500, function() {
                        $('#vres').fadeIn(500, function() {
                            $('#vtype').fadeIn(500, function() {
                                $('#atype').fadeIn(500, function() {
                                    $('#achan').fadeIn(500);
                                });
                            });
                        });
                    });
                });
                $('#info').fadeIn(1000);
                $('#panel_info').fadeIn(1000);

                /* Scrolling right pannel */
                var div_scroll = $('.scroll');
                var id = <?PHP echo $movie['id'] ?>;
                div_scroll.jScrollPane( {
                    showArrows: true,
                    animateScroll: true,
                    stickToTop: true
                });
                if (id > 1) {
                    var api = div_scroll.data('jsp');
                    api.scrollToElement('#'+id,true);
                }
                    
                /* Switching right pannel list to options */
                $('div#img_options').click(function(){
                    $('div#panel_list').fadeOut('slow', function() {
                        $('div#panel_options').fadeIn('slow');
                    });
                });
                $('div#img_back').click(function(){
                    $('div#panel_options').fadeOut('slow', function() {
                        $('div#panel_list').fadeIn('slow');
                    });
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
        <img id="bg" src="cache/<?PHP echo $movie['id'] ?>-fanart.jpg" alt="">
        <div id="panel_title">
            <div id="title"><?PHP echo $movie['title'] ?></div>
            <div id="tagline"><?PHP echo $movie['tagline'] ?></div>
        </div>
        <table id="info">
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

        <div id="panel_info">
            <div id="plot_text"><span id="plot"><?PHP echo $lang['i_plot'] ?>: </span><?PHP echo $movie['plot'] ?></div>
        </div>
        <div id="poster"><img id="img_poster" src="cache/<?PHP echo $movie['id'] ?>.jpg" alt=""></div>
        <div id="img_flag"><?PHP echo $img_flag ?></div>
        <div id="panel_right">
            <div id="panel_list" class="scroll">
                <div id="img_options" class="opacity"><img src="img/options.png" alt=""></div>
                <?PHP echo $nav ?>
                <div class="bold"><?PHP echo $lang['i_list_title'] ?>:</div>
                <div><?PHP echo $panel_list ?></div>
                <?PHP echo $nav ?>
            </div>
            <div id="panel_options">
                <div id="img_back" class="opacity"><img src="img/back.png" alt=""></div>
                <div class="bold"><?PHP echo $lang['i_search'] ?>:</div>
                <div id="search"><?PHP echo $search_text ?></div>
                <div class="bold"><?PHP echo $lang['i_sort'] ?>:</div>
                <div><?PHP echo $sort_menu ?></div>
                <div class="bold"><?PHP echo $lang['i_genre'] ?>:</div>
                <div id="genre_menu"><?PHP echo $genre_menu ?></div>
            </div>
        </div>
    </body>
</html>