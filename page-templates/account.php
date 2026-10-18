<?php
/**
 * Template Name: Tài khoản khách hàng
 */
get_header();
if ( is_user_logged_in() ) {
	get_template_part( 'template-parts/account-dashboard' );
} else {
	get_template_part( 'template-parts/account-auth' );
}
get_footer();
