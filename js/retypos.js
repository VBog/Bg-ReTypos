jQuery (document).ready(function() {
	if (retypos === undefined) return false;
	if (retypos.container == '') retypos.container = 'body';
	
	// Модальное окно исправления ошибки
	jQuery('<div id="retyposModal" class="retypos"></div>').appendTo('body');
	var retyposHTML = ''+
'	<div class="retypos-content">\n'+
'		<div class="retypos-header">\n'+
'			<span class="retypos-close">&times;</span>\n'+
'			<span class="retypos-title">Система исправления ошибок</span>\n'+
'		</div>\n'+
'		<div class="retypos-body">\n'+
'			<label for="retyposText">Текст с ошибкой:<div id="retyposText"></div></label>\n'+
'			<input id="retyposTypo" type="hidden" value="" />\n'+
'			<input id="retyposContext" type="hidden" value="" />\n'+
'			<label for="retyposCorrected">Ваш вариант исправления:<input id="retyposCorrected" type="text" value="" /></label>\n'+
'			<label for="retyposComment">Комментарий <i>(необязательно)</i>:<input id="retyposComment" type="text" value="" /></label>\n'+
'		</div>\n'+
'		<div class="retypos-footer">\n'+
'			<button id="retyposOk">Ok</button> <button id="retyposCancel">Отмена</button><br>\n'+
'			<p id="retyposMessage"></p>\n'+
'		</div>\n'+
'	</div>\n';
	jQuery('div#retyposModal').html(retyposHTML);

	// Контекстное меню при выделении текста
	jQuery('<div id="retyposContext" class="retypos-contextmenu">Сообщить об ошибке</div>').appendTo('body');


	jQuery(document).bind('selectionchange', function(e){ 
	
		var range, rect;
		var x = 0, y = 0;
		var sel = window.getSelection();
		var str = sel.toString().trim().length;
		if (!sel.isCollapsed && (str > 4 && str < 31)) {
			//	Координаты выбранного текста на странице браузера
			//	http://qaru.site/questions/199247/coordinates-of-selected-text-in-browser-page
			if (sel.rangeCount) {
				range = sel.getRangeAt(0).cloneRange();
				if (range.getBoundingClientRect) {
					rect = range.getBoundingClientRect();
					x = rect.left;
					y = rect.bottom;
					w = rect.width;
				}
				// Возврат к вставке временного элемента
				if (x == 0 && y == 0) {
					var span = document.createElement("span");
					if (span.getBoundingClientRect) {
						// Обеспечим наличие у span размеров и координат,
						// добавив символ пробела нулевой ширины
						span.appendChild( document.createTextNode("\u200b") );
						range.insertNode(span);
						rect = span.getBoundingClientRect();
						x = rect.left;
						y = rect.bottom;
						w = rect.width;
						var spanParent = span.parentNode;
						spanParent.removeChild(span);

						// Склеиваем все сломанные текстовые узлы снова вместе
						spanParent.normalize();
					}
				}
			}
			
			// Задаем положение меню под серединой блока с выделенным текстом 
			var contextmenuWidth = jQuery('div.retypos-contextmenu').outerWidth(true);
			x = jQuery(document).scrollLeft()+x+(w-contextmenuWidth)/2;
			if (x < 0) x = 0;
			if (x > jQuery(window).outerWidth(true) - contextmenuWidth) x = jQuery(window).width() - contextmenuWidth;
			y = jQuery(document).scrollTop()+y+3;
            jQuery('div.retypos-contextmenu')
 			.show()
			.css({
                left:	x+'px',	// Задаем позицию меню на X
                top:	y+'px'	// Задаем позицию меню по Y
            });
			
			jQuery('div.retypos-contextmenu').bind('click', function () {
				// если нажата кнопка "Сообщить об ошибке" всплывающего меню
				openModalWindow();
			});
		} else {
			jQuery('div.retypos-contextmenu').hide();
		}
	});

	// Открыть модальное окно для исправления ошибки
	jQuery(document).keydown(function (e) {
		// если нажаты клавиши Ctrl+Enter
		if (e.ctrlKey && e.keyCode == 13) {	
			if (!openModalWindow()) alert("Выделите в тексте фрагмент, содержащий ошибку, длиной от 5 до 30 символов");
		}
	});
	jQuery('img.retypos-banner').click(function () {
		if (!openModalWindow()) alert("Выделите в тексте фрагмент, содержащий ошибку, длиной от 5 до 30 символов");
	});
	
	var openModalWindow = function () {
		var sel = window.getSelection();
		var str = sel.toString().trim().length;
		if (sel.isCollapsed || (str < 5 || str > 30)) return false;		// Ничего не выделено
		
		var range = sel.getRangeAt(0);
		var startNode = range.startContainer;
		var startOffset = range.startOffset;
		// Выделенный текст с ошибкой
		var typo = range.toString();
		
		// Получить контекст с ошибкой (самый глубокий Node который содержит выделенный текст)
		if (range.commonAncestorContainer.nodeType === Node.TEXT_NODE) {
			var context = range.commonAncestorContainer.textContent;
			var contextText = range.commonAncestorContainer.textContent;
		} else {
			var context = range.commonAncestorContainer.innerText;
			var contextText = range.commonAncestorContainer.innerText;
		}
		// Отображение контекста на экране 
		contextText = contextText.replace(typo, '<span class="selectedTypo">'+typo+'</span>');
	
		// Заполняем тело модального окна
		jQuery('div#retyposText').html(contextText);
		jQuery('input#retyposTypo').val(escapeHtml(typo));
		jQuery('input#retyposContext').val(escapeHtml(context));
		jQuery('input#retyposCorrected').val(typo);
		
		jQuery('div#retyposModal').show();
		
		return true;
	}
	
	// Отправить данные на сервер
	jQuery('button#retyposOk').bind('click', function() {
		if (!jQuery('input').is('#retyposTypo')) return;
		var typo = jQuery('input#retyposTypo').val();
		if (!typo) return;
		var context = jQuery('input#retyposContext').val();
		var correct = jQuery('input#retyposCorrected').val();
		var comment = jQuery('input#retyposComment').val();
		var data = {
			language: "ru",
			url: window.location.href,	// Url of the page with a typo
			text: typo,					// Typo text
			context: context,			// TODO: context
			corrected: correct,			// This is a correct variant
			comment: comment,			// This is a comment for a correction
		}

		// Отправляем
		jQuery.ajax({
			method: "POST",
			data: data,
			url: "//eterfund.ru/api/typos/server.php",
			success: function(result){
				result = JSON.parse(result);
				jQuery('p#retyposMessage').html(result.message);
				console.log("sendRequest success:", result.message);
			},
			error: function (jqXHR, exception) {
				console.log("Status:"+jqXHR.status+" Response:"+jqXHR.responseText+" Exception:"+exception);
				jQuery('p#retyposMessage').html('<span style="color:red;">Ошибка отправки данных.</span>');
			}
		});
		setTimeout(function() {
			jQuery('p#retyposMessage').html("");
			jQuery('div#retyposModal').hide();
		}, 3000);
	});
	
	// Закрыть модальное окно
	jQuery('span.retypos-close').click(function() {
		jQuery('div#retyposModal').hide();
	});
	jQuery('button#retyposCancel').click(function() {
		jQuery('div#retyposModal').hide();
	});

	function escapeHtml(text) {
	  var map = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#039;'
	  };

	  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
	}
});
