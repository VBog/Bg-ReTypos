<?php
/**
 * Created by PhpStorm.
 * User: ambulance
 * Date: 25.05.18
 * Time: 19:47
 */

namespace Etersoft\Typos;

/**
 * Class TyposArticle
 *
 * An article object with a text and id
 *
 * @package Etersoft\Typos
 */
class TyposArticle
{
    public $id;
    public $text;

    public $title;
    public $subtitle;

    /**
     * TyposArticle constructor.
     * @param $id   integer Article id
     * @param $text string Article text
     * @param $title string Article title
     * @param $subtitle string Article subtitle
     */
    public function __construct($id, $text, $title, $subtitle)
    {
        $this->id = $id;
        $this->text = $text;
        $this->title = $title;
        $this->subtitle = $subtitle;
    }
}
