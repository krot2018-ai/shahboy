<div class="ajax_machine_container">
	<form method="get" class="ajax_machine">
		<div class="shade"></div>
		<img src="icon/load.gif" class="loader">
		<div><input type="text" class="input_file" name="file" value=""></div>
		<div class="input_block" ><input type="text" class="input_name" name="name[]" value="">:&nbsp;<input class="input_value" type="text" name="value[]" value=""></div>
		<div><button class="new_param_button">Добавить новый параметр</button></div>
		<div><input type="submit" name="submit" value="отправить"></div>
		<div class="message"></div>
		<div><button class="remove_machine_button">Удалить машину</button></div>
		<div class="form_result"></div>
	</form>
	<div><button class="new_machine_button">Новая машина</button></div>
	<div></div>
</div>
<script>
$('body').on('click','.new_param_button', function(){
	new_input_block = '<div class="input_block" ><input class="input_name" type="text" name="name[]" value="">:&nbsp;<input type="text" class="input_value" name="value[]" value=""><button class="delete_param_button">Удалить</button></div>';
	$(this).parent('div').before(new_input_block);
	return false;
});

$('body').on('click','.new_machine_button', function(){
	new_machine_block = $(this).parent('div').prev('.ajax_machine').clone();
	$(this).parent('div').before(new_machine_block);
	return false;
});
$('body').on('click','.remove_machine_button', function(){
	new_machine_block = $(this).closest('form.ajax_machine').remove();
	return false;
});

$('body').on('click','.delete_param_button', function(){
	$(this).parent('div').remove() ;
	return false;
});

function new_machine(file, params_list){
	machine_html = '<form method="get" class="ajax_machine"><div class="shade"></div><img src="icon/load.gif" class="loader"><div><input type="text" class="input_file" name="file" value=""></div><div class="input_block" ><input type="text" class="input_name" name="name[]" value="">:&nbsp;<input class="input_value" type="text" name="value[]" value=""></div><div><button class="new_param_button">Добавить новый параметр</button></div><div><input type="submit" name="submit" value="отправить"></div><div class="message"></div><div><button class="remove_machine_button">Удалить машину</button></div><div class="form_result"></div></form>';
	new_m = $('.ajax_machine_container form.ajax_machine').last().after(machine_html);
	
	//console.log(params_list);
	if(file.length > 0){
		$('.ajax_machine_container form.ajax_machine').last().find('input.input_file').val(file);
	}
	
	//console.log('params.length '+params_list.length);
	
	//if(params_list.length > 0){console.log('params_list');
		$.each( params_list, function(k, v) {
			$('.ajax_machine_container form.ajax_machine').last().find('button.new_param_button').before('<div class="input_block" ><input class="input_name" type="text" name="name[]" value="'+k+'">:&nbsp;<input type="text" class="input_value" name="value[]" value="'+v+'"><button class="delete_param_button">Удалить</button></div>');
		});
		/*params_list.forEach(function(item, index, arr){
			$('.ajax_machine_container form.ajax_machine').last().find('button.new_param_button').before('<div class="input_block" ><input class="input_name" type="text" name="name[]" value="'+index+'">:&nbsp;<input type="text" class="input_value" name="value[]" value="'+item+'"><button class="delete_param_button">Удалить</button></div>');
		});*/
	//}	
}

$(document).on('submit', '.ajax_machine', function() {
	if(!ajax_processing){
		that = this;
		$(that).find('.loader').show();
		$(that).find('.shade').show();
		ajax_processing = true;
		$(that).find('.message').show().text('запрос обрабатывается');
		
		file = $(that).find('.input_file').val();
		pass = getRandomIntFromRange(1, 999999999999);
		params = '';
		$(that).find('.input_block').each(function( index ) {
			cur_param = $(this).find('input.input_name').val();
			if(cur_param.length > 0){
				params += cur_param;
				params += '=';
				params += $(this).find('input.input_value').val();
				params += '&';
			}
		});
		params +=  'pass=' + pass;
		//alert('submit');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: file,
			data: params,
			success: function(data) {
				/*console.log(data);
				arr = JSON.parse(data);
				$('#json').val(data);
				$('div.unit[data-id='+arr['id']+']').addClass('selected');
				$('div#td_' + arr['y'] + '_' + arr['x']).addClass('shot');
				$('.loading').hide();*/
				ajax_processing = false;
				$(that).find('.message').text('запрос обработан').show();
				//$(that).find('.form_result').html(data).show();//$(data).find('.container').eq(0)
				//result = $(data).parse();
				result = data;
				if(data.message !== undefined){
					//console.log('result.message !== undefined');
					$(that).find('.form_result').text(data.message);
					
				}
				/*else
				{
					console.log('result.message === undefined');
				}*/
				if(data.gamefield !== undefined)
					$('.ajax').html(data.gamefield);
				$(that).find('.loader').hide();
				$(that).find('.shade').hide();
			},
			error:  function(xhr, str){
				alert('Возникла ошибка: ' + xhr.responseCode);
				$(that).find('.message').text('Возникла ошибка: ' + xhr.responseCode).show();
				$(that).find('.loader').hide();
				$(that).find('.shade').hide();
				ajax_processing = false;
			}
		});
	}else{
		//console.log('другой запрос уже обрабатывается');
		$(that).find('.message').text('другой запрос уже обрабатывается').show();
	}
	return false;
});
</script>