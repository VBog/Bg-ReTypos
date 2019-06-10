<?php
/* 
    Plugin Name: Retypos 
    Plugin URI: https://gitlab.eterfund.ru/eterfund/
    Description: Позволяет пользователям Вашего сайта отправлять сообщения об опечатках на его страницах.
    Version: 1.3.3
    Author: VBog
    Author URI: https://bogaiskov.ru 
	License:     GPL2
	GitHub Plugin URI: https://github.com/VBog/Bg-ReTypos/
*/

/*  Copyright 2019  Vadim Bogaiskov  (email: vadim.bogaiskov@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/*****************************************************************************************
	Блок загрузки плагина
	
******************************************************************************************/

// Запрет прямого запуска скрипта
if ( !defined('ABSPATH') ) {
	die( 'Sorry, you are not allowed to access this page directly.' ); 
}

define('RETYPOS_VERSION', '1.3.3');
define('RETYPOS_DEBUG_FILE', dirname(__FILE__ )."/retypos.log");

//	options - страница настроек плагина
require_once __DIR__ .'/inc/options.php';

$option = get_option('retypos_options');
define('RETYPOS_DEBUG', $option['debug']?true:false );
define('RETYPOS_SERVER_IP', $option['server_ip']);

// JS скрипт 
function retypos_enqueue_frontend_scripts () {
/**************************************************************************************	
	Retypos-Webclient - это виджет, который позволяет пользователям Вашего сайта 
	отправлять сообщения об опечатках на его страницах. Основан на React JS и Bootstrap.
	На странице сайта при нажатии комбинации клавиш "Ctrl+Enter" будет открыт модальный диалог, 
	с помощью которого пользователи могут отправить сообщение об опечатках.
***************************************************************************************/
	if ( is_single() || is_page()) 
		wp_enqueue_script( 'retypos_proc', "https://unpkg.com/@etersoft/retypos-webclient", false, RETYPOS_VERSION, true );
}	 
if ( !is_admin() ) {
	add_action( 'wp_enqueue_scripts' , 'retypos_enqueue_frontend_scripts' ); 
}
// Функция, исполняемая при активации плагина
function retypos_activate() {
	if (file_exists(get_home_path().'/reTypo.php')) unlink (get_home_path().'/reTypo.php');
	reTypo_rewrite_rule();
	flush_rewrite_rules();
}
// Добавляет новое правило перезаписи URL (ЧПУ) в структуру правил WordPress: 
// заменить correctTypo на correctTypo.php в папаке плагина (см. .htaccess)
function reTypo_rewrite_rule() {
	add_rewrite_rule('correctTypo$', str_replace(site_url('/'), '', plugins_url( 'correctTypo.php', __FILE__ )));
}
register_activation_hook( __FILE__, 'retypos_activate' );

// Функция, исполняемая при деактивации плагина
function retypos_deactivate() {
	// Сбрасываем настройки ЧПУ, чтобы они пересоздались с новыми данными
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'retypos_deactivate' );

//	JsonRPC - библиотека поддержки  протокола удалённого вызова процедур, 
//	использующий JSON для кодирования сообщений. 
require_once __DIR__ .'/installJsonRPC.php';

//	TyposClient - компонент, который обрабатывает запросы на исправление опечаток
//	и автоматически применяет их к тексту
require_once __DIR__ .'/src/TyposClient.php';

//	TyposClientInterface - компонент, чьи абстрактные методы необходимо 
//	реализовать пользователю и передать в качестве зависимости TyposClient
require_once __DIR__ .'/src/TyposClientInterface.php';


//	TyposArticle - объект, представление статьи. Состоит из уникального id и текста
require_once __DIR__ .'/src/TyposArticle.php';

//	MyClientInterface - реализация клиентского интерфейса
require_once __DIR__ .'/MyClientInterface.php';

// Баннер
function retypos_banner() {
	if ( is_single() || is_page()) {
?>	
<img src="<?php echo plugin_dir_url( __FILE__ ); ?>img/retypos.png" alt="Выделите текст и нажмите Ctrl-Enter, если заметили опечатку" title="Выделите текст и нажмите Ctrl-Enter, если заметили опечатку">
<?php
	}
}
// Регистрируем шорт-код [retypos_banner]
add_shortcode( 'retypos_banner', 'retypos_banner_func' );
function retypos_banner_func( ) {
	ob_start();
	retypos_banner();
	return ob_get_clean();
}
