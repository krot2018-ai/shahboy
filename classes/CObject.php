<?

class Unit
{
	public $y;
	public $x;
	//public $img;
	public $color;
	public $dirMain;
	
	public $id;
	public $dead = false;
	
	function __construct($color, $y, $x)
	{
		if(GameField::checkCorrectField($y, $x)){
			$this -> id = $GLOBALS['idNext'];
			
			$this -> color = $color;
			$this -> y = $y;
			$this -> x = $x;
			
			
			GameField::$arrObject[$this -> id] = &$this;
			GameField::$matrix[$y][$x] = &$this;
			if(isset($GLOBALS['idNext']))
				$GLOBALS['idNext'] ++;
			
			//$this -> dirMain = Party :: $direction[$this -> color];
			$this -> dirMain = Player :: $array[$this -> color]['direction'];
			
			//$this -> img = 'img/'.$this -> color.'/'.$this -> $img;
			//$this -> img = 'img/'.$this -> color.$this :: $img;
		}
	}
	
	public function MoveWithoutTest(){
		GameField::$matrix[$this -> y][$this -> x] = false;
	}
	
	public function RealizeStep($arr){// Совершить ход без проверки на его допустимость
		if($arr['type'] == 'crush' || $arr['type'] == 'shot+'){
			if(is_object(GameField::$matrix[$arr['y']][$arr['x']])){
				GameField::$matrix[$arr['y']][$arr['x']] -> deleteFull();
			}
		}
		if($arr['type'] == 'crush' || $arr['type'] == 'run'){
			unset(GameField::$matrix[$this -> y][$this -> x]);
			$this -> y = $arr['y'];
			$this -> x = $arr['x'];
			GameField::$matrix[$arr['y']][$arr['x']] = $this;
		}
	}	

	public function deleteFull(){
		unset(GameField::$matrix[$this -> y][$this -> x]);
		GameField::$cemetery[$this -> id] = &$this;
		unset(GameField::$arrObject[$this -> id]);
		if(isset(Player :: $array[$this -> color]['soldier_last'][$this -> id])){
			unset(Player :: $array[$this -> color]['soldier_last'][$this -> id]);
		}
		//unset($this);
	}
}

class Tank extends Unit
{
	
	/*
	https://gest.livejournal.com/889387.html
	Танк неуязвим для пехоты, кавалерии и пулемётов. 
	Танк. С танком всё просто. Танк ходит на одну или на две клетки по диагонали, вертикали или горизонтали. Обратите внимание, что танк способен уничтожить выдвинутую в поле артиллерию, зайдя ей в тыл - двигается он быстрее, ход на две клетки позволяет ему проскочить сквозь зону поражения, и в случае атаки с тыла артиллерия беззащитна. Ну а пулемёты и пехоту танк просто давит.

	*/
	public $text = 'Танк<br>
Перемещение: В любом направление до 2 клеток за ход. Ходить через фигуры не может.<br>
Захват: захватывая противника занимает его место.<br>
Поражает: боец,пулемет,конница,танк,пушка<br>
Уязвимость: танк,пушка,самолет,штаб<br>
Ценность:7';
	public $name_ru = 'Танк';
	public $name = 'tank';
	/*//public $img = 'tank.png';
	public $controllable = true;
	function __construct($color, $y, $x) {
		parent::__construct($color, $y, $x);	
		$this -> img = 'img/'.$this -> color.'/tank.png';
	}*/
	
	public $img = '/tank.png';
	public $controllable = true;
	public $impossible_target = ['Plane'];
	
	function __construct($color, $y, $x) {
		parent::__construct($color, $y, $x);	
		$this -> img = 'img/'.$this -> color.$this -> img;
	}
	
	function getVariants(){
		$result = [];
		
		$i = 1;
		
		$dir = $this -> dirMain;
		while ($i < 9)
		{			
			$this -> AddVariantField($result, $dir);
			$dir = Direction :: turnNext($dir);
			$i ++;
		}
		
		return $result;
	}
	
	function AddVariantField(&$result, $dir){
		$dirProps = Direction :: $properties[$dir];
		if(isset(GameField :: $matrix[$this -> y + $dirProps['y']][$this -> x + $dirProps['x']]))
    		$field = GameField :: $matrix[$this -> y + $dirProps['y']][$this -> x + $dirProps['x']];
		else
			$field = false;
		/*echo '<pre>GameField::$matrix: ';
		print_r(GameField::$matrix);
		echo '</pre>';*/
		
		if(isset($field) && is_object($field)){
			if($field -> color != $this -> color && (!in_array(get_class($field), $this -> impossible_target))/*get_class($field) != 'Plane'*/){
				if(GameField::checkCorrectField($this -> y + $dirProps['y'], $this -> x + $dirProps['x']))
					$result[] = ['y' => $this -> y + $dirProps['y'], 'x' => $this -> x + $dirProps['x'], 'type' => 'crush'];
			}else{
				
			}
		}elseif(GameField::checkCorrectField($this -> y + $dirProps['y'], $this -> x + $dirProps['x'])){// если клетка не выходит за пределы поля
			$result[] = ['y' => $this -> y + $dirProps['y'], 'x' => $this -> x + $dirProps['x'], 'type' => 'run'];
			
			if(!isset(GameField :: $matrix[$this -> y + $dirProps['y'] + $dirProps['y']][$this -> x + $dirProps['x'] + $dirProps['x']]) || !is_object(GameField :: $matrix[$this -> y + $dirProps['y'] + $dirProps['y']][$this -> x + $dirProps['x'] + $dirProps['x']])){
				$result[] = ['y' => $this -> y + $dirProps['y'] + $dirProps['y'], 'x' => $this -> x + $dirProps['x'] + $dirProps['x'], 'type' => 'run'];
			}elseif(GameField :: $matrix[$this -> y + $dirProps['y'] + $dirProps['y']][$this -> x + $dirProps['x'] + $dirProps['x']] -> color != $this -> color){
				$class_enemy = get_class(GameField :: $matrix[$this -> y + $dirProps['y'] + $dirProps['y']][$this -> x + $dirProps['x'] + $dirProps['x']]);
				if(!in_array($class_enemy, $this -> impossible_target)){
					$result[] = ['y' => $this -> y + $dirProps['y'] + $dirProps['y'], 'x' => $this -> x + $dirProps['x'] + $dirProps['x'], 'type' => 'crush'];
				}
			}
		}
	}
}

class Head extends Unit
{
	/*
	https://gest.livejournal.com/889387.html
	*/
	public $name_ru = 'Штаб';
	public $text = 'Штаб<br>
Перемещение: на одну клетку в любую сторону. Не может ходить через фигуры.<br>
Захват: Захватывая противника занимает его место.<br>
Поражает: все фигуры<br>
Уязвимость: всеми фигурами<br>
Ценность:Бесценен';
	public $img;
	public $name = 'head';
	public $controllable = true;
	public $impossible_target = [];
	
	function __construct($color, $y, $x) {
		parent::__construct($color, $y, $x);
		$this -> img = 'img/'.$this -> color.'/head.png';
		Player::$array[$this -> color]['head'] ++;
	}
	
	function getVariants(){
		$result = [];
		
		$i = 1;
		
		$dir = $this -> dirMain;
		while ($i < 9)
		{			
			$this -> AddVariantField($result, $dir);
			$dir = Direction :: turnNext($dir);
			$i ++;
		}
		
		return $result;
	}
	
	function AddVariantField(&$result, $dir){
		$dirProps = Direction :: $properties[$dir];
		if(isset(GameField :: $matrix[$this -> y + $dirProps['y']][$this -> x + $dirProps['x']]))
		    $field = GameField :: $matrix[$this -> y + $dirProps['y']][$this -> x + $dirProps['x']];
		else
		    $field = false;
		if(isset($field) && is_object($field)){
			// < возможность штаба атаковать вражеские фигуры
			if($field -> color != $this -> color && (!in_array(get_class($field), $this -> impossible_target))){
				$result[] = ['y' => $this -> y + $dirProps['y'], 'x' => $this -> x + $dirProps['x'], 'type' => 'crush'];
			}
			// > возможность штаба атаковать вражеские фигуры
		}else{
			if(GameField::checkCorrectField($this -> y + $dirProps['y'], $this -> x + $dirProps['x']))
				$result[] = ['y' => $this -> y + $dirProps['y'], 'x' => $this -> x + $dirProps['x'], 'type' => 'run'];
		}
	}
}

class Plane extends Unit
{
	public $img;
	public $name = 'plane';
	public $name_ru = 'Самолёт';
	public $text = 'Самолёт<br>
Перемещение:в любом направление по прямым линиям и во всю глубину доски.Совершает ходы через одну свою фигуру, но не вражескую.<br>
Захват: Захватывая противника занимает его место.<br>
Поражает: все фигуры<br>
Уязвимость: боец,пулемет,пушка,самолет,штаб<br>
Ценность:10<br>';
	public $controllable = true;
	public $impossible_target = [];
	
	/*
	самолёт уязвим для артиллерии и пулемётов, но не для танка и кавалерии
	
	https://gest.livejournal.com/889387.html
	Наконец, самолёт. Это, скорее, штурмовик, а не дальний бомбардировщик. Он стоит рядом со штабом, занимая место ферзя, и ходит как ферзь, в любую сторону и на любое расстояние. При этом, во время хода, самолёт имеет право перепрыгнуть через дружественную фигуру, но только через одну за раз, и, повторяю, только через фигуру своего цвета. Это даёт самолёту возможность совершать боевые вылеты из глубины своих рядов, подобно фигуре пао-вао. Кстати, штурмовиком я назвал его потому, что его может сбить пехота; также самолёт уязвим для артиллерии и пулемётов, но не для танка и кавалерии. Они против него беззащитны.
	*/
	
	function __construct($color, $y, $x) {
		parent::__construct($color, $y, $x);
		$this -> img = 'img/'.$this -> color.'/plane.png';
	}
	
	function getVariants(){
		
		$result = [];
		
		$i = 1;
		
		$dir = $this -> dirMain;
		while ($i < 9)
		{			
			$this -> GetVariantDir($result, $dir);
			$dir = Direction :: turnNext($dir);
			$i ++;
		}
		
		return $result;
	}
	
	function GetVariantDir(&$result, $dir){
		$dirProps = Direction :: $properties[$dir];

		/*echo '<pre>GameField::$matrix: ';
		print_r(GameField::$matrix);
		echo '</pre>';*/
		
		$this -> GetVariantDirField($result, $this -> y + $dirProps['y'], $this -> x + $dirProps['x'], $dirProps, 0);
		
		/*if(is_object($field) && $field -> color != $this -> color){
				$result[] = ['y' => $this -> y + $dirProps['y'], 'x' => $this -> x + $dirProps['x'], 'type' => 'crush'];
		}else{
			if(is_object($field) && $field -> color == $this -> color){
				$jumping = 1;
				$result[] = ['y' => $this -> y + $dirProps['y'], 'x' => $this -> x + $dirProps['x'], 'type' => 'jump'];
			}else{
				$result[] = ['y' => $this -> y + $dirProps['y'], 'x' => $this -> x + $dirProps['x'], 'type' => 'run'];

				if(!is_object(GameField :: $matrix[$this -> y + $dirProps['y'] + $dirProps['y']][$this -> x + $dirProps['x'] + $dirProps['x']])){
					$result[] = ['y' => $this -> y + $dirProps['y'] + $dirProps['y'], 'x' => $this -> x + $dirProps['x'] + $dirProps['x'], 'type' => 'run'];
				}elseif(GameField :: $matrix[$this -> y + $dirProps['y'] + $dirProps['y']][$this -> x + $dirProps['x'] + $dirProps['x']] -> color != $this -> color){
					$result[] = ['y' => $this -> y + $dirProps['y'] + $dirProps['y'], 'x' => $this -> x + $dirProps['x'] + $dirProps['x'], 'type' => 'crush'];
				}
			}
		}*/
	}
	
	function GetVariantDirField(&$result, $y, $x, $dirProps, $jumping){
		//echo '<br>GetVariantDirField';
		//global $jumping;
		//echo '<br> $jumping: '.$jumping;
		if(GameField::checkCorrectField($y, $x)){// если клетка не выходит за пределы поля
		//if($y >= 0 && $y <= GameField::$y && $x >= 0 && $x <= GameField::$x){
		    if(isset(GameField :: $matrix[$y][$x]))
			    $field = GameField :: $matrix[$y][$x];
			else
				$field = false;
			$abort = false;
			if(isset($field) && is_object($field) && $field -> color == $this -> color){	// если фигура своего цвета
				$jumping ++;
				if($jumping < 2){
					$result[] = ['y' => $y, 'x' => $x, 'type' => 'jump'];
					//$this -> GetVariantDirField($result, $y, $x, $dirProps);
				}else{
					$abort = true;
				}
			}else{
				if(!isset($field) || !is_object($field)){
					$result[] = ['y' => $y, 'x' => $x, 'type' => 'run'];	
				}elseif($field -> color != $this -> color){// + $dirProps['y']  + $dirProps['x']
					//echo '<br>$y: '.$y.' $x: '.$x;
					//echo '<br>$field -> color: '.$field -> color.' $this -> color: '.$this -> color;
					$result[] = ['y' => $y, 'x' => $x, 'type' => 'crush'];
					$abort = true;
				}else{
					$abort = true;
				}
			}	
		}else{
			$abort = true;
		}
		if(!$abort){
			$this -> GetVariantDirField($result, $y + $dirProps['y'], $x + $dirProps['x'], $dirProps, $jumping);
		}
	}
}

class soldier extends Unit
{
	public $img;
	public $name_ru = 'Боец';
	public $name = 'private';
	public $text = 'Боец<br>
Перемещение:<br>
С черной клетки ходит на одну  соседнюю клетку в любом направлении.<br>
С белой клетки ходит на две клетки  в любом направлении<br>
Через фигуры совершать ходы не может.<br>
Захват: Захватывает только фигуры расположенные на соседних клетках, и занимает их место. Не может делать ход с захватом фигуры  находящихся за его спиной.<br>
Поражает: боец,пулемет,конница,пушка,самолет.<br>
Уязвимость: всеми фигурами<br>
Прорыв пехоты в тыл:боец, дошедший до последней линии вражеского фронта (со стороны противника), может остаться в игре.<br>
Или выйти из игры, забрав с собой любую фигуру противника с поля боя (кроме фигуры штаба), при условии, что бойца не могут срубит фигуры противника.<br>
Ценность:1';
	public $controllable = true;
	public $impossible_target = ['Tank'];
	
	function __construct($color, $y, $x) {
		parent::__construct($color, $y, $x);
		$this -> img = 'img/'.$this -> color.'/private.png';
		
		Player::$array[$this -> color]['soldier'] = Player::$array[$this -> color]['soldier'] + 1;
		//echo 'Player soldier '.Player::$array[$this -> color]['soldier'];
	}
	
	/*
	https://gest.livejournal.com/889387.html
	Пехота - царица полей. Пехотинец двигается, как король в обычных шахматах, на одну клетку в любую сторону. Это крутая, тренированная пехота. Но пехотинец не может брать вражескую фигуру во время отступления, когда он двигатется назад по прямой или по диагонали, поэтому он угрожает только пяти ближайшим клеткам из восьми. Что всё равно гораздо круче, чем ходы традиционной пешки, которая не может отступать и которая угрожает только двум клеткам перед собой. Также у пехоты в "Шах-Бое" есть ещё один, особый ход - если пехотинец стоит на белой клетке, он может переместится на две клетки в любую сторону (соответственно, приземлившись на другую белую клетку), если между ним и этой клеткой нет других фигур. Не знаю, что хотел выразить этим автор. Неоднородность поля боя? Повышенную мобильность пехоты, в связи с возможностью перебрасывать её автотранспортом? В любом случае, пехотинец не может атаковать этим ходом; чтобы вступить в бой, надо "спешится".
	
	! когда доходит до конца поля, убирает с доски любую вражескую фигуру кроме штаба
	
	*/
	
	function getVariants(){
		$result = [];
		
		$opp = Direction :: $properties[$this -> dirMain]['opposite'];
		$arrOpp = [$opp, Direction :: turnNext($opp), Direction :: turnPrev($opp)];

		GameField :: makeWhiteFields();
		if(array_search($this -> y.'_'.$this -> x, GameField :: $whiteFields) !== false){
			$ableToRun = true;
		}else{
			$ableToRun = false;
		}
		
		$i = 1;
		
		$dir = $this -> dirMain;
		while ($i < 9)
		{			
			$this -> AddVariantField($result, $dir, $arrOpp, $ableToRun);
			$dir = Direction :: turnNext($dir);
			$i ++;
		}
		
		return $result;
	}

	function AddVariantField(&$result, $dir, $arrOpp, $ableToRun){
		$dirProps = Direction :: $properties[$dir];
		if(isset(GameField :: $matrix[$this -> y + $dirProps['y']][$this -> x + $dirProps['x']]))
		    $field = GameField :: $matrix[$this -> y + $dirProps['y']][$this -> x + $dirProps['x']];
        else
            $field = false;
		/*echo '<pre>GameField::$matrix: ';
		print_r(GameField::$matrix);
		echo '</pre>';*/
		
		if(isset($field) && is_object($field)){
			if($field -> color != $this -> color && array_search($dir, $arrOpp) === false && (!in_array(get_class($field), $this -> impossible_target))/*get_class($field) != 'Tank'*/){
				$result[] = ['y' => $this -> y + $dirProps['y'], 'x' => $this -> x + $dirProps['x'], 'type' => 'crush'];
			}else{
				
			}
		}else{
			if(GameField::checkCorrectField($this -> y + $dirProps['y'], $this -> x + $dirProps['x'])){// если клетка не выходит за пределы поля
				$result[] = ['y' => $this -> y + $dirProps['y'], 'x' => $this -> x + $dirProps['x'], 'type' => 'run'];
				if($ableToRun){
					if(!isset(GameField :: $matrix[$this -> y + $dirProps['y'] + $dirProps['y']][$this -> x + $dirProps['x'] + $dirProps['x']]) || !is_object(GameField :: $matrix[$this -> y + $dirProps['y'] + $dirProps['y']][$this -> x + $dirProps['x'] + $dirProps['x']])){
						$result[] = ['y' => $this -> y + $dirProps['y'] + $dirProps['y'], 'x' => $this -> x + $dirProps['x'] + $dirProps['x'], 'type' => 'run'];
					}
				}
			}
		}
	}

}

class horse extends Unit
{
	/*
	 Не может нанести вред самолету и танку. 
	*/
	
	public $img;
	public $name = 'horse';
	public $name_ru = 'Конница';
	public $text = 'Конница<br>
Перемещение:  принцип ходьбы буквой  «Г».Ходит прямым или тупым углом на третью клетку (клетка, на которой стоит фигура не считается).Может перепрыгивать через фигуры своего цвета, но не противника.<br>
Захват:  захватывая противника занимает его место.(встав на третью клетку)<br>
Поражает: боец,пушка,пулемет,конница<br>
Уязвимость: всеми фигурами<br>
Ценность:5';
	public $controllable = true;
	public $impossible_target = ['Tank', 'Plane'];
	
	function __construct($color, $y, $x) {
		parent::__construct($color, $y, $x);
		$this -> img = 'img/'.$this -> color.'/horse.png';
	}
	
	function getVariants(){
		$result = [];
		$result_y_x = [];
		foreach(Direction :: $properties as $k => $v){
			$this -> GetVariantDir($result, $result_y_x, $k);
		}
		return $result;
	}

	function GetVariantDir(&$result, &$result_y_x, $dir){
		//min_diag_right
		//right obtuse, direct diag, min max
		//angle direction order
		//$order: long-short, short-long; $dir_type; $angle: straight obtuse; $side: left, right;
		
		$this -> GetHorseZigzag($result, $result_y_x, $dir, 'long-short', 'straight', 'left');	//error
		$this -> GetHorseZigzag($result, $result_y_x, $dir, 'long-short', 'straight', 'right');	//error
		$this -> GetHorseZigzag($result, $result_y_x, $dir, 'long-short', 'obtuse', 'left');		//error
		$this -> GetHorseZigzag($result, $result_y_x, $dir, 'long-short', 'obtuse', 'right');		//error
		$this -> GetHorseZigzag($result, $result_y_x, $dir, 'short-long', 'straight', 'left');
		$this -> GetHorseZigzag($result, $result_y_x, $dir, 'short-long', 'straight', 'right');
		$this -> GetHorseZigzag($result, $result_y_x, $dir, 'short-long', 'obtuse', 'left');
		$this -> GetHorseZigzag($result, $result_y_x, $dir, 'short-long', 'obtuse', 'right');
	}
	
	function GetHorseZigzag(&$result, &$result_y_x, $dir, $order, $angle, $side){
		//if($dir == 'dr'){
		/*echo '<pre>';
		print_r($result);
		echo '</pre>'; */
		//echo '<br> GetHorseZigzag('.$dir.', '.$order.', '.$angle.', '.$side.')';
		$abort = false;
		
		// < первый шаг
		
		// < блок 1, в отдельную функцию
		// 	< блок 1.1, отличие для функции
		
		//echo '<br>$this -> y: '.$this -> y;
		//echo '<br>$this -> x: '.$this -> x;
		
		$y_new = $this -> y + Direction :: $properties[$dir]['y'];
		$x_new = $this -> x + Direction :: $properties[$dir]['x'];
		
		/*echo '<br>step 1';
		echo '<br>$y_new '.$y_new;
		echo '<br>$x_new '.$x_new;*/
		
		// 	> блок 1.1, отличие для функции
		if(isset(GameField :: $matrix[$y_new][$x_new]))
		    $field = GameField :: $matrix[$y_new][$x_new];
		else
		    $field = false;
		if(GameField::checkCorrectField($y_new, $x_new)){ // если клетка не выходит за пределы поля
			
			if(!isset($field) || !is_object($field) || (is_object($field) && $field -> color == $this -> color)){//echo '<br>jump'; // если клетка пустая, или с фигурой своего цвета
				if(!isset($result_y_x[$y_new][$x_new])){
					//$result[] = ['y' => $y_new, 'x' => $x_new, 'type' => 'jump'];
					//$result_y_x[$y_new][$x_new] = 'jump';
				}
			}else{//echo '<br>$abort 1';
				$abort = true;
			}
			
		}else{//echo '<br>$abort 2';
			$abort = true;
		}

		// > блок 1, в отдельную функцию
		// > первый шаг
		
		// < второй шаг
		if($abort == false){
			if($order == 'long-short'){
				// < блок 1, в отдельную функцию
				// 	< блок 1.1, отличие для функции
				$y_new = $y_new + Direction :: $properties[$dir]['y'];
				$x_new = $x_new + Direction :: $properties[$dir]['x'];
				$new_dir = $dir;
				// 	> блок 1.1, отличие для функции
				if(isset(GameField :: $matrix[$y_new][$x_new]))
				    $field = GameField :: $matrix[$y_new][$x_new];
				else
				    $field = false;
				/*echo '<br>step 2';
				echo '<br>$y_new '.$y_new;
				echo '<br>$x_new '.$x_new;*/
				
				if(GameField::checkCorrectField($y_new, $x_new)){ // если клетка не выходит за пределы поля
					if(!isset($field) || !is_object($field) || (is_object($field) && $field -> color == $this -> color)){ // если клетка пустая, или с фигурой своего цвета
						//$result[] = ['y' => $y_new, 'x' => $x_new, 'type' => 'jump'];
						if(!isset($result_y_x[$y_new][$x_new])){
							//$result[] = ['y' => $y_new, 'x' => $x_new, 'type' => 'jump'];
							//$result_y_x[$y_new][$x_new] = 'jump';
						}
					}else{
						$abort = true;
					}
					
				}else{
					$abort = true;
				}

				// > блок 1, в отдельную функцию
			}elseif($order == 'short-long'){
				if($angle == 'straight'){
					if($side == 'left'){
						$new_dir = Direction :: turnPrev(Direction :: turnPrev($dir));
					}elseif($side == 'right'){
						$new_dir = Direction :: turnNext(Direction :: turnNext($dir));
					}
				}elseif($angle == 'obtuse'){
					if($side == 'left'){
						$new_dir = Direction :: turnPrev($dir);
					}elseif($side == 'right'){
						$new_dir = Direction :: turnNext($dir);
					}
				}
				// < блок 1, в отдельную функцию

				// 	< блок 1.2, отличие для функции
				$y_new = $y_new + Direction :: $properties[$new_dir]['y'];
				$x_new = $x_new + Direction :: $properties[$new_dir]['x'];
				// 	> блок 1.2, отличие для функции
				if(isset(GameField :: $matrix[$y_new][$x_new]))
				    $field = GameField :: $matrix[$y_new][$x_new];
				else
				    $field = false;
				if(GameField::checkCorrectField($y_new, $x_new)){ // если клетка не выходит за пределы поля
					if(!isset($field) || !is_object($field) || (is_object($field) && $field -> color == $this -> color)){ // если клетка пустая, или с фигурой своего цвета
						//$result[] = ['y' => $y_new, 'x' => $x_new, 'type' => 'jump'];
						if(!isset($result_y_x[$y_new][$x_new])){
							//$result[] = ['y' => $y_new, 'x' => $x_new, 'type' => 'jump'];
							//$result_y_x[$y_new][$x_new] = 'jump';
						}
					}else{
						$abort = true;
					}
				}else{
					$abort = true;
				}
				// > блок 1, в отдельную функцию
			}
		}
		// > второй шаг
		
		// < третий шаг
		if($abort == false){
			if($order == 'long-short'){
				if($angle == 'straight'){
					if($side == 'left'){
						$new_dir = Direction :: turnPrev(Direction :: turnPrev($dir));
					}elseif($side == 'right'){
						$new_dir = Direction :: turnNext(Direction :: turnNext($dir));
					}
				}elseif($angle == 'obtuse'){
					if($side == 'left'){
						$new_dir = Direction :: turnPrev($dir);
					}elseif($side == 'right'){
						$new_dir = Direction :: turnNext($dir);
					}
				}

			}elseif($order == 'short-long'){
				// 	< блок 1.3, отличие для функции

				// 	> блок 1.3, отличие для функции
			}
			$y_new = $y_new + Direction :: $properties[$new_dir]['y'];
			$x_new = $x_new + Direction :: $properties[$new_dir]['x'];
			
			/*echo '<br>step 3';
			echo '<br>$y_new '.$y_new;
			echo '<br>$x_new '.$x_new;*/
			if(isset(GameField :: $matrix[$y_new][$x_new]))
			    $field = GameField :: $matrix[$y_new][$x_new];
			else
				$field = false;
			if(GameField::checkCorrectField($y_new, $x_new)){ // если клетка не выходит за пределы поля
				if(isset($field) && !is_object($field)){ // если клетка пустая, или с фигурой своего цвета
					//$result[] = ['y' => $y_new, 'x' => $x_new, 'type' => 'run'];
					if(!isset($result_y_x[$y_new][$x_new]) || $result_y_x[$y_new][$x_new] == 'jump'){
						$result[] = ['y' => $y_new, 'x' => $x_new, 'type' => 'run'];
						$result_y_x[$y_new][$x_new] = 'run';
					}
				}elseif(isset($field) && is_object($field) && $field -> color != $this -> color && (!in_array(get_class($field), $this -> impossible_target))/*get_class($field) != 'Tank' && get_class($field) != 'Plane'*/){
					//$result[] = ['y' => $y_new, 'x' => $x_new, 'type' => 'crush'];
					if(!isset($result_y_x[$y_new][$x_new]) || $result_y_x[$y_new][$x_new] == 'jump'){
						$result[] = ['y' => $y_new, 'x' => $x_new, 'type' => 'crush'];
						$result_y_x[$y_new][$x_new] = 'crush';
					}
				}
			}else{
				$abort = true;
			}
		}
		// > третий шаг
		//}
	}

	function AddVariantField(&$result, $dir){
		
	}
}

class mashinegun extends Unit
{
	public $img;
	public $name = 'mashinegun';
	public $name_ru = 'Пулемёт';
	public $text = 'Пулемёт<br>
Перемещение: Ходит на одну клетку в любом направлении, совершать ходы через фигуры не может.<br>
Ход огнем: Пулемёт не может ходить и стрелять одновременно. Стреляет до трёх клеток в любом направлении и через одну фигуру своего цвета.<br>
Поражает: боец,пулемет,конница,пушка,самолет.<br>
Уязвимость:всеми фигурами<br>
Ценность:3';
    public $controllable = true;
	public $impossible_target = ['Tank'];
	
	function __construct($color, $y, $x) {
		parent::__construct($color, $y, $x);
		$this -> img = 'img/'.$this -> color.'/machinegun.png';
	}
	
	function getVariants(){
		$result = [];
		
		$i = 1;
		
		$dir = $this -> dirMain;
		while ($i < 9)
		{			
			$this -> AddVariantField($result, $dir);
			$dir = Direction :: turnNext($dir);
			$i ++;
		}
		
		return $result;
	}

	function AddVariantField(&$result, $dir){
		$dirProps = Direction :: $properties[$dir];
		if(isset(GameField :: $matrix[$this -> y + $dirProps['y']][$this -> x + $dirProps['x']]))
		    $field = GameField :: $matrix[$this -> y + $dirProps['y']][$this -> x + $dirProps['x']];
		else
			$field = false;
		if(!isset($field) || !is_object($field)){
			$result[] = ['y' => $this -> y + $dirProps['y'], 'x' => $this -> x + $dirProps['x'], 'type' => 'run'];
		}
		
		$i = 1;
		$abort = false;
		while ($i <= 3){
			if(GameField::checkCorrectField($this -> y + $dirProps['y']*$i, $this -> x + $dirProps['x']*$i)){
				if($abort == false){
				    if(isset(GameField :: $matrix[$this -> y + $dirProps['y']*$i][$this -> x + $dirProps['x']*$i]))
					    $field = GameField :: $matrix[$this -> y + $dirProps['y']*$i][$this -> x + $dirProps['x']*$i];
					else
					   $field = false;
					if(!isset($field) || !is_object($field)){
						$result[] = ['y' => $this -> y + $dirProps['y']*$i, 'x' => $this -> x + $dirProps['x']*$i, 'type' => 'shot'];
					}else{
						if(($field -> color != $this -> color) && (!in_array(get_class($field), $this -> impossible_target))/*&& (get_class($field) != 'Tank')*/){
							$result[] = ['y' => $this -> y + $dirProps['y']*$i, 'x' => $this -> x + $dirProps['x']*$i, 'type' => 'shot+'];
						}
						$abort = true;
					}
				}
			}else{
				$abort = true;
			}
			$i ++;
		}
	}
}

class cannon extends Unit
{
	public $img;
	public $name = 'cannon';
	public $name_ru = 'Пушка';
	public $text = 'Пушка<br>
Перемещение: В любом направлении на 1 клетку, не может ходить через фигуры.<br>
Ход огнем: стреляет до пяти клеточек в любом направлении, но не назад.Может произвести выстрел через одну свою фигуру.Не может делать ход на другую<br>
клетку и стрелять одновременно.<br>
Поражает:боец,пулемет,конница,танк,самолет,пушка<br>
Уязвимость: всеми фигурами<br>
Ценность:9';
	public $controllable = true;
	public $impossible_target = [];
	
	function __construct($color, $y, $x) {
		parent::__construct($color, $y, $x);
		$this -> img = 'img/'.$this -> color.'/cannon.png';
	}
	
	function getVariants(){
		$result = [];
		
		$opp = Direction :: $properties[$this -> dirMain]['opposite'];
		$arrOpp = [$opp, Direction :: turnNext($opp), Direction :: turnPrev($opp)];
		
		$i = 1;
		
		$dir = $this -> dirMain;
		while ($i < 9)
		{			
			$this -> AddVariantField($result, $dir, $arrOpp);
			$dir = Direction :: turnNext($dir);
			$i ++;
		}
		
		return $result;
	}

	function AddVariantField(&$result, $dir, $arrOpp){
		$dirProps = Direction :: $properties[$dir];
		if(GameField::checkCorrectField($this -> y + $dirProps['y'], $this -> x + $dirProps['x'])){
		    if(isset(GameField :: $matrix[$this -> y + $dirProps['y']][$this -> x + $dirProps['x']]))
    			$field = GameField :: $matrix[$this -> y + $dirProps['y']][$this -> x + $dirProps['x']];
			
			if(!isset($field) || !is_object($field)){
				$result[] = ['y' => $this -> y + $dirProps['y'], 'x' => $this -> x + $dirProps['x'], 'type' => 'run'];
			}
		}
		$i = 1;
		$abort = false;
		$jump = false;
		while ($i <= 5){
			if(GameField::checkCorrectField($this -> y + $dirProps['y']*$i, $this -> x + $dirProps['x']*$i)){
				if($abort == false){
					if(array_search($dir, $arrOpp) === false){
					    $field = false;
						if(isset(GameField :: $matrix[$this -> y + $dirProps['y']*$i][$this -> x + $dirProps['x']*$i]))
    						$field = GameField :: $matrix[$this -> y + $dirProps['y']*$i][$this -> x + $dirProps['x']*$i];
						if(!isset($field) || !is_object($field)){ // пустое поле
							$result[] = ['y' => $this -> y + $dirProps['y']*$i, 'x' => $this -> x + $dirProps['x']*$i, 'type' => 'shot'];

						}else{
							
							if($field -> color != $this -> color){ // чужая фигура
								$result[] = ['y' => $this -> y + $dirProps['y']*$i, 'x' => $this -> x + $dirProps['x']*$i, 'type' => 'shot+'];
								$abort = true;
							}else{ // своя фигура
								if($jump == true){
									$abort = true;
								}else{
									$jump = true;
								}
							}
						}
					}else{

					}
				}else{

				}
			}else{
				$abort = true;
			}
			$i ++;
		}
	}
}
?>
