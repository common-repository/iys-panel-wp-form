<?php

$email_label       = esc_html__( 'Email address', 'iys-panel-wp-form' );
$email_placeholder = esc_html__( 'Your email address', 'iys-panel-wp-form' );
$signup_button     = esc_html__( 'Sign up', 'iys-panel-wp-form' );

$content  = "<p>\n\t<label>{$email_label}: \n";
$content .= "\t\t<input type=\"email\" name=\"EMAIL\" placeholder=\"{$email_placeholder}\" required />\n</label>\n</p>\n\n";
$content .= "<p>\n\t<input type=\"submit\" value=\"{$signup_button}\" />\n</p>";

return $content;
