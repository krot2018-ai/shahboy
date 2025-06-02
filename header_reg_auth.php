<?ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);
date_default_timezone_set("Europe/Moscow");
session_start();
//echo '<h1>время сейчас: '.date("Y-m-d H:i:s").'</h1>';

define ('DB_HOST', 'localhost');
define ('DB_BASENAME', 'z91273sk_furn');
define ('DB_USER', 'z91273sk_furn');
define ('DB_PASSWORD', '^J^}YUJ1');
define ('DB_TABLES_PREFIX', 'tf_');

define ('TABLE_USER', 'sh_users');
define ('TABLE_PARTY', 'sh_party');
define ('TABLE_REL', 'sh_party_user_rel');

define ('STORE_PATH', 'store/');
/*
define ('DB_HOST', 'localhost');
define ('DB_BASENAME', 'shahboy');
define ('DB_USER', 'root');
define ('DB_PASSWORD', '');
define ('DB_TABLES_PREFIX', 'tf_');

define ('TABLE_USER', 'user');
define ('TABLE_PARTY', 'party');
define ('TABLE_REL', 'party_user_rel');
*/

try {
	$db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_BASENAME, DB_USER, DB_PASSWORD);
	$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
	$db->query("SET lc_time_names = 'ru_RU'");
	$db->query("SET NAMES 'utf8'");
} catch (PDOException $e) {
	$_SESSION['message'] = 'Ошибка подключения к БД: '.$e->getMessage();
	$_SESSION['error'] = true;
}
?>