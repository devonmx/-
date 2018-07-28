
header();

function header() {
	$('.menu-toggle').on('click', function() {
		open_header('menu-action');
	});

	$('.header-actions .close').on('click', function() {
		close_header();
	});

	$('.menu li:has(ul)').children('ul').hide();
	$('.menu li:has(ul)').find('a').on('click', function() {
		var parent = $(this).parent(),
		submenu = $(this).next('ul');

		if (submenu.is(':visible')) {
			parent.find('ul').slideUp(250);
		}

		if (submenu.is(':hidden')) {
			parent.siblings().find('ul').slideUp(250);
			submenu.css('height', 'auto').slideDown(250);
		}

		if (parent.children('ul').length == 0) {
			return true;
		} else {
			return false;
		}
	});
}

function open_header(action) {
	$('.header-overlay').addClass('visible');
	$('.header-actions').addClass('visible');
	$('.menu-toggle').fadeOut();
	$('.header-actions .action.' + action).addClass('visible');

	setTimeout(function() {
		$('body').addClass('noscroll');
	}, 100);
}

function close_header() {
	$('body').removeClass('noscroll');
	$('.header-overlay').removeClass('visible');
	$('.header-actions').removeClass('visible');
	$('.header-actions .action').removeClass('visible');
	$('.menu-toggle').fadeIn();
}

$(window).load(function(){
	$('body').addClass('loaded');
});

$('.gallery').magnificPopup({
		delegate: 'a',
		type: 'image',
		closeOnContentClick: false,
		closeBtnInside: false,
		mainClass: 'mfp-with-zoom mfp-img-mobile',
		image: {
			verticalFit: true,
		},
		gallery: {
			enabled: true
		},
		zoom: {
			enabled: true,
			duration: 300, // don't foget to change the duration also in CSS
			opener: function(element) {
				return element.find('img');
			}
		}
		
	});