<?require "header_reg_auth.php";

$sql_string = 'SELECT id, name FROM sh_school WHERE name LIKE ?';
$sql_array = array('%'.$_REQUEST['string'].'%');
$stmt = $db->prepare($sql_string);
$stmt->execute($sql_array);
$finded = false;
//echo '<div class="request">'.$_REQUEST['string'].'</div> ';
while($arrTag = $stmt->fetch()){
	$finded = true;
	echo '<div class="tag_result"><span class="id">'.$arrTag['id'].'</span><span class="name">'.$arrTag['name'].'</span></div>';
}
if(!$finded){
	echo 'Ничего не найдено';
}

?>