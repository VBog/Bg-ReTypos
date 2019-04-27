### RETypos ###
Contributors: VBog

Tags: ошибка, опечатка, исправление

Requires at least: 3.0.1

Tested up to: 5.1.0

Stable tag: trunk

License: GPLv2

License URI: http://www.gnu.org/licenses/gpl-2.0.html

Позволяет пользователям Вашего сайта отправлять сообщения об опечатках на его страницах.

## Description ##
Плагин позволяет пользователям Вашего сайта отправлять сообщения об опечатках на его страницах (в тексте, заголовке и аннотации). Основан на React JS и Bootstrap.

На странице сайта при нажатии комбинации клавиш "Ctrl+Enter" будет открыт модальный диалог, с помощью которого пользователи могут отправить сообщение об опечатках.

Сервис состоит из трёх частей:

1. сервер и админка
`https://gitlab.eterfund.ru/eterfund/typoservice`
2. клиент, который подключается на сайт
`https://gitlab.eterfund.ru/eterfund/typos`
3. php-шаблон для встраивания в сайт для исправления опечаток
`https://gitlab.eterfund.ru/eterfund/retypos-adapter`

Админка сервиса: `https://eterfund.ru/api/typos/cp/`

JS-клиент для отправки сообщения об ошибке на сервер: `https://unpkg.com/@etersoft/retypos-webclient` - подгружается плагином.

Для работы сервиса используется Json-RPC PHP client/server библиотека [Simple Json-RPC PHP](https://github.com/matasarei/JsonRPC).
Для загрузки серверной части этой библиотеки используется файл `installJsonRPC.php`.

Следует иметь ввиду, что сервис отправляет запрос на URL https://your-site.ru/project/correctTypo который обрабатывается файлом, `reTypo.php`, расположенном в корне сайта.

При активации плагин автоматически добавляет новое правило перезаписи URL в структуру правил WordPress в файле  `.htaccess`:
```
RewriteRule ^correctTypo$ /propovedi/reTypo.php [L]
```
и удаляет его при деактивации плагина.

Установить баннер на странице можно используя либо шорт-код `[retypos_banner]`, либо функцию `retypos_banner();`:
```php
	<?php if (function_exists('retypos_banner')) echo retypos_banner(); ?>
```

## Changelog ##

= 1.2.0 =

* Добавлен баннер

= 1.1.1 =

* В класс TyposClientInterface добавлена абстрактная функция для очистки текста от тегов и специальных символов.

= 1.1.0 =

* Исправлены логические и программые ошибки в классе `TyposClientInterface`
* Разрешены пользовательские типы записей
* Добавлена обработка `post_excerpt` в качестве `subtitle`


= 1.0.5 =

* Удаляет RewriteRule при деактивации плагина


= 1.0.4 =

* При активации плагин автоматически добавляет правило перезаписи URL в структуру правил WordPress в файле  `.htaccess`


= 1.0.3 =

* Переименовал файл в корне файла correctTypo.php в reTypo.php

= 1.0.2 =

* Проверка IP сервера.

= 1.0.1 =

* Исправлена ошибка определения site_url().
* Исправлен `.htaccess`

= 1.0 =

* Первый релиз плагина.
