<?
/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

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

session_start();

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
	
if(isset($_SESSION['idNext'])){
	$GLOBALS['idNext'] = $_SESSION['idNext'];
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

//require_once ('classes/CObject.php');

/*$log = date('Y-m-d H:i:s') . ' ';
foreach($_REQUEST as $k => $v)
{
	$log .= $k.': ';
	$log .= $v.'; ';
}
file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);*/

if(!empty($_REQUEST['restart'])){
	$firstStep = true;
	unset($_SESSION['GameField']);
	
	$GLOBALS['idNext'] = 1;

 //< стандартная расстановка	
require_once ('start_position/standart.php');
 //> стандартная расстановка
//< расстановка для теста диверсий
//require_once ('start_position/diversion.php');
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
	
	$_SESSION['idNext'] = $GLOBALS['idNext'];
	$_SESSION['Player'] = Player :: $array;
	
	$_SESSION['stepcolor'] = 'red';
	$_SESSION['stepnum'] = 1;
	
	
}else{
	if(!empty($_SESSION['GameField']['arrObject']))
		GameField::$arrObject = $_SESSION['GameField']['arrObject'];
	if(!empty($_SESSION['GameField']['matrix']))
		GameField::$matrix = $_SESSION['GameField']['matrix'];
	if(!empty($_SESSION['GameField']['stage']))
		GameField::$stage = $_SESSION['GameField']['stage'];
	if(!empty($_SESSION['GameField']['reverse']))
		GameField::$reverse = $_SESSION['GameField']['reverse'];
	//if(!empty($_SESSION['Party']['direction']))
		//Party::$direction = $_SESSION['Party']['direction'];
	if(!empty($_SESSION['Player']))
		Player :: $array = $_SESSION['Player'];
	
	$message = '';
	//sleep(5);
	//echo '<script>alert("123");</script>>';
	// < ход
	if($_SESSION['stepcolor'] == 'red'){
		$oppositcolor = 'black';
	}else{
		$oppositcolor = 'red';
	}
	if(isset($_REQUEST['saboteur']) && isset($_REQUEST['target'])){ // жертва пешки для уничтожения вражеской фигуры
		if(GameField::$arrObject[$_REQUEST['saboteur']] -> color == $_SESSION['stepcolor'] && GameField::$arrObject[$_REQUEST['target']] -> color == $oppositcolor){
			$unset_class = get_class(GameField::$arrObject[$_REQUEST['target']]); // тип уничтоженной фигуры для дальнейшей проверки
			$unset_color = GameField::$arrObject[$_REQUEST['target']] -> color;
			//$message = '$unset_class '.$unset_class;
			if($unset_class != 'Head' && $_SESSION['sacrifice'] == true && $_SESSION['sacrifice_id'] == $_REQUEST['saboteur']){
				GameField::$arrObject[$_REQUEST['saboteur']] -> deleteFull();
				GameField::$arrObject[$_REQUEST['target']] -> deleteFull();
				
				Player::$array[$_SESSION['stepcolor']]['soldier'] --;
				if(Player::$array[$_SESSION['stepcolor']]['soldier'] < 1){
					$message .= $_SESSION['stepcolor'].' проиграл. Закончилась пехота.';
					Player::$array[$_SESSION['stepcolor']]['defeated'] = true;
				}
				
				$_SESSION['stepcolor'] = $oppositcolor;
				$_SESSION['stepnum']++;
				$not_change_player = false;

				if($unset_class == 'soldier')
				{
					Player::$array[$unset_color]['soldier'] --;
					if(Player::$array[$unset_color]['soldier'] < 1){
						$message .= $unset_color.' проиграл. Закончилась пехота.';
						Player::$array[$unset_color]['defeated'] = true;
					}
				}
				$resurs = [Player::$array];
				$_SESSION['sacrifice'] = false;
				unset($_SESSION['sacrifice_id']);
			}
			else
			{
				// < отладка
				if(!($unset_class != 'Head'))
				{
					$message .= 'Условие !($unset_class != \'Head\') не выполнено.';
				}
				
				if(!($_SESSION['sacrifice'] == true))
				{
					$message .= 'Условие $_SESSION[\'sacrifice\'] == true не выполнено.';
				}
				
				if(!($_SESSION['sacrifice_id'] == $_REQUEST['saboteur']))
				{
					$message .= 'Условие $_SESSION[\'sacrifice_id\'] == $_REQUEST[\'saboteur\'] не выполнено.';
				}
				// > отладка
			}
			unset($unset_class);
			unset($unset_color);
		}else{
			$approved = false;
			$is_error = true;
			$message .= 'Цвет диверсанта и цели не подходит';
		}
	}elseif(GameField::checkCorrectField($_REQUEST['y'], $_REQUEST['x'])){ // Передвижение, раздавливание или выстрел // если клетка не выходит за пределы поля
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
		
		if(GameField::$arrObject[$UNIT_ID] -> color == $_SESSION['stepcolor']){
			if(isset($_REQUEST['id'])){ // передвижение, в том числе раздавливание вражеской фигуры
				if(isset($_SESSION['Variants']) && isset($_SESSION['Variants_object']) && ($_SESSION['Variants_object'] == $_REQUEST['id'])){
					$variants = $_SESSION['Variants'];
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
						if(is_object((GameField :: $matrix[$_REQUEST['y']][$_REQUEST['x']]))){ // если место занято
							$c_ob = GameField :: $matrix[$_REQUEST['y']][$_REQUEST['x']];
							//Warning: get_class() expects parameter 1 to be object, boolean given in Z:\home\localhost\www\shahboy\ajax.php on line 186
							$unset_class = get_class($c_ob);
							$unset_color = $c_ob -> color;
							$c_id = $c_ob -> id;
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
							$object -> y = $_REQUEST['y'];		// перемещение активного юнита на целевые координаты
							$object -> x = $_REQUEST['x'];		// перемещение активного юнита на целевые координаты
							//$c_id_g = $c_id;
							if(isset($c_id))
								unset(GameField::$arrObject[$c_id]); // удаление юнита, раздавленного целевым юнитом
							GameField :: $matrix[$_REQUEST['y']][$_REQUEST['x']] = $object;
							//$message = 'Ход сделан';
							unset($_SESSION['Variants_object']);
							unset($_SESSION['Variants']);
							// возможно нужна новая функция
						
							if( get_class(GameField::$arrObject[$_REQUEST['id']]) == 'soldier' /*&&  GameField::checkCorrectField($object -> y + Direction :: $properties[Player :: $array[$_SESSION['stepcolor']]['direction']]['y'], $object -> x + Direction :: $properties[Player :: $array[$_SESSION['stepcolor']]['direction']]['x'])*/){//GameField::checkCorrectField($object -> y + Direction :: $properties[Player :: $array[$_SESSION['stepcolor']]['direction']]['y'], $object -> x + Direction :: $properties[Player :: $array[$_SESSION['stepcolor']]['direction']]['x'])
								$new_y = $object -> y + Direction :: $properties[Player :: $array[$_SESSION['stepcolor']]['direction']]['y'];
								$new_x = $object -> x + Direction :: $properties[Player :: $array[$_SESSION['stepcolor']]['direction']]['x'];
								if($new_y < 0 || $new_y > GameField::$y || $new_x < 0 || $new_x > GameField::$x)
								{
									//$message = 'Жертва пешки';
									$not_change_player = true;
									$_SESSION['sacrifice'] = true;
									$_SESSION['sacrifice_id'] = $_REQUEST['id'];
								}
							}
							// смена активного игрока
							if(!$not_change_player){
								/*if($_SESSION['stepcolor'] == 'black')
									$_SESSION['stepcolor'] = 'red';
								else
									$_SESSION['stepcolor'] = 'black';*/
								$_SESSION['stepcolor'] = $oppositcolor;
								$_SESSION['stepnum']++;
							}
						}	
					}else{
						$message .= 'Ходить на выбранное поле нельзя';
						$is_error = true;
					}
				}
			}elseif(isset($_REQUEST['shot_id'])){ // выстрел
				if(isset($_SESSION['Variants']) && isset($_SESSION['Variants_object']) && ($_SESSION['Variants_object'] == $_REQUEST['shot_id'])){
					$variants = $_SESSION['Variants'];
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
							unset(GameField::$arrObject[$c_id]);
							GameField :: $matrix[$_REQUEST['y']][$_REQUEST['x']] = false;
							unset($_SESSION['Variants_object']);
							unset($_SESSION['Variants']);
							
							// смена активного игрока
							/*if($_SESSION['stepcolor'] == 'black')
								$_SESSION['stepcolor'] = 'red';
							else
								$_SESSION['stepcolor'] = 'black';*/
							$_SESSION['stepcolor'] = $oppositcolor;
							$_SESSION['stepnum']++;
						}
						
					}else{
						$message .= 'Стрелять в выбранное поле нельзя';
						$is_error = true;
					}
				}
			}
		}else{
			$message .= 'Ход другого игрока';
			$is_error = true;
		}
		
		if(isset($_SESSION['Variants']))
			unset($_SESSION['Variants']);
		if(isset($_SESSION['Variants_object']))
			unset($_SESSION['Variants_object']);
		
		if(isset($unset_class) && isset($unset_color) && !$is_error)
		{
			if($unset_class == 'soldier')
			{
				Player::$array[$unset_color]['soldier'] --;
				if(Player::$array[$unset_color]['soldier'] < 1){
					$message .= $unset_color.' проиграл. Закончилась пехота.';
					Player::$array[$unset_color]['defeated'] = true;
				}
			}
			elseif($unset_class == 'Head')
			{
				Player::$array[$unset_color]['head'] --;
				if(Player::$array[$unset_color]['head'] < 1){
					$message .= $unset_color.' проиграл. Разрушен штаб.';
					Player::$array[$unset_color]['defeated'] = true;
				}
			}
			$resurs = [Player::$array];
		}			
	}/*else{
		echo '<div>Ошибка: целевая клетка выходит за пределы поля</div>';
	}*/
	// > ход
	
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

$_SESSION['GameField']['arrObject'] = GameField :: $arrObject;
$_SESSION['GameField']['matrix'] = GameField :: $matrix;
$_SESSION['GameField']['stage'] = GameField :: $stage;
$_SESSION['GameField']['whiteFields'] = GameField :: $whiteFields;
$_SESSION['GameField']['reverse'] = GameField :: $reverse;
//$_SESSION['Party']['direction'] = Party :: $direction;


/*echo '<pre>Player :: $array ';
print_r(Player :: $array);
echo '</pre>';*/
$_SESSION['Player'] = Player :: $array;


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
$result = [/*'variants' => $variants*/];
if(!empty($message)){
	$result['message'] = $message;
}
if($not_change_player){
	$result['change_player'] = false;	
}else{
	$result['change_player'] = true;		
}
if(!$is_error){
	$gamefield = GameField::draw(true);
	$gamefield = str_replace("\"", "'", $gamefield);
	$result['gamefield'] = $gamefield;
	//$result['c_id'] = $c_id_g;
	if(isset($resurs))
		$result['resurs'] = $resurs;
	//$result['gamefield'] = "ggg";
}
else
{
	$result['error'] = true;
}
echo json_encode($result);