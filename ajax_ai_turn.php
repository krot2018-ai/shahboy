<?
/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

require_once ('classes/GameField.php');
require_once ('classes/Direction.php');
require_once ('classes/CObject.php');
//require_once ('classes/Party.php');

session_start();

if(!empty($_SESSION['GameField']['arrObject']))
	GameField::$arrObject = $_SESSION['GameField']['arrObject'];
if(!empty($_SESSION['GameField']['matrix']))
	GameField::$matrix = $_SESSION['GameField']['matrix'];
if(!empty($_SESSION['GameField']['whiteFields']))
	GameField::$whiteFields = $_SESSION['GameField']['whiteFields'];
if(!empty($_SESSION['GameField']['stage']))
	GameField::$stage = $_SESSION['GameField']['stage'];

$arrTurns = [];
//sleep(10);

$log = date('Y-m-d H:i:s') . ' ajax_ai_turn ';
foreach($_REQUEST as $k => $v)
{
	$log .= $k.': ';
	$log .= $v.'; ';
}
file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);

foreach(GameField::$arrObject as $k => $obj){
	
	$object = GameField::$arrObject[$k];
	
	$log = $k;
	file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);
	
	if($object -> color == $_REQUEST['player']){//stepcolor
		$result = $object -> getVariants();
		
		foreach($result as $k2 => $v2){
			//echo '<br>before';
			//print_r($v2);
			$v2['id'] = $object -> id;
			//echo '<br>after';
			//print_r($v2);
			// < проверка, что клетка не заходит за пределы доски и не находится в непроходимой области
			if(!(($v2['y'] < 2 && $v2['x'] < 2) || ($v2['y'] > 9 && $v2['x'] < 2) || ($v2['y'] < 2 && $v2['x'] > 9) || ($v2['y'] > 9 && $v2['x'] > 9)) && ($v2['x'] >= 0 && $v2['y'] >= 0 && $v2['x'] < 12 && $v2['y'] < 12)){
			// > проверка, что клетка не заходит за пределы доски и не находится в непроходимой области
				// < проверка, что это передвижение, выстрел или ближняя атака
				if($v2['type'] == 'shot+' || $v2['type'] == 'run' || $v2['type'] == 'crush'){
				// > проверка, что это передвижение, выстрел или ближняя атака
					$arrTurns[] = $v2;
				}
			}
		}
	}
}

$count = count($arrTurns);
//echo '<br>'.--$count
$i = rand(0, --$count);//random_int
$turn_rand = $arrTurns[$i];
$json = json_encode($turn_rand);
//$json = json_encode($arrTurns);
echo $json;