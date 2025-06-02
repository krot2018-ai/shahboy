<?
echo '<h2>Новая партия</h2>';
$sql_string = 'SELECT `id`, `login`, `datetime_change`, `avatar`, `admin` FROM `'.TABLE_USER.'` WHERE `datetime_change` >= NOW() - INTERVAL 10 SECOND'; //WHERE datetime_change >= CURDATE() – INTERVAL 3 DAY
// >= CURDATE() – INTERVAL 3 DAY;
// >= UNIX_TIMESTAMP() – 10';
// >= DATEADD(minute,-5000,GETDATE())
$stmt = $db->prepare($sql_string);
$stmt->execute([]);

$users_list = array();
$main_user = '';
while($arrMark = $stmt->fetch()){
	if(isset($arrMark['avatar']) && strlen($arrMark['avatar']) > 0)
		$img = $arrMark['avatar'];
	else
		$img = 'img_avatar.png';
	//echo '<pre>$arrMark: '; print_r($arrMark); echo '</pre>';
	if($arrMark['id'] != $_SESSION['user_id']){
	    if(isset($party) && $party == true){
    		$users_list[] = '<div class="user_'.$arrMark['id'].' user_block" style="">
    		<div style="width: 62px; position: relative;">
    	  <img class="inline-block size-[62px] rounded-full" src="'.$img.'" alt="Avatar">
    	  <span class="absolute bottom-0 end-0 block size-3.5 rounded-full ring-2 ring-white bg-teal-400 dark:ring-neutral-900"></span>
    		</div><span class="u_login">'.$arrMark['login'].'</span></div>';//datetime_change: '.$arrMark['datetime_change'].'&nbsp;
	    }else{
    		$users_list[] = '<div class="user_'.$arrMark['id'].' user_block" style="">
    		<div style="width: 62px; position: relative;">
    	  <img class="inline-block size-[62px] rounded-full" src="'.$img.'" alt="Avatar">
    	  <span class="absolute bottom-0 end-0 block size-3.5 rounded-full ring-2 ring-white bg-teal-400 dark:ring-neutral-900"></span>
    		</div><span class="u_login">'.$arrMark['login'].'</span>&nbsp;<br><input type="checkbox" name="users[]" value="'.$arrMark['id'].'"></div>';//datetime_change: '.$arrMark['datetime_change'].'&nbsp;  
	    }
	}/*else{
		$main_user = '<div class="user_'.$arrMark['id'].' user_block" style="">
		<div style="width: 62px; position: relative;">
	  <img class="inline-block size-[62px] rounded-full" src="'.$img.'" alt="Avatar">
	  <span class="absolute bottom-0 end-0 block size-3.5 rounded-full ring-2 ring-white bg-teal-400 dark:ring-neutral-900"></span>
		</div>
		'.$arrMark['id'].' '.$arrMark['login'].'</div><div><a href="?logout=Y">Выйти</a></div></div>';
	}*/
}
/*echo '<fieldset ><legend>main</legend>';
echo $main_user;
echo '</fieldset>';*/
if(count($users_list) > 0){
    echo '<fieldset class="user_list"><legend>Выберите предполагаемых соперников для партии.</legend>';
    
    foreach($users_list as $k => $v){
    	echo $v;
    }
    echo '<div><button class="party_submit" type="submit" name="new" value="Отправить">Предложить партию</button></div>
    <input class="unvis" type="checkbox" name="verification" value="Y">
    </fieldset>';
}else{
    echo '<fieldset class="user_list"><legend>Выберите предполагаемых соперников для партии.</legend><h2 class="nobody">Никого нет на сервере</h2><div><button style="display: none;" class="party_submit" type="submit" name="new" value="Отправить">Предложить партию</button></div>
    <input class="unvis" type="checkbox" name="verification" value="Y"></fieldset>';
}

//<span class="online">Y</span>
?>



<script src="jquery-3.3.1.min.js"></script>
<script>
	function get_ajax() {
	    $('#loading').show();
		$.ajax({
			url: 'users_list_ajax.php',
			type: 'post',
			data: { "user_id" : "<?=$_SESSION['user_id']?>", "party" : "<?=$_SESSION['party']?>"},
			dataType: 'json',
			success: function(data){
				//alert('!23');
				//$('span.online').text('N');
				$('.user_block span.rounded-full').removeClass('bg-teal-400').addClass('bg-red-400');
				//let arr = JSON.parse(data);
				$.each(data.users, function(key, value){
					let elem = document.querySelector('.user_' + value.id);
					if(elem){
						$('.user_' + value.id + ' span.online').text('Y');
						$('.user_' + value.id + ' span.rounded-full').removeClass('bg-red-400').addClass('bg-teal-400');
					}else{
						//if(value.avatar !== undefined)
						//	let img = value.avatar;
						//else
						//	let img = 'img_avatar.png';
						$('.user_list').prepend('<div class="user_' + value.id + ' user_block" style=""><div style="width: 62px; position: relative;"><img class="inline-block size-[62px] rounded-full" src="'+value.avatar+'" alt="Avatar"><span class="absolute bottom-0 end-0 block size-3.5 rounded-full ring-2 ring-white bg-teal-400 dark:ring-neutral-900"></span></div>' + value.login + '<br><input type="checkbox" name="users[]" value="'+value.id+'"></div></div>');
					}
					$('.party_submit').show();
					$('.nobody').hide();
				});
				$('.party_ajax').empty().prepend(data.party);
				$('#loading').hide();
			}	
		});
	}
    $(document).ready(function() {
    	get_ajax();
    });
	let interval = setInterval(get_ajax,5000);
	
</script>
<link rel="stylesheet" href="users_list.css" media="all" />