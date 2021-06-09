<?php
/**
 * Theme header
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php wp_head(); ?>
    
    <script> </script><!-- to prevent Chrome bug https://bugs.chromium.org/p/chromium/issues/detail?id=332189 -->
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<header class="site-header">
        
    </header>

	<main id="content" class="site-content">
