<?php
/**
 * Rollen "Prislista och dokument": en inloggning som bara ser och kan ändra Prislista och Dokument.
 *
 * Behörigheten hjp_hantera styr de två menyerna. Den ges till rollen här samt till administratörer
 * och redaktörer, som hade åtkomst sedan tidigare. Rollen får också ladda upp filer (för dokumenten).
 * För rollen döljs övriga menyer, och adminpanelens startsida leder direkt till Prislista.
 * Profilen (för att byta lösenord) nås via namnet uppe till höger.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HJP_ROLL', 'hjp_redaktor' );
define( 'HJP_ROLL_VERSION', '1' );

add_action( 'init', function () {
	if ( get_option( 'hjp_roll_version' ) === HJP_ROLL_VERSION ) {
		return;
	}
	remove_role( HJP_ROLL );
	add_role( HJP_ROLL, 'Prislista och dokument', array(
		'read'         => true,
		'upload_files' => true,
		'hjp_hantera'  => true,
	) );
	foreach ( array( 'administrator', 'editor' ) as $namn ) {
		$roll = get_role( $namn );
		if ( $roll ) {
			$roll->add_cap( 'hjp_hantera' );
		}
	}
	update_option( 'hjp_roll_version', HJP_ROLL_VERSION );
}, 5 );

/** Sant för en användare som bara har den här rollen (inte administratörer eller redaktörer). */
function hjp_roll_begransad() {
	$anv = wp_get_current_user();
	return $anv && in_array( HJP_ROLL, (array) $anv->roles, true ) && ! current_user_can( 'edit_pages' );
}

// Bara Prislista och Dokument i menyn.
add_action( 'admin_menu', function () {
	if ( ! hjp_roll_begransad() ) {
		return;
	}
	global $menu;
	$behall = array( 'hjuviks-prislista', 'hjuviks-dokument' );
	foreach ( (array) $menu as $post ) {
		if ( isset( $post[2] ) && ! in_array( $post[2], $behall, true ) ) {
			remove_menu_page( $post[2] );
		}
	}
}, 9999 );

// Adminpanelens startsida och mediebiblioteket leder till Prislista. Profilen är kvar.
add_action( 'current_screen', function ( $skarm ) {
	if ( ! hjp_roll_begransad() || wp_doing_ajax() ) {
		return;
	}
	$tillatna = array( 'toplevel_page_hjuviks-prislista', 'toplevel_page_hjuviks-dokument', 'profile' );
	if ( ! in_array( $skarm->id, $tillatna, true ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=hjuviks-prislista' ) );
		exit;
	}
} );

// Efter inloggning: direkt till Prislista.
add_filter( 'login_redirect', function ( $till, $begard, $anv ) {
	if ( $anv instanceof WP_User && in_array( HJP_ROLL, (array) $anv->roles, true ) && ! user_can( $anv, 'edit_pages' ) ) {
		return admin_url( 'admin.php?page=hjuviks-prislista' );
	}
	return $till;
}, 10, 3 );

// Verktygsfältet: bara webbplatsens namn och det egna kontot.
add_action( 'admin_bar_menu', function ( $falt ) {
	if ( ! hjp_roll_begransad() ) {
		return;
	}
	foreach ( array( 'wp-logo', 'new-content', 'comments', 'updates', 'search' ) as $nod ) {
		$falt->remove_node( $nod );
	}
}, 999 );
