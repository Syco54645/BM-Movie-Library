<?PHP

/*##########
* Functions#
*/##########

// Delete table
function delete_table($table_name, $lang) {
    $sql_drop = 'DROP TABLE IF EXISTS ' . $table_name . ';';
    $dir = opendir('cache/');
    while (false !== ($file = readdir($dir))) {
        if ($file !== "." && $file !== "..") {
            unlink('cache/' . $file);
        }
    }
    closedir();
    if (!mysql_query($sql_drop)) {
        $output = $lang['f_tab_cant_delete'];
        return $output;
    } else {
        $output = $lang['f_tab_deleted'] . ': ' . $table_name;
        return $output;
    }
}

// Create empty table
function create_table($table_name, $lang) {
    $sql_create = 'CREATE TABLE ' . $table_name . ' (
        `id` int(5) AUTO_INCREMENT UNIQUE NOT NULL,
        `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci UNIQUE NOT NULL,
        `originaltitle` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        `rating` float NOT NULL,
        `year` int(4) NOT NULL,
        `plot` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        `tagline` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        `runtime` int(3) NOT NULL,
        `genre` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        `country` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        `director` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        `v_codec` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        `v_aspect` float NOT NULL,
        `v_width` int(4) NOT NULL,
        `v_height` int(4) NOT NULL,
        `a_codec` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        `a_channels` int(1) NOT NULL,
        `img_poster` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        `img_fanart` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`id`)) 
        ENGINE=MYISAM  DEFAULT CHARSET=utf8 CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
    if (!mysql_query($sql_create)) {
        $output = $lang['f_tab_cant_create'];
        return $output;
    } else {
        $output = $lang['f_tab_created'] . ': ' . $table_name;
        return $output;
    }
}

// Format title
function format_title($title_format) {
// invalid characters list
    $char_invalid = array(':', ' ', '?', '/');
    $char_changed_to = array('_', '_', '_', '_');
    $output = (str_replace($char_invalid, $char_changed_to, $title_format));
    return $output;
}

// Database movie list
function database_list($table_name, $lang, $detect_encoding) {
    $output = '';
    if (!isset($_POST['del'])) {
        $database_sql = 'SELECT id, title, img_poster, img_fanart FROM ' . $table_name . ' ORDER BY title';
        $database_result = mysql_query($database_sql);
        if (!$database_result) {
            $output = $lang['f_list_tab_not_exist'];
            return $output;
        }
        $i = 0;
        $output.= '<div class="p_checked"><span class="select">' . $lang['f_list_select_all'] . '</span> / <span class="unselect">' . $lang['f_list_unselect_all'] . '</span></div>';
        $output.= '<form action="panel.php" method="post"><table id="p_table"><tr><td class="p_top"></td><td id="p_title" class="p_top">' . $lang['f_list_movies'] . '</td><td class="p_top"><img src="img/p.png" title="' . $lang['f_list_poster'] . '" alt=""></td><td class="p_top"><img src="img/f.png" title="' . $lang['f_list_fanart'] . '" alt=""></td><td class="p_top"><img src="img/pt.png" title="' . $lang['f_list_poster_thumb'] . '" alt=""></td><td class="p_top"><img src="img/ft.png" title="' . $lang['f_list_fanart_thumb'] . '" alt=""></td><td class="p_top"><input class="opacity" type="image" src="img/no.png" alt="del"></td></tr>';
        while ($database = mysql_fetch_array($database_result)) {
            $i++;
            $output.= '<tr><td>' . $i . '</td>';
            $output.= '<td>' . $database['title'] . '</td>';
            if (file_exists($database['img_poster']) or file_exists(iconv('utf-8', $detect_encoding, $database['img_poster']))) {
                $output.= '<td><img src="img/ok.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/no.png" alt=""></td>';
            }
            if (file_exists($database['img_fanart']) or file_exists(iconv('utf-8', $detect_encoding, $database['img_fanart']))) {
                $output.= '<td><img src="img/ok.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/no.png" alt=""></td>';
            }
            if (file_exists('cache/' . $database['id'] . '.jpg')) {
                $output.= '<td><img src="img/ok.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/no.png" alt=""></td>';
            }
            if (file_exists('cache/' . $database['id'] . '-fanart.jpg')) {
                $output.= '<td><img src="img/ok.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/no.png" alt=""></td>';
            }
            $output.= '<td><input class="check" name="del[]" value="' . $database['id'] . '" type="checkbox" /></td></tr>';
        }
        if ($i == 0) {
            $output = $lang['f_list_empty_tab'];
        } else {
            $output.= '<tr><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"><input class="opacity" type="image" src="img/no.png" alt="del"></td></tr></table></form>';
            $output.= '<div class="p_checked"><span class="select">' . $lang['f_list_select_all'] . '</span> / <span class="unselect">' . $lang['f_list_unselect_all'] . '</span></div>';
        }
    } else {
        foreach ($_POST['del'] as $del) {
            $database_sql = 'SELECT id, title FROM ' . $table_name . ' WHERE id ="' . $del . '" ORDER by title';
            $database_result = mysql_query($database_sql);
            while ($database = mysql_fetch_array($database_result)) {
                if (!mysql_query('DELETE FROM ' . $table_name . ' WHERE id = "' . $del . '"')) {
                    $output.= '<span class="red">' . $lang['f_list_cant_del'] . '</span>: ' . $database['title'] . '<br>';
                } else {
                    if (file_exists('cache/' . $del . '.jpg')) {
                        unlink('cache/' . $del . '.jpg');
                    }
                    if (file_exists('cache/' . $del . '-f.jpg')) {
                        unlink('cache/' . $del . '-f.jpg');
                    }
                    $output.= '<span class="green">' . $lang['f_list_successful_del'] . '</span>: ' . $database['title'] . '<br>';
                }
            }
        }
    }
    return $output;
}

// List movie from xml file
function xml_file_info($xml_file, $lang, $detect_encoding) {
    $xml = simplexml_load_file('export/' . $xml_file);
    if (!$xml) {
        $output = $lang['f_xml_file_error'];
        return $output;
    } else {
        $i = 0;
        $output = '<table id="p_table"><tr><td class="p_top"></td><td id="p_title" class="p_top">' . $lang['f_xml_movie_to_import'] . '</td><td class="p_top"><img src="img/p.png" title="' . $lang['f_xml_poster'] . '" alt=""></td><td class="p_top"><img src="img/f.png" title="' . $lang['f_xml_fanart'] . '" alt=""></td></tr>';
        foreach ($xml->movie as $movie_val) {
            $i++;
            $output.= '<tr><td>' . $i . '</td><td>' . $movie_val->title . '</td>';
            $title = $movie_val->title;
            $year = $movie_val->year;
            if (!file_exists('export/movies/' . iconv('UTF-8', $detect_encoding, format_title($title)) . '_' . $year . '.tbn')) {
                $output.= '<td><img src="img/no.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/ok.png" alt=""></td>';
            }
            if (!file_exists('export/movies/' . iconv('UTF-8', $detect_encoding, format_title($title)) . '_' . $year . '-fanart.jpg')) {
                $output.= '<td><img src="img/no.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/ok.png" alt=""></td></tr>';
            }
        }
        return $output;
    }
}

// List nfo files
function nfo_file_info($table_name, $lang, $detect_encoding) {
    if (!isset($_POST['add'])) {
        $output = '<div class="p_checked"><span class="select">' . $lang['f_nfo_select_all'] . '</span> / <span class="unselect">' . $lang['f_nfo_unselect_all'] . '</span></div>';
        $output.= '<form action="panel.php?option=nfo_file_info" method="post"><table id="p_table"><tr><td class="p_top"></td><td id="p_title" class="p_top">' . $lang['f_nfo_files'] . '</td><td class="p_top"><img src="img/p.png" title="' . $lang['f_nfo_poster'] . '" alt=""></td><td class="p_top"><img src="img/f.png" title="' . $lang['f_nfo_fanart'] . '" alt=""></td><td class="p_top"><input class="opacity" type="image" src="img/plus.png" alt="add"></td></tr>';
        $i = 0;
        $dir = opendir('export/');
        while (false !== ($file = readdir($dir))) {
            $info = pathinfo($file);
            if ($file !== "." && $file !== ".." && isset($info['extension']) && $info['extension'] == "nfo") {
                $i++;
                $output.='<tr><td>' . $i . '</td><td>' . iconv($detect_encoding, 'UTF-8', $info['basename']) . '</td>';
                if (file_exists('export/' . $info['filename'] . '.tbn')) {
                    $output.= '<td><img src="img/ok.png" alt=""></td>';
                } else {
                    $output.= '<td><img src="img/no.png" alt=""></td>';
                }
                if (file_exists('export/' . $info['filename'] . '-fanart.jpg')) {
                    $output.= '<td><img src="img/ok.png" alt=""></td>';
                } else {
                    $output.= '<td><img src="img/no.png" alt=""></td>';
                }
                $output.= '<td><input class="check" name="add[]" value="' . iconv($detect_encoding, 'utf-8', $info['basename']) . '" type="checkbox" /></td>';
            }
        }
        closedir();
        $output.='<tr><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"><input class="opacity" type="image" src="img/plus.png" alt="add"></td></tr></table></form>';
        $output.= '<div class="p_checked"><span class="select">' . $lang['f_nfo_select_all'] . '</span> / <span class="unselect">' . $lang['f_nfo_select_all'] . '</span></div>';
    } else {
        $output = import_xml($_POST['add'], $table_name, $lang, $detect_encoding);
    }
    return $output;
}


// Import movie from file
function import_xml($xml_file, $table_name, $lang, $detect_encoding) {
    $rename = '';
    if (!is_array($xml_file)) {
        $xml = simplexml_load_file('export/' . $xml_file);
        $xml_movie = $xml->movie;
        rename('export/' . $xml_file, 'export/' . $xml_file . '.bak');
        $rename = $lang['f_import_renamed'] . ' ' . $xml_file . '.bak<br/><br/>';
    } else {
        foreach ($xml_file as $file) {
            $file = iconv('utf-8', $detect_encoding, $file);
            $xml = simplexml_load_file('export/' . $file);
            $info_path = pathinfo($file);
            $xml->filename = iconv($detect_encoding, 'utf-8', $info_path['filename']);
            $xml_movie[] = $xml;
            rename('export/' . $file, 'export/' . $file . '.bak');
            $rename.= $lang['f_import_renamed'] . ' ' . $file . '.bak<br/><br/>';
        }
    }
    $e = 0;
    $i = 0;
    $error = '';
    $ok = '';
    foreach ($xml_movie as $movie_val) {
        $genre = array();
        foreach ($movie_val->genre as $genre_val) {
            $genre[] = (string) $genre_val;
        }
        $country = array();
        foreach ($movie_val->country as $country_val) {
            $country[] = (string) $country_val;
        }
        $f_title = format_title($movie_val->title);
             
        if (file_exists('export/' . iconv('utf-8', $detect_encoding, $movie_val->filename) . '.tbn')) {
            $img_poster = 'export/' . $movie_val->filename . '.tbn';
        } elseif (file_exists('export/movies/' . iconv('UTF-8', $detect_encoding, $f_title) . '_' . $movie_val->year . '.tbn')) {
            $img_poster = 'export/movies/' . $f_title . '_' . $movie_val->year . '.tbn';
        } else {
            $img_poster = 'img/d_poster.jpg';
        }
        
        if (file_exists('export/' . iconv('UTF-8', $detect_encoding, $movie_val->filename) . '-fanart.jpg')) {
            $img_fanart = 'export/' . $movie_val->filename . '-fanart.jpg';
        } elseif (file_exists('export/movies/' . iconv('UTF-8', $detect_encoding, $f_title) . '_' . $movie_val->year . '-fanart.jpg')) {
            $img_fanart = 'export/movies/' . $f_title . '_' . $movie_val->year . '-fanart.jpg';
        } else {
            $img_fanart = 'img/d_fanart.jpg';
        }
        
       
        $sql_insert = 'INSERT INTO `' . $table_name . '` (
            `title`,
            `originaltitle`,
            `rating`,
            `year`,
            `plot`,
            `tagline`,
            `runtime`,
            `genre`,
            `country`,
            `director`,
            `v_codec`,
            `v_aspect`,
            `v_width`,
            `v_height`,
            `a_codec`,
            `a_channels`,
            `img_poster`,
            `img_fanart`
        ) VALUES (
            "' . $movie_val->title . '",
            "' . addslashes($movie_val->originaltitle) . '",    
            "' . $movie_val->rating . '",
            "' . $movie_val->year . '",
            "' . addslashes($movie_val->plot) . '",
            "' . addslashes($movie_val->tagline) . '",
            "' . $movie_val->runtime . '",
            "' . implode(' / ', $genre) . '",
            "' . implode(' / ', $country) . '",
            "' . $movie_val->director . '",
            "' . $movie_val->fileinfo->streamdetails->video->codec . '",
            "' . $movie_val->fileinfo->streamdetails->video->aspect . '",
            "' . $movie_val->fileinfo->streamdetails->video->width . '",
            "' . $movie_val->fileinfo->streamdetails->video->height . '",
            "' . $movie_val->fileinfo->streamdetails->audio->codec . '",
            "' . $movie_val->fileinfo->streamdetails->audio->channels . '",
            "' . $img_poster . '",
            "' . $img_fanart . '"
            );';
        $title = $movie_val->title;
        $insert_result = mysql_query($sql_insert);
        if (!$insert_result) {
            $e++;
            $error.= '<tr><td>' . $e . '</td><td>' . $title . '</td><td><img src="img/no.png" title="' . mysql_error() . '" alt=""></td></tr>';
        } else {
            $i++;
            $ok.= '<tr><td>' . $i . '</td><td>' . $title . '</td><td><img src="img/ok.png" alt=""></td></tr>';
        }
        $info = $lang['f_import_succes'] . ': <span class="green">' . $i . '</span> ' . $lang['f_import_error'] . ': <span class="red">' . $e . '</span><br/><br/>';
    }
    $end = '<table id="p_table"><tr><td class="p_top"></td><td id="p_title" class="p_top">' . $lang['f_import_imported_movie'] . '</td><td class="p_top"><img src="img/i.png" title="' . $lang['f_import_mysql_info'] . '" alt=""></td></tr>' . $error . $ok . '</table>';
    $output = $rename . $info . $end;
    return $output;
}

// GD conversion, create poster and fanart cache
function gd_convert($id, $poster, $fanart, $detect_encoding) {
    $cache_poster = 'cache/' . $id . '.jpg';
    $poster = iconv('utf-8', $detect_encoding, $poster);
    if (!file_exists($cache_poster) && file_exists($poster)) {

        // create poster
        $img = imagecreatefromjpeg($poster);
        $width = imagesx($img);
        $height = imagesy($img);
        $new_width = 200;
        $new_height = 280;
        $img_temp = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($img_temp, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagejpeg($img_temp, $cache_poster, 80);
    }
    $cache_fanart = 'cache/' . $id . '-fanart.jpg';
    $fanart = iconv('utf-8', $detect_encoding, $fanart);
    if (!file_exists($cache_fanart) && file_exists($fanart)) {

        // create fanart
        $img = imagecreatefromjpeg($fanart);
        $width = imagesx($img);
        $height = imagesy($img);
        $new_width = 1280;
        $new_height = 720;
        $img_temp = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($img_temp, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagejpeg($img_temp, $cache_fanart, 80);
    }
}

// Clear cache
function clear_cache($lang) {
$dir = opendir('cache/');
    while (false !== ($file = readdir($dir))) {
        if ($file !== "." && $file !== "..") {
            unlink('cache/' . $file);
        }
    }
    closedir();
    $output = $lang['f_cache_cleared'];
    return $output;
}
?>