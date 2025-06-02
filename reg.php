<?
$MAIN_PAGE = 'Y';
require "header_reg_auth.php";

function gen_password($length = 6)
{
	$password = '';
	$arr = array(
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 
		'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',  
		'1', '2', '3', '4', '5', '6', '7', '8', '9', '0'
	);
	//	'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
	//	'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
	for ($i = 0; $i < $length; $i++) {
		//$password .= $arr[random_int(0, count($arr) - 1)];
		$password .= $arr[rand(0, count($arr) - 1)];
	}
	return $password;
}

if(isset($_SESSION['stay'])){
	unset($_SESSION['stay']);
}

if(!isset($_REQUEST['verification'])){
	if(isset($_REQUEST['user_reg'])){
		$_SESSION['message'] = '';
		$_SESSION['error'] = false;
		if(!(isset($_REQUEST['username']) && strlen($_REQUEST['username']) > 0)){
			$_SESSION['message'] .= 'Не указаны ФИО.<br>';
			$_SESSION['error'] = true;
		}
		if(!(isset($_REQUEST['place']) && strlen($_REQUEST['place']) > 0)){
			$_SESSION['message'] .= 'Не указано место учёбы.<br>';
			$_SESSION['error'] = true;
		}
		if(!(isset($_REQUEST['age']) && strlen($_REQUEST['age']) > 0)){
			$_SESSION['message'] .= 'Не указан возраст.<br>';
			$_SESSION['error'] = true;
		}
		if(!(isset($_REQUEST['login']) && strlen($_REQUEST['login']) > 0)){
			$_SESSION['message'] .= 'Не указан желаемый логин.<br>';
			$_SESSION['error'] = true;
		}
		if(!(isset($_REQUEST['confirm']) && strlen($_REQUEST['confirm']) > 0)){
			$_SESSION['message'] .= 'Необходимо дать согласие на обработку персональных данных.<br>';
			$_SESSION['error'] = true;
		}
		
		if($_SESSION['error'] != true){
			$sql_string = 'SELECT id FROM '.TABLE_USER.' WHERE login = ?';
			$stmt = $db->prepare($sql_string);
			$itemFinded = false;
			/*$stmt->execute(array($_REQUEST['username']));
			while($arrItem = $stmt->fetch()){
				//$itemFinded = true;
				echo '<pre>'; print_r($arrItem); echo '</pre>';
				$_SESSION['message'] = 'Логин занят.';
				$_SESSION['error'] = true;
				//header('Location: '.$_SERVER['SCRIPT_NAME']);
			}*/
			if(!$stmt->execute(array($_REQUEST['login']))){
				$_SESSION['message'] = 'Ошибка базы данных: '; 
				$_SESSION['message'] .= implode('<br>', $stmt->errorInfo());
				$_SESSION['error'] = true;
			}else{
				$arrItems = $stmt->fetchAll();
				foreach($arrItems as $k => $arrItem){
					$itemFinded = true;
					//echo '<pre>'; print_r($arrItem); echo '</pre>';
					$_SESSION['message'] .= 'Логин занят. Выберите другой логин.';
					$_SESSION['error'] = true;
					//header('Location: '.$_SERVER['SCRIPT_NAME']);
					//die();
				}
				if(!$itemFinded){
					$stmt = $db->prepare("INSERT INTO ".TABLE_USER." (login, pass, fio, birthdate, place) VALUES (?, ?, ?, ?, ?)");
					$password = gen_password(12);
					if(!$stmt->execute(array($_REQUEST['login'], password_hash($password, PASSWORD_DEFAULT), $_REQUEST['username'], $_REQUEST['age'], $_REQUEST['place']))){//
						$_SESSION['message'] = 'Ошибка базы данных: '; 
						$_SESSION['message'] .= implode('<br>', $stmt->errorInfo());
						$_SESSION['error'] = true;
					}else{
                        $_SESSION['message'] = 'Пользователь зарегистрирован. Ваш логин: '.$_REQUEST['login'].'. Ваш пароль: <span style="font-size: 1.5rem; color: red; font-weight: 600;">'.$password.'</span>. Запишите  его.';
                        $_SESSION['error'] = false;
						$_SESSION['stay'] = true;
						
						$_SESSION['user_id'] = $db->lastInsertId();
						$_SESSION['user_login'] = $_REQUEST['login'];
						$cookie_time = 365;
						setcookie("user_login", $_REQUEST['login'], time() + 3600 * 24 * $cookie_time, '/');
						setcookie("user_password", $password, time() + 3600 * 24 * $cookie_time, '/');
						setcookie("auto_login", 'Y', time() + 3600 * 24 * $cookie_time, '/');
						//header('Location: game.php');
						//die();
					}
				}
			}
		}
	}

	if(isset($_REQUEST['user_auth'])){
		$stmt = $db->prepare("SELECT id, pass FROM ".TABLE_USER." WHERE login = ?");
		$stmt->execute(array($_REQUEST['username']));
		$_SESSION['message'] = 'Пользователь с данным логином или паролем не зарегистрирован. <a class="reg" onclick="$(this).hide();" href="#">Пройдите&nbsp;регистрацию</a>';
		$_SESSION['error'] = true;
		while($arrMark = $stmt->fetch()){
			if (password_verify($_REQUEST['password'], $arrMark['pass'])) {
				$_SESSION['message'] = 'Поздравляем! Вы все вспомнили верно.';
				$_SESSION['error'] = false;
				$_SESSION['user_id'] = $arrMark['id'];
				$_SESSION['user_login'] = $_REQUEST['username'];
				$cookie_time = 365;
				setcookie("user_login", $_REQUEST['username'], time() + 3600 * 24 * $cookie_time, '/');
				setcookie("user_password", $_REQUEST['password'], time() + 3600 * 24 * $cookie_time, '/');
				setcookie("auto_login", 'Y', time() + 3600 * 24 * $cookie_time, '/');
				header('Location: game.php');
				die();
			}
		}
	}
}else{
	$_SESSION['message'] = 'Не пройдена проверка на бота.<br>';
	$_SESSION['error'] = true;
}

if((!isset($_SESSION['user_id']) || strlen($_SESSION['user_id']) <= 0) && (!isset($_SESSION['stay']))){
	if(isset($_COOKIE['user_login']) && strlen($_COOKIE['user_login']) > 0 && isset($_COOKIE['auto_login']) && $_COOKIE['auto_login'] == 'Y' && !isset($_SESSION['stay'])){
		$stmt = $db->prepare("SELECT id, pass, login, admin FROM ".TABLE_USER." WHERE login = ?");
		$stmt->execute(array($_COOKIE['user_login']));
		if(isset($_COOKIE['auto_login']) && $_COOKIE['auto_login'] == 'Y'){
			//$_SESSION['message'] = 'Пользователь с данным логином или паролем не зарегистрирован.';
			$_SESSION['error'] = true;
		}
		while($arrMark = $stmt->fetch()){
		    //echo '<pre>$arrMark: '; print_r($arrMark); echo '</pre>';
			if (password_verify($_COOKIE['user_password'], $arrMark['pass'])) {
				//$_SESSION['message'] = 'Вы авторизованы.';
				$_SESSION['error'] = false;
				$_SESSION['user_id'] = $arrMark['id'];
				$_SESSION['user_login'] = $arrMark['login'];
				$cookie_time = 365;
				setcookie("user_login", $_COOKIE['user_login'], time() + 3600 * 24 * $cookie_time, '/');
				setcookie("user_password", $_COOKIE['user_password'], time() + 3600 * 24 * $cookie_time, '/');
				setcookie("auto_login", 'Y', time() + 3600 * 24 * $cookie_time, '/');
				header('Location: game.php');
				die();
				//header('Location: '.SITE_REQUEST_URI);
				//die();
			}else{
				setcookie("user_login", $_COOKIE['user_login'], time() - 1, '/');
				setcookie("user_password", $_COOKIE['user_password'], time() - 1, '/');
				setcookie('auto_login', '', -1, '/');
				if(isset($_COOKIE['auto_login']) && $_COOKIE['auto_login'] == 'Y'){
					//$_SESSION['message'] = 'Пользователь с данным логином или паролем не зарегистрирован.';
					$_SESSION['error'] = true;
				}
				//header('Location: '.SITE_REQUEST_URI);
				//die();
			}
		}
		if($_SESSION['error'] == true){
			setcookie("user_login", $_COOKIE['user_login'], time() - 1, '/');
			setcookie("user_password", $_COOKIE['user_password'], time() - 1, '/');
			setcookie('auto_login', '', -1, '/');
		}
	}/*else{
		//if($_SERVER['SCRIPT_NAME'] != SITE_FOLDER_.'/login/person_page.php'){
			header('Location: person_page.php');
			die();
		//}
	}*/
}else{
	header('Location: game.php');
	die();
}

if(isset($_SESSION['stay'])){
	echo 'isset($_SESSION[stay]';
}
if(isset($_REQUEST['username'])){
	$request_username = $_REQUEST['username'];
}else{
	$request_username = '';
}

if(isset($_REQUEST['login'])){
	$request_login = $_REQUEST['login'];
}elseif(isset($_COOKIE['user_login'])){
	$request_login = $_COOKIE['user_login'];
}else{
	$request_login = '';
}

if(isset($_REQUEST['password'])){
	$request_password = $_REQUEST['password'];
}elseif(isset($_COOKIE['user_password'])){
	$request_password = $_COOKIE['user_password'];
}else{
	$request_password = '';
}

if(isset($_REQUEST['place'])){
	$request_place = $_REQUEST['place'];
}else{
	$request_place = '';
}

if(isset($_REQUEST['age'])){
	$request_age = $_REQUEST['age'];
}else{
	$request_age = '';
}

if(isset($_REQUEST['confirm'])){
	$request_confirm = $_REQUEST['confirm'];
}else{
	$request_confirm = '';
}
?>
<html>
	<head>
		<title>Советские шахматы</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head> 
	<body>
		<?include 'header.php';?>
		<div class="login-page">
		  <div class="form">
		  <?/*if(isset($_SESSION['user_id']))
		  echo '<br>user_id: '.$_SESSION['user_id'];
		  if(isset($_SESSION['user_login']))
		echo '<br>user_login: '.$_SESSION['user_login'];
		if(isset($_COOKIE['user_login']))
			echo '<br>$_COOKIE user_login: '.$_COOKIE['user_login'];
		if(isset($_COOKIE['user_password']))
			echo '<br>$_COOKIE user_password: '.$_COOKIE['user_password'];
		if(isset($_COOKIE['auto_login']))
			echo '<br>$_COOKIE auto_login: '.$_COOKIE['auto_login'];*/?>
				<?if(isset($_SESSION['message'])){?>
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
					<?/*if(isset($_SESSION['error']) && $_SESSION['error'] == true){
						echo '<p class="message">';
					}else{
						echo '<p class="message green">';
					}*/
					echo $_SESSION['message']/*.'</p>'*/;
					unset($_SESSION['message']);
					unset($_SESSION['error']);
					echo '</div>';
				}
				//$('.workplaceCont').hide('1000') $('.workplaceCont').hide()
				?>

			<form method="post" class="register-form">
				<label for="username"><b>ФИО</b></label>
			  <input id="username" required="required" type="text" name="username" value="<?=$request_username?>" placeholder="ФИО"/>
			  <label for="place"><b>Место учёбы</b></label>
			  <input class="workplace" required="required" autocomplete="off" id="place" onblur="" type="text" name="place" value="<?=$request_place?>" placeholder="Место учёбы"/>
			  <div class="workplaceCont"></div>
			  <input class="workplaceId" value="" type="number" name="workplaceId">
			  <div class="onestring" style="margin-bottom: 16px; ">
			<span><b>Ваш возраст</b></span>
			<div><input required="required" name="age" id="14" type="radio" value="14-16"<?if($request_age == '14-16') echo ' checked="checked"'?>><label for="14">14 - 16 лет</label></div>
		   <div><input required="required" name="age" id="17" type="radio" value="17-19"<?if($request_age == '17-19') echo ' checked="checked"'?>><label for="17">17 - 19 лет</label></div>
			<div><input required="required" name="age" id="20" type="radio" value="20-25"<?if($request_age == '20-25') echo ' checked="checked"'?>><label for="20">20 - 25 лет</label></div>
			<div><input required="required" name="age" id="o" type="radio" value="Другое"<?if($request_age == 'Другое') echo ' checked="checked"'?>><label for="o">Другое</label></div>
			</div>
			<label for="login"><b>Желаемый логин</b></label>
			  <input required="required" type="text" id="login" name="login" value="<?=$request_login?>" placeholder="Желаемый логин"/>
			<div class="onestring" style="margin-bottom: 16px;"><input style="float: left; margin: 10px;" required="required" id="confirm" name="confirm" type="checkbox" value="Y"<?if($request_confirm == 'Y') echo ' checked="checked"'?>>
				<label for="confirm">Согласие на обработку персональных данных</label>
			</div>
			  <?/*input type="password" name="password" value="<?=$request_password?>" placeholder="Пароль"/*/?>
			  <?/*input type="text" placeholder="Email"/*/?>
			  <input type="hidden" name="user_reg" value="Y"/>
			  <input class="control" type="checkbox" name="verification" value="Y">
			  <button>Создать аккаунт</button>
			  <p class="message">Уже есть аккаунт? <a href="#">Авторизоваться</a></p>
			</form>
			<form method="post" class="login-form">
			  <input type="text"  name="username" value="<?=$request_username?>" placeholder="Логин"/>
			  <input type="password" name="password" value="<?=$request_password?>" placeholder="Пароль"/>
			  <input type="hidden" name="user_auth" value="Y"/>
			  <input class="control" type="checkbox" name="verification" value="Y">
			  <button>Авторизоваться</button>
			  <p class="message">Не зарегистрированы? <a href="#">Создать аккаунт</a></p>
			</form>
		  </div>
		</div>
		<link rel="stylesheet" href="reg_auth.css" media="all" />
		<link rel="stylesheet" href="font-awesome.min.css" media="all" />
		<style>
			<?if(isset($_REQUEST['user_auth'])){
				echo '.form .register-form {
				  display: none;
				}';
			}else{
				echo '.form .login-form {
				  display: none;
				}';
			}?>
		</style>
		<script src="jquery-3.3.1.min.js"></script>
		<script>
$(document).mousedown(function(e) {
    currElem = e.target;
    if(!$(currElem).is('.name')){
        $('.workplaceCont').hide();
    };
});
		
	$('body').on('input click','input.workplace', function(){
		cont = $(this).siblings('.workplaceCont');
		var search = $(this).val();
		cont.css('display', 'block');
		cont.html('Поиск...');
		//alert('поиск');
		$.post(
			'ajax_get_school.php',
			{
				string: search
			},
			function(data){
				//if(search == $(that).val()){
					cont.html(data);
					search = false;
				//}
			}
		);
	});
	
	
	
	$('body').on('click','.tag_result', function(){//alert('1');
		tagname = $(this).find('.name').text();
		workplaceId = $(this).find('.id').text();
	
		tagname_input = $(this).closest('.workplaceCont').siblings('.workplace');
		tagname_input.val(tagname).addClass('sel');
		delete_link = $(this).closest('.workplaceCont').siblings('.tagDelete');
		tagidinput = $(this).closest('.workplaceCont').siblings('.workplaceId');
		$(this).closest('.workplaceCont').siblings('.tagLinks').remove();

		$(this).closest('.workplaceCont').siblings('.workplace').attr('size', tagname.length || 10);
		$(this).closest('.workplaceCont').siblings('.workplaceId').val(workplaceId);
		var tagDiv = $(this).closest('.tagDiv');
		$(this).parent('.workplaceCont').empty().hide();
	});
		
		
		$('.message a, a.reg').click(function(){
		   $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
		});
		</script>
	</body>
</html>