<?
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

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

if(isset($_REQUEST['id'])){
	if(is_object(GameField :: $arrObject[$_REQUEST['id']])){
		echo '<pre>';
		print_r(GameField :: $arrObject[$_REQUEST['id']]);
		echo '</pre>';
	}
}













