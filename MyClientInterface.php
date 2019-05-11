<?php
namespace My; 

use Etersoft\Typos\TyposArticle;
use Etersoft\Typos\TyposClientInterface;

/**
 * Реализация клиентского интерфейса
 */
class MyClientInterface extends TyposClientInterface {

    private $baseUrl;
    private $editPath = "/wp-admin/post.php?action=edit&post=";

	public function __construct()
	{
		$this->baseUrl = site_url();
	}
    /**
     * Должна возвращать текст записи по заданной ссылке на запись.
     *
     * @param string $link  - Ссылка на запись. Пользователь должен определить id записи.
     *
     * @return \Etersoft\Typos\TyposArticle
     *
     * @throws \InvalidArgumentException  - Если пост не может быть получен из ссылки
    */
    protected function getArticleFromLink(string $link)
    {
//		$id = url_to_postid($link);
//		$post = get_post($id);

		$path_parts = pathinfo($link);
		$slug = explode("#", $path_parts['filename']); 				// Удаляем якорь, если есть
		$slug = $slug[0]; 
		if (substr($slug, 0, 2) == 'm-') $slug = substr($slug, 2);	// Обрабатываем бред с "корварами"
		if(RETYPOS_DEBUG) error_log( PHP_EOL . "^getArticleFromLink: slug= ".$slug, 3, RETYPOS_DEBUG_FILE);
		// Список типов записей имеющих страницу во форонте
		$post_types = get_post_types( [ 'publicly_queryable'=>1 ] );
		$post_types['page'] = 'page';       // встроенный тип не имеет publicly_queryable
		unset( $post_types['attachment'] ); // удалим attachment

		$post = get_page_by_path($slug, OBJECT, array_values($post_types));
		if ($post) {
			if(RETYPOS_DEBUG) error_log( PHP_EOL . "^getArticleFromLink: id= ".$post->ID. " title= ".$post->post_title, 3, RETYPOS_DEBUG_FILE);
			return new TyposArticle($post->ID, $post->post_content, $post->post_title, $post->post_excerpt);
		} else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * Должна сохранить данный пост в базе данных.
     *
     * @param \Etersoft\Typos\TyposArticle $article  - Статья
     * @return void
     */
    protected function saveArticle(TyposArticle $article)
    {
		$edited_post = array(
			'ID'			=> $article->id,
			'post_title'	=> $article->title, 
			'post_excerpt'	=> $article->subtitle,
			'post_content'	=> $article->text
		);
		wp_update_post( $edited_post);
 		if(RETYPOS_DEBUG) error_log( PHP_EOL . "^saveArticle: id= ". $article->id. " title= ".$article->title, 3, RETYPOS_DEBUG_FILE);
	}

    /**
     * Должна возвращать ID записи для заданного URL записи
     *
     * @param string $link  - URL записи
     * @return integer  - ID записи
     *
     * @throws \InvalidArgumentException  - Если ID не может быть получен из ссылки
     */
    protected function getArticleIdFromLink(string $link)
    {
        // $link = https://some-site.org/?article=$link

//		$id = url_to_postid($link);

		$path_parts = pathinfo($link);
		$slug = explode("#", $path_parts['filename']); 				// Удаляем якорь, если есть
		$slug = $slug[0]; 
		if (substr($slug, 0, 2) == 'm-') $slug = substr($slug, 2);	// Обрабатываем бред с "корварами"
		if(RETYPOS_DEBUG) error_log( PHP_EOL . "^getArticleIdFromLink: slug= ".$slug, 3, RETYPOS_DEBUG_FILE);
		// Список типов записей имеющих страницу во форонте
		$post_types = get_post_types( [ 'publicly_queryable'=>1 ] );
		$post_types['page'] = 'page';       // встроенный тип не имеет publicly_queryable
		unset( $post_types['attachment'] ); // удалим attachment

		$post = get_page_by_path($slug, OBJECT, array_values($post_types));

		$id = $post->ID;	
 		if(RETYPOS_DEBUG) error_log( PHP_EOL . "^getArticleIdFromLink: id= ".$id, 3, RETYPOS_DEBUG_FILE);

        // Осуществить все необходимые проверки
		// ID не может быть получен из ссылки
        if ($id == 0) {
            throw new \InvalidArgumentException();
        }

        // Возвращает ID записи
        return $id;
    }

    /**
     * Должна возвращать ссылку на редактирование записи с указанным ID
     *
     * @param int $id  - ID записи
     * @return string  - URL редактирования записи
     */
    protected function getArticleEditLink(int $id)
    {
        // https://some-site.org/edit?article=$id
		if(RETYPOS_DEBUG) error_log( PHP_EOL . '^getArticleEditLink:'. "{$this->baseUrl}{$this->editPath}$id", 3, RETYPOS_DEBUG_FILE);

        return $this->baseUrl.$this->editPath.$id;

    }
    /**
     * Очищает текст от тегов, заменяет специальные символы стандартными.
	 * Удаляет пробельные символы из начала и конца текста.
	 * В некотором смысле, функция является обратной для wptexturize(); - функция WordPress.
     *
     * @param string $text
     *
     * @return string       Очищенный текст
     */
    protected function clearText(string $text) {
	
		$option = get_option('retypos_options');
		if (isset($option['clear_off']) && $option['clear_off']) return $text;

		// Strip all tags from text
		$text = strip_tags($text);
		
		/*	Double quotes		*/
		$text = str_replace (array('&#8220;','&#8221;','&#8243;','&#171;','&#187;'), '\"', $text);
		/*	Single quotes		*/
		$text = str_replace (array('&#8218;','&#8219;','&#8242;'), '\'', $text);
		/*	Hyphens	*/
		$text = str_replace (array('&#8209;','&#8210;','&#8211;','&#8212;'), '-', $text);
		/*	Spaces	*/
		$text = preg_replace ('/[\xA0\t\v\f]/u', " ", $text);
		/*	End of line		*/
		$text = preg_replace ('/\r\n|\n\r|\r/u', "\n", $text);

		/*	Dots	*/
		$text = str_replace ('&#8230;', "...", $text);
		/*	Trade Mark	*/
		$text = str_replace ('&#8482;', "(tm)", $text);
		/*	Multiplication sign	*/
		$text = str_replace ('&#215;', "x", $text);

		// Strip duble hyphenes and whitespaces
		$text = preg_replace ('/-{2,}/', "-", $text);
		$text = preg_replace ('/ {2,}/u', " ", $text);
		$text = preg_replace ('/\n{2,}/u', "\n", $text);

		// Strip whitespace from the beginning and end of a string
		$text = trim ($text);

		if(RETYPOS_DEBUG) error_log( PHP_EOL . '^clearText: '. $text, 3, RETYPOS_DEBUG_FILE);
		
		return $text;

	}
}
