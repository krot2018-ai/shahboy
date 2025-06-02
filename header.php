		<div class="header">
			<div class="header_cont"><a href=""><img src="img/логотип АНО.png" alt=""></a></div>
			<div class="header_cont"><a href=""><img src="img/ЛОГО 80 ЛЕТ.png" alt="80 ЛЕТ"></a></div>
			<div class="header_cont"><a href=""><img src="img/ЛОГО ИСТОРИЯ БЕЗ ФАЛЬШИ.png" alt="ИСТОРИЯ БЕЗ ФАЛЬШИ"></a></div>
			<div class="header_cont"><a href=""><img src="img/ЛОГО ПЕРВЫЕ.png" alt=""></a></div>
			<div class="header_cont"><a href=""><img src="img/ЛОГО ФПГ.png" alt=""></a></div>
		</div>
		<div class="header2">
		    <h3 style="text-align: center;">
		    <?if(!isset($MAIN_PAGE)){?>
	    		<a href="/game.php">На главную</a>&nbsp;
			<?}?>
			<?if(isset($_SESSION['user_id'])){?>
    			&nbsp;<a href="?logout=Y">Выйти из аккаунта</a>
			<?}?>
			</h3>
		</div>