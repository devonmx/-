<?php
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>
<div id="container">
	<div id="gallery" class="gallery">
		
	</div>
	<div class='icon-scroll'></div>
	<div id="limite" class="clearfix"></div>
</div>


<?php
require_once 'includes/footer.php';
?>
<script>
	$(".menu-toggle").trigger("click").addClass("over-white");
	$(".header-actions").addClass("gris");
	$("body").ezBgResize({
		img : "img/about.jpg",
		center : true
	});

	var photo = [{"id":990, "category":"hombre"}, {"id":776, "category":"mujer"}, {"id":836, "category":"hombre"}, {"id":143, "category":"mujer"}, {"id":960, "category":"hombre"}, {"id":765, "category":"pareja"}, {"id":806, "category":"pareja"}, {"id":591, "category":"pareja"}, {"id":518, "category":"mujer"}, {"id":598, "category":"mujer"}, {"id":583, "category":"pareja"}, {"id":287, "category":"hombre"}, {"id":484, "category":"pareja"}, {"id":106, "category":"hombre"}, {"id":157, "category":"pareja"}, {"id":856, "category":"pareja"}, {"id":841, "category":"pareja"}, {"id":379, "category":"hombre"}, {"id":853, "category":"mujer"}, {"id":886, "category":"hombre"}, {"id":945, "category":"hombre"}, {"id":908, "category":"pareja"}, {"id":316, "category":"pareja"}, {"id":519, "category":"mujer"}, {"id":584, "category":"hombre"}, {"id":970, "category":"mujer"}, {"id":834, "category":"mujer"}, {"id":134, "category":"mujer"}, {"id":726, "category":"pareja"}, {"id":358, "category":"pareja"}, {"id":247, "category":"mujer"}, {"id":147, "category":"hombre"}, {"id":856, "category":"pareja"}, {"id":841, "category":"pareja"}, {"id":379, "category":"mujer"}, {"id":853, "category":"mujer"}, {"id":886, "category":"pareja"}, {"id":945, "category":"mujer"}, {"id":908, "category":"hombre"}, {"id":316, "category":"pareja"}, {"id":519, "category":"hombre"}, {"id":584, "category":"pareja"}, {"id":970, "category":"pareja"}, {"id":834, "category":"hombre"}, {"id":134, "category":"pareja"}, {"id":726, "category":"mujer"}, {"id":358, "category":"hombre"}, {"id":247, "category":"mujer"}, {"id":147, "category":"pareja"}, {"id":990, "category":"mujer"}, {"id":776, "category":"hombre"}, {"id":836, "category":"pareja"}, {"id":143, "category":"pareja"}, {"id":960, "category":"mujer"}, {"id":765, "category":"mujer"}, {"id":806, "category":"mujer"}, {"id":591, "category":"pareja"}, {"id":518, "category":"hombre"}, {"id":598, "category":"pareja"}, {"id":583, "category":"mujer"}, {"id":287, "category":"mujer"}, {"id":484, "category":"mujer"}, {"id":990, "category":"pareja"}, {"id":776, "category":"pareja"}, {"id":836, "category":"hombre"}, {"id":143, "category":"pareja"}, {"id":960, "category":"mujer"}, {"id":765, "category":"mujer"}, {"id":806, "category":"mujer"}, {"id":591, "category":"pareja"}, {"id":518, "category":"pareja"}, {"id":598, "category":"mujer"}, {"id":583, "category":"pareja"}, {"id":287, "category":"mujer"}, {"id":484, "category":"pareja"}, {"id":106, "category":"mujer"}, {"id":157, "category":"pareja"}, {"id":856, "category":"hombre"}, {"id":841, "category":"pareja"}, {"id":379, "category":"hombre"}, {"id":853, "category":"mujer"}, {"id":886, "category":"pareja"}, {"id":945, "category":"mujer"}, {"id":908, "category":"pareja"}, {"id":316, "category":"pareja"}, {"id":519, "category":"hombre"}, {"id":584, "category":"hombre"}, {"id":970, "category":"hombre"}, {"id":834, "category":"pareja"}, {"id":134, "category":"mujer"}, {"id":726, "category":"mujer"}, {"id":358, "category":"pareja"}, {"id":247, "category":"pareja"}, {"id":147, "category":"pareja"}, {"id":856, "category":"hombre"}, {"id":841, "category":"mujer"}, {"id":379, "category":"pareja"}, {"id":853, "category":"mujer"}, {"id":886, "category":"pareja"}, {"id":945, "category":"mujer"}, {"id":908, "category":"pareja"}, {"id":316, "category":"pareja"}, {"id":519, "category":"pareja"}, {"id":584, "category":"mujer"}, {"id":970, "category":"pareja"}, {"id":834, "category":"pareja"}, {"id":134, "category":"mujer"}, {"id":726, "category":"mujer"}, {"id":358, "category":"mujer"}, {"id":247, "category":"pareja"}, {"id":147, "category":"pareja"}, {"id":990, "category":"mujer"}, {"id":776, "category":"pareja"}, {"id":836, "category":"pareja"}, {"id":143, "category":"mujer"}, {"id":960, "category":"hombre"}, {"id":765, "category":"hombre"}, {"id":806, "category":"pareja"}, {"id":591, "category":"hombre"}, {"id":518, "category":"pareja"}, {"id":598, "category":"hombre"}, {"id":583, "category":"mujer"}, {"id":287, "category":"hombre"}, {"id":484, "category":"mujer"} ];

	loadPhoto();

	function loadPhoto(page){
		if(!page){
			$.each(photo, function(i, item){
				$('.gallery').append(`<a href="https://picsum.photos/400?image=${item.id}" class="mix all ${item.category}"><img src="https://picsum.photos/400?image=${item.id}" alt="Image"></a>`);
				// return i<39;
			});
			console.log("Nuevos");
		}else{
			var lng = $('#gallery a').length-1;
			$.each(photo, function(i, item){
				if(i>lng){
					$('.gallery').append(`<a href="https://picsum.photos/400?image=${item.id}" class="mix all ${item.category}"><img src="https://picsum.photos/400?image=${item.id}" alt="Image"></a>`);
				}
				return i<lng+20;
			});
		}
	}

	$(window).scroll(function() {
		if($(window).scrollTop() + $(window).height() == $(document).height()) {
			// loadPhoto(1);
			$('.icon-scroll').fadeOut();
		}else{
			$('.icon-scroll').fadeIn();
		}
	});

	// RESIZE Y AJUSTE AL TAMAÑO DE LA VENTANA //
	function wwidth() {
		var windowWidth = $(window).innerWidth();
		if(windowWidth > 1300){
			$('#gallery').addClass('more-pics');
		}else{
			$('#gallery').removeClass('more-pics');
		}
	}

	wwidth();
	$(window).resize(function () {
		wwidth();
	});

	$('.header .close').remove();

	//FILTER
    var targetSelector = '.mix';

    function getSelectorFromHash() {
        var hash = window.location.hash.replace(/^#/g, '');

        var selector = hash ? '.' + hash : targetSelector;

        return selector;
    }

    function setHash(state) {
        var selector = state.activeFilter.selector;
        var newHash = '#' + selector.replace(/^./g, '');

        if (selector === targetSelector && window.location.hash) {
            // Equivalent to filter "all", remove the hash

            history.pushState(null, document.title, window.location.pathname);
        } else if (newHash !== window.location.hash && selector !== targetSelector) {
            // Change the hash

            history.pushState(null, document.title, window.location.pathname + newHash);
        }
    }

    var mixer = mixitup('#gallery', {
        selectors: {
            target: targetSelector
        },
        load: {
            filter: getSelectorFromHash()
        },
        callbacks: {
            onMixEnd: setHash
        }
    });

    window.onhashchange = function() {
        var selector = getSelectorFromHash();
        if (selector === mixer.getState().activeFilter.selector) return; // no change
        mixer.filter(selector);
    };
</script>
</body>
</html>

