<?php
/******************************************************************************************
	Страница настроек плагина
	
*******************************************************************************************/
// Начальные значения
add_option('retypos_options', array('server_ip'=>'91.232.225.9','clear_off'=>0, 'debug'=>0));
$val = get_option('retypos_options');
if (!isset($val['server_ip'])) {
	$val['server_ip'] = '91.232.225.9';
}	
if (!isset($val['clear_off'])) {
	$val['clear_off'] = 0;
}	
if (!isset($val['debug'])) {
	$val['debug'] = 0;
}	
update_option( 'retypos_options', $val );

add_action('admin_menu', 'retypos_add_plugin_page');

function retypos_add_plugin_page(){
	add_options_page( 'Настройки reTypos', 'reTypos', 'manage_options', 'retypos_slug', 'retypos_options_page_output' );
}

function retypos_options_page_output(){
	$val = get_option('retypos_options');

	?>
	<br>
	<div class='notice notice-info'>
		<p>The plugin allows users send messages about typos on your site pages (in the post content, title and excerpt). <a href='https://gitlab.eterfund.ru/VBog/RETypos' target='_blank'>See details here</a>.<br> 
		Based on React JS and Bootstrap. Server and admin desktop: <a href='https://gitlab.eterfund.ru/eterfund/typoservice' target='_blank'>https://gitlab.eterfund.ru/eterfund/typoservice</a>. Webclient: <a href='https://gitlab.eterfund.ru/eterfund/typoservice' target='_blank'>https://gitlab.eterfund.ru/eterfund/typos</a></p>
	</div>
	<div class="wrap">
		<h2><?php echo get_admin_page_title() ?></h2>
		<p>Версия <b><?php echo	RETYPOS_VERSION; ?></b></p>
		<form action="options.php" method="POST">
		<?php
			settings_fields( 'retypos_option_group' );	// скрытые защитные поля
			do_settings_sections( 'retypos_page' ); 		// секции с настройками (опциями) 'section_1'
			submit_button();
		?>
		</form>
	</div>
	<?php
}

/**
 * Регистрируем настройки.
 * Настройки будут храниться в массиве, а не одна настройка = одна опция.
 */
add_action('admin_init', 'retypos_settings');
function retypos_settings(){
	// параметры: $option_group, $option_name, $sanitize_callback
	register_setting( 'retypos_option_group', 'retypos_options', 'retypos_sanitize_callback' );

	// параметры: $id, $title, $callback, $page
	add_settings_section( 'section_1','Основные параметры', '', 'retypos_page' ); 

	// параметры: $id, $title, $callback, $page, $section, $args
	add_settings_field('retypos_server_ip', 'IP сервера', 'fill_retypos_server_ip', 'retypos_page', 'section_1' );
	add_settings_field('retypos_clear_off', 'Отключить очистку текста', 'fill_retypos_clear_off', 'retypos_page', 'section_1' );
	add_settings_field('retypos_debug', 'Включить отладку', 'fill_retypos_debug', 'retypos_page', 'section_1' );
}

## Заполняем опцию 1
function fill_retypos_server_ip(){
	$val = get_option('retypos_options');
	$val = $val['server_ip']; 
	?>
	<input type="text" name="retypos_options[server_ip]" value="<?php echo esc_attr( $val ) ?>" size="60" /><br>
	(укажите IP разрешенного сервера. По умолчанию 91.232.225.9)
	<?php
}
## Заполняем опцию 2
function fill_retypos_clear_off(){
	$val = get_option('retypos_options');
	$val = $val ? $val['clear_off'] : null;
	?>
	<label><input type="checkbox" name="retypos_options[clear_off]" value="1" <?php checked(1, $val ); ?>/> отметьте, чтобы отключить очистку контекста и текста. </label><br>
	По умолчанию перед поиском контекста в тексте статьи производится удаление всех html-тегов, замена разных видов двойных и одинарных кавычек и дефисов на стандартные, 
	всех пробельных символов на пробел, а конец строки - на \n, удаляются задвоенные дефисы, пробелы и символы конца строки, а также пробельные символы в начале и конце текста.
	<?php
}
## Заполняем опцию 3
function fill_retypos_debug(){
	$val = get_option('retypos_options');
	$val = $val ? $val['debug'] : null;
	?>
	<label><input type="checkbox" name="retypos_options[debug]" value="1" <?php checked(1, $val ); ?>/> отметьте, чтобы в консоли отображалась отладочная информация </label>
	<?php
}
## Очистка данных
function retypos_sanitize_callback( $options ){ 
	// очищаем
	foreach( $options as $name => &$val ){
		
		if( $name == 'server_ip') {
			$num = explode('.', $val );
			if (count($num) == 4) {
				$val = intval( $num[0] ).'.'. intval( $num[1] ).'.'. intval( $num[2] ).'.'. intval( $num[3] );
			} else {
				$val = '91.232.225.9';
			}
		} else {
			$val = intval( $val );
		}
	}
	return $options;
}
