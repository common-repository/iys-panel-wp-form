<?php
defined( 'ABSPATH' ) or exit;

// fake post to prevent notices in wp_enqueue_scripts call
$GLOBALS['post'] = new \WP_Post( (object) array( 'filter' => 'raw' ) );

// render simple page with form in it.
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<?php
	wp_enqueue_scripts();
	wp_print_styles();
	wp_print_head_scripts();

	if ( function_exists( 'wp_custom_css_cb' ) ) {
		wp_custom_css_cb();
	}
	?>
	<style type="text/css">
		body{ 
			background: white;
			width: 100%;
			max-width: 100%;
			text-align: left;
		 }

		/* hide all other elements */
		body::before,
		body::after,
		body > *:not(#form-preview) { 
			display:none !important; 
		}

		#form-preview {
			display: block !important;
			width: 100%;
			height: 100%;
			padding: 20px;
			border: 0;
			margin: 0; 
		}
	</style>
</head>
<body class="page-template-default page ">
	<div id="form-preview" class="page type-page status-publish hentry post post-content">
		<?php im4wp_show_form( $form_id ); ?>
	</div>
	<?php wp_footer(); ?>
</body>
</html>
