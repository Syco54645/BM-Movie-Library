<?PHP

/* #############
 * # FUNCTIONS #
 */#############

/* #################
 * # Check trailer #
 */#################

function trailer($title, $year, $originaltitle, $url_trailer) {
    $check_url_trailer = preg_match('/youtube.*?id=(.*?)$/', $url_trailer, $val);
    if ($check_url_trailer == 1) {
        $id_yt = $val[1];
    } else {
        if ($originaltitle !== '') {
            $title = $originaltitle;
        }
        $title = str_replace(' ', '+', $title);
        $title = str_replace('&', '', $title);
        $url = 'http://gdata.youtube.com/feeds/api/videos?v=2&format=5&alt=jsonc&max-results=1&q=allintitle:' . $title . '+' . $year . '+trailer+-review&hl=en&gl=US';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, 'http://gdata.youtube.com');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
        $content = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($content);
        if (!isset($json->data->items[0]->id)) {
             $id_yt = '';
        } else {
            $id_yt = $json->data->items[0]->id;
        }
    }
    return $id_yt;
}

/* ################
 * # Delete table #
 */################

function delete_table($lang) {
    $drop_movie_sql = 'DROP TABLE IF EXISTS movie';
    $drop_streamdetails_sql = 'DROP TABLE IF EXISTS streamdetails';
    $dir = opendir('cache/');
    while (false !== ($file = readdir($dir))) {
        if ($file !== "." && $file !== "..") {
            unlink('cache/' . $file);
        }
    }
    closedir();
    if (!mysql_query($drop_movie_sql)) {
        $output = $lang['f_tab_cant_delete'] . ': ' . mysql_error() . '<br/>';
    } else {
        $output = $lang['f_tab_deleted'] . ': movie<br/>';
    }
    if (!mysql_query($drop_streamdetails_sql)) {
        $output.= $lang['f_tab_cant_delete'] . ': ' . mysql_error();
    } else {
        $output.= $lang['f_tab_deleted'] . ': streamdetails';
    }
    return $output;
}

/* ######################
 * # Create empty table #
 */######################

function create_table($col, $lang) {
    $create_movie_sql = 'CREATE TABLE IF NOT EXISTS `movie` (
                `' . $col['id_movie'] . '` int(11) NOT NULL AUTO_INCREMENT,
                `' . $col['id_file'] . '` int(11) NOT NULL,
                `' . $col['title'] . '` varchar(100) UNIQUE,
                `' . $col['plot'] . '` text,
                `' . $col['outline'] . '` text,
                `' . $col['tagline'] . '` text,
                `' . $col['votes'] . '` text,
                `' . $col['rating'] . '` text,
                `' . $col['credits'] . '` text,
                `' . $col['year'] . '` text,
                `' . $col['poster'] . '` text,
                `' . $col['imdb_id'] . '` text,
                `' . $col['title_format'] . '` text,
                `' . $col['runtime'] . '` text,
                `' . $col['mpaa'] . '` text,
                `' . $col['top250'] . '` text,
                `' . $col['genre'] . '` text,
                `' . $col['director'] . '` text,
                `' . $col['originaltitle'] . '` text,
                `' . $col['thumb_url'] . '` text,
                `' . $col['studio'] . '` text,
                `' . $col['trailer'] . '` text,
                `' . $col['fanart'] . '` text,
                `' . $col['country'] . '` text,
                `' . $col['file_path'] . '` text,
                `' . $col['id_path'] . '` text,
                `check` INT(1) DEFAULT NULL,
                PRIMARY KEY (`' . $col['id_movie'] . '`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8';
    $create_streamdetails_sql = 'CREATE TABLE IF NOT EXISTS `streamdetails` (
                  `idFile` int(11) DEFAULT NULL,
                  `iStreamType` int(11) DEFAULT NULL,
                  `strVideoCodec` text,
                  `fVideoAspect` float DEFAULT NULL,
                  `iVideoWidth` int(11) DEFAULT NULL,
                  `iVideoHeight` int(11) DEFAULT NULL,
                  `strAudioCodec` text,
                  `iAudioChannels` int(11) DEFAULT NULL,
                  `strAudioLanguage` text,
                  `strSubtitleLanguage` text,
                  `iVideoDuration` int(11) DEFAULT NULL,
                  KEY (`idFile`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8';
    if (!mysql_query($create_movie_sql)) {
        $output = $lang['f_tab_cant_create'] . ': ' . mysql_error() . '<br/>';
    } else {
        $output = $lang['f_tab_created'] . ': movie<br/>';
    }
    if (!mysql_query($create_streamdetails_sql)) {
        $output.= $lang['f_tab_cant_create'] . ': ' . mysql_error();
    } else {
        $output.= $lang['f_tab_created'] . ': streamdetails';
    }
    return $output;
}

/* #######################
 * # Database movie list #
 */#######################

function database_list($col, $mode, $lang) {
    $output = '';
    if (!isset($_POST['del'])) {
        $database_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['title'] . ', ' . $col['poster'] . ', ' . $col['fanart'] . ' FROM movie ORDER BY ' . $col['title'];
        $database_result = mysql_query($database_sql);
        if (!$database_result) {
            $output = $lang['f_list_tab_not_exist'];
            return $output;
        }
        $i = 0;
        $output.= '<div class="p_checked"><span class="select">' . $lang['f_list_select_all'] . '</span> / <span class="unselect">' . $lang['f_list_unselect_all'] . '</span></div>';
        $output.= '<form action="panel.php?option=show_table" method="post"><table id="p_table"><tr><td class="p_top"></td><td id="p_title" class="p_top">' . $lang['f_list_movies'] . '</td><td class="p_top"><img src="img/p.png" title="' . $lang['f_list_poster'] . '" alt=""></td><td class="p_top"><img src="img/f.png" title="' . $lang['f_list_fanart'] . '" alt=""></td><td class="p_top"><img src="img/pt.png" title="' . $lang['f_list_poster_thumb'] . '" alt=""></td><td class="p_top"><img src="img/ft.png" title="' . $lang['f_list_fanart_thumb'] . '" alt=""></td><td class="p_top"><input class="opacity" type="image" src="img/no.png" alt="del"></td></tr>';
        while ($database = mysql_fetch_array($database_result)) {
            $i++;
            $output.= '<tr><td>' . $i . '</td>';
            $output.= '<td>' . $database[$col['title']] . '</td>';

            // Check file or url exist
            if (preg_match('/<thumb.*>(.*?)<\/thumb>/', $database[$col['poster']]) == 1) {
                $output.= '<td><img src="img/ok.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/no.png" alt=""></td>';
            }
            if (preg_match('/<thumb.*>(.*?)<\/thumb>/', $database[$col['fanart']]) == 1) {
                $output.= '<td><img src="img/ok.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/no.png" alt=""></td>';
            }

            // Check cache exist
            if (file_exists('cache/' . $database[$col['id_movie']] . '.jpg')) {
                $output.= '<td><img src="img/ok.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/no.png" alt=""></td>';
            }
            if (file_exists('cache/' . $database[$col['id_movie']] . '-fanart.jpg')) {
                $output.= '<td><img src="img/ok.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/no.png" alt=""></td>';
            }
            $output.= '<td><input class="check" name="del[]" value="' . $database[$col['id_movie']] . '" type="checkbox" /></td></tr>';
        }
        if ($i == 0) {
            $output = $lang['f_list_empty_tab'];
        } else {
            $output.= '<tr><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"><input class="opacity" type="image" src="img/no.png" alt="del"></td></tr></table></form>';
            $output.= '<div class="p_checked"><span class="select">' . $lang['f_list_select_all'] . '</span> / <span class="unselect">' . $lang['f_list_unselect_all'] . '</span></div>';
        }
    } else {
        if ($mode == 1) {
            $output = $lang['f_list_cant_del_mode_1'];
        } else {
            foreach ($_POST['del'] as $val) {
                $database_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['title'] . ' FROM movie WHERE ' . $col['id_movie'] . ' = "' . $val . '"';
                $database_result = mysql_query($database_sql);
                $database = mysql_fetch_array($database_result);
                $delete = mysql_query('DELETE FROM movie WHERE ' . $col['id_movie'] . ' = "' . $val . '"');
                if (!$delete) {
                    $output.= '<span class="red">' . $lang['f_list_cant_del'] . '</span>: ' . $database[$col['title']] . '<br>';
                } else {
                    if (file_exists('cache/' . $val . '.jpg')) {
                        unlink('cache/' . $val . '.jpg');
                    }
                    if (file_exists('cache/' . $val . '-f.jpg')) {
                        unlink('cache/' . $val . '-f.jpg');
                    }
                    $output.= '<span class="green">' . $lang['f_list_successful_del'] . '</span>: ' . $database[$col['title']] . '<br>';
                }
            }
        }
    }
    return $output;
}

/* ############################
 * # List movie from xml file #
 */############################

function xml_file_info($xml_file, $lang) {
    $xml = @simplexml_load_file('export/' . $xml_file);
    if (!$xml) {
        $output = '<span class="red">' . $lang['f_xml_file_error_format'] . '</span>';
    } else {
        $i = 0;
        $output = '<table id="p_table"><tr><td class="p_top"></td><td id="p_title" class="p_top">' . $lang['f_xml_movie_to_import'] . '</td><td class="p_top"><img src="img/p.png" title="' . $lang['f_xml_poster'] . '" alt=""></td><td class="p_top"><img src="img/f.png" title="' . $lang['f_xml_fanart'] . '" alt=""></td></tr>';
        foreach ($xml->movie as $movie_val) {
            $i++;
            $output.= '<tr><td>' . $i . '</td><td>' . $movie_val->title . '</td>';
            if (isset($movie_val->thumb)) {
                $output.= '<td><img src="img/ok.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/no.png" alt=""></td>';
            }
            if (isset($movie_val->fanart->thumb)) {
                $output.= '<td><img src="img/ok.png" alt=""></td>';
            } else {
                $output.= '<td><img src="img/no.png" alt=""></td></tr>';
            }
        }
    }
    return $output;
}

/* ##################
 * # List nfo files #
 */##################

function nfo_file_info($col, $lang) {
    if (!isset($_POST['add'])) {
        $output = '<div class="p_checked"><span class="select">' . $lang['f_nfo_select_all'] . '</span> / <span class="unselect">' . $lang['f_nfo_unselect_all'] . '</span></div>';
        $output.= '<form action="panel.php?option=nfo_file_info" method="post"><table id="p_table"><tr><td class="p_top"></td><td id="p_title" class="p_top">' . $lang['f_nfo_files'] . '</td><td class="p_top"><img src="img/p.png" title="' . $lang['f_nfo_poster'] . '" alt=""></td><td class="p_top"><img src="img/f.png" title="' . $lang['f_nfo_fanart'] . '" alt=""></td><td class="p_top"><input class="opacity" type="image" src="img/plus.png" alt="add"></td></tr>';
        $i = 0;
        $error = '';
        $dir = opendir('export/');
        while (false !== ($file = readdir($dir))) {
            $info = pathinfo($file);
            if ($file !== "." && $file !== ".." && isset($info['extension']) && $info['extension'] == "nfo") {
                $xml = @simplexml_load_file('export/' . $file);
                if (!$xml) {
                    $error.= '<span class="red">' . $lang['f_xml_file_error_format'] . ':</span> ' . $file . '<br/>';
                } else {
                    $i++;
                    $output.='<tr><td>' . $i . '</td><td>' . $xml->title . '</td>';

                    // check if the poster url exists
                    if (isset($xml->thumb)) {
                        $output.= '<td><img src="img/ok.png" alt=""></td>';
                    } else {
                        $output.= '<td><img src="img/no.png" alt=""></td>';
                    }

                    // check if the fanart url exists
                    if (isset($xml->fanart->thumb)) {
                        $output.= '<td><img src="img/ok.png" alt=""></td>';
                    } else {
                        $output.= '<td><img src="img/no.png" alt=""></td>';
                    }
                    $output.= '<td><input class="check" name="add[]" value="' . utf8_encode($file) . '" type="checkbox" /></td>';
                }
            }
        }
        closedir();
        $output.='<tr><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"></td><td class="p_top"><input class="opacity" type="image" src="img/plus.png" alt="add"></td></tr></table></form>';
        $output.= '<div class="p_checked"><span class="select">' . $lang['f_nfo_select_all'] . '</span> / <span class="unselect">' . $lang['f_nfo_select_all'] . '</span></div><br/>';
        $output.= $error;
    } else {
        $output = import_xml($_POST['add'], $col, $lang);
    }
    return $output;
}

/* ##########################
 * # Import movie from file #
 */##########################

function import_xml($xml_file, $col, $lang) {
    $rename = '';
    if (!is_array($xml_file)) {
        $xml = simplexml_load_file('export/' . $xml_file);
        $xml_movie = $xml->movie;
        rename('export/' . $xml_file, 'export/' . $xml_file . '.bak');
    } else {
        foreach ($xml_file as $val) {
            $file = utf8_decode($val);
            $xml = simplexml_load_file('export/' . $file);
            $xml_movie[] = $xml;
            rename('export/' . $file, 'export/' . $file . '.bak');
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

        $credits = array();
        foreach ($movie_val->credits as $credits_val) {
            $credits[] = (string) $credits_val;
        }

        $insert_movie_sql = 'INSERT INTO `movie` (
            `' . $col['id_file'] . '`,
            `' . $col['title'] . '`,
            `' . $col['plot'] . '`,
            `' . $col['outline'] . '`,
            `' . $col['tagline'] . '`,
            `' . $col['votes'] . '`,
            `' . $col['rating'] . '`,
            `' . $col['credits'] . '`,
            `' . $col['year'] . '`,
            `' . $col['poster'] . '`,
            `' . $col['imdb_id'] . '`,
            `' . $col['title_format'] . '`,
            `' . $col['runtime'] . '`,
            `' . $col['mpaa'] . '`,
            `' . $col['top250'] . '`,
            `' . $col['genre'] . '`,
            `' . $col['director'] . '`,
            `' . $col['originaltitle'] . '`,
            `' . $col['thumb_url'] . '`,
            `' . $col['studio'] . '`,
            `' . $col['trailer'] . '`,
            `' . $col['fanart'] . '`,
            `' . $col['country'] . '`,
            `' . $col['file_path'] . '`,
            `' . $col['id_path'] . '`
        ) VALUES (
            LAST_INSERT_ID()+1,
            "' . addslashes($movie_val->title) . '",
            "' . (isset($movie_val->plot) ? addslashes($movie_val->plot) : '') . '",
            "' . (isset($movie_val->outline) ? addslashes($movie_val->outline) : '') . '",
            "' . (isset($movie_val->tagline) ? addslashes($movie_val->tagline) : '') . '",
            "' . (isset($movie_val->votes) ? $movie_val->votes : '') . '",
            "' . (isset($movie_val->rating) ? $movie_val->rating : '') . '",
            "' . (isset($credits) ? implode(' / ', $credits) : '') . '",
            "' . (isset($movie_val->year) ? $movie_val->year : '') . '",
            "' . (isset($movie_val->thumb) ? '<thumb>' . $movie_val->thumb[0] . '</thumb>' : '') . '",
            "' . (isset($movie_val->id) ? $movie_val->id : '') . '",
            "' . (isset($movie_val->formatedtitle) ? $movie_val->formatedtitle : '') . '",
            "' . (isset($movie_val->runtime) ? $movie_val->runtime : '') . '",
            "' . (isset($movie_val->mpaa) ? addslashes($movie_val->mpaa) : '') . '",
            "' . (isset($movie_val->top250) ? addslashes($movie_val->top250) : '') . '",
            "' . (isset($genre) ? implode(' / ', $genre) : '') . '",
            "' . (isset($movie_val->director) ? $movie_val->director : '') . '",
            "' . (isset($movie_val->originaltitle) ? addslashes($movie_val->originaltitle) : '') . '",
            "",
            "' . (isset($movie_val->studio) ? $movie_val->studio : '') . '",
            "' . (isset($movie_val->trailer) ? $movie_val->trailer : '') . '",
            "' . (isset($movie_val->fanart->thumb) ? '<thumb>' . $movie_val->fanart->thumb[0] . '</thumb>' : '') . '",
            "' . (isset($country) ? implode(' / ', $country) : '') . '",
            "",
            "")';

        $insert_streamdetails_0_sql = 'INSERT INTO `streamdetails` (
            `idFile`,
            `iStreamType`,
            `strVideoCodec`,
            `fVideoAspect`,
            `iVideoWidth`,
            `iVideoHeight`,
            `iVideoDuration`
        ) VALUES (
            LAST_INSERT_ID(),
            "0",
            "' . (isset($movie_val->fileinfo->streamdetails->video->codec) ? $movie_val->fileinfo->streamdetails->video->codec : '') . '",
            "' . (isset($movie_val->fileinfo->streamdetails->video->aspect) ? $movie_val->fileinfo->streamdetails->video->aspect : '0') . '",
            "' . (isset($movie_val->fileinfo->streamdetails->video->width) ? $movie_val->fileinfo->streamdetails->video->width : '0') . '",
            "' . (isset($movie_val->fileinfo->streamdetails->video->height) ? $movie_val->fileinfo->streamdetails->video->height : '0') . '",
            "' . (isset($movie_val->fileinfo->streamdetails->video->durationinseconds) ? $movie_val->fileinfo->streamdetails->video->durationinseconds : '0') . '"
            )';

        $insert_streamdetails_1_sql = 'INSERT INTO `streamdetails` (
            `idFile`,
            `iStreamType`,
            `strAudioCodec`,
            `iAudioChannels`
        ) VALUES (
            LAST_INSERT_ID(),
            "1",
            "' . (isset($movie_val->fileinfo->streamdetails->audio->codec) ? $movie_val->fileinfo->streamdetails->audio->codec : '') . '",
            "' . (isset($movie_val->fileinfo->streamdetails->audio->channels) ? $movie_val->fileinfo->streamdetails->audio->channels : '0') . '"
            )';
        $insert_movie_result = mysql_query($insert_movie_sql);
        if (!$insert_movie_result) {
            $e++;
            $error.= '<tr><td>' . $e . '</td><td>' . $movie_val->title . '</td><td><img src="img/no.png" title="' . addslashes(mysql_error()) . '" alt=""></td></tr>';
        } else {
            mysql_query($insert_streamdetails_0_sql);
            mysql_query($insert_streamdetails_1_sql);
            $i++;
            $ok.= '<tr><td>' . $i . '</td><td>' . $movie_val->title . '</td><td><img src="img/ok.png" alt=""></td></tr>';
        }
        $info = $lang['f_import_succes'] . ': <span class="green">' . $i . '</span> ' . $lang['f_import_error'] . ': <span class="red">' . $e . '</span><br/><br/>';
    }
    $rename = $lang['f_import_renamed'] . '<br/><br/>';
    $end = '<table id="p_table"><tr><td class="p_top"></td><td id="p_title" class="p_top">' . $lang['f_import_imported_movie'] . '</td><td class="p_top"><img src="img/i.png" title="' . $lang['f_import_mysql_info'] . '" alt=""></td></tr>' . $error . $ok . '</table>';
    $output = $rename . $info . $end;
    return $output;
}

/* ##################################
 * # Synch remote database to local #
 */##################################

function synch_database($col, $mysql_database, $connect, $remote_connection, $lang) {
    $mysql_database_remote = $remote_connection[4];
    // Check connection to remote
    $connect_remote = @mysql_connect($remote_connection[0] . ':' . $remote_connection[1], $remote_connection[2], $remote_connection[3]);
    if (!$connect_remote) {
        die($lang['f_synch_could_connect'] . ': ' . mysql_error());
    }
    $select_remote = @mysql_select_db($mysql_database_remote, $connect_remote);
    if (!$select_remote) {
        die($lang['f_synch_could_select'] . ': ' . mysql_error());
    }

    // Check id movie from remote
    $remote_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['id_file'] . ' FROM movie ORDER BY ' . $col['id_movie'];
    mysql_select_db($mysql_database_remote, $connect_remote);
    $remote_result = mysql_query($remote_sql, $connect_remote);
    $id_remote_assoc = array();
    $file_remote_assoc = array();
    while ($remote = mysql_fetch_assoc($remote_result)) {
        array_push($id_remote_assoc, $remote[$col['id_movie']]);
        array_push($file_remote_assoc, $remote[$col['id_file']]);
    }

    // Check id movie from local
    $local_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['id_file'] . ' FROM movie ORDER BY ' . $col['id_movie'];
    mysql_select_db($mysql_database, $connect);
    $local_result = mysql_query($local_sql, $connect);
    $id_local_assoc = array();
    $file_local_assoc = array();
    while ($local = mysql_fetch_assoc($local_result)) {
        array_push($id_local_assoc, $local[$col['id_movie']]);
        array_push($file_local_assoc, $local[$col['id_file']]);
    }

    // Set movie to remove
    $id_to_remove = array();
    $file_to_remove = array();
    foreach ($id_local_assoc as $key => $val) {
        if (!in_array($val, $id_remote_assoc)) {
            array_push($id_to_remove, $val);
            array_push($file_to_remove, $file_local_assoc[$key]);
        }
    }

    // Set movie to add
    $id_to_add = array();
    foreach ($id_remote_assoc as $val) {
        if (!in_array($val, $id_local_assoc)) {
            array_push($id_to_add, $val);
        }
    }

    // Delete a no exist movies
    foreach ($id_to_remove as $key => $val) {
        $delete_movie_sql = 'DELETE FROM movie WHERE ' . $col['id_movie'] . ' = "' . $val . '"';
        $delete_stream_sql = 'DELETE FROM streamdetails WHERE ' . $col['id_file'] . ' = "' . $file_to_remove[$key] . '"';
        mysql_select_db($mysql_database, $connect);
        mysql_query($delete_movie_sql, $connect);
        mysql_query($delete_stream_sql, $connect);
        if (file_exists('cache/' . $val . '.jpg')) {
            unlink('cache/' . $val . '.jpg');
        }
        if (file_exists('cache/' . $val . '-fanart.jpg')) {
            unlink('cache/' . $val . '-fanart.jpg');
        }
    }

    // Add new movies
    $count = count($id_to_add);
    if ($count !== 0) {
        echo '<body style="background-color:#000; color:#FFF">';
        echo $lang['f_synch_remained'] . ': ' . $count . '<br />' . $lang['f_synch_id'] . ': ' . $id_to_add[0];
        $select_sql = 'SELECT * FROM movie WHERE ' . $col['id_movie'] . ' = "' . $id_to_add[0] . '"';
        mysql_select_db($mysql_database_remote, $connect_remote);
        mysql_query('SET CHARACTER SET utf8', $connect_remote);
        mysql_query('SET NAMES utf8', $connect_remote);
        $select_result = mysql_query($select_sql, $connect_remote);
        $movie = mysql_fetch_assoc($select_result);
        $select_stream_sql = 'SELECT * FROM streamdetails WHERE ' . $col['id_file'] . ' = "' . $movie['idFile'] . '" ORDER BY iStreamType';
        $select_stream_result = mysql_query($select_stream_sql, $connect_remote);
        $stream_assoc = array();
        while ($stream = mysql_fetch_assoc($select_stream_result)) {
            array_push($stream_assoc, $stream);
        }
        $insert_sql = 'INSERT INTO `movie` (
      `' . $col['id_movie'] . '`,
      `' . $col['id_file'] . '`,
      `' . $col['title'] . '`,
      `' . $col['plot'] . '`,
      `' . $col['outline'] . '`,
      `' . $col['tagline'] . '`,
      `' . $col['votes'] . '`,
      `' . $col['rating'] . '`,
      `' . $col['credits'] . '`,
      `' . $col['year'] . '`,
      `' . $col['poster'] . '`,
      `' . $col['imdb_id'] . '`,
      `' . $col['title_format'] . '`,
      `' . $col['runtime'] . '`,
      `' . $col['mpaa'] . '`,
      `' . $col['top250'] . '`,
      `' . $col['genre'] . '`,
      `' . $col['director'] . '`,
      `' . $col['originaltitle'] . '`,
      `' . $col['thumb_url'] . '`,
      `' . $col['studio'] . '`,
      `' . $col['trailer'] . '`,
      `' . $col['fanart'] . '`,
      `' . $col['country'] . '`,
      `' . $col['file_path'] . '`,
      `' . $col['id_path'] . '`
      ) VALUES (
      "' . $movie[$col['id_movie']] . '",
      "' . $movie[$col['id_file']] . '",
      "' . addslashes($movie[$col['title']]) . '",
      "' . addslashes($movie[$col['plot']]) . '",
      "' . addslashes($movie[$col['outline']]) . '",
      "' . addslashes($movie[$col['tagline']]) . '",
      "' . addslashes($movie[$col['votes']]) . '",
      "' . addslashes($movie[$col['rating']]) . '",
      "' . addslashes($movie[$col['credits']]) . '",
      "' . addslashes($movie[$col['year']]) . '",
      "' . addslashes($movie[$col['poster']]) . '",
      "' . addslashes($movie[$col['imdb_id']]) . '",
      "' . addslashes($movie[$col['title_format']]) . '",
      "' . addslashes($movie[$col['runtime']]) . '",
      "' . addslashes($movie[$col['mpaa']]) . '",
      "' . addslashes($movie[$col['top250']]) . '",
      "' . addslashes($movie[$col['genre']]) . '",
      "' . addslashes($movie[$col['director']]) . '",
      "' . addslashes($movie[$col['originaltitle']]) . '",
      "' . addslashes($movie[$col['thumb_url']]) . '",
      "' . addslashes($movie[$col['studio']]) . '",
      "' . addslashes($movie[$col['trailer']]) . '",
      "' . addslashes($movie[$col['fanart']]) . '",
      "' . addslashes($movie[$col['country']]) . '",
      "' . (isset($movie[$col['file_path']]) ? addslashes($movie[$col['file_path']]) : '') . '",
      "' . (isset($movie[$col['id_path']]) ? addslashes($movie[$col['id_path']]) : '') . '"
      )';

        $insert_streamdetails_0_sql = 'INSERT INTO `streamdetails` (
        `idFile`,
        `iStreamType`,
        `strVideoCodec`,
        `fVideoAspect`,
        `iVideoWidth`,
        `iVideoHeight`,
        `iVideoDuration`
        ) VALUES (
        "' . $stream_assoc[0]['idFile'] . '",
        "' . $stream_assoc[0]['iStreamType'] . '",
        "' . $stream_assoc[0]['strVideoCodec'] . '",
        "' . $stream_assoc[0]['fVideoAspect'] . '",
        "' . $stream_assoc[0]['iVideoWidth'] . '",
        "' . $stream_assoc[0]['iVideoHeight'] . '",
        "' . $stream_assoc[0]['iVideoDuration'] . '"
        )';

        $insert_streamdetails_1_sql = 'INSERT INTO `streamdetails` (
        `idFile`,
        `iStreamType`,
        `strAudioCodec`,
        `iAudioChannels`
        ) VALUES (
        "' . $stream_assoc[1]['idFile'] . '",
        "' . $stream_assoc[1]['iStreamType'] . '",
        "' . $stream_assoc[1]['strAudioCodec'] . '",
        "' . $stream_assoc[1]['iAudioChannels'] . '"
        )';
        mysql_select_db($mysql_database, $connect);
        mysql_query('SET CHARACTER SET utf8', $connect);
        mysql_query('SET NAMES utf8', $connect);
        $insert = mysql_query($insert_sql, $connect);
        if (!$insert) {
            echo '<br />' . $lang['f_synch_error'] . ': ' . mysql_error($connect);
            exit;
        } else {
            mysql_query($insert_streamdetails_0_sql, $connect);
            mysql_query($insert_streamdetails_1_sql, $connect);
        }
        $output = '';
        echo '<script>window.location="panel.php?option=synch";</script>';
    } else {
        $output = $lang['f_synch_ok'];
    }
    return $output;
}

/* #################################################
 * # GD conversion, create poster and fanart cache #
 */#################################################

function gd_convert($id, $poster, $fanart) {

    // create poster
    $cache_poster = 'cache/' . $id . '.jpg';
    if (!file_exists($cache_poster) and !empty($poster)) {
        foreach ($poster as $val) {
            $img = @imagecreatefromjpeg($val);
            if ($img) {
                $width = imagesx($img);
                $height = imagesy($img);
                $new_width = 200;
                $new_height = 280;
                $img_temp = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($img_temp, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagejpeg($img_temp, $cache_poster, 80);
                break;
            }
        }
    }

    // create fanart
    $cache_fanart = 'cache/' . $id . '-fanart.jpg';
    if (!file_exists($cache_fanart) and !empty($fanart)) {
        foreach ($fanart as $val) {
            $img = @imagecreatefromjpeg($val);
            if ($img) {
                $width = imagesx($img);
                $height = imagesy($img);
                $new_width = 1280;
                $new_height = 720;
                $img_temp = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($img_temp, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagejpeg($img_temp, $cache_fanart, 80);
                break;
            }
        }
    }
}

/* ################
 * # Delete cache #
 */################

function delete_cache($lang) {
    $dir = opendir('cache/');
    while (false !== ($file = readdir($dir))) {
        if ($file !== "." && $file !== "..") {
            unlink('cache/' . $file);
        }
    }
    closedir();
    $output = $lang['f_cache_deleted'];
    return $output;
}

/* ######################
 * # Create Cache files #
 */######################

function create_cache($col, $lang) {

    $limit = 1;
    if (!isset($_GET['start'])) {
        $count_movies_sql = 'SELECT COUNT(*) FROM movie';
        $count_movies_result = mysql_query($count_movies_sql);
        $count = mysql_result($count_movies_result, 0);
        $start = 0;
    } else {
        $start = $_GET['start'];
        $count = $_GET['count'];
    }
    echo $lang['f_cache_created'] . ': ' . ($start+1) . ' / ' . $count . ' ' . $lang['f_cache_wait'] . '...';
    $create_cache_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['poster'] . ', ' . $col['fanart'] . ' FROM movie ORDER BY ' . $col['id_movie'] . ' LIMIT ' . $start . ', ' . $limit;
    $create_cache_result = mysql_query($create_cache_sql);
    while ($cache = mysql_fetch_array($create_cache_result)) {
        if (!file_exists('cache/' . $cache[$col['id_movie']] . '.jpg') or !file_exists('cache/' . $cache[$col['id_movie']] . '-fanart.jpg')) {
            preg_match_all('/<thumb.*?>(.*?)<\/thumb>/', $cache[$col['poster']], $poster);
            preg_match_all('/<thumb.*?>(.*?)<\/thumb>/', $cache[$col['fanart']], $fanart);
            $poster = (isset($poster[1]) ? $poster[1] : '');
            $fanart = (isset($fanart[1]) ? $fanart[1] : '');
            gd_convert($cache[$col['id_movie']], $poster, $fanart);
        }
        $start++;
    }
    if ($start < $count) {
        echo '<script>window.location="panel.php?option=create_cache&start=' . $start . '&count=' . $count . '";</script>';
    } else {
        echo '<script>window.location="panel.php";</script>';
    }
}

/* #####################
 * # Clear cache files #
 */#####################

function clear_cache($col, $lang) {
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
    $check_id_sql = 'SELECT ' . $col['id_movie'] . ' FROM movie';
    $check_id_result = mysql_query($check_id_sql);
    $cache_id_assoc = array();
    while ($id = mysql_fetch_assoc($check_id_result)) {
        array_push($cache_id_assoc, $id[$col['id_movie']]);
    }

// Delete poster file not exist in database
    foreach ($cache_poster_assoc as $val) {
        if (!in_array($val, $cache_id_assoc)) {
            if (file_exists('cache/' . $val . '.jpg')) {
                unlink('cache/' . $val . '.jpg');
            }
        }
    }

// Delete fanart file not exist in database
    foreach ($cache_fanart_assoc as $val) {
        preg_match('/(.*)-fanart/', $val, $val);
        if (!in_array($val[1], $cache_id_assoc)) {
            if (file_exists('cache/' . $val[1] . '-fanart.jpg')) {
                unlink('cache/' . $val[1] . '-fanart.jpg');
            }
        }
    }
    $output = $lang['f_cache_cleared'];
    return $output;
}

?>