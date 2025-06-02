<?require "header_reg_auth.php";

require_once ('classes/GameField.php');
require_once ('classes/Direction.php');
require_once ('classes/CObject.php');
//require_once ('classes/Party.php');
require_once ('classes/Player.php');

/*
$_SESSION['sacrifice']
$_SESSION['sacrifice_id']
$_SESSION['stepcolor']

*/

//session_start();
$action = '';
$json = file_get_contents(STORE_PATH.$_REQUEST['party_id'].'_'.$_REQUEST['party_pass'].'_main.txt', true);
if($json == 'START'){
    $action = 'restart';
}else{
    //$MAIN = json_decode($json, true);
    $MAIN = unserialize($json);
    if(!isset($MAIN['step_id'])){
        $MAIN['step_id'] = 1;
    }
}

$is_error = false;

function addToArr($array, $value, $idKey=false){
	if(!$idKey){
		if(!empty($array)){
			$array[] = $value;
		}else{
			$array = [$value];
		}
	}else{
		if(!empty($array)){
			$array[$value] = $value;
		}else{
			$array = [$value => $value];
		}
	}
	return $array;
}

function print_pre($array, $name=''){
	echo '<br>'.$name;
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

function getPartyValue($prop){
	global $db;
	$sql_string = 'SELECT `'.$prop.'` FROM `'.TABLE_PARTY.'` WHERE id = ?';
	$sql_arr = [$_REQUEST['party_id']];
	$stmt = $db->prepare($sql_string);
	$stmt->execute($sql_arr);
	while($arrItem = $stmt->fetch()){
		if(isset($arrItem[$prop]))
			$result = $arrItem[$prop];
		else
			$result = false;
	}
	return $result;
}

function updPartyValue($prop, $value){
	global $db;
	$sql_string = 'SELECT `'.$prop.'` FROM `'.TABLE_PARTY.'` WHERE id = ?';
	$sql_arr = [$_REQUEST['party_id']];
	$stmt = $db->prepare($sql_string);
	$stmt->execute($sql_arr);
	
	$sql_string = 'UPDATE `'.TABLE_PARTY.'` SET `'.$prop.'` = ? WHERE id = ?';
	$sql_arr = [$value, $_REQUEST['party_id']];
	$stmt = $db->prepare($sql_string);
	$stmt->execute($sql_arr);
	
	return true;
}
/*function isConflict($arr){
	$Error = false;
	$arConflict = false;
	if(count($arr['begin']) > 1 || count($arr['static']) > 1){
		$Error = 'multi begin or static';
	}
	if((count($arr) > 1) || (count($arr['end']) > 1)){
		$arConflict = [];
		foreach($arr as $k => $v){
			foreach($v as $k2 => $v2){
				$arConflict[$v2] = $k;
			}
		}
	}
	return ['error' => $Error, 'arConflict' => $arConflict];
}*/

//if(isset($_COOKIE['name'])){
	
//sleep(5);
	
if(isset($MAIN['idNext'])){
	$GLOBALS['idNext'] = $MAIN['idNext'];
}else{
	$GLOBALS['idNext'] = 1;
}

/*require_once ('classes/GameField.php');

require_once ('classes/Direction.php');

require_once ('classes/CObject.php');*/

class CObjectAll
{
	//global GameField;
	public static $objects = '';//GameField::$arrObject;
	
	public static function create() {
		self::$objects = GameField::$arrObject;
    }
	
	/*public static function tryRemove() {	// удалить попытки передвижения у всех юнитов
		self::create();
		foreach(self::$objects as $k => $v){
			//GameField::$arrObject[$k] -> PrepareMove($arr[1]);
			GameField::$arrObject[$k] -> yTry = false;
			GameField::$arrObject[$k] -> xTry = false;
			GameField::$arrObject[$k] -> dirTry = false;
		}
    }*/
}

$result['return_shah'] = '';

$add_result = [];
$add_result['return_shah'] = '';
function check_shah_(){
    global $result;
    global $add_result;
	global $oppositcolor;
	global $MAIN;
	$isshah_result = GameField::isshah();
	
	if($isshah_result != false){
		
		$_SESSION['shah'] = [];
		/*foreach($isshah_result as $k => $v){
			if($v -> color == $_SESSION['stepcolor'])
				$selfshah = true;
		}*/
		foreach($isshah_result as $k => $v){
		    $selfshah = false;
    		if($v['target'] -> color == $MAIN['stepcolor'])
    			$selfshah = true;
    		if($selfshah == true){//echo '<br> if 1';
    			//GameField::return_shah();
    			//$result['return_shah'] = 'return_shah1 target: '.$isshah_result['target'] -> id.' agr '.$isshah_result['agr'] -> id.' Ход не разрешён из-за угрозы штабу '.$_SESSION['stepcolor'].'.';
    			$result['return_shah'] .= 'Ход не разрешён из-за угрозы штабу.';
    			$_SESSION['shah'][$MAIN['stepcolor']] = $MAIN['stepcolor'];
    			$result['shah_flag_return'] = true;
    		}else{//echo '<br> else 1';
    			//GameField::shah_message();
    			//$result['return_shah'] .= 'Шах штабу игрока '.$oppositcolor.'. ';
				if($oppositcolor == 'black'){
					$result['return_shah'] .= 'ПО ШТАБУ<br>Штаб чёрного игрока находится под боем фигуры противника.';
					if(isset($add_result['return_shah'])){
						$result['return_shah'] .= 'ПО ШТАБУ<br>Штаб чёрного игрока находится под боем фигуры противника.';
						$add_result['return_shah'] .= 'ПО ШТАБУ<br>Штаб чёрного игрока находится под боем фигуры противника.';
					}else
						$add_result['return_shah'] = 'ПО ШТАБУ<br>Штаб чёрного игрока находится под боем фигуры противника.';
				}elseif($oppositcolor == 'red'){
					if(isset($add_result['return_shah'])){
						$result['return_shah'] .= 'ПО ШТАБУ<br>Штаб красного игрока находится под боем фигуры противника.';
						$add_result['return_shah'] .= 'ПО ШТАБУ<br>Штаб красного игрока находится под боем фигуры противника.';
					}else
						$add_result['return_shah'] = 'ПО ШТАБУ<br>Штаб красного игрока находится под боем фигуры противника.';
				}
    			$MAIN['shah'][$oppositcolor] = $oppositcolor;
    			$result['shah_flag_message'] = true;
    			$add_result['shah_flag_message'] = true;
    			//echo '<br> $add_result[return_shah]: '.$add_result['return_shah'];
    		}
		}
	}
	if(isset($result['shah_flag_return']) && $result['shah_flag_return'] == true){
	    return $result;
	}else{
	    return true;
	}
}
//require_once ('classes/CObject.php');

/*$log = date('Y-m-d H:i:s') . ' ';
foreach($_REQUEST as $k => $v)
{
	$log .= $k.': ';
	$log .= $v.'; ';
}
file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);*/

if($action == 'restart'){//echo '<br>$action = restart';
	$firstStep = true;
	//unset($MAIN['GameField']);
	$MAIN = [];
	$GLOBALS['idNext'] = 1;

 //< стандартная расстановка	
require_once ('start_position/standart.php');
//require_once ('start_position/plans.php');
 //> стандартная расстановка
//< расстановка для теста диверсий
//require_once ('start_position/diversion.php');
//require_once ('start_position/diversion2.php');
//require_once ('start_position/diversion3.php');
// >  расстановка для теста диверсий
 
	/*$t1 = new soldier('red', 3, 9);
	$t1 = new soldier('black', 4, 9);
	$t1 = new Tank('red', 5, 4);
	$t1 = new Tank('red', 3, 4);
	$t1 = new soldier('red', 4, 6);
	$t1 = new soldier('red', 3, 6);
	$t1 = new soldier('black', 4, 8);
	$t1 = new soldier('black', 4, 7);
	$t1 = new soldier('black', 5, 7);
	$t1 = new Head('red', 5, 8);
	
	$t1 = new Plane('red', 3, 0);*/
	
	//echo '<pre>$t1'; print_r($t1); echo '</pre>';
	$resurs = [Player::$array];
	
	$MAIN['idNext'] = $GLOBALS['idNext'];
	$MAIN['Player'] = Player :: $array;
	
	$MAIN['stepcolor'] = 'red';
	$oppositcolor = 'black';
	$MAIN['stepnum'] = 1;
}else{
	if(!empty($MAIN['GameField']['arrObject']))
		GameField::$arrObject = $MAIN['GameField']['arrObject'];
	if(!empty($MAIN['GameField']['matrix']))
		GameField::$matrix = $MAIN['GameField']['matrix'];
	if(!empty($MAIN['GameField']['stage']))
		GameField::$stage = $MAIN['GameField']['stage'];
	if(!empty($MAIN['GameField']['reverse']))
		GameField::$reverse = $MAIN['GameField']['reverse'];
	//if(!empty($_SESSION['Party']['direction']))
		//Party::$direction = $_SESSION['Party']['direction'];
	if(!empty($MAIN['Player']))
		Player :: $array = $MAIN['Player'];
	
	$party_end = false;
	
	/*$master = getPartyValue('master');
	$contragent = getPartyValue('contragent');
	if($_SESSION['user_id'] == $master){
		$current_user_role = 'master';
	}elseif($_SESSION['user_id'] == $contragent){
		$current_user_role = 'contragent';
	}*/
	
	$red = getPartyValue('red');
	$black = getPartyValue('black');
	
	if($_SESSION['user_id'] == $red){
		$current_user_color = 'red';
		$opposit_user_color = 'black';
	}elseif($_SESSION['user_id'] == $black){
		$current_user_color = 'black';
		$opposit_user_color = 'red';
	}
	
	
	foreach(Player :: $array as $k => $v){
		if((isset($v['victory']) && $v['victory'] == true) || (isset($v['defeated']) &&  $v['defeated'] == true)){
			$party_end = true;
		}
	}
	
	$message = '';
	//$message = '$MAIN[stepcolor]: '.$MAIN['stepcolor'];
	
	//sleep(5);
	//echo '<script>alert("123");</script>>';
	// < ход
	if(!isset($MAIN['stepcolor'])){
	    $MAIN['stepcolor'] = 'red';
		$oppositcolor = 'black';
	}else{
		if($MAIN['stepcolor'] == 'black'){
			$oppositcolor = 'red';
		}elseif($MAIN['stepcolor'] == 'red'){
			$oppositcolor = 'black';
		}
	}
	if($party_end == false){
		if(isset($_REQUEST['saboteur']) && isset($_REQUEST['target'])){ // жертва пешки для уничтожения вражеской фигуры
			if((isset($_REQUEST['saboteur']) && !strlen($_REQUEST['target']) > 0) || (GameField::$arrObject[$_REQUEST['saboteur']] -> color == $MAIN['stepcolor'] && GameField::$arrObject[$_REQUEST['target']] -> color == $oppositcolor)){
				if(isset($_REQUEST['target']) && strlen($_REQUEST['target']) > 0){
					$unset_class = get_class(GameField::$arrObject[$_REQUEST['target']]); // тип уничтоженной фигуры для дальнейшей проверки
					$unset_color = GameField::$arrObject[$_REQUEST['target']] -> color;
				}
				//$message = '$unset_class '.$unset_class;
				if(isset($unset_class) && isset($unset_color)){
					if($unset_class != 'Head' && $MAIN['sacrifice'] == true && $MAIN['sacrifice_id'] == $_REQUEST['saboteur']){
						GameField::$arrObject[$_REQUEST['saboteur']] -> deleteFull();
						GameField::$arrObject[$_REQUEST['target']] -> deleteFull();
						
						Player::$array[$MAIN['stepcolor']]['soldier'] --;
						if(Player::$array[$MAIN['stepcolor']]['soldier'] < 1){
							$message .= $MAIN['stepcolor'].' проиграл. Закончилась пехота.';
							Player::$array[$MAIN['stepcolor']]['defeated'] = true;
							Player::$array[Player::$array[$unset_color]['opposit']]['victory'] = true;
							
							Player::$array[$MAIN['stepcolor']]['message'] = '<h2>Закончилась пехота.</h2>Вы проиграли.';
							Player::$array[Player::$array[$MAIN['stepcolor']]['opposit']]['message'] = '<h2>Закончилась пехота.</h2>Вы выиграли.';
							updPartyValue('status', 'end');
							updPartyValue('active', '0');
							updPartyValue('winner', Player::$array[$unset_color]['opposit']);
						}
						
						$MAIN['stepcolor'] = $oppositcolor;
						$message .= '
	change 1';
						$MAIN['stepnum']++;
						$not_change_player = false;
						//echo 'Player::$array[$oppositcolor][type]: '. Player::$array[$oppositcolor]['type'];
						if(Player::$array[$oppositcolor]['type'] == 'ai'){
							$next_turn_auto = 1;
						}
						
						if($unset_class == 'soldier')
						{
							Player::$array[$unset_color]['soldier'] --;
							if(Player::$array[$unset_color]['soldier'] < 1){
								$message .= $unset_color.' проиграл. Закончилась пехота.';
								Player::$array[$unset_color]['defeated'] = true;
								Player::$array[Player::$array[$unset_color]['opposit']]['victory'] = true;
								
								Player::$array[$unset_color]['message'] = '<h2>Закончилась пехота.</h2>Вы проиграли.';
								Player::$array[Player::$array[$unset_color]['opposit']]['message'] = '<h2>Закончилась пехота.</h2>Вы выиграли.';
								updPartyValue('status', 'end');
								updPartyValue('active', '0');
								updPartyValue('winner', Player::$array[$unset_color]['opposit']);
							}
						}
						$resurs = [Player::$array];
						$MAIN['sacrifice'] = false;
						unset($MAIN['sacrifice_id']);
					}
					else
					{
						// < отладка
						if(!($unset_class != 'Head'))
						{
							$message .= 'Условие !($unset_class != \'Head\') не выполнено.';
						}
						
						if(!($MAIN['sacrifice'] == true))
						{
							$message .= 'Условие $_SESSION[\'sacrifice\'] == true не выполнено.';
						}
						
						if(!($MAIN['sacrifice_id'] == $_REQUEST['saboteur']))
						{
							$message .= 'Условие $_SESSION[\'sacrifice_id\'] == $_REQUEST[\'saboteur\'] не выполнено.';
						}
						// > отладка
					}
				}else{
					$MAIN['stepcolor'] = $oppositcolor;
					$message .= '
	change 2';
					$MAIN['stepnum']++;
					$not_change_player = false;
					unset($MAIN['sacrifice']);
					//echo 'Player::$array[$oppositcolor][type]: '. Player::$array[$oppositcolor]['type'];
					if(Player::$array[$oppositcolor]['type'] == 'ai'){
						$next_turn_auto = 1;
					}
				}
				unset($unset_class);
				unset($unset_color);
			}else{
				$approved = false;
				$is_error = true;
				$message .= 'Цвет диверсанта и цели не подходит';
			}
		}elseif(isset($_REQUEST['saboteur']) && (!isset($_REQUEST['target']) || strlen($_REQUEST['target']) > 0)){
			/*$_SESSION['stepcolor'] = $oppositcolor;
			$_SESSION['stepnum']++;*/
			$not_change_player = false;
		}elseif((isset($_REQUEST['y']) && isset($_REQUEST['x'])) && GameField::checkCorrectField($_REQUEST['y'], $_REQUEST['x'])){ // Передвижение, раздавливание или выстрел // если клетка не выходит за пределы поля
			//echo '<script>alert(\'автоход\');</script>';
			/*if(isset($_SESSION['Variants_object'])){
				echo '<script>console.log(Variants_object - '.$_SESSION['Variants_object'].');</script>';
			}
			if(isset($_SESSION['Variants'])){
				foreach($_SESSION['Variants'] as $k => $v){
					echo '<script>console.log('.$k.' - '.print_r($v).');</script>';
				}
			}*/
			if(isset($_REQUEST['id']))
			{
				$UNIT_ID = $_REQUEST['id'];
			}
			elseif(isset($_REQUEST['shot_id']))
			{
				$UNIT_ID = $_REQUEST['shot_id'];
			}	
			if(isset($UNIT_ID) && strlen($UNIT_ID) > 0){
				if(GameField::$arrObject[$UNIT_ID] -> color == $MAIN['stepcolor']){
					if(isset($_REQUEST['id'])){ // передвижение, в том числе раздавливание вражеской фигуры
						$backup = ['type' => 'move', 'id1' => $_REQUEST['id'], 'actor1' => GameField::$arrObject[$_REQUEST['id']], 'x1' => GameField::$arrObject[$_REQUEST['id']]->x, 'y1' => GameField::$arrObject[$_REQUEST['id']]->y];
						$activn = 'движение';
						if(isset($MAIN['Variants']) && isset($MAIN['Variants_object']) && ($MAIN['Variants_object'] == $_REQUEST['id'])){
							$variants = $MAIN['Variants'];
						}else{
							$object = GameField::$arrObject[$_REQUEST['id']];
							$variants = $object -> getVariants();
						}
						if(isset($variants)){
							$approved = false;
							foreach($variants as $k => $v){
								if($v['x'] == $_REQUEST['x'] && $v['y'] == $_REQUEST['y'] && ($v['type'] == 'crush' || $v['type'] == 'run')){
									$approved = true;
								}
							}
							if($approved){
								if(isset(GameField :: $matrix[$_REQUEST['y']][$_REQUEST['x']]) && is_object((GameField :: $matrix[$_REQUEST['y']][$_REQUEST['x']]))){ // если место занято
									$c_ob = GameField :: $matrix[$_REQUEST['y']][$_REQUEST['x']];
									
									$backup['actor2'] = $c_ob;
									//Warning: get_class() expects parameter 1 to be object, boolean given in Z:\home\localhost\www\shahboy\ajax.php on line 186
									$unset_class = get_class($c_ob);
									$unset_color = $c_ob -> color;
									$c_id = $c_ob -> id;
									$backup['id2'] = $c_id;
									if($unset_color == GameField::$arrObject[$_REQUEST['id']] -> color) // Если цвет атакующего не отличается от цвета атакованного
									{
										$approved = false;
										$is_error = true;
										$message .= 'Атака своего юнита';
									}
									
									if(in_array($unset_class, GameField::$arrObject[$_REQUEST['id']] -> impossible_target)) // Если атакующий юнит может атаковать цель за счёт своего класса
									{
										$approved = false;
										$is_error = true;
										$message .= 'Данный юнит защищён от таких атак';
									}			
								}
								
								if($approved){
									$object = GameField::$arrObject[$_REQUEST['id']];//$_REQUEST['id']
									unset(GameField :: $matrix[$object -> y][$object -> x]); // очищение старых кординат активного юнита
									$last_x = $object -> x;
									$last_y = $object -> y;
									$object -> y = $_REQUEST['y'];		// перемещение активного юнита на целевые координаты
									$object -> x = $_REQUEST['x'];		// перемещение активного юнита на целевые координаты
									//$c_id_g = $c_id;
									if(isset($c_id)){
										unset(GameField::$arrObject[$c_id]); // удаление юнита, раздавленного целевым юнитом
										GameField::$cemetery[$c_id] = $object;
										//GameField::$arrObject[$c_id] -> dead = true;
									}
									GameField :: $matrix[$_REQUEST['y']][$_REQUEST['x']] = $object;
									//$message = 'Ход сделан';
									unset($MAIN['Variants_object']);
									unset($MAIN['Variants']);
									// возможно нужна новая функция
								
									if( get_class(GameField::$arrObject[$_REQUEST['id']]) == 'soldier' /*&&  GameField::checkCorrectField($object -> y + Direction :: $properties[Player :: $array[$_SESSION['stepcolor']]['direction']]['y'], $object -> x + Direction :: $properties[Player :: $array[$_SESSION['stepcolor']]['direction']]['x'])*/){//GameField::checkCorrectField($object -> y + Direction :: $properties[Player :: $array[$_SESSION['stepcolor']]['direction']]['y'], $object -> x + Direction :: $properties[Player :: $array[$_SESSION['stepcolor']]['direction']]['x'])
										$new_y = $object -> y + Direction :: $properties[Player :: $array[$MAIN['stepcolor']]['direction']]['y'];
										$new_x = $object -> x + Direction :: $properties[Player :: $array[$MAIN['stepcolor']]['direction']]['x'];
										
										$last_new_y = $last_y + Direction :: $properties[Player :: $array[$MAIN['stepcolor']]['direction']]['y'];
										$last_new_x = $last_x + Direction :: $properties[Player :: $array[$MAIN['stepcolor']]['direction']]['x'];
										
										if(($new_y < 0 || $new_y > GameField::$y || $new_x < 0 || $new_x > GameField::$x) && !($last_new_y < 0 || $last_new_y > GameField::$y || $last_new_x < 0 || $last_new_x > GameField::$x))
										{
											//Player :: $array[$MAIN['stepcolor']]['soldier_last'][$_REQUEST['id']] = $_REQUEST['id'];
											$isshah = GameField::isshah($_REQUEST['y'], $_REQUEST['x']);
											if(!$isshah){ // проверка на то что прорвавшаяся пешка не находится под боем
												$message = 'Жертва пешки';
												//echo 'Жертва пешки';
												$not_change_player = true;
												$MAIN['sacrifice'] = true;
												$MAIN['sacrifice_id'] = $_REQUEST['id'];
											}else{
												$shah_message = '';
												foreach($isshah as $k => $v){
													$shah_message .= $v['agr']->name.', y:'.$v['agr']->y.', x:'.$v['agr']->x.';';
												}
												$not_change_player = true;
												$approved = false;
												GameField::return_shah($backup);
												$no_change_step_id = true;
												//$message = 'шах. не Жертва пешки';
												//console.log('шах. не Жертва пешки');
												//echo 'шах. не Жертва пешки';
											}
											//$MAIN['Player']['soldier_last'][] = $_REQUEST['id'];
										}/*else{
											if(isset(Player :: $array[$MAIN['stepcolor']]['soldier_last'][$_REQUEST['id']]){
												unset(Player :: $array[$MAIN['stepcolor']]['soldier_last'][$_REQUEST['id']]);
											}
										}*/
									}else{

									}
									$check_shah_result = check_shah_();
									 //echo '<pre>$check_shah_result: '; print_r($check_shah_result); echo '</pre>';
									if($check_shah_result !== true){
										//$is_error = true;
										$not_change_player = true;
										$approved = false;
										GameField::return_shah($backup);
										$no_change_step_id = true;
										$add_result = $check_shah_result;
										/*if(!empty($_SESSION['GameField']['arrObject']))
											GameField::$arrObject = $_SESSION['GameField']['arrObject'];
										if(!empty($_SESSION['GameField']['matrix']))
											GameField::$matrix = $_SESSION['GameField']['matrix'];
										if(!empty($_SESSION['GameField']['stage']))
											GameField::$stage = $_SESSION['GameField']['stage'];
										if(!empty($_SESSION['GameField']['reverse']))
											GameField::$reverse = $_SESSION['GameField']['reverse'];
										unset($_SESSION['Variants_object']);
										unset($_SESSION['Variants']);*/
									}else{
										unset($MAIN['shah'][$MAIN['stepcolor']]);
									}
									// проверка шаха
									// смена активного игрока
									if(!isset($not_change_player) || !$not_change_player){
										/*if($_SESSION['stepcolor'] == 'black')
											$_SESSION['stepcolor'] = 'red';
										else
											$_SESSION['stepcolor'] = 'black';*/
										
										/*$MAIN['stepcolor'] = $oppositcolor;
										$MAIN['stepnum']++;*/
										
										if($MAIN['stepcolor'] == 'red'){
											$MAIN['stepcolor'] = 'black';
										}elseif($MAIN['stepcolor'] == 'black'){
											$MAIN['stepcolor'] = 'red';
											$MAIN['stepnum']++;
										}
										//echo 'Player::$array[$oppositcolor][type]: '. Player::$array[$oppositcolor]['type'];
										/*if(Player::$array[$oppositcolor]['type'] == 'ai'){
											$next_turn_auto = 1;
										}*/
									}
								}	
							}else{
								$message .= 'Ходить на выбранное поле нельзя';
								$is_error = true;
							}
						}
					}elseif(isset($_REQUEST['shot_id'])){ // выстрел
						if(isset($MAIN['Variants']) && isset($MAIN['Variants_object']) && ($MAIN['Variants_object'] == $_REQUEST['shot_id'])){
							$variants = $MAIN['Variants'];
						}else{
							$object = GameField::$arrObject[$UNIT_ID];//$UNIT_ID
							$variants = $object -> getVariants();
						}
						if(isset($variants)){
							$approved = false;
							foreach($variants as $k => $v){
								if($v['x'] == $_REQUEST['x'] && $v['y'] == $_REQUEST['y'] && $v['type'] == 'shot+'){
									$approved = true;
								}
							}
							if($approved){
								$c_ob = GameField :: $matrix[$_REQUEST['y']][$_REQUEST['x']];
								$c_id = $c_ob -> id;
								$unset_class = get_class($c_ob); // тип уничтоженной фигуры для дальнейшей проверки игрока на проигрыш
								$unset_color = $c_ob -> color; // цвет уничтоженной фигуры для дальнейшей проверки игрока на проигрыш
								
								if($unset_color == GameField::$arrObject[$_REQUEST['shot_id']] -> color) // Если цвет атакующего не отличается от цвета атакованного
								{
									$approved = false;
									$is_error = true;
									$message .= 'Атака своего юнита';
								}
								
								if(in_array($unset_class, GameField::$arrObject[$_REQUEST['shot_id']] -> impossible_target)) // Если атакующий юнит может атаковать цель за счёт своего класса
								{
									$approved = false;
									$is_error = true;
									$message .= 'Данный юнит защищён от таких атак';
								}
								
								if($approved){
									GameField::$cemetery[$c_id] = &GameField::$arrObject[$c_id];
									unset(GameField::$arrObject[$c_id]);
									//GameField::$arrObject[$c_id] -> dead = true;
									GameField :: $matrix[$_REQUEST['y']][$_REQUEST['x']] = false;
									unset($MAIN['Variants_object']);
									unset($MAIN['Variants']);
									
									// смена активного игрока
									/*if($_SESSION['stepcolor'] == 'black')
										$_SESSION['stepcolor'] = 'red';
									else
										$_SESSION['stepcolor'] = 'black';*/
									$MAIN['stepcolor'] = $oppositcolor;
							$message .= '
		change 3';
									$MAIN['stepnum']++;
									//echo 'Player::$array[$oppositcolor][type]: '. Player::$array[$oppositcolor]['type'];
									if(Player::$array[$oppositcolor]['type'] == 'ai'){
										$next_turn_auto = 1;
									}
								}
								
							}else{
								$message .= 'Стрелять в выбранное поле нельзя';
								$is_error = true;
							}
						}
					}
				}else{
					$message .= 'Ход другого игрока GameField:'.GameField::$arrObject[$UNIT_ID] -> color.'; MAIN:'.$MAIN['stepcolor'];
					$is_error = true;
				}
			}else{
				$message .= 'Нет UNIT_ID';
				$is_error = true;
			}
			if(isset($MAIN['Variants']))
				unset($MAIN['Variants']);
			if(isset($MAIN['Variants_object']))
				unset($MAIN['Variants_object']);
			
			if(isset($unset_class) && isset($unset_color) && !$is_error)
			{
				if($unset_class == 'soldier')
				{
					Player::$array[$unset_color]['soldier'] --;
					if(Player::$array[$unset_color]['soldier'] < 1){
						$message .= $unset_color.' проиграл. Закончилась пехота.';
						Player::$array[$unset_color]['defeated'] = true;
						Player::$array[$unset_color]['message'] = '<h2>Закончилась пехота.</h2>Вы проиграли.';
						Player::$array[Player::$array[$unset_color]['opposit']]['victory'] = true;
						Player::$array[Player::$array[$unset_color]['opposit']]['message'] = '<h2>Закончилась пехота.</h2>Вы выиграли.';
						updPartyValue('status', 'end');
						updPartyValue('active', '0');
						updPartyValue('winner', Player::$array[$unset_color]['opposit']);
					}
				}
				elseif($unset_class == 'Head')
				{
					Player::$array[$unset_color]['head'] --;
					if(Player::$array[$unset_color]['head'] < 1){
						//$message .= $unset_color.' проиграл. Разрушен штаб.';
						Player::$array[$opposit_user_color]['defeated'] = true;
						Player::$array[$opposit_user_color]['message'] = '<h2>Штаб-бит.</h2>Вы проиграли.';
						Player::$array[$current_user_color]['victory'] = true;
						Player::$array[$current_user_color]['message'] = '<h2>Штаб-бит.</h2>Вы выиграли.';
						updPartyValue('status', 'end');
						updPartyValue('active', '0');
						updPartyValue('winner', $_SESSION['user_id']);
					}
				}
				$resurs = [Player::$array];
			}			
		}/*else{
			echo '<div>Ошибка: целевая клетка выходит за пределы поля</div>';
		}*/
		// > ход
	}else{
		$result = [];
		if(isset(Player::$array[$MAIN['stepcolor']]['message'])){
			$result['message'] = Player::$array[$current_user_color]['message'];
		}
		if(Player::$array[$current_user_color]['victory'] == true){
			$result['end'] = 'victory';
		}elseif(Player::$array[$current_user_color]['defeated'] == true){
			$result['end'] = 'defeated';
		}
		foreach(Player :: $array as $k => $v){
			$result['s'][$k] = $v['soldier'];
		}
		$gamefield = GameField::draw(true);
		$gamefield = str_replace("\"", "'", $gamefield);
		$result['gamefield'] = $gamefield;
		$result['object'] = Player::$array;
	}
	
}

if(!empty($_REQUEST['board_reverse'])){
	
	if(GameField::$reverse == true){
		GameField::$reverse = false;
	}else{
		GameField::$reverse = true;
	}
	//echo '<br>GameField::$reverse<br>'.GameField::$reverse;
}

if(!empty($_REQUEST['ai'])){
	Player::$array[$_REQUEST['ai']]['type'] = $_REQUEST['action'];
}
/*echo '<pre>$arrObject: ';
print_r(GameField::$arrObject);
echo '</pre>';
echo '<pre>$_REQUEST[action]: ';
print_r($_REQUEST['action']);
echo '</pre>';
echo '<pre>$_SESSION[GameField]: ';
print_r($_SESSION['GameField']);
echo '</pre>';*/

// < обработка  хода

if(isset($presetPhase)){
	$phaseRequest = $presetPhase;
}elseif(isset($_REQUEST['phase'])){
	$phaseRequest = $_REQUEST['phase'];
}

// > обработка  хода

$MAIN['GameField']['arrObject'] = GameField :: $arrObject;
$MAIN['GameField']['matrix'] = GameField :: $matrix;
$MAIN['GameField']['stage'] = GameField :: $stage;
$MAIN['GameField']['whiteFields'] = GameField :: $whiteFields;
$MAIN['GameField']['reverse'] = GameField :: $reverse;
//$_SESSION['Party']['direction'] = Party :: $direction;


/*echo '<pre>Player :: $array ';
print_r(Player :: $array);
echo '</pre>';*/
$MAIN['Player'] = Player :: $array;
if(isset($MAIN['step_id'])){
	if((!isset($no_change_step_id)) && (!isset($_REQUEST['check'])))
		$MAIN['step_id'] = $MAIN['step_id'] + 1;
}else
    $MAIN['step_id'] = 1;
//$json = json_encode($MAIN);
if((isset($_REQUEST['saboteur']) && isset($_REQUEST['target'])) || ((isset($_REQUEST['y']) && isset($_REQUEST['x'])) && GameField::checkCorrectField($_REQUEST['y'], $_REQUEST['x'])) || $action == 'restart'){
    $json = serialize($MAIN);
    $filename = STORE_PATH.$_REQUEST['party_id'].'_'.$_REQUEST['party_pass'].'_main.txt';
    $fh = fopen($filename, 'w');
    fwrite($fh, $json);
    fclose($fh);
}

if(empty($phaseRequest))
	$phaseRequest = 'attempt';

$i = 0;
foreach(GameField::$arrObject as $k => $obj){
	$i++;
	$object = GameField::$arrObject[$k];
	
	GameField :: $arrObject[$k] = $object;
	GameField::$matrix[$object -> y][$object -> x] = $object;
	/*echo '<div>';
	echo '<b>'.$i.'</b> ';
	echo $object -> name;
	echo ' # ';
	echo $object -> color;
	echo '<br>';
	echo 'x: '.$object -> x;
	echo ', y: '.$object -> y;
	echo '</div>';*/
}

/*if(!empty($message)){
	echo '<dev class="message">';
	echo $message;
	echo '</dev>';
}
echo '</div>';*/
if(!isset($party_end) || !$party_end){
	$result = [/*'variants' => $variants*/];

	if(isset($MAIN['sacrifice']) && $MAIN['sacrifice'] == true){
		$result['sacrifice'] = true;
	}
	//$message .= '$MAIN[stepcolor]2: '.$MAIN['stepcolor'];
	foreach(Player :: $array as $k => $v){
		$result['s'][$k] = $v['soldier'];
	}
	if(!empty($message)){
		$result['message'] = $message;
		//echo 'is $message';
	}else{
		//echo 'empty($message';
	}
	if(isset($not_change_player) && $not_change_player){
		$result['change_player'] = false;	
	}else{
		$result['change_player'] = true;
		$sql_string3 = 'UPDATE `'.TABLE_PARTY.'` SET current=? WHERE id = ?';
		$sql_arr3 = [$MAIN['stepcolor'], $_REQUEST['party_id']];
		$stmt3 = $db->prepare($sql_string3);
		$stmt3->execute($sql_arr3);
	}
	if(!$is_error){
		$gamefield = GameField::draw(true);
		$gamefield = str_replace("\"", "'", $gamefield);
		$result['gamefield'] = $gamefield;
		$result['stepcolor'] = $MAIN['stepcolor'];
		$result['stepnum'] = $MAIN['stepnum'];
		//$result['c_id'] = $c_id_g;
		if(isset($resurs))
			$result['resurs'] = $resurs;
		//$result['gamefield'] = "ggg";
	}
	else
	{
		$result['error'] = true;
	}
	if(isset($next_turn_auto)){
		$result['next_turn_auto'] = 1;
	}
	
	if(isset($shah_message)){
		$result['shah_message'] = $shah_message;
	}
}

    			
if(isset($add_result)){
	$result = array_merge($result, $add_result);
}

if(isset($MAIN['shah'][$MAIN['stepcolor']])){
	if($current_user_color == $MAIN['stepcolor'])
		$new_message  = 'По штабу. <br>Ваш штаб в опасности.';
	else
		$new_message = 'По штабу. <br>Вы угрожаете вражескому штабу.';
}
if(isset($new_message)){
	if(isset($result['message'])){
		$result['message'] .= $new_message;	
	}else{
		$result['message'] = $new_message;
	}
}

$result['step_id'] = $MAIN['step_id'];
echo json_encode($result);
/*$ser = serialize($result);
echo $ser;*/
?>
