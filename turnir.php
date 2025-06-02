<?
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

if(!isset($_REQUEST['verification']) && isset($_REQUEST['users']) && count($_REQUEST['users']) > 0){
	if(isset($_REQUEST['new'])){
		$newPass = gen_password(8);
		//$LastElement = end($files_in_folder);
		//$exp = explode('_', $LastElement);
		//$new_int = str_pad((++$exp[0]), 4, '0', STR_PAD_LEFT);
		/*file_put_contents('store/'.$new_int.'_'.$newPass.'_main.txt', 'Vasya');
		//file_put_contents('store/'.$new_int.'_'.$newPass.'_active.txt', 'Vasya');
		//file_put_contents('store/'.$new_int.'_'.$newPass.'_chat.txt', 'Vasya');
		file_put_contents('store/'.$new_int.'_'.$newPass.'_log.txt', 'Vasya');*/
		
		$sql_string = 'INSERT INTO '.TABLE_PARTY.' (master, datetime_create, pass, active, status, current) VALUES (?, ?, ?, ?, ?, ?)';
		$sql_array = array($_SESSION['user_id'], date("Y-m-d H:i:s"), $newPass, true, 'prepare', 'red');
		$stmt = $db->prepare($sql_string);
		if(!$stmt->execute($sql_array)){
			$_SESSION['message'] .= 'Ошибка базы данных: <br>'; 
			$_SESSION['message'] .= implode('<br>', $stmt->errorInfo());
			$_SESSION['message'] .= '<br>';
			$_SESSION['error'] = true;
		}else{
			$last_id = $db->lastInsertId();
		    file_put_contents(STORE_PATH.$last_id.'_'.$newPass.'_main.txt', 'START');
		    file_put_contents(STORE_PATH.$last_id.'_'.$newPass.'_log.txt', 'START');
			//$_SESSION['message'] = 'Партия создана.<br>';
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
}

$stmt = $db->prepare("SELECT id, pass, status, current, red, black FROM ".TABLE_PARTY." WHERE master = ? AND active = ?");
//echo '<div>$_SESSION[user_id]: '.$_SESSION['user_id'].'</div>';
$stmt->execute(array($_SESSION['user_id'], '1'));
$party = false;
$error = false;
$arrPartySelf = [];

$_SESSION['party'] = 0;

while($arrParty = $stmt->fetch()){
	//echo '<pre>$arrParty: '; print_r($arrParty); echo '</pre>';
	/*echo '<pre>$arrParty:';
	print_r($arrParty);
	echo '</pre>';
	echo '<div>$_SESSION[user_id]: '.$_SESSION['user_id'].'</div>';*/
	if($party == false){
		$party = true;
		$_SESSION['party'] = $arrParty['id'];
		$party_pass = $arrParty['pass'];
	}else{
		$error = 'Many parties';
	}
	$arrPartySelf = $arrParty;
	
	if($arrPartySelf['red'] == $_SESSION['user_id']){
	    $curent_user_color = 'red';
	}elseif($arrPartySelf['black'] == $_SESSION['user_id']){
	    $curent_user_color = 'black';
	}
	if(($arrPartySelf['current'] == 'red' && $arrPartySelf['red'] == $_SESSION['user_id']) || ($arrPartySelf['current'] == 'black' && $arrPartySelf['black'] == $_SESSION['user_id'])){
		//echo '<script>curent_user_step = true; stepcolor = \''.$arrPartySelf['current'].'\';</script>';
		$curent_user_step = true;
		$stepcolor = $arrPartySelf['current'];
	}else{
	    /*if($arrParty['current'] == 'red'){
	        $opposit_color = 'black';
	    }elseif($arrParty['current'] == 'black'){
	        $opposit_color = 'red';
	    }*/
		//echo '<script>curent_user_step = false; stepcolor = \''.$arrPartySelf['current'].'\';</script>';
		$curent_user_step = false;
		$stepcolor = $arrPartySelf['current'];
	}
}

// Выбор партии, к которой присоединился текущий игрок.

$stmt = $db->prepare("SELECT ".TABLE_REL.".id as rel_id, ".TABLE_PARTY.".id as party_id, ".TABLE_PARTY.".current as party_current, ".TABLE_PARTY.".red as party_red, ".TABLE_PARTY.".black as party_black, ".TABLE_PARTY.".status, ".TABLE_PARTY.".pass as party_pass FROM ".TABLE_REL.", ".TABLE_PARTY." WHERE ".TABLE_REL.".relation = ? AND ".TABLE_REL.".user = ? AND ".TABLE_PARTY.".status = ? AND ".TABLE_REL.".party = ".TABLE_PARTY.".id AND ".TABLE_PARTY.".active = ?");
//$stmt = $db->prepare("SELECT ".TABLE_REL.".id as rel_id, ".TABLE_PARTY.".id as party_id, ".TABLE_PARTY.".status as FROM ".TABLE_REL.", ".TABLE_PARTY." ");//
/* AND ".TABLE_REL.".user = ? AND ".TABLE_PARTY.".status = ? AND ".TABLE_REL.".party = ".TABLE_PARTY.".id AND ".TABLE_PARTY.".active = ?*/
$stmt->execute(array('join', $_SESSION['user_id'], 'process', '1'));
//$stmt->execute(array('join'));
$party_join = false;
$error = false;

while($arr2 = $stmt->fetch()){
    //echo '<pre>$arr2: '; print_r($arr2); echo '</pre>';
	if($party_join == false){
		$party_join = true;
		$_SESSION['party_join'] = $arr2['party_id'];
		$party_join_pass = $arr2['party_pass'];
	}else{
		$error = 'Many parties';
	}
	/*echo '<pre>$arr2:';
	print_r($arr2);
	echo '</pre>';
	echo '<div>$_SESSION[user_id]: '.$_SESSION['user_id'].'</div>';*/
	
	$arrPartySelf = $arr2;
	if($arr2['party_red'] == $_SESSION['user_id']){
	    $curent_user_color = 'red';
	}elseif($arr2['party_black'] == $_SESSION['user_id']){
	    $curent_user_color = 'black';
	}
	if(($arr2['party_current'] == 'red' && $arr2['party_red'] == $_SESSION['user_id']) || ($arr2['party_current'] == 'black' && $arr2['party_black'] == $_SESSION['user_id'])){
		//echo '<script>curent_user_step = true; stepcolor = \''.$arr2['party_current'].'\';</script>';
		$curent_user_step = true;
		$stepcolor = $arr2['party_current'];
	}else{
	    /*if($arrParty['current'] == 'red'){
	        $opposit_color = 'black';
	    }elseif($arrParty['current'] == 'black'){
	        $opposit_color = 'red';
	    }*/
	    if(!isset($_REQUEST['exit_party']) && !isset($_REQUEST['join']) && !isset($_REQUEST['delete_party']))
		    echo '<script>curent_user_step = false; stepcolor = \''.$arr2['party_current'].'\';</script>';
		$curent_user_step = false;
		$stepcolor = $arr2['party_current'];
	}
}

//echo '<div>Выбор партии, к которой присоединился текущий игрок.</div>';

if(isset($_REQUEST['delete_party'])){
    $stmt = $db->prepare("UPDATE ".TABLE_PARTY." SET active=? WHERE id = ?");
    $stmt->execute([0, $_SESSION['party']]);
	header('Location: '.$_SERVER['PHP_SELF']);
	die();
}

if(isset($_REQUEST['exit_party'])){
	$stmt = $db->prepare("SELECT ".TABLE_REL.".id as rel_id, ".TABLE_PARTY.".id as party_id, ".TABLE_PARTY.".status as party_status FROM ".TABLE_REL.", ".TABLE_PARTY." WHERE ".TABLE_REL.".party = ".TABLE_PARTY.".id AND ".TABLE_REL.".user = ? AND ".TABLE_REL.".relation = ? AND ".TABLE_PARTY.".status = ?");
	$stmt->execute([$_SESSION['user_id'], 'join', 'process']);
	while($arrItem = $stmt->fetch()){
	    if($arrItem['party_status'] != 'process')
		    $partyID = $arrItem['party_id'];
	}
    
    if(isset($partyID)){
        $stmt = $db->prepare("UPDATE ".TABLE_PARTY." SET status=?, contragent=? WHERE id = ?");
        $stmt->execute(['prepare', false, $partyID]);
        $stmt = $db->prepare("UPDATE ".TABLE_REL." SET relation=? WHERE user = ? AND relation = ?");
        $stmt->execute(['view', $_SESSION['user_id'], 'join']);
    }
	header('Location: '.$_SERVER['PHP_SELF']);
	die();
}

if(isset($_REQUEST['join'])){
    $stmt = $db->prepare("SELECT id, pass FROM ".TABLE_PARTY." WHERE pass = ? AND status = ? AND active = ? ");
    $stmt->execute([$_REQUEST['join'], 'prepare', '1']);
    $join_partyId = '';
    while($arrParty2 = $stmt->fetch()){
    	//echo '<pre>$arrParty: '; print_r($arrParty); echo '</pre>';
    	$join_partyId = $arrParty2['id'];
    }
    
    if(strlen($join_partyId) > 0){
        $stmt = $db->prepare("UPDATE ".TABLE_PARTY." SET status=?, contragent=? WHERE id = ?");
        $stmt->execute(['process', $_SESSION['user_id'], $join_partyId]);
        
        $stmt = $db->prepare("SELECT id FROM ".TABLE_REL." WHERE relation = ? AND user = ? AND party = ?");
        $stmt->execute(['view', $_SESSION['user_id'], $join_partyId]);
        $rel_ = '';
        while($rel = $stmt->fetch()){
        	//echo '<pre>$arrParty: '; print_r($arrParty); echo '</pre>';
            $stmt = $db->prepare("UPDATE ".TABLE_REL." SET relation=? WHERE id = ?");
            $stmt->execute(['join', $rel['id']]);
        	header('Location: '.$_SERVER['PHP_SELF']);
        	die();
        }
    }
    
	header('Location: '.$_SERVER['PHP_SELF']);
	die();
}
?>
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
		<?$sql_string = 'SELECT `id`, `login`, `datetime_change`, `avatar`, `admin` FROM `'.TABLE_USER.'` WHERE id = ?';
		// >= CURDATE() – INTERVAL 3 DAY;
		// >= UNIX_TIMESTAMP() – 10';
		// >= DATEADD(minute,-5000,GETDATE())
		$stmt = $db->prepare($sql_string);
		$stmt->execute([$_SESSION['user_id']]);
		$main_user = '';
		while($arrMark = $stmt->fetch()){
			if(isset($arrMark['avatar']) && strlen($arrMark['avatar']) > 0)
				$img = $arrMark['avatar'];
			else
				$img = 'img_avatar.png';
			echo '<div  style="" class="user_block_2">
			<div style="width: 62px; position: relative;">
			<img class="inline-block size-[62px] rounded-full" src="'.$img.'" alt="Avatar">
			<span class="absolute bottom-0 end-0 block size-3.5 rounded-full ring-2 ring-white bg-teal-400 dark:ring-neutral-900"></span>
			</div><span class="u_login">'.$arrMark['login'].'</span><br><a href="?logout=Y">Выйти</a></div></div>'; //class="user_'.$arrMark['id'].' user_block"
		}
		
        $sql_string = "SELECT ".TABLE_PARTY.".id as party_id, ".TABLE_PARTY.".pass, ".TABLE_PARTY.".status, ".TABLE_PARTY.".datetime_create as party_date, ".TABLE_REL.".owner as party_owner, ".TABLE_REL.".user as party_user, ".TABLE_USER.".login as user_login FROM ".TABLE_REL.", ".TABLE_USER.", ".TABLE_PARTY." WHERE ".TABLE_PARTY.".active = '1' AND (".TABLE_REL.".user = ? OR ".TABLE_REL.".owner = ?) AND ".TABLE_REL.".party = ".TABLE_PARTY.".id AND ".TABLE_REL.".user = ".TABLE_USER.".id AND ".TABLE_PARTY.".status = 'prepare'";
       //$sql_string = "SELECT ".TABLE_PARTY.".id FROM ".TABLE_PARTY." WHERE ".TABLE_PARTY.".active = '1' ";
        //$sql_string = "SELECT ".TABLE_PARTY.".id, ".TABLE_PARTY.".pass, ".TABLE_PARTY.".owner FROM ".TABLE_REL.", ".TABLE_USER.", ".TABLE_PARTY." WHERE ".TABLE_PARTY.".active = '1' ";
        //AND (".TABLE_REL.".user = ? OR ".TABLE_PARTY.".owner = ?) AND ".TABLE_REL.".party = ".TABLE_PARTY.".id
        
        /* OR ".TABLE_REL.".owner = ?*/
		$stmt = $db->prepare($sql_string);
		$stmt->execute([$_SESSION['user_id'], $_SESSION['user_login']]);//
		$arrParty_ = [];
		while($arrParty = $stmt->fetch()){
			//echo '<pre>$arrParty: '; print_r($arrParty); echo '</pre>';
			if(isset($arrParty_[$arrParty['party_id']])){
			    $arrParty_[$arrParty['party_id']]['users'][$arrParty['party_user']] = '<b>'.$arrParty['user_login'].'</b>';
			}else{
			    $arrParty_[$arrParty['party_id']] = $arrParty;
			    $arrParty_[$arrParty['party_id']]['users'] = [$arrParty['party_user'] => '<b>'.$arrParty['user_login'].'</b>'];
			}
			//$arrParty_[$arrParty['party_id']] = $arrParty;
			//echo '<pre>$arrParty: '; print_r($arrParty_); echo '</pre>';
		}
		
		?>
<?if($party == true || $party_join == true){//echo '$party == true';
//echo '<div style="margin: auto; display: flex; width: max-content">';//; 

		echo '<div class="flex_s" "><div id="scroll" style="overflow-y: auto; height: 200px;"><div class="message_note" style="overflow-y: auto;"></div><a href="#bottom"></a></div>'; //<div id="scroll" style="overflow-y: auto; height: 200px;"><div class="message_note" style="overflow-y: auto;"></div><a href="#bottom"></a></div>
//<div><a href="" class="reverse">Перевернуть доску</a></div>
//				<div><a href="" onclick="change_golor(); return false;">Сменить цвет</a></div>
		echo '<div class="flex_1"><h2 class="step_id"></h2><div class="ajax blur"></div></div>
		<div class="message flex_2">';
		echo '<div class="status_message_2"><div class="close" onclick="$(\'.status_message_2\').slideUp(1000);">x</div><div class="status_message"></div></div>';
	    echo '<h3 class="status_prepare"';
	    if($arrPartySelf['status'] == 'prepare') echo ' style="display: none"';
	    echo '>Подготовка партии. Ждём второго участника партии.</h3>';
	    
	    echo '<h3 class="status_process"';
	    if($arrPartySelf['status'] == 'process') echo ' style="display: none"'; 
	    echo '>Оба участника на месте. Партия начата.</h3>';
	    
	    echo '<div class="contr_status">Ваш соперник: <span class="contragent"></span>. <span class="contragent_offline" style="color: red;">Не на сайте. Прошло: <b></b> сек.</span><span class="contragent_online" style="color: green;">На сайте.</span></div>';
	     echo '<div class="ajax_text"></div>';
	    //Статус партии:
		foreach($arrParty_ as $k => $v){
		    if(($v['status'] == 'prepare') && ($k == $_SESSION['party'])){
		        echo '<fieldset class="join">Приглашённые: '.implode(', ', $v['users']).'.</fieldset>';
		    }
		}

	       
		echo '<div class="checkmate_div"><a class="checkmate" href="">Проверить на Штаб-бит</a></div>';
		echo '<div class="checkmate_cont unvisible"></div>';
$fi = true;
  foreach(Player :: $array as $k => $v){

    			echo '<table class="player_table table_'.$k.'" style="display: inline-block; margin-top: 5px; margin-right: 50px; vertical-align: top;';
    			if($fi == true){
    			    $fi = false;
    			}else{
    			    echo ' float: right;';
    			}
    			echo '">';//border: 1px solid gray; class="cont_table cont_'.$k.'" 
    			echo '<tr><td>'.$v['ru'].'</td><td></td></tr>';
    			echo '<tr><td class="td_login_1"></td><td class="td_login_2"></td></tr>';
    			echo '<tr><td class="td_timer"></td><td></td></tr>';
    			echo '<tr><td></td><td><b>Пехота:</b> <span class="'.$k.'_soldier">'.$v['soldier'].'</span></td></tr>';
    			echo '<tr class="robot_load"><td colspan="2">Робот думает...</td></tr>';
    			echo '</table>';
			}
			
			//echo '<a onclick = "setTimeout(function() {alert(\'timer\')}, 1000); return false;">Клик</a>
			//<br>
			echo '<div class="loading"><img src="loader.gif"></div>
			<br>
			<input type="hidden" name="" value="" id="json">
			<?/*div class="mes"></div*/?>
			<div class="text"></div>';
		if($party == true)
	        echo '<div><a href="?delete_party=Y">Отменить партию</a></div>';
	   elseif($party_join == true)
	       echo '<div><a href="?exit_party=Y">Выйти из партии</a></div>';
		echo '</div></div>';

if(isset($_SESSION['party']) && $_SESSION['party'] > 0){
    $session_ajax = $_SESSION['party'];
}elseif(isset($party_join) && $party_join > 0){
    $session_ajax = $_SESSION['party_join'];
}

if(isset($party_join_pass) && strlen($party_join_pass) > 0){
    $party_pass_ajax = $party_join_pass;
}elseif(isset($party_pass) && strlen($party_pass) > 0){
    $party_pass_ajax = $party_pass;
}
//echo '<div>$curent_user_step: '.$curent_user_step.'</div>';
?>

<script src="jquery-3.3.1.min.js"></script>
<script>
	/*$(function() {
		$.config = {
    		party_id: '<?=$session_ajax;?>',
    		party_pass: '<?=$party_pass_ajax?>';
		};
	});*/

	party_id = '<?=$session_ajax;?>';
	party_pass = '<?=$party_pass_ajax?>';
</script>
<script>
    //curent_user_step = true;
    //curent_user_step = true;
    popup_message = false;
    more_battle_ajax = true;
    sacrifice_stop = false;
    ajax_loaded = false;
	if(typeof(stepnum) == "undefined" || stepnum === null)
		stepnum = 1;
	curent_user_step = false;
	step_id = 0;
    <?
	/*if(isset($curent_user_step)){
	    if($curent_user_step){
	        echo '
curent_user_step = true;';
	    }else{
	        echo '
curent_user_step = false;';     
	    }
	}*/
	if(isset($stepcolor)){
	    echo '
stepcolor = \''.$stepcolor.'\';';
	}
	
if(isset($curent_user_color)){?>
	curent_user_color = '<?=$curent_user_color?>';
<?}else{?>
	curent_user_color = false;
<?}?>
    if(curent_user_step !== false){
		//console.log('curent_user_step: '+curent_user_step);
		$.ajax({
			url: 'battle_ajax.php',
			type: 'post',
			data: {"party_pass" : "<?=$party_pass_ajax?>", "user_id" : "<?=$_SESSION['user_id']?>", "party_id" : "<?=$session_ajax;?>",  "status" : status},
			dataType: 'json',
			success: function(data){
                if(data.gamefield !== undefined){
					$('.ajax').html(data.gamefield);
					console.log('y');
			        stepcolor = data.stepcolor;
			        stepnum = data.stepnum;
			        if(data.stepcolor == 'red'){
            			$('.mes').text('№ хода:'+data.stepnum+'. Ход красных.');
            			$('.table_red').addClass('active');
            			$('.table_black').removeClass('active');
						console.log('stepcolor1: '+data.stepcolor);
			        }else if(data.stepcolor == 'black'){
            			$('.mes').text('№ хода:'+data.stepnum+'. Ход чёрных.');
            			$('.table_black').addClass('active');
            			$('.table_red').removeClass('active');
						console.log('stepcolor2: '+data.stepcolor);
			        }
				/*	if((typeof(step_id) !== undefined && step_id !== null) && (!(data.step_id > step_id))){
						//more_battle_ajax = true;
						console.log('more_battle_ajax');
					}*/
    				if((typeof(step_id) === undefined || step_id === null) || ((data.step_id > step_id))){
    					more_battle_ajax = false;
    					console.log('no more_battle_ajax');
    				}
                    $('.step_id').text(data.step_id);
					step_id = data.step_id;
					console.log('step_id '+data.step_id+' step_id loc'+step_id);	
                }else{
                    console.log('n');
                }
			}	
		});
    }else{
		//console.log('curent_user_step: '+curent_user_step);
	}
	
	function get_ajax_battle_inner() {
		$.ajax({
			url: 'battle_ajax.php',
			type: 'post',
			data: {"party_pass" : "<?=$party_pass_ajax?>", "user_id" : "<?=$_SESSION['user_id']?>", "party_id" : "<?=$session_ajax;?>",  "status" : status, 'check' : 'Y'},
			dataType: 'json',
			success: function(data){
				if(data.step_id !== undefined && (Number(data.step_id) > Number($('.step_id').text())))
					popup_message = false;
					if( data.message !== undefined && data.message.length > 0 && popup_message == false){
					$('.status_message_2').slideUp(1000);
				}
    			if(data.end !== undefined){
					if( popup_message == false){
						$('.ajax').html(data.gamefield);
						$('.controllable').removeClass('controllable');
						$('.ajax').addClass('blur');
						$curent_user_step = false;
					}

    				$('.step_id').text(data.step_id);
                    $('span.red_soldier').text(data.s.red);
                    $('span.black_soldier').text(data.s.black);
    				step_id = data.step_id;
				}else{
					if(data.sacrifice !== undefined){
						sacrifice_stop = true;
					}else{
						sacrifice_stop = false;
						phase_type = 'simple';
					}
    				if(data.stepcolor == curent_user_color){
    					curent_user_step = true;
    					stepcolor = curent_user_color;
    					if(data.stepcolor == 'red'){
    						$('.mes').text('№ хода:'+data.stepnum+'. Ход красных.');
    						$('.table_red').addClass('active');
    						$('.table_black').removeClass('active');
    						//console.log('stepcolor1: '+data.stepcolor);
    					}else if(data.stepcolor == 'black'){
    						$('.mes').text('№ хода:'+data.stepnum+'. Ход чёрных.');
    						$('.table_black').addClass('active');
    						$('.table_red').removeClass('active');
    						//console.log('stepcolor2: '+data.stepcolor);
    					}
    					/*if(stepcolor == 'red'){
    						stepcolor = 'black';
    					}else if(stepcolor == 'black'){
    						stepcolor = 'red';
    					}*/
    				}
    				if(data.gamefield !== undefined){
    				    if((curent_user_step === false || more_battle_ajax === true || data.step_id > step_id) && (sacrifice_stop == false || ajax_loaded == false )){
							 if(curent_user_step === false)
								console.log( '!!!curent_user_step === false');
							 if(more_battle_ajax === true)
								console.log( '!!!more_battle_ajax === true');
							 if(data.step_id > step_id)
								console.log( '!!!data.step_id > step_id');
							 if(sacrifice_stop == false)
								console.log( '!!!sacrifice_stop == false');
							 if(ajax_loaded == false)
								console.log( '!!!ajax_loaded == false ');

    					    $('.ajax').html(data.gamefield);
    					    ajax_loaded = true;
    				    }else{
    				        console.log(' !!!! sacrifice_stop !!!!'+sacrifice_stop);
    				    }
    					//console.log('y');
    				}else{
    					//console.log('n');
    				}
    				/*if((typeof(step_id) !== undefined && step_id !== null) && (!(data.step_id > step_id))){
    					//more_battle_ajax = true;
    					console.log('more_battle_ajax');
    				}*/
    			   if(data.step_id > step_id){
    					more_battle_ajax = false;
    					//console.log('no more_battle_ajax');
    				}
    				$('.step_id').text(data.step_id);
                    $('span.red_soldier').text(data.s.red);
                    $('span.black_soldier').text(data.s.black);
    				step_id = data.step_id;
    				console.log('step_id '+data.step_id+' step_id loc'+step_id);
				}
				if( data.message !== undefined && data.message.length > 0 && popup_message == false){
					//console.log(data.message);
					$('.status_message').html(data.message);
					$('.status_message_2').slideDown(1000);
					if(data.message == 'По штабу. <br>Ваш штаб в опасности.'){
					  myAudio = new Audio;
					  myAudio.src = "dragon.mp3";
					  myAudio.play();
					 }
				}
				if( popup_message == false){
					popup_message = true;
				}
			}	
		});
	}
	function get_ajax_battle() {
	    //$('#loading').show();
		//console.log('get_ajax_battle curent_user_step '+curent_user_step);
	    //if(curent_user_step === false || more_battle_ajax === true){
	        console.log('ajax ');
    		get_ajax_battle_inner();
	    //}//else{
		//	console.log('noajax ');
		//}
	}
    $(document).ready(function() {
    	get_ajax_battle_inner();
    });

	let interval2 = setInterval(get_ajax_battle,1000);

	
js_begin = false;
horn = 0;
status = '<?=$arrPartySelf['status']?>';
	function get_ajax() {
	    //$('#loading').show();
		$.ajax({
			url: 'users_list_ajax.php',
			type: 'post',
			data: { "user_id" : "<?=$_SESSION['user_id']?>", "party" : "<?=$session_ajax;?>",  "status" : status},
			dataType: 'json',
			success: function(data){
                if(data.status == 'prepare'){
                    $('.status_prepare').show();
                    $('.status_process').hide();
                    $('.contr_status').hide();
                }else if(data.status == 'process'){
                    if(status == 'prepare'){// && horn == 1 && horn == 0
                		$.ajax({
                			url: 'battle_ajax.php',
                			type: 'post',
                			data: {"party_pass" : "<?=$party_pass_ajax?>", "user_id" : "<?=$_SESSION['user_id']?>", "party_id" : "<?=$session_ajax;?>",  "status" : status},
                			dataType: 'json',
                			success: function(data){
                                if(data.gamefield !== undefined)
            						$('.ajax').html(data.gamefield);
                			}	
                		});
                      myAudio = new Audio;
                      myAudio.src = "horn.mp3";
                      //console.log(myAudio.play());
                      myAudio.play();
                        //horn = 1;
			/*if(data.color_eng == 'red')
				curent_user_step = true;*/
			//you_color = data.color_eng;

                    }
	            if(data.color_eng == 'red'){
	                color_opp = 'black';
	            }else if(data.color_eng == 'black'){
	                color_opp = 'red';
	            }
console.log('js_begin ' +js_begin );
                    //if(js_begin == false){
                        $('.table_'+data.color_eng+' .td_login_1').text($('.user_block_2 .u_login').text());
                        $('.table_'+data.color_eng+' .td_login_2').text('(это Вы)');

                        $('.table_'+data.color_eng+' .td_login_1').text($('.user_block_2 .u_login').text());
                        $('.table_'+data.color_eng+' .td_login_2').text('(это Вы)');
						if(typeof(color_opp) != "undefined" && color_opp !== null){
							$('.table_'+color_opp+' .td_login_1').text(data.contragent);
							$('.table_'+color_opp+' .td_login_2').text('(ваш соперник)');
						}
                        js_begin = true;
                    //}
                    $('table.player_table').removeClass('active');
                    $('.table_'+data.current_color).addClass('active');

					if(data.color_eng == data.current_color){
						curent_user_step = true;
						$('.ajax').removeClass('blur');
					}else{
						$('.ajax').addClass('blur');
						$curent_user_step = false;
					}
					stepcolor = data.current_color;
            
                    $('.status_prepare').hide();
                    $('fieldset.join').hide();
                    $('.status_process').show();
                    $('.contr_status').show();
                }
                $('.contragent').text(data.contragent);
                if(data.contragent.length > 0){
                    if(data.contragent_time > 10){
                        $('.contragent_offline').show();
                        $('.contragent_offline b').text(data.contragent_time);
                        $('.contragent_online').hide();
                    }else{
                        $('.contragent_offline').hide();
                        $('.contragent_online').show();
                    }
                }else{
                    $('.contragent_online').hide();
                    $('.contragent_offline').hide();
                }
                if(data.color.length > 0){
                    $('.ajax_text').text('Ваш цвет: '+data.color+'.');
                }
                status = data.status;
                if(stepcolor != data.current_color)
                    timeout_2 = setTimeout(get_ajax_battle(), 1000);
                stepcolor = data.current_color;
			}	
		});
	}
    $(document).ready(function() {
        timeout_1 = setTimeout(get_ajax(), 1000);
    });
	let interval = setInterval(get_ajax,5000);
	
	$('body').on('click', 'div.unit.controllable', function(){console.log('phase '+phase+' phase_type: '+phase_type+' stepcolor: '+stepcolor+' curent_user_step: '+curent_user_step);	
		if(phase == 'collision' && phase_type !== 'sacrifice' && curent_user_step == true){
		    console.log('stepcolor: '+stepcolor);
			if($(this).attr('data-color') == stepcolor){
				$('div.unit.controllable').removeClass('selected');
				$('.run_').removeClass('run_');
				$('.crush_').removeClass('crush_');
				$('.shot').removeClass('shot');
				$('.shot_').removeClass('shot_');
				$('.jump').removeClass('jump');
				$(this).addClass('selected');
				
				//that = this;
				if(typeof(ajax_processing) == "undefined" || ajax_processing === null || ajax_processing == false){
					$('.loading').show();
					ajax_processing = true;
					myArray = {
						'x': $(this).attr('data-x'),
						'y': $(this).attr('data-y'),
						"party_pass" : "<?=$party_pass_ajax?>", 
						"party_id" : "<?=$session_ajax;?>"
					}
					//new_machine('ajax_variants.php', myArray);
					$.ajax({
						type: 'POST',
						url: 'ajax_variants.php',
						data: myArray,
						//type : $(this).attr('data-type'), color : $(this).attr('data-color'), 
						success: function(data) {
							//$('.ajax').html(data);
							//toglePhase();
							//alert('ok');
							var arr = JSON.parse(data);

							/*arr.forEach(function(item2, i2, arr2) {
								
								arr2.forEach(function(item, i, arr) {
									
								});
								console.log(i2);
								if(arr2[i2].type == 'run')
									$('#td_'+arr2[i2].y+'_'+arr2[i2].x).addClass('run_');
								else if(arr[i2].type == 'crush')
									$('#td_'+arr2[i2].y+'_'+arr2[i2].x).addClass('crush_');
								else if(arr2[i2].type == 'shot')
									$('#td_'+arr2[i2].y+'_'+arr2[i2].x).addClass('shot');
								else if(arr[i2].type == 'shot+')
									$('#td_'+arr2[i2].y+'_'+arr2[i2].x).addClass('shot_');
								//else if(arr[i].type == 'jump')
								//	$('#td_'+arr[i].y+'_'+arr[i].x).addClass('jump');
							});*/
									
							//arr.forEach(function(item, i, arr) {
								//if(i == 'variants'){
									arr['variants'].forEach(function(item2, i2, arr2) {
										//console.log('i2 '+i2);
										//console.log(arr);
										if(arr2[i2].type == 'run')
											$('#td_'+arr2[i2].y+'_'+arr2[i2].x).addClass('run_');
										else if(arr2[i2].type == 'crush')
											$('#td_'+arr2[i2].y+'_'+arr2[i2].x).addClass('crush_');
										else if(arr2[i2].type == 'shot')
											$('#td_'+arr2[i2].y+'_'+arr2[i2].x).addClass('shot');
										else if(arr2[i2].type == 'shot+')
											$('#td_'+arr2[i2].y+'_'+arr2[i2].x).addClass('shot_');
										//else if(arr[i].type == 'jump')
										//	$('#td_'+arr[i].y+'_'+arr[i].x).addClass('jump');
									});
								//}
								//if(i == 'text'){
									$('.text').html(arr['text']);
								//}
							//});
							$('.loading').hide();
							ajax_processing = false;
						},
						error:  function(xhr, str){
							add_error(xhr, str);
							$('.loading').hide();
							ajax_processing = false;
						}
					});
				}
			}
		}
	});	
// на сколько минут ставим таймер
//var count = 60;
// запущен таймер или нет
//started = false;

// запуск таймера по кнопке
//stop = false;

timers = {'red': {'stop': true, started: false, count: 60, remain_time: 0, stop_time: 0, start_time: 0}, 'black': {'stop': true, started: false, count: 60, remain_time: 0, stop_time: 0, start_time: 0}};
function stop_timer(color) {
	timers[color]['stop'] = true;
}
function return_timer(color) {
	timers[color]['stop'] = false;
}
function start(color) {
  // если таймер уже запущен — выходим из функции
  if (timers[color]['started']) {return};
  // запоминаем время нажатия
  var start_time = new Date(); 
  // получаем время окончания таймера
  var stop_time = start_time.setMinutes(start_time.getMinutes() + timers[color]['count']);//timers[color]['stop_time']
 var now = new Date().getTime();
remain_time = stop_time - now;
  // запускаем ежесекундный отсчёт
  var countdown = setInterval(function() {
	  if(stop == false){
		// текущее время
		
		// сколько времени осталось до конца таймера
		//var remain = stop_time - now;
		remain_time = remain_time - 1000;
		// переводим миллисекунды в минуты и секунды
		var min = Math.floor( (remain_time % (1000 * 60 * 60)) / (1000 * 60) );
		var sec = Math.floor( (remain_time % (1000 * 60)) / 1000 );
		// если значение текущей секунды меньше 10, добавляем вначале ведущий ноль
		sec = sec < 10 ? "0" + sec : sec;
		// отправляем значение таймера на страницу в нужный раздел
		document.getElementById("timer").innerHTML = min + ":" + sec;
		// если время вышло
		if (remain_time < 0) {
		  // останавливаем отсчёт
		  clearInterval(countdown);
		  // пишем текст вместо цифр
		  document.getElementById("timer").innerHTML = "Всё!";
		 }
	  }else{
		//stop_time = stop_time + 1000;
		//stop_time = start_time + remain;
	  }
  }, 1000);
  // помечаем, что таймер уже запущен
  started = true;
}
//console.log('script.js curent_user_step '+curent_user_step);
$('body').on('click', 'a.checkmate', function(){
	$('.checkmate_cont').slideDown(1000);
	$('.checkmate_cont').html('<img src="circle.gif">');
	$.ajax({
		url: 'check_mate.php',
		type: 'post',
		data: {"party_pass" : "<?=$party_pass_ajax?>", "party_id" : "<?=$session_ajax;?>"},
		//dataType: 'json',
		success: function(data){
			$('.checkmate_cont').html(data);
		}
	});
	return false;
});

</script>

<script src="script.js?i=18"></script>

<script>
//console.log('script.js2 curent_user_step '+curent_user_step);
</script>
	<link rel="stylesheet" href="style.css?i=8" media="all" />
	<link rel="stylesheet" href="users_list.css" media="all" />
	<style>
        .container {
            max-width: max-content !important;
            float: right;
        }
        .ajax, .message {
            display: inline-block !important;
        }
        .ajax.blur{
            filter: opacity(80%)blur(1px);
        }
    </style>
<?echo '</div>';
}else{//echo '$party == false';?>
    <form method="post" style="width: max-content; margin: auto;">
    	<fieldset>
    		<?require 'users_list.php';?>    
<?
		//echo '<pre>$arrParty_: '; print_r($arrParty_); echo '</pre>';
		
		echo '<h2>Планируемые партии:</h2>';
		echo '<div class="party_ajax">';
		foreach($arrParty_ as $k => $v){
		    if($v['status'] == 'prepare'){
    		    if($v['party_owner'] == $_SESSION['user_login']){
    		        echo '<fieldset>Создатель: Вы, приглашённые: '.implode(', ', $v['users']).'. '.$v['party_date'].'</fieldset>';
    		        /*foreach($v['users'] as $k2 => $v2){
                        echo $v2.', ';  
    		        }*/
                    //echo '</fieldset>';
    		    }else{
    		        echo '<fieldset>'.$v['party_date'].', Создатель: '.$v['party_owner'].', приглашённые: '.implode(', ', $v['users']).'. '.$v['party_date'].' <a href="?join='.$v['pass'].'"><b>+ Присоединиться</b></a></fieldset>';
    		    }
		    }
		}
		echo '</div>';
		echo '<img style="position: fixed; left: 0px; bottom: 0px; width: 150px; height: 150px;" id="loading" src="spinner-icon-gif-24.gif">';//loading-105_2.gif
		
?>
    	</fieldset>
    </form>
    <div class="ajax_parties"></div>
	</div>
<?}?>
</div>

		

<style>
	.unvis{
		display: none;
	}
</style>
		<?//include 'ajax_machine.php';?>
		<link rel="stylesheet" href="reg_auth.css" media="all" />
	</body>
</html>
