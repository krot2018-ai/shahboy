<?
class GameField
{
	public static $y = 11;
	public static $x = 11;
	
	public static $whiteFields = [];
	
	public static $arrObject = [];
	public static $cemetery = [];
	public static $matrix = [];
	
	public static $arrStage = ['stable', 'solution', 'collision'];
	
	public static $stage = ''; // stable solution collision
	
	public static $reverse = false;
	
	public static $log = [];
	
	public static $need_check = false;

	public static $backup = [];

	public static function turn(){
		
	}
	
	public static function coord_revers($y, $x){
		$hor = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
		$x_ = $hor[$x];
		$y_ = 12 - $y;
		$result = ['y' => $y_, 'x' => $x_];
		return $result;
	}
	
	public static function return_shah($backup){
        //$backup['actor1'] = 
		$backup['actor1'] -> y = $backup['y1'];
		$backup['actor1'] -> x = $backup['x1'];
		self::$arrObject[$backup['id1']] = $backup['actor1'];
		unset(self::$matrix[$_REQUEST['y']][$_REQUEST['x']]);
		
		self::$matrix[$backup['y1']][$backup['x1']] = $backup['actor1'];
		//echo 'y1: '.$backup['y1'].' x1: '.$backup['x1'];
		if(isset($backup['actor2'])){
			self::$arrObject[$backup['id2']] = $backup['actor2'];
			if(isset(self::$cemetery[$backup['id2']]))
				unset(self::$cemetery[$backup['id2']]);
			self::$matrix[$backup['actor2']->y][$backup['actor2']->x] = $backup['actor2'];
		}
	}
	
	public static function make_backup(){
		self::$backup['arrObject'] = self::$arrObject;
		self::$backup['matrix'] = self::$matrix;
		self::$backup['Player'] = Player::$array;
		self::$backup['cemetery'] = self::$cemetery;
	}

	public static function return_backup(){
		if(isset(self::$backup['arrObject']) && isset(self::$backup['matrix'])){
			self::$arrObject = self::$backup['arrObject'];
			self::$matrix = self::$backup['matrix'];
			Player::$array = self::$backup['Player'];
			self::$cemetery = self::$backup['cemetery'];
			self::$backup = [];
			return true;
		}else{
			return false;
		}
	}
	
	public static function shah_message(){
		
	}
	
	public static function isshah($y=false, $x=false, $head_color = false){
		$result = [];
		foreach(self::$arrObject as $k => $v){
			$variants = $v -> getVariants();
			$color1 = $v -> color;
			foreach($variants as $k2 => $v2){
			    //сократить число циклов, искать только по координатам штабов.
			    if($v2['y'] === $y && $v2['x'] === $x){
			        if(isset(self :: $matrix[$v2['y']][$v2['x']]) && is_object((self :: $matrix[$v2['y']][$v2['x']]))){
/*echo '<br><br>';
echo '<br>y: '.$y;
echo '<br>x: '.$x;
			             echo ' is_object ';*/
			             $object = self :: $matrix[$v2['y']][$v2['x']];
/*if($object -> name == 'private'){
	echo '  $object -> name == private  ';
}else{
	echo '  !$object -> name: '.$object -> name;
}

if($object -> color != $color1){
	echo '  $object -> color != $color1  ';
}else{
	echo '  !$object -> color != $color1  ';
}
if(isset(self :: $matrix[$v2['y']][$v2['x']]) && is_object((self :: $matrix[$v2['y']][$v2['x']]))){
		echo '<br>$v name: '.$v -> name;
		echo '<br>$v x: '.$v -> x;
		echo '<br>$v y: '.$v -> y;
}*/

			        }/*else{
						echo ' !is_object ' ;
					}*/
			    }
			    
			    
			    
				if(isset(self :: $matrix[$v2['y']][$v2['x']]) && is_object((self :: $matrix[$v2['y']][$v2['x']]))){
					$object = self :: $matrix[$v2['y']][$v2['x']];
					if(($y === false && $x === false && $object -> name == 'head' && $object -> color != $color1) || ($y !== false && $x !== false && $object -> name == 'private' && $v2['y'] === $y  && $v2['x'] === $x && $object -> color != $color1)){
						//result = true; 
						//$result[] = [$object];
						if($head_color === false || $object -> color == $head_color){
							$result[] = ['target' => $object, 'agr' => $v];
							//echo '<br>y: '.$v2['y'].' x: '.$v2['x'];
						}
						//echo '<br><br>';
					//	echo '$result OK ';
					}/*else{
						echo '<br><br>';
						echo '<br>$y: '.$y.'; $x: '.$x;
						if($y !== false){
							echo '<br> $y != false  ';
						}else{
							echo '<br> !$y != false  ';
						}
						if($x !== false){
							echo '<br> $x != false  ';
						}else{
							echo '<br> !$x != false  ';
						}
						if($object -> name == 'private'){
							echo '<br> $object -> name == private ';
						}else{
							echo '<br> !$object -> name == private: '.$object -> name;
						}
						if($v2['y'] == $y){
							echo '<br> $v2[y] == $y  ';
						}else{
							echo '<br> !$v2[y] == $y  ';
						}
						if($v2['x'] == $x){
							echo '<br> $v2[x] == $x  ';
						}else{
							echo '<br> !$v2[x] == $x  ';
						}
						if($object -> color != $color1){
							echo '<br> $object -> color != $color1  ';
						}else{
							echo '<br> !$object -> color != $color1 ';
						}
					}*/
				}
			}
		}
		if(count($result) == 0){	
			$result = false;
		}
		return $result;
	}
	
	
	public static function checkCorrectField($y, $x){ // проверка на то, что поле не выходит за пределы доски, учитывая непроходимые углы
		//echo '<br>checkCorrectField ';
		//echo '<br>$y '.$y;
		//echo '<br>$x '.$x;
		
		/*if(!(($y < 2 && $x < 2) || ($y > 9 && $x < 2) || ($y < 2 && $x > 9) || ($y > 9 && $x > 9))){
			//echo '<br>1 критерий +';
		}else{
			//echo '<br>1 критерий -';
		}
		
		if($x >= 0 && $y >= 0 && $x < 12 && $y < 12){
			//echo '<br>2 критерий +';
		}else{
			//echo '<br>2 критерий -';
		}*/
		
		
		if(!(($y < 2 && $x < 2) || ($y > 9 && $x < 2) || ($y < 2 && $x > 9) || ($y > 9 && $x > 9)) && ($x >= 0 && $y >= 0 && $x < (self :: $x + 1) && $y < (self :: $y + 1))){
			//echo '<br>общий критерий +';
			return true;
		}else{
			//echo '<br>общий критерий -';
			return false;
		}
	}
	
	public static function makeWhiteFields(){
		for ($y = 0; $y <= self :: $y; $y ++){
			//if(self :: $reverse == false){
				if(isset($color)){
					if($color == 'white'){
						$color = 'gray';
					}elseif($color == 'gray'){
						$color = 'white';
					}
				}else{
					//if(self :: $reverse == false){
						$color = 'white';
					/*}else{
						$color = 'gray';
					}*/
				}
				for ($x = 0; $x <= self :: $x; $x ++){
					if($color == 'white'){
						self :: $whiteFields[] = $y.'_'.$x;
						$color = 'gray';
					}elseif($color == 'gray'){
						$color = 'white';
					}
				}
			/*}else{
				if(isset($color)){
					if($color == 'white'){
						$color = 'gray';
					}elseif($color == 'gray'){
						$color = 'white';
					}
				}else{
					$color = 'gray';
				}
				for ($x = 0; $x <= self :: $x; $x ++){
					if($color == 'white'){
						self :: $whiteFields[] = $y.'_'.$x;
						$color = 'gray';
					}elseif($color == 'gray'){
						$color = 'white';
					}
				}
			}*/
		}
	}
	
	public static function draw($return=false, $reverse=false){
		/*echo '<pre>';
		print_r(self :: $matrix);
		echo '</pre>';*/
		$result = '';
		//$result .= 'self::$reverse: '.self::$reverse;
		$result .= '<div class="not_sacr" style="display: none; padding: 5px;">';
		$result .= '<div class="td diversion" data-id=""></div><span style="padding: 5px;">Не жертвовать пешку</span>';
		$result .= '</div>';
		$result .= '<div class="container">';
			
		/*if(self::$reverse){
			$x_fin = self :: $x - $x;
			$y_fin = self :: $y - $y;
		}else{
			$x_fin = self :: $x;
			$y_fin = self :: $y;
		}*/
		$lit = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
		$result .= '<div class="tr note"><div class="td"></div>';
		//$this_x = self :: $x;
		for ($x = 0; $x <= self :: $x; $x ++){
			$result .= '<div class="td">';
			if(self::$reverse){
				$result .= $lit[self :: $x - $x];	
			}else{

				$result .= $lit[$x];
			}
			$result .= '</div>';
		}
		$result .= '<div class="td"></div></div>';
		for ($y = 0; $y <= self :: $y; $y ++){
			if(isset($color)){
				if($color == 'white'){
					$color = 'gray';
				}elseif($color == 'gray'){
					$color = 'white';
				}
			}else{
				//if(self :: $reverse == false){
					$color = 'white';
				/*}else{
					$color = 'gray';
				}*/
			}
			
			$result .= '<div class="tr">';
			$result .= '<div class="td note">'.(self :: $y - $y+1).'</div>';
			
			for ($x = 0; $x <= self :: $x; $x ++){
				
				// < поправка на переворачивание доски
				if(self::$reverse){
					$x_fin = self :: $x - $x;
					$y_fin = self :: $y - $y;
				}else{
					$x_fin = $x;
					$y_fin = $y;
				}
				// > поправка на переворачивание доски
				
				$result .= '<div ';
				if(($y_fin < 2 && $x_fin < 2) || ($y_fin > 9 && $x_fin < 2) || ($y_fin < 2 && $x_fin > 9) || ($y_fin > 9 && $x_fin > 9)){
					$result .= 'class="td black">';//class="td black"
					//$result .= ' $y: '.$y.' $x: '.$x.' $y_fin: '.$y_fin.' $x_fin: '.$x_fin ;
				}else{
					$result .= 'class="td '.$color.'" id="td_'.$y_fin.'_'.$x_fin.'">';
					$result .= '<div class="coor_table">y:'.$y_fin.'<br>x:'.$x_fin.'</div>';
					//$result .= ' $y: '.$y.' $x: '.$x.' $y_fin: '.$y_fin.' $x_fin: '.$x_fin ;
				}
				if($color == 'white'){
					self :: $whiteFields[] = $y_fin.'_'.$x_fin;
					$color = 'gray';
				}elseif($color == 'gray'){
					$color = 'white';
				}
				/*echo '<span class="unit"';
				
				foreach($arAttr as $attr){
					if(isset($arObj[$objWeb[$k][$k2]][$attr])){
						echo ' '.$attr.'="'.$arObj[$objWeb[$k][$k2]][$attr].'"';
					}
				}
				echo '>'.$v2.'</span>';*/
				//if(is_object(self :: $matrix[$y_fin][$x_fin]) && self :: $matrix[$y_fin][$x_fin] -> dead == false){
				if(isset(self :: $matrix[$y_fin][$x_fin]) && is_object(self :: $matrix[$y_fin][$x_fin]) && self :: $matrix[$y_fin][$x_fin] -> dead == false){
					$result .= '<div data-type="'.get_class(self :: $matrix[$y_fin][$x_fin]).'" data-x="'.self :: $matrix[$y_fin][$x_fin] -> x.'" data-y="'.self :: $matrix[$y_fin][$x_fin] -> y.'" data-color="'.self :: $matrix[$y_fin][$x_fin] -> color.'"';
					if(self :: $matrix[$y_fin][$x_fin] -> class = 'unit'){
						$result .= ' class="relative unit';
						if(isset(self :: $matrix[$y_fin][$x_fin] -> controllable) && self :: $matrix[$y_fin][$x_fin] -> controllable == true)
							$result .= ' controllable';
						$result .= '"';
						$result .= ' data-id="' . self :: $matrix[$y_fin][$x_fin] -> id . '"';
					}
					$result .= '>';
					
					// < TEST
					/*echo '<div class="sign">';
					echo self :: $matrix[$y][$x] -> id;
					echo '</div>';*/
					// > TEST
					
					/*echo '<div class="sign unvisible">↷</div>';*/
					$result .= '<img class="iconMain';
					if(!empty(self :: $matrix[$y_fin][$x_fin] -> turnableSprite)){
						$result .= ' turnable '.self :: $matrix[$y_fin][$x_fin] -> direction;
					}
					$result .= '" src="'.self :: $matrix[$y_fin][$x_fin] -> img.'" alt="'.self :: $matrix[$y_fin][$x_fin] -> name.'" />';
					$result .= '<div class="id">'.self :: $matrix[$y_fin][$x_fin] -> id.'</div>';
					$result .= '</div>';
				}/*elseif(($y < 2 && $x < 2) || ($y > 9 && $x < 2) || ($y < 2 && $x > 9) || ($y > 9 && $x > 9)){
					echo '<div class="black"></div>';
				}*/else{
					$result .=  
					'<div class="relative"></div>';
				}
				$result .= '</div>';
			}
			$result .= '<div class="td note">'.(self :: $y - $y+1).'</div></div>';
		}
    	$result .= '<div class="tr note"><div class="td"></div>';
    	//$this_x = self :: $x;
    	for ($x = 0; $x <= self :: $x; $x ++){
    		$result .= '<div class="td">';
    		if(self::$reverse){
    			$result .= $lit[self :: $x - $x];	
    		}else{
    
    			$result .= $lit[$x];
    		}
    		$result .= '</div>';
    	}
    	$result .= '<div class="td"></div></div>';
		//echo '<pre>'; print_r(self :: $whiteFields); echo '</pre>';
		$result .= '</div>';
		/*foreach(self::$matrix as $k => $v){
			echo '<div class="tr">';
			foreach($v as $k2 => $v2){
				echo '<div class="td">';*/
				/*echo '<span class="unit"';
				
				foreach($arAttr as $attr){
					if(isset($arObj[$objWeb[$k][$k2]][$attr])){
						echo ' '.$attr.'="'.$arObj[$objWeb[$k][$k2]][$attr].'"';
					}
				}
				echo '>'.$v2.'</span>';*/
				/*if(is_object($v2)){
					echo '<img src="'.$v2 -> $img.'" alt="альтернативный текст" />';
				}
				echo '</div>';
			}
			echo '</div>';
		}*/
		if($return)
		{
			return $result;
		}
		else
		{
			echo $result;
		}
    }
}
