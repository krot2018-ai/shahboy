//$(function() {
   // party_id = $.config.party_id;
    //party_pass = $.config.party_pass;
//});

			/*
			stepcolor - игрок, который ходит в данный момент
			phase (collision, action) - фаза хода
			phase_type (simple, sacrifice) - диверсия или обычный ход
			*/
	if(typeof(stepnum) != "undefined" && stepnum !== null)
		stepnum = 1;
		
			function change_color(color_) {
				/*if(stepcolor == 'red'){
					stepcolor = 'black';
					$('.mes').text('№ хода:'+stepnum+'. Ход чёрных.');
				}else{
					stepnum ++;
					stepcolor = 'red';
					$('.mes').text('№ хода:'+stepnum+'. Ход красных.');
				}*/
				if(color_ == 'red'){
					stepcolor = 'black';
					$('.mes').text('№ хода:'+stepnum+'. Ход чёрных.');
					$('.cont_black').addClass('active2');
					$('.cont_red').removeClass('active2');
				}else if(color_ == 'black'){
					stepnum ++;
					stepcolor = 'red';
					$('.mes').text('№ хода:'+stepnum+'. Ход красных.');
					$('.cont_red').addClass('active2');
					$('.cont_black').removeClass('active2');
				}
			}
			
			function add_message(message) {
				/*$('div.message_note_item span.note').each(function( index ) {
					num = parseInt($(this).text());
					$(this).text(num + 1);
				});
				message_text = message;
				message_text = '<div class="message_note_item"><span class="note">0</span>' + message_text + '<div>';
				$('.message_note').append(message_text);
				//var block = document.getElementById('scroll');
				//block.scrollTop = block.scrollHeight;*/

			}
			
			function add_error(xhr, str) {
				/*alert('Возникла ошибка: ' + xhr.responseCode);
				$('div.message_note_item span.note').each(function( index ) {
					num = parseInt($(this).text());
					$(this).text(num + 1);
				});
				error_text = 'str: ' + str + '<br>';
				error_text = error_text + 'xhr: ' + JSON.stringify(xhr) + '<br>';
				error_text = '<div class="message_note_item"><span class="note">0</span>' + error_text + '<div>';
				$('.message_note').append(error_text);
				var block = document.getElementById('scroll');
				block.scrollTop = block.scrollHeight;*/
			}
			
			function getRandomIntFromRange(min, max) {
				max ++;
				min = Math.ceil(min); // вычисляет и возвращает наименьшее целое число, которое больше или равно переданному числу (округляет число вверх)
				max = Math.floor(max); // вычисляет и возвращает наибольшее целое число, которое меньше или равно переданному числу (округляет число вниз)
				return Math.floor(Math.random() * (max - min)) + min; 
			}

			//stepcolor = 'red';
			//stepnum = 1;
			//$('.mes').text('№ хода:'+stepnum+'. Ход красных.');
			//$('.cont_red').addClass('active');
			ajax_script = 'battle_ajax.php';
			

			function toglePhase(){
				if(phase == 'collision')
					phase = 'action';
				else
					phase = 'collision';
			}

			phase = 'collision';
			phase_type = 'simple';
			
			function alertObj(obj) { 
				var str = ""; 
				for(k in obj) { 
					str += k+": "+ obj[k]+"\r\n"; 
				} 
				alert(str); 
			}
			
			function planeAiTurn(){
				if(!ajax_processing){
					$('#planeAiTurn').hide();
					$('#getAiTurn').show();
					$('.loading').show();
					ajax_processing = true;
					$.ajax({
						type: 'POST',
						url: 'ajax_ai_turn.php?player=' + stepcolor,
						//data: {},
						success: function(data) {
							console.log(data);
							arr = JSON.parse(data);
							$('#json').val(data);
							$('div.unit[data-id='+arr['id']+']').addClass('selected');
							$('div#td_' + arr['y'] + '_' + arr['x']).addClass('shot');
							$('.loading').hide();
							ajax_processing = false;
							$('.cont_'+stepcolor+' .robot_load').hide();
							$('.cont_'+stepcolor).removeClass('active');
							getAiTurn();
						},
						error:  function(xhr, str){
							add_error(xhr, str);
							$('.loading').hide();
							ajax_processing = false;
							$('.cont_'+stepcolor+' .robot_load').hide();
							$('.cont_'+stepcolor).removeClass('active');
						}
					});
				}
				return false;
			}
			
			function getAiTurn(){	// ход искуственного интеллекта
				if(!ajax_processing){//console.log('no');
					//$('#getAiTurn').hide();
					//$('#planeAiTurn').show();
					
					data = $('#json').val();
					arr = JSON.parse(data);
					if(phase == 'collision' && phase_type == 'sacrifice'){
						//< ! проверить, есть ли проверка на сервере на корректность диверсанта и жертвы
						id = $('.unit.selected').find('.id').text();
						target_id = $('.diversion_target').find('.id').text();
						req = {
							'saboteur': id,
							'target': target_id
						}
					}else{
						req = {x : arr['x'], y : arr['y']}
						if(arr['type'] != 'shot+'){
							req['id'] = arr['id'];
						}else{
							req['shot_id'] = arr['id'];
						}
					}
					req['party_pass'] = party_pass;
					req['party_id'] = party_id;
					$('.loading').show();
					ajax_processing = true;
					$.ajax({
						type: 'POST',
						url: 'battle_ajax.php',
						dataType: 'json',
						data: req,//action: 'crush', 
						success: function(data2) {
							//$('.ajax').html(data2);
							result = data2;
							
							if(result.message !== undefined){
								add_message(result.message);
							}
							if(phase == 'collision' && phase_type == 'sacrifice'){
								// < на сокращение. в 2х местах
								result = data2;
								if(result.message !== undefined){
									add_message(result.message);
								}
								if(result.gamefield !== undefined)
									$('.ajax').html(result.gamefield);
								console.log('result.error '+result.error+' result.change_player '+result.change_player);
								/*if(result.error === undefined && result.change_player === true ){ console.log('Yes');
									console.log('color changed');
									change_color(stepcolor);
									if(result.resurs !== undefined){
										$.each( result.resurs, function( key, value ) {
											$.each( value, function( key2, value2 ) {
												if(value2.defeated === true){
													if (document.getElementById('defeat_container') === null) {
													    $('.message').prepend('<h1 id="defeat_container" style="color: red"></h1>');
													}
												}
												console.log( key2 + ": " + value2 );
												$.each( value2, function( key3, value3 ) {
													console.log( key3 + ": " + value3 );
													$('.message .'+key2+'_'+key3).text(value3);
												});
											});
										});
									}
									//toglePhase();
									$('#getAiTurn').hide();
									$('#planeAiTurn').show();
									phase_type = 'simple';
								}else{
									console.log('No');
								}*/
								// > на сокращение. в 2х местах
							}else{
								if(result.gamefield !== undefined)
									$('.ajax').html(result.gamefield);
								console.log('result.shah_message: '+result.shah_message);
								console.log('typeof(result.shah_message): '+typeof(result.shah_message));
								if(result.error === undefined && result.change_player === true ){
									$('#getAiTurn').hide();
									$('#planeAiTurn').show();
									console.log('color changed');
									change_color(stepcolor);
									/*if(result.resurs !== undefined){
										$.each( result.resurs, function( key, value ) {

											$.each( value, function( key2, value2 ) {
												if(value2.defeated === true){
													if (document.getElementById('defeat_container') === null) {
													    $('.message').prepend('<h1 id="defeat_container" style="color: red"></h1>');
													}
												}
												//alert( key + ": " + value );
												console.log( key2 + ": " + value2 );
												//$('.message .'+key+'_'+key2).text(value2);
												$.each( value2, function( key3, value3 ) {
													//alert( key + ": " + value );
													console.log( key3 + ": " + value3 );
													$('.message .'+key2+'_'+key3).text(value3);
												});
											});
										});
									}*/
									//getResurs();
									planeAiTurn();
								}else if(result.error === undefined && result.change_player === false && result.shah_flag_return !== true && result.shah_message === undefined){ // жертва пешки
									if(stepcolor == 'red'){
										oppositcolor = 'black';
									}else{
										oppositcolor = 'red';
									}
									phase_type = 'sacrifice';
									$('.not_sacr').show();
									$('.unit[data-color="'+oppositcolor+'"]:not([data-type="Head"])').addClass('diversion');
									$('.unit[data-id="'+req['id']+'"]').addClass('selected');
									$('.unit[data-id="'+req['id']+'"]').addClass('pulse');
									size = $('.diversion').length;
									i_num = Math.floor(Math.random() * size);
									console.log('i_num '+i_num);
									$('.diversion').eq(i_num).addClass('diversion_target');
								}else if(result.shah_flag_return === true){
									$('.status_message').html(result.return_shah);
									//$('div[data-type=Head][data-color='+stepcolor+']').addClass('pulse');
									//$('.status_message_2').fadeIn(1000);
									$('.status_message_2').slideDown(1000);
									  myAudio = new Audio;
									  myAudio.src = "perkussiya-odinochnyiy-trevojnyiy.mp3";
									  myAudio.play();
								}else if(result.shah_message !== undefined){
									$('.status_message').html('Поле бито. '+result.shah_message);
									//$('div[data-type=Head][data-color='+stepcolor+']').addClass('pulse');
									//$('.status_message_2').fadeIn(1000);
									$('.status_message_2').slideDown(1000);
									  myAudio = new Audio;
									  myAudio.src = "perkussiya-odinochnyiy-trevojnyiy.mp3";
									  myAudio.play();
								}
							}
							//setTimeout(function() {alert('timer')}, 1000);
							$('.loading').hide();
							ajax_processing = false;
							// ускорение автоходов
							if(result.next_turn_auto !== undefined){//alert('next_turn_auto');
								$('.cont_'+stepcolor+' .robot_load').show();
								$('.cont_'+stepcolor).addClass('active');
								let timerId = setTimeout(planeAiTurn, 1000);
							}

						},
						error:  function(xhr, str){
							add_error(xhr, str);
							$('.loading').hide();
							ajax_processing = false;
						}
					});
				}else{console.log('yes');}
				return false;
			}

			$('body').on('click', 'a.reverse', function(){
				if(!ajax_processing){
					$('.loading').show();
					ajax_processing = true;
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: 'battle_ajax.php?board_reverse=Y',
						//data: {},
						success: function(data) {
							//$('.ajax').html(data);
							result = data;
							if(result.message !== undefined){
								add_message(result.message);
							}
							if(result.gamefield !== undefined)
								$('.ajax').html(result.gamefield);
							
							$('.loading').hide();
							ajax_processing = false;
						},
						error:  function(xhr, str){
							add_error(xhr, str);
							$('.loading').hide();
							ajax_processing = false;
						}
					});
				}
				return false;
			});
			
			$('body').on('click', 'input.check_player_status', function(){
				if ($(this).is(":checked")) {
					action = 'ai';
				} else {
					action = 'human';
				}
				pl = $(this).val();
				if(!ajax_processing){
					$('.loading').show();
					ajax_processing = true;
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: 'battle_ajax.php?ai='+pl+'&action='+action,
						//data: {},
						success: function(data) {
							//$('.ajax').html(data);
							
							if(result.message !== undefined){
								//$('.message_note').text(result.message);
								add_message(result.message);
							}
							if(result.gamefield !== undefined)
								$('.ajax').html(result.gamefield);
							
							//setTimeout(function() {alert('timer')}, 1000);
							$('.loading').hide();
							ajax_processing = false;
						},
						error:  function(xhr, str){
							add_error(xhr, str);
							$('.loading').hide();
							ajax_processing = false;
						}
					});
				}
			});

			$('body').on('click', 'div.crush_, div.run_', function(){	// передвижение или ближняя атака (раздавливание)
				if(!ajax_processing){//console.log('no');
					id = $('.unit.selected').find('.id').text();
					arr = $(this).attr('id').split('_');
					y_ = arr[1];
					x_ = arr[2];
					
					$('.loading').show();
					ajax_processing = true;
					myArray = {
						'x': x_,
						'y': y_,
						'id': id,
						'party_pass' : party_pass, 
						'party_id' : party_id
					}
					//new_machine('ajax.php', myArray);
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: 'battle_ajax.php',
						data: myArray,//action: 'crush', 
						success: function(data) {
							result = data;
//console.log(result.gamefield);
							if(result.message !== undefined){
								add_message(result.message);
							}
							if(result.gamefield !== undefined)
								$('.ajax').html(result.gamefield);
							console.log('result.shah_message: '+result.shah_message);
							console.log('typeof(result.shah_message): '+typeof(result.shah_message));
							if(typeof(result.error) == "undefined" && result.change_player === true ){
								console.log('color changed1: '+stepcolor);
								change_color(stepcolor);
								console.log('color changed2: '+stepcolor);
								$('table.player_table').removeClass('active');
								$('.table_'+stepcolor).addClass('active');
								//getResurs();
								/*if(result.resurs !== undefined){
									$.each( result.resurs, function( key, value ) {
										//if(value.defeated === true)
										//	$('.message').prepend('<h1 style="color: red">Игрок '+key+' проиграл!</h1>');
										$.each( value, function( key2, value2 ) {
											if(value2.defeated === true){
												if (document.getElementById('defeat_container') === null) {
												    $('.message').prepend('<h1 id="defeat_container" style="color: red"></h1>');
												}
											}
											//alert( key + ": " + value );
											console.log( key2 + ": " + value2 );
											//$('.message .'+key+'_'+key2).text(value2);
											$.each( value2, function( key3, value3 ) {
												//alert( key + ": " + value );
												console.log( key3 + ": " + value3 );
												$('.message .'+key2+'_'+key3).text(value3);
											});
										});
									});
								}*/
								curent_user_step = false;
                			    $('.ajax').addClass('blur'); 
							}else if(result.error === undefined && result.change_player === false && result.shah_flag_return !== true && result.shah_message === undefined){ // жертва пешки
								if(stepcolor == 'red'){
									oppositcolor = 'black';
								}else{
									oppositcolor = 'red';
								}
								phase_type = 'sacrifice';
								$('.not_sacr').show();
								$('.unit[data-color="'+oppositcolor+'"]:not([data-type="Head"])').addClass('diversion');
								$('.unit[data-id="'+id+'"]').addClass('selected');
								$('.unit[data-id="'+id+'"]').addClass('pulse');
								//id
							}else if(result.shah_flag_return === true){
								$('.status_message').html(result.return_shah);
								//$('div[data-type=Head][data-color='+stepcolor+']').addClass('pulse');
								//$('.status_message_2').fadeIn(1000);
								$('.status_message_2').slideDown(1000);
                                  myAudio = new Audio;
                                  myAudio.src = "perkussiya-odinochnyiy-trevojnyiy.mp3";
                                  myAudio.play();
							}else if(result.shah_message !== undefined){
								$('.status_message').html('Поле бито. '+result.shah_message);
								//$('div[data-type=Head][data-color='+stepcolor+']').addClass('pulse');
								//$('.status_message_2').fadeIn(1000);
								$('.status_message_2').slideDown(1000);
								  myAudio = new Audio;
								  myAudio.src = "perkussiya-odinochnyiy-trevojnyiy.mp3";
								  myAudio.play();
							}
							
							
							if((typeof(step_id) !== undefined && step_id !== null) && (!(data.step_id > step_id)) && result.shah_message === undefined && result.shah_flag_return === undefined){
								more_battle_ajax = true;
								console.log('more_battle_ajax');
            				}else{
             					more_battle_ajax = false;
            				}
							step_id = data.step_id;
				    		$('.step_id').text(data.step_id);
							
							//setTimeout(function() {alert('timer')}, 1000);
							$('.loading').hide();
							ajax_processing = false;
							if(result.next_turn_auto !== undefined){//alert('next_turn_auto');
								$('.cont_'+stepcolor+' .robot_load').show();
								$('.cont_'+stepcolor).addClass('active');
								let timerId = setTimeout(planeAiTurn, 1000);
							}
						},
						error:  function(xhr, str){
							add_error(xhr, str);
							$('.loading').hide();
							ajax_processing = false;
						}
					});
				}else{console.log('yes');}
			});
			
			$('body').on('click', 'div.shot_', function(){	// стрельба

				if(!ajax_processing){
					arr = $(this).attr('id').split('_');
					y_ = arr[1];
					x_ = arr[2];
					id = $('.unit.selected').find('.id').text();
					myArray = {
						'x': x_,
						'y': y_,
						'shot_id': id,
						'party_pass' : party_pass, 
						'party_id' : party_id
					}
					//new_machine('ajax.php', myArray);
					
					$('.loading').show();
					ajax_processing = true;
					$.ajax({
						type: 'POST',
						url: 'battle_ajax.php',
						data: myArray,
						dataType: 'json',
						success: function(data) {
							//$('.ajax').html(data);
							result = data;
							if(result.message !== undefined){
								add_message(result.message);
							}
							if(result.gamefield !== undefined)
								$('.ajax').html(result.gamefield);
							
							if(result.error === undefined && result.change_player === true ){
								console.log('color changed');
								change_color(stepcolor);
								$('table.player_table').removeClass('active');
								$('.table_'+stepcolor).addClass('active');
								//getResurs();
								/*if(result.resurs !== undefined){
									$.each( result.resurs, function( key, value ) {
										//if(value.defeated === true)
										//	$('.message').prepend('<h1 style="color: red">Игрок '+key+' проиграл!</h1>');
										$.each( value, function( key2, value2 ) {
											if(value2.defeated === true){
												if (document.getElementById('defeat_container') === null) {
												    $('.message').prepend('<h1 id="defeat_container" style="color: red"></h1>');
												}
											}
											//alert( key + ": " + value );
											console.log( key2 + ": " + value2 );
											//$('.message .'+key+'_'+key2).text(value2);
											$.each( value2, function( key3, value3 ) {
												//alert( key + ": " + value );
												console.log( key3 + ": " + value3 );
												$('.message .'+key2+'_'+key3).text(value3);
											});
										});
									});
								}*/
								//setTimeout(function() {alert('timer')}, 1000);
								curent_user_step = false;
                			    $('.ajax').addClass('blur'); 
							}
							
							
							if((typeof(step_id) !== undefined && step_id !== null) && (!(data.step_id > step_id)) && result.shah_message === undefined && result.shah_flag_return === undefined){
								more_battle_ajax = true;
								console.log('more_battle_ajax');
            				}else{
             					more_battle_ajax = false;
            				}
							step_id = data.step_id;
				    		$('.step_id').text(data.step_id);
							
							$('.loading').hide();
							ajax_processing = false;
							if(result.next_turn_auto !== undefined){//alert('next_turn_auto');
								$('.cont_'+stepcolor+' .robot_load').show();
								$('.cont_'+stepcolor).addClass('active');
								let timerId = setTimeout(planeAiTurn, 1000);
							}	
						},
						error:  function(xhr, str){
							add_error(xhr, str);
							$('.loading').hide();
							ajax_processing = false;
						}
					});
				}
			});

			$('body').on('click', 'div.diversion', function(){
				//alert('click div.diversion');
				myArray = {
					'saboteur': id,
					'target': $(this).attr('data-id'),
					'party_pass' : party_pass, 
					'party_id' : party_id
				}
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: 'battle_ajax.php',
					data: myArray,//action: 'crush', 
					success: function(data) {
						// < на сокращение. в 2х местах
						result = data;
						if(result.message !== undefined){
							add_message(result.message);
						}
						if(result.gamefield !== undefined)
							$('.ajax').html(result.gamefield);
						console.log('result.error '+result.error+' result.change_player '+result.change_player);
						if(result.error === undefined && result.change_player === true ){ console.log('Yes');
							console.log('color changed');
							change_color(stepcolor);
							$('table.player_table').removeClass('active');
							$('.table_'+stepcolor).addClass('active');
							/*if(result.resurs !== undefined){
								$.each( result.resurs, function( key, value ) {
									$.each( value, function( key2, value2 ) {
										if(value2.defeated === true){
											if (document.getElementById('defeat_container') === null) {
											    $('.message').prepend('<h1 id="defeat_container" style="color: red"></h1>');
											}
										}
										console.log( key2 + ": " + value2 );
										$.each( value2, function( key3, value3 ) {
											console.log( key3 + ": " + value3 );
											$('.message .'+key2+'_'+key3).text(value3);
										});
									});
								});
							}*/
							phase_type = 'simple';
							curent_user_step = false;
                        	$('.ajax').addClass('blur'); 
            				if((typeof(step_id) !== undefined && step_id !== null) && (!(result.step_id > step_id)) && result.shah_message === undefined && result.shah_flag_return === undefined){
            					more_battle_ajax = true;
            					console.log('more_battle_ajax');
            				}else{
             					more_battle_ajax = false;
            				}
				    		$('.step_id').text(result.step_id);
				    		step_id = result.step_id;
						}else{
							console.log('No');
						}
						// > на сокращение. в 2х местах
					}
				});
			});
			//getResurs();
			// > Подгрузка сразу же при открытии страницы
