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
require_once ('classes/Player.php');
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
if(!empty($MAIN['Player']))
	Player :: $array = $MAIN['Player'];
$arrVariantsGlobal = [];
$arrVariantsGlobalText = '';
$isshah_result2 = GameField::isshah(false, false, $MAIN['stepcolor']);
if($isshah_result2 !== false){
	foreach(GameField::$arrObject as $k => $v){
		if($v -> color == $MAIN['stepcolor']){
			/*if($v->name == 'head'){
				echo '<br>HEAD';
			}*/
			$variants = $v -> getVariants();
			$coor_new2 = GameField::coord_revers($v->y, $v->x);
			foreach($variants as $k2 => $v2){
				if($v2['type'] == 'crush' || $v2['type'] == 'shot+' || $v2['type'] == 'run'){
					GameField::make_backup();
					//$vy = $v->y;
					//$vx = $v->x;
			/*if($v->name == 'head'){
				echo '<br>1H y: '.$v->y.' x: '.$v->x;
				echo '<pre>step: '; print_r($v2); echo '</pre>';
			}*/
					$v -> RealizeStep($v2); // сделать ход без проверки его возможности и с возможностью отката
			/*if($v->name == 'head'){
				echo '<br>2H y: '.$v->y.' x: '.$v->x;
			}*/
					$isshah_result = GameField::isshah(false, false, $MAIN['stepcolor']);
					if($isshah_result !== false){
						//echo '<h1>Result</h1>';
						//echo '<br>SHAH_RESULT';
						//echo '<pre>'; print_r($isshah_result); echo '</pre>';
					}else{
						//echo '<h1>Result</h1>';
						$coor_new = GameField::coord_revers($v2['y'], $v2['x']);
						$arrVariantsGlobal[] = ['name' => $v->name, 'id' => $v->id, 'y' => $coor_new['y'], 'x' => $coor_new['x'], 'type' => $v2['type']];//, '$vy' => $vy, '$vx' => $vx
						if($v2['type'] == 'crush')
							$type = 'наезд';
						elseif($v2['type'] == 'shot+')
							$type = 'выстрел';
						elseif($v2['type'] == 'run')
							$type = '-';
						$arrVariantsGlobalText .= '<br>'.$v->name_ru.' '.$coor_new2['x'].$coor_new2['y'].' '.$type.' '.$coor_new['x'].$coor_new['y'];
					}
					GameField::return_backup();
				}
			}
			/*if($v->name == 'head'){
				echo '<br>END HEAD';
			}*/
		}
	}
	if(count($arrVariantsGlobal) > 0){
		//echo '<pre>'; print_r($arrVariantsGlobal); echo '</pre>';
		echo '<h2 style="margin: 0px;">Список возможных ходов:</h2> '.$arrVariantsGlobalText;
	}else
		echo 'Штаб бит. Вы проиграли.';
}else{
	echo 'Штабу  ничего не угрожает.';
}

?>
