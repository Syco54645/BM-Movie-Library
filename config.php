<?PHP
/* #############################################################
 * # This is the configuration file.                           #
 * # Edit options for connecting to the database.              #
 * # Copy the xml file with a movie library exported from XBMC #
 * # Go to the panel.php in web browser                        #
 * # Click on the import button to import movies               #
 */#############################################################

/* #################
 * # OPTIONS START #
 */#################

// Database
$mysql_host = 'localhost'; // Database host
$mysql_port = '3306'; // Database port, default is 3306
$mysql_login = 'login'; // Database login
$mysql_pass = 'pass'; // Database password
$mysql_database = 'database'; // Database name

// Config
$xml_file = 'videodb.xml'; // File with movie library, default is videodb.xml
$language = 'lang_en.php'; // The file that contains the language, file must be in the lang/ folder
$panel_pass = 'admin'; // Password to admin panel
$per_page = 30; // Movies per page, If you do not want to have pagination, type 0
$random_limit = 5; // Movies in random panel, to turn off panel type 0
$recently_limit = 5; // Movies in recently added panel, to turn off panel type 0

// Remote database
$mysql_host_remote = 'host.com'; // Database host
$mysql_port_remote = '3306'; // Database port, default is 3306
$mysql_login_remote = 'login'; // Database login
$mysql_pass_remote = 'pass'; // Database password
$mysql_database_remote = 'database'; // Database name

/* ########################################
 * # Don't edit anything below this line! #
 */########################################

// Connection to database
$connect = @mysql_connect($mysql_host . ':' . $mysql_port, $mysql_login, $mysql_pass);
if (!$connect) {
    die('Could not connect: ' . mysql_error());
}
$select = @mysql_select_db($mysql_database);
if (!$select) {
    die('Could not connect: ' . mysql_error());
}

// Connection to remote
$remote_connection = array($mysql_host_remote, $mysql_port_remote, $mysql_login_remote, $mysql_pass_remote, $mysql_database_remote);

// Sets utf8 connections
mysql_query('SET CHARACTER SET utf8');
mysql_query('SET NAMES utf8');

// Language 
require 'lang/' . $language;

// Video resolution
$vres_array = array('sd', 480, 576, 540, 720, 1080);
$width_height = array(0 => 0, 720 => 480, 768 => 576, 960 => 544, 1280 => 720, 1920 => 1080);

// Video codec
$vtype = array(
    '3iv2' => '3ivx',
    '3ivd' => '3ivx',
    '3ivx' => '3ivx',
    '8bps' => 'qt',
    'advj' => 'qt',
    'avrn' => 'qt',
    'rle' => 'qt',
    'rpza' => 'qt',
    'smc' => 'qt',
    'sv10' => 'qt',
    'svq' => 'qt',
    'qt' => 'qt',
    'zygo' => 'qt',
    'avc' => 'avc',
    'avc1' => 'avc1',
    'dca' => 'dts',
    'div1' => 'divx',
    'div2' => 'divx',
    'div3' => 'divx',
    'div4' => 'divx',
    'div5' => 'divx',
    'div6' => 'divx',
    'divx' => 'divx',
    'dm4v' => 'mpeg4',
    'dx50' => 'mpeg4',
    'geox' => 'mpeg4',
    'm4s2' => 'mpeg4',
    'mpeg4' => 'mpeg4',
    'mpeg-4' => 'mpeg4',
    'nds' => 'mpeg4',
    'ndx' => 'mpeg4',
    'pvmm' => 'mpeg4',
    'em2v' => 'mpeg2',
    'lmp2' => 'mpeg2',
    'mmes' => 'mpeg2',
    'mpeg-2' => 'mpeg2',
    'mpeg2' => 'mpeg2',
    'flv' => 'flv',
    'h264' => 'h264',
    'mp4' => 'mp4',
    'mpeg' => 'mpeg',
    'pim1' => 'mpeg',
    'vc1' => 'vc1',
    'wvc1' => 'vc1',
    'wmv' => 'wmv',
    'wmva' => 'wmva',
    'xvid' => 'xvid',
    'xvix' => 'xvid'
);

// Audio codec
$atype = array(
    'a_vorbis' => 'ogg',
    'ogg' => 'ogg',
    'vorbis' => 'ogg',
    'aac' => 'aac',
    'ac3' => 'ac3',
    'aif' => 'aif',
    'aifc' => 'aifc',
    'aiff' => 'aiff',
    'ape' => 'ape',
    'dca' => 'dts',
    'dts' => 'dts',
    'dd' => 'dd',
    'dolbydigital' => 'dd',
    'dtshr' => 'dtshd',
    'dtsma' => 'dtshd',
    'dtshd' => 'dtshd',
    'flac' => 'flac',
    'mp1' => 'mp1',
    'mp2' => 'mp2',
    'mp3' => 'mp3',
    'truehd' => 'truehd',
    'wma' => 'wma',
    'wmav2' => 'wma',
    'wmahd' => 'wmahd',
    'wmapro' => 'wmapro'
);

// Audio channel
$achan = array(
    '1' => '1',
    '2' => '2',
    '6' => '6',
    '8' => '8'
);

// Set var
$var = array('sort' => 1, 'genre' => 'all', 'search' => '', 'page' => 1);
foreach ($var as $key => $val) {
    if (isset($_GET[$key])) {
        $$key = $_GET[$key];
    } else {
        $$key = $val;
    }
}

// Database column
$col['id_movie']        =   'idMovie';
$col['id_file']         =   'idFile';
$col['title']           =   'c00';
$col['plot']            =   'c01';
$col['outline']         =   'c02';
$col['tagline']         =   'c03';
$col['votes']           =   'c04';
$col['rating']          =   'c05';
$col['credits']         =   'c06';
$col['year']            =   'c07';
$col['poster']          =   'c08';
$col['imdb_id']         =   'c09';
$col['title_format']    =   'c10';
$col['runtime']         =   'c11';
$col['mpaa']            =   'c12';
$col['top250']          =   'c13';
$col['genre']           =   'c14';
$col['director']        =   'c15';
$col['originaltitle']   =   'c16';
$col['thumb_url']       =   'c17';
$col['studio']          =   'c18';
$col['trailer']         =   'c19';
$col['fanart']          =   'c20';
$col['country']         =   'c21';
$col['file_path']       =   'c22';
$col['id_path']         =   'c23';
?>