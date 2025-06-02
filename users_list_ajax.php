<?require "header_reg_auth.php";

$sql_string = 'UPDATE `'.TABLE_USER.'` SET datetime_change=? WHERE id = ?';
$sql_array = array(date("Y-m-d H:i:s"), $_REQUEST['user_id']);
$stmt = $db->prepare($sql_string);
$stmt->execute($sql_array);

$sql_string = 'SELECT `id`, `login`, `datetime_change`, `avatar`, `admin` FROM `'.TABLE_USER.'` WHERE `datetime_change` >= NOW() - INTERVAL 10 SECOND';
$stmt = $db->prepare($sql_string);
$stmt->execute([]);
$arresult = ['users' => []];
while($arrMark = $stmt->fetch()){
    if($arrMark['id'] != $_SESSION['user_id']){
    	if(strlen($arrMark['avatar']) > 0){
    		$img = $arrMark['avatar'];
    	}else{
    		$img = 'img_avatar.png';
    	}
    	$arresult['users'][$arrMark['id']] = ['id' => $arrMark['id'], 'avatar' => $img, 'login' => $arrMark['login'], 'admin' => $arrMark['admin'], 'datetime_change' => $arrMark['datetime_change']];
    }
}


$partyHTML = '';
$sql_string = "SELECT ".TABLE_PARTY.".id as party_id, ".TABLE_PARTY.".pass, ".TABLE_PARTY.".status, ".TABLE_PARTY.".datetime_create as party_date, ".TABLE_REL.".owner as party_owner, ".TABLE_REL.".user as party_user, ".TABLE_USER.".login as user_login FROM ".TABLE_REL.", ".TABLE_USER.", ".TABLE_PARTY." WHERE ".TABLE_PARTY.".active = '1' AND (".TABLE_REL.".user = ? OR ".TABLE_REL.".owner = ?) AND ".TABLE_REL.".party = ".TABLE_PARTY.".id AND ".TABLE_REL.".user = ".TABLE_USER.".id AND ".TABLE_PARTY.".status = 'prepare'";
$stmt = $db->prepare($sql_string);
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_login']]);
$arrParty_ = [];
while($arrParty = $stmt->fetch()){
	//echo '<pre>$arrParty: '; print_r($arrParty); echo '</pre>';
	if(isset($arrParty_[$arrParty['party_id']])){
	    $arrParty_[$arrParty['party_id']]['users'][$arrParty['party_user']] = '<b>'.$arrParty['user_login'].'</b>';
	}else{
	    $arrParty_[$arrParty['party_id']] = $arrParty;
	    $arrParty_[$arrParty['party_id']]['users'] = [$arrParty['party_user'] => '<b>'.$arrParty['user_login'].'</b>'];
	}
	//$arrParty_[$arrParty['party_id']] = $arrParty;
	//echo '<pre>$arrParty: '; print_r($arrParty_); echo '</pre>';
}
	
if(isset($_REQUEST['party']) && $_REQUEST['party'] > 0){
    $sql_string = "SELECT status, master, contragent, red, black, current FROM ".TABLE_PARTY." WHERE id = ?";
    $stmt = $db->prepare($sql_string);
    $stmt->execute([$_REQUEST['party']]);
    
    while($arrParty2 = $stmt->fetch()){
        $sql_arr2 = [$arrParty2['master'], $arrParty2['contragent']];
        $sql_string2 = 'SELECT id, login, datetime_change FROM '.TABLE_USER.' WHERE id IN ('. str_repeat('?,', count($sql_arr2)-1) . '?)';
        $stmt2 = $db->prepare($sql_string2);
        $stmt2->execute($sql_arr2);
        $contragent = '';
        $contragent_time = 0;
        $contragent_id = '';
        $color = '';
        $current_color = '';
         $color_eng = '';
        if(strlen($arrParty2['red']) > 0 && $arrParty2['red'] == $_SESSION['user_id']){
            $color = 'красный';
            $color_eng = 'red';
        }elseif(strlen($arrParty2['black']) > 0 && $arrParty2['black'] == $_SESSION['user_id']){
            $color = 'чёрный';
            $color_eng = 'black';
        }

if(isset($arrParty2['current']) && strlen($arrParty2['current']) > 0){
    $current_color = $arrParty2['current']; 
}else{
    $current_color = 'red'; 
}
        /*$sql_string3 = 'UPDATE `'.TABLE_PARTY.'` SET current=? WHERE id = ?';
        $sql_arr3 = [$current_color, $_REQUEST['party']];
        $stmt3 = $db->prepare($sql_string3);
        $stmt3->execute($sql_arr3);*/
                
        while($arrUser = $stmt2->fetch()){
            $raz = strtotime('now') - strtotime($arrUser['datetime_change']);
            $partyHTML .= '<br>'.$arrUser['login'].' '.$raz.' сек.';
            if($arrUser['id'] != $_SESSION['user_id']){
                $contragent = $arrUser['login'];
                $contragent_id = $arrUser['id'];
                $contragent_time = $raz;
                
            }
        }
    	//echo '<pre>$arrParty: '; print_r($arrParty); echo '</pre>';
    	
    	if($arrParty2['status'] != $_REQUEST['status']){
    	    //$arresult['party'] = $arrParty2['status'];
    	    $partyHTML .= '<br>статус изменился. '.$_REQUEST['status'].' -> '.$arrParty2['status'];
    	    if($_REQUEST['status'] == 'prepare' && $arrParty2['status'] == 'process'){ // начало партии
    	        $master_color = rand(0, 1) ? 'red' : 'black';
    	        if($master_color == 'red'){
    	           $contragent_color = 'black';

    	        }elseif($master_color == 'black'){
    	            $contragent_color = 'red';

    	        }
                if(isset($_SESSION['party']) && $_SESSION['party'] > 0 && $_SESSION['party'] == $_REQUEST['party']){
                    if($master_color == 'red'){
                        $color = 'красный';
                        $color_eng = 'red';
                    }elseif($master_color == 'black'){
                        $color = 'чёрный';
                        $color_eng = 'black';
                    }
                    ${$master_color.'_c'} = $_SESSION['user_id'];
                    ${$contragent_color.'_c'} = $contragent_id;
                }elseif(isset($party_join) && $party_join > 0 && $party_join == $_REQUEST['party']){
                    if($contragent_color == 'red'){
                        $color = 'красный';
                        $color_eng = 'red';
                    }elseif($contragent_color == 'black'){
                        $color = 'чёрный';
                        $color_eng = 'black';
                    }
                    ${$master_color.'_c'} = $contragent_id;
                    ${$contragent_color.'_c'} = $_SESSION['user_id'];
                }
                $sql_string3 = 'UPDATE `'.TABLE_PARTY.'` SET red=?, black=? WHERE id = ?';
                $sql_arr3 = [$red_c, $black_c, $_REQUEST['party']];
                $stmt3 = $db->prepare($sql_string3);
                $stmt3->execute($sql_arr3);
    	    }
    	}else{
    	    $partyHTML .= '<br>статус не менялся.';
    	}
    	$arresult['color_eng'] = $color_eng;
    	$arresult['color'] = $color;
    	$arresult['current_color'] = $current_color;
    	$arresult['party'] = $partyHTML;
    	$arresult['contragent'] = $contragent;
    	$arresult['contragent_time'] = $contragent_time;
    	$arresult['status'] = $arrParty2['status'];
    }
}else{
    foreach($arrParty_ as $k => $v){
        if($v['status'] == 'prepare'){
    	    if($v['party_owner'] == $_SESSION['user_login']){
    	        $partyHTML .= '<fieldset>Создатель: Вы, приглашённые: '.implode(', ', $v['users']).'. '.$v['party_date'].'</fieldset>';
    	    }else{
    	        $partyHTML .= '<fieldset>'.$v['party_date'].', Создатель: '.$v['party_owner'].', приглашённые: '.implode(', ', $v['users']).'. '.$v['party_date'].' <a href="?join='.$v['pass'].'"><b>+ Присоединиться</b></a></fieldset>';
    	    }
        }
    }
    $arresult['party'] = $partyHTML;
}
echo json_encode($arresult);








