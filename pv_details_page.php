<div id="main-img-holder">
<?php
	
		
		$main_title = isset($_POST['title']) ? $_POST['title'] : '';
		$main_content = isset($_POST['content']) ? $_POST['content'] : '';
		$main_image = isset($_POST['main_image']) ? $_POST['main_image'] : '';
		$price = isset($_POST['price']) ? $_POST['price'] : '';
		$currency = isset($_POST['currency']) ? $_POST['currency'] : '';
		$loader = isset($_POST['loader']) ? $_POST['loader'] : "";
		$priceformated = isset($_POST['priceformated']) ? $_POST['priceformated'] : '';
	
		
?>
<div id="content-viewer"> 
<h1><?php echo $main_title; ?></h1>
<p><?php echo $main_content; ?></p>
<?php
if($price > 0) {
?>
	<p id="price"><span class="pv-price"><?php echo $currency; ?></span> <?php echo $priceformated ?></p>
<?php
}
?>
</div>

<div id="pv-loader-holder">
<img class="pv-main-image" src="<?php echo $main_image;  ?>" />

<img class="pv-loader" src="<?php echo $loader; ?>" />
</div>
</div>