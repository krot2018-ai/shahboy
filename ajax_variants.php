<?
require "header_reg_auth.php";
/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/
function print_pre($array, $name=''){
	echo '<br>'.$name;
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

require_once ('classes/GameField.php');
require_once ('classes/Direction.php');
require_once ('classes/CObject.php');
//require_once ('classes/Party.php');

//session_start();

$json = file_get_contents(STORE_PATH.$_REQUEST['party_id'].'_'.$_REQUEST['party_pass'].'_main.txt', true);
//echo '<br>$json: '.STORE_PATH.$_REQUEST['party_id'].'_'.$_REQUEST['party_pass'].'_main.txt'.'<br>';
//$MAIN = json_decode($json, true);
$MAIN = unserialize($json);

//print_pre($MAIN, 'main');
	
if(!empty($MAIN['GameField']['arrObject']))
	GameField::$arrObject = $MAIN['GameField']['arrObject'];
if(!empty($MAIN['GameField']['matrix']))
	GameField::$matrix = $MAIN['GameField']['matrix'];
if(!empty($MAIN['GameField']['whiteFields']))
	GameField::$whiteFields = $MAIN['GameField']['whiteFields'];
if(!empty($MAIN['GameField']['stage']))
	GameField::$stage = $MAIN['GameField']['stage'];

$object = GameField::$matrix[$_REQUEST['y']][$_REQUEST['x']];

//if(is_object($object))
$result = [];
//echo '<br>$_REQUEST[y]: '.$_REQUEST['y'];
//print_pre(GameField::$matrix, 'matrix');
//print_pre(GameField::$matrix[$_REQUEST['y']], 'request_y');
//echo 'ok1';
//if(isset(GameField::$matrix[$_REQUEST['y']][$_REQUEST['x']]))
if(is_object($object))
//if(isset($object))
{
	$result['variants'] = $object -> getVariants();
	$result['text'] = $object -> text;
	//echo 'ok2';
}
else
{
	//print_pre(GameField :: $matrix);
	
	//print_pre($MAIN['GameField']['matrix']);
	
	//print_pre(GameField::$matrix, 'none_obj');
	$result['text'] = 'ошибка. не объект';
	//echo 'не объект';
}

/*
Fatal error: Uncaught Error: Call to a member function getVariants() on null in Z:\home\localhost\www\shahboy\ajax_variants.php:31 Stack trace: #0 {main} thrown in Z:\home\localhost\www\shahboy\ajax_variants.php on line 31

SyntaxError: JSON.parse: unexpected character at line 1 column 1 of the JSON data
url: 'ajax_variants.php', строка 417 в index.php 
*/

/*
Возвращает:
[
	{
		"y": 3,
		"x": 9,
		"type": "run"
	},
	{
		"y": 3,
		"x": 8,
		"type": "run"
	},
	{
		"y": 2,
		"x": 10,
		"type": "run"
	},
	{
		"y": 3,
		"x": 10,
		"type": "run"
	}
]
*/
/*$MAIN['Variants'] = $result['variants'];
$MAIN['Variants_object'] = $object -> id;

$json = json_encode($MAIN);
$filename = 'store/'.$_REQUEST['party_id'].'_'.$_REQUEST['party_pass'].'_main.txt';
$fh = fopen($filename, 'w');
fwrite($fh, $json);
fclose($fh);*/

//$json = json_encode($result['variants']);
if(isset($result)){
    $json = json_encode($result);
    echo $json;
}
?>
