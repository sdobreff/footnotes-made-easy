<?php

// Multisite domain
$main_site = 'example.com';

// Type of Multisite
$subdomain_install = false;

$new_site = 'multisite';

// URL param activated
for ( $i = 0; $i < 800; $i++ ) {

	// Create a new user
	$rand_number = rand( 1, 2000 );
	$username    = 'user-' . $i;
	$password    = 'fake-password';
	// $password = wp_generate_password( 12, false );
	$email   = "email+$i@example.com";
	$user_id = wpmu_create_user( $username, $password, $email );
	// wp_new_user_notification( $user_id, $password );

	// Create site
	if ( $subdomain_install ) {
		$newdomain = "$new_site.$i.$main_site";
		$path      = '/';
	} else {
		$newdomain = $main_site;
		$path      = '/' . $new_site . $i . '/';
	}
	$title   = $new_site . $i;
	$blog_id = wpmu_create_blog( $newdomain, $path, $title, $user_id, array( 'public' => 1 ) );
	echo "New blog with ID = $blog_id";
}

