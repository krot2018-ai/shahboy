<?
class Direction
{
	public static $properties = [
		'u' => ['x' => 0, 'y' => -1, 'layout' => 'left: 0; bottom: 100%;', 'opposite' => 'd'],
		'ur' => ['x' => 1, 'y' => -1, 'layout' => 'left: 100%; bottom: 100%;', 'opposite' => 'dl'],
		'r' => ['y' => 0, 'x' => 1, 'layout' => 'left: 100%; bottom: 0;', 'opposite' => 'l'],
		'dr' => ['y' => 1, 'x' => 1, 'layout' => 'left: 100%; bottom: -100%;', 'opposite' => 'ul'],
		'd' => ['x' => 0, 'y' => 1, 'layout' => 'left: 0; bottom: -100%;', 'opposite' => 'u'],
		'dl' => ['y' => 1, 'x' => -1, 'layout' => 'left: -100%; bottom: -100%;', 'opposite' => 'ur'],
		'l' => ['y' => 0, 'x' => -1, 'layout' => 'left: -100%; bottom: 0;', 'opposite' => 'r'],
		'ul' => ['x' => -1, 'y' => -1, 'layout' => 'left: -100; bottom: 100%;', 'opposite' => 'dr']
	];
	
    public static function getDirection($name, $value = false) {
		if(!empty($value)){
			return self::$properties[$name][$value];
		}else{
			return self::$properties[$name];
		}
    }
	
	public static function turnNext($dir) {
		if($dir == 'u'){
			return 'ur';
		}elseif($dir == 'ur'){
			return 'r';
		}elseif($dir == 'r'){
			return 'dr';
		}elseif($dir == 'dr'){
			return 'd';
		}elseif($dir == 'd'){
			return 'dl';
		}elseif($dir == 'dl'){
			return 'l';
		}elseif($dir == 'l'){
			return 'ul';
		}elseif($dir == 'ul'){
			return 'u';
		}
    }
	
	public static function turnPrev($dir) {
		if($dir == 'u'){
			return 'ul';
		}elseif($dir == 'ul'){
			return 'l';
		}elseif($dir == 'l'){
			return 'dl';
		}elseif($dir == 'dl'){
			return 'd';
		}elseif($dir == 'd'){
			return 'dr';
		}elseif($dir == 'dr'){
			return 'r';
		}elseif($dir == 'r'){
			return 'ur';
		}elseif($dir == 'ur'){
			return 'u';
		}
    }
	
    public static function getRand() {	
		$rand_key = array_rand(self::$properties);
		return ['name' => $rand_key, 'axis' => self::$properties[$rand_key]['axis'], 'num' => self::$properties[$rand_key]['num']];
    }
}
?>