<?php
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div id="container">
	<div class="about-us">
		<h1>PROVOKE</h1>
		
		<p>Quisque maximus tristique augue quis dignissim. Integer venenatis mi at tellus semper varius. Aenean ligula risus, pharetra non sem condimentum, rutrum tempor massa. Cras pretium, ex non ullamcorper rhoncus, nulla dolor.</p>
	
		<a href="javascript:;">NEED WORK?</a>
	</div>
</div>


<?php
require_once 'includes/footer.php';
?>
<script>
	$(".menu-toggle").trigger("click").addClass("over-white");
    $("body").ezBgResize({
        img : "img/about.jpg",
        center : true
    });
</script>

</body>
</html>
