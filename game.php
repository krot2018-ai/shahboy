<?
$MAIN_PAGE = 'Y';
require "header_reg_auth.php";
if(isset($_REQUEST['logout'])){
	unset($_SESSION['user_id']);
	unset($_SESSION['user_login']);
	unset($_SESSION['message']);
	unset($_SESSION['error']);
	//setcookie("user_login", '', -1, '/');
	//setcookie("user_password", '', -1, '/');
	setcookie('auto_login', '', -1, '/');
}

if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0){
	if(isset($_COOKIE['user_login']) && strlen($_COOKIE['user_login']) > 0 && isset($_COOKIE['auto_login']) && $_COOKIE['auto_login'] == 'Y' && !isset($_REQUEST['logout'])){
		$stmt = $db->prepare("SELECT id, pass, login FROM ".TABLE_USER." WHERE login = ?");
		$stmt->execute(array($_COOKIE['user_login']));
		//$_SESSION['message'] = 'Пользователь с данным логином или паролем не зарегистрирован.';
		//$_SESSION['error'] = true;
		while($arrMark = $stmt->fetch()){
			if (password_verify($_COOKIE['user_password'], $arrMark['pass'])) {
				$_SESSION['message'] = 'Вы авторизованы.';
				$_SESSION['error'] = false;
				$_SESSION['user_id'] = $arrMark['id'];
				$_SESSION['user_login'] = $arrMark['login'];
				$cookie_time = 365;
				setcookie("user_login", $_COOKIE['user_login'], time() + 3600 * 24 * $cookie_time, '/');
				setcookie("user_password", $_COOKIE['user_password'], time() + 3600 * 24 * $cookie_time, '/');
				setcookie("auto_login", 'Y', time() + 3600 * 24 * $cookie_time, '/');
				//header('Location: '.SITE_REQUEST_URI);
				//die();
			}else{
				setcookie("user_login", $_COOKIE['user_login'], time() - 1, '/');
				setcookie("user_password", $_COOKIE['user_password'], time() - 1, '/');
				setcookie('auto_login', '', -1, '/');
				//$_SESSION['message'] = 'Пользователь с данным логином или паролем не зарегистрирован.';
				//$_SESSION['error'] = true;
				header('Location: reg.php');
				die();
			}
		}
		header('Location: reg.php');
		die();
	}else{
		//if($_SERVER['SCRIPT_NAME'] != SITE_FOLDER_.'/login/person_page.php'){
			header('Location: reg.php');
			die();
		//}
	}
}

		/*if(isset($_SESSION['message'])){?>
	<div style="
		z-index: 5;
		max-width: 100%;
		padding: .25rem .5rem;
		margin-top: .1rem;
		font-size: .75rem;
		color: #fff;
		background-color: rgba(230,24,13,.95);
		border-radius: 6px;
		margin-bottom: 15px;
		padding-bottom: 15px;
		padding-top: 15px;
		">
			<?
			echo $_SESSION['message'];
			unset($_SESSION['message']);
			unset($_SESSION['error']);
			echo '</div>';
		}*/
		
function gen_password($length = 6)
{
	$password = '';
	$arr = array(
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 
		'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
		'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 
		'1', '2', '3', '4', '5', '6', '7', '8', '9', '0'
	);

	for ($i = 0; $i < $length; $i++) {
		//$password .= $arr[random_int(0, count($arr) - 1)]; // новый PHP
		$password .= $arr[rand(0, count($arr) - 1)];
	}

	return $password;
}

/*if(isset($_SESSION['user_id']))
	echo '<br>user_id: '.$_SESSION['user_id'];
 if(isset($_SESSION['user_login']))
	echo '<br>user_login: '.$_SESSION['user_login'];
if(isset($_COOKIE['user_login']))
	echo '<br>$_COOKIE user_login: '.$_COOKIE['user_login'];
if(isset($_COOKIE['user_password']))
	echo '<br>$_COOKIE user_password: '.$_COOKIE['user_password'];*/

$dir = 'store';
$files_in_folder = scandir($dir);
asort($files_in_folder);
$is_request = false;

$filesInFolder = [];

if(!isset($_REQUEST['verification'])){
	if(isset($_REQUEST['new'])){
		$newPass = gen_password(8);
		//$LastElement = end($files_in_folder);
		//$exp = explode('_', $LastElement);
		//$new_int = str_pad((++$exp[0]), 4, '0', STR_PAD_LEFT);
		/*file_put_contents('store/'.$new_int.'_'.$newPass.'_main.txt', 'Vasya');
		//file_put_contents('store/'.$new_int.'_'.$newPass.'_active.txt', 'Vasya');
		//file_put_contents('store/'.$new_int.'_'.$newPass.'_chat.txt', 'Vasya');
		file_put_contents('store/'.$new_int.'_'.$newPass.'_log.txt', 'Vasya');*/
		
		$sql_string = 'INSERT INTO '.TABLE_PARTY.' (master, datetime_create, pass, active) VALUES (?, ?, ?, ?)';
		$sql_array = array($_SESSION['user_id'], date("Y-m-d H:i:s"), $newPass, true);
		$stmt = $db->prepare($sql_string);
		if(!$stmt->execute($sql_array)){
			$_SESSION['message'] .= 'Ошибка базы данных: <br>'; 
			$_SESSION['message'] .= implode('<br>', $stmt->errorInfo());
			$_SESSION['message'] .= '<br>';
			$_SESSION['error'] = true;
		}else{
			$last_id = $db->lastInsertId();
		    file_put_contents('store/'.$last_id.'_'.$newPass.'_main.txt', 'Vasya');
		    file_put_contents('store/'.$last_id.'_'.$newPass.'_log.txt', 'Vasya');
			$_SESSION['message'] = 'Партия создана.<br>';
			//$_SESSION['error'] = false;
			if(isset($_REQUEST['users']) && count($_REQUEST['users']) > 0){
				foreach($_REQUEST['users'] as $k => $v){
					$sql_string = 'INSERT INTO '.TABLE_REL.' (user, party, relation, owner) VALUES (?, ?, ?, ?)';
					$sql_array = array($v, $last_id, 'view', $_SESSION['user_login']);
					$stmt = $db->prepare($sql_string);
					if(!$stmt->execute($sql_array)){
						$_SESSION['message'] .= 'Ошибка базы данных: <br>'; 
						$_SESSION['message'] .= implode('<br>', $stmt->errorInfo());
						$_SESSION['message'] .= '<br>';
						$_SESSION['error'] = true;
					}else{
						$last_id_2 = $db->lastInsertId();
					}
				}
			}
			
		}
		//header("Refresh:0");
		header('Location: '.$_SERVER['PHP_SELF']);
		die();
	}
}?>
<html>
	<head>
		<title>Советские шахматы</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head> 
	<body>
	    <?include 'header.php';?>
		<?
		require_once ('classes/Player.php');

//echo '<pre>$_REQUEST: '; print_r($_REQUEST); echo '</pre>';


?>
<div style="width: auto;">
	<div style="width: max-content; margin: auto;">
        <?if(isset($_SESSION['message']) && strlen($_SESSION['message']) > 0){?>
        	<div style="
        		z-index: 5;
        		max-width: 100%;
        		padding: .25rem .5rem;
        		margin-top: .1rem;
        		font-size: .75rem;
        		color: #fff;
        		<? if(isset($_SESSION['error']) && $_SESSION['error'] == true) echo ' background-color: rgba(230,24,13,.95);'; else echo ' background-color: rgba(24,230,13,.95);';?>
        		border-radius: 6px;
        		margin-bottom: 15px;
        		padding-bottom: 15px;
        		padding-top: 15px;
        		">
        			<?
        			echo $_SESSION['message'];
        			unset($_SESSION['message']);
        			unset($_SESSION['error']);
        			echo '</div>';
        		}?>
    <?if(isset($_REQUEST['info'])){
        echo '
			<h1 >Источники:</h1> 
			<ul>
				<li><a target="_blank" href="https://www.marpravda.ru/news/zhizn-v-mariy-el/v-mezhdunarodnyy-den-shakhmat-predlagaem-vspomnit-o-zamechatelnoy-raznovidnosti-shakhmat-shakh-boy/">В Международный день шахмат предлагаем вспомнить о замечательной разновидности шахмат "Шах-бой"</a></li>
				<li><a target="_blank" href="https://zen.yandex.ru/media/aprelev/tankom-hodi-zabytye-sovetskie-shahmaty-60bb7c6fbbd5974178138fb8">Танком ходи! Забытые "советские шахматы"</a></li>
				<li><a target="_blank" href="https://gest.livejournal.com/889387.html">Советские шахматы</a></li>
			</ul>
        <a href="?"><button class="btn btn-2 btn-sep icon-send">НА ГЛАВНУЮ СТРАНИЦУ</button></a>';
    }else{?>
        <div style="width: max-content; margin: auto;">
            <a href="training/"><button class="btn btn-2 btn-sep icon-info">ОБУЧЕНИЕ ИГРЕ</button></a>
            <a href="turnir.php"><button class="btn btn-2 btn-sep icon-cart">ИГРАТЬ</button></a>
            <?/*button class="btn btn-2 btn-sep icon-cart">ИГРАТЬ</button*/?>
        </div>
        <div style="width: max-content; margin: auto;">
            <a href="rules.pdf" target="_blank"><button class="btn btn-2 btn-sep icon-heart">МЕТОДИЧКА С ПРАВИЛАМИ ИГРЫ</button></a>
            <a href="?info=Y"><button class="btn btn-2 btn-sep icon-send">ИСТОЧНИКИ</button></a>
        </div>
    <?}?>
</div>
<?//echo '<pre>$files_in_folder: '; print_r($files_in_folder); echo '</pre>';

//$dir = $_SERVER['DOCUMENT_ROOT'].'/store/';

//$dir = '/store/';
//$dir = SITE_FILES_STORE_FOLDER;
//echo '<div>$dir: '.$dir.'</div>';
//$files_in_folder = scan($dir);

/*foreach($files_in_folder as $k => $v){
	//$pieces = explode(SITE_FOLDER, $v);
	//unset($pieces[0]);
	//$newPath = implode(SITE_FOLDER, $pieces);
	$filesInFolder[$newPath] = $newPath;
}
echo '<pre>$filesInFolder: '; print_r($filesInFolder); echo '</pre>';
*/

/*

$stmt = $db->prepare("SELECT id, pass FROM ".TABLE_PARTY." WHERE master = ? AND active = ?");
echo '<div>$_SESSION[user_id]: '.$_SESSION['user_id'].'</div>';
$stmt->execute(array($_SESSION['user_id'], '1'));
$party = false;
$error = false;
$arrParty_ = [];

$_SESSION['party'] = 0;

while($arrParty = $stmt->fetch()){
	echo '<pre>$arrParty: '; print_r($arrParty); echo '</pre>';
	if($party == false){
		$party = true;
		$_SESSION['party'] = $arrParty['id'];
	}else{
		$error = 'Many parties';
	}
	$arrParty_ = $arrParty;
}
if(isset($error) && strlen($error) > 0)
	echo '<div>'.$error.'</div>';
if($party == true){
	echo '<div id="scroll" style="overflow-y: auto; height: 200px;"><div class="message_note" style="overflow-y: auto;"></div><a href="#bottom"></a></div>';
	echo '<div class="ajax"></div>
	<div class="message">
		<div class="titr">
			<p>
				<h3 style="padding-left:40px">Источники:</h3> 
				<ul>
					<li><a target="_blank" href="https://www.marpravda.ru/news/zhizn-v-mariy-el/v-mezhdunarodnyy-den-shakhmat-predlagaem-vspomnit-o-zamechatelnoy-raznovidnosti-shakhmat-shakh-boy/">В Международный день шахмат предлагаем вспомнить о замечательной разновидности шахмат "Шах-бой"</a></li>
					<li><a target="_blank" href="https://zen.yandex.ru/media/aprelev/tankom-hodi-zabytye-sovetskie-shahmaty-60bb7c6fbbd5974178138fb8">Танком ходи! Забытые "советские шахматы"</a></li>
					<li><a target="_blank" href="https://gest.livejournal.com/889387.html">Советские шахматы</a></li>
				</ul>
			</p>
			<div><a href="" class="reverse">Перевернуть доску</a></div>
			<div><a href="" onclick="change_golor(); return false;">Сменить цвет</a></div>
		</div>';
		echo '<div><a href="?logout=Y">Выйти</a></div>';
		foreach(Player :: $array as $k => $v){
			echo '<table class="cont_table cont_'.$k.'" style="display: inline-block; margin-top: 5px; margin-right: 5px; vertical-align: top;">';//border: 1px solid gray; 
			echo '<tr><td>'.$v['ru'].'</td><td><input class="check_player_status" type="checkbox" name="robot" id="'.$k.'" ';
			if($v['type'] == 'ai')
				echo 'checked="checked" ';
			echo 'value="'.$k.'"><label for="'.$k.'">робот</label></td></tr>';
			echo '<tr><td><b>Штаб:</b> <span class="'.$k.'_head">'.$v['head'].'</span></td><td><b>Пехота:</b> <span class="'.$k.'_soldier">'.$v['soldier'].'</span></td></tr>';
			echo '<tr class="robot_load"><td colspan="2">Робот думает...</td></tr>';
			echo '</table>';
		}
		
		//echo '<a onclick = "setTimeout(function() {alert(\'timer\')}, 1000); return false;">Клик</a>
		//<br>
		echo '
		<br>
		<input type="hidden" name="" value="" id="json">
		<div class="mes"></div>
		<a id="planeAiTurn" onclick = "planeAiTurn(); return false;"><button class="constructos-knopok klubnicus">Доверить ход компьютеру</button></a>
		<a style="display: none" id="getAiTurn" onclick = "getAiTurn(); return false;"><button class="constructos-knopok klubnicus">Доверить ход компьютеру</button></a>
		<div class="text"></div>
		<div class="loading"><img src="loader.gif"></div>
	</div>';//Совершить автоход
}*/
?>
		
<style>
.user_block, .user_block_2{
	display: inline-block;
	padding: 5px; 
	border: 2px solid #ababab;
	margin: 3px;
	border-radius: 5px;
}
.size-\[62px\] {
  width: 62px;
  height: 62px;
}
.rounded-full {
  border-radius: 9999px;
}
img, video {
  //max-width: 100%;
  height: auto;
}
.ring-white {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(255 255 255 / var(--tw-ring-opacity));
    border: 2px solid white;
}
.ring-2 {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color) !important;
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color) !important;
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000) !important;

}
.bg-yellow-400 {
  --tw-bg-opacity: 1;
  background-color: rgb(250 204 21 / var(--tw-bg-opacity));
}

.bg-teal-400 {
  --tw-bg-opacity: 1;
  background-color: rgb(45 212 191 / var(--tw-bg-opacity));
}

.bg-red-400 {
  --tw-bg-opacity: 1;
  background-color: rgb(248 113 113 / var(--tw-bg-opacity));
}

.rounded-full {
  border-radius: 9999px;
}
.size-3\.5 {
  width: 0.875rem;
  height: 0.875rem;
}
.block {
  display: block;
}
.end-0 {
  inset-inline-end: 0px;
}
.bottom-0 {
  bottom: 0px;
}
.absolute {
  position: absolute;
}
</style>
<style>
	.unvis{
		display: none;
	}
</style>
		<?//include 'ajax_machine.php';?>
		<link rel="stylesheet" href="reg_auth.css" media="all" />
	</body>
</html>