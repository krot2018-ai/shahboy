<?
class Player
{
	public static $array = [
		'red' => 
		[
			'type' => 'human',
			'defeated' => false,
			'victory' => false,
			'soldier' => 0,
			'head' => 0,
			'direction' => 'u',
			'ru' => 'Красный',
			'opposit' => 'black',
			'message' => '',
			'soldier_last' => []
		],
		'black' => 
		[
			'type' => 'human',
			'defeated' => false,
			'victory' => false,
			'soldier' => 0,
			'head' => 0,
			'direction' => 'd',
			'ru' => 'Чёрный',
			'opposit' => 'red',
			'message' => '',
			'soldier_last' => []
		],
	];
}
?>