<?php
/**
 * Blanketter och dokument: en lista där varje rad har en rubrik och en fil att ladda ned.
 *
 * Admin: menyn "Dokument". Lägg till, byt namn på, sortera och ta bort rader, och välj eller
 * ladda upp en fil per rad från mediebiblioteket.
 * Webbplats: kortkoden [hjuviks_dokument] eller Elementor-widgeten "Hjuviks dokument".
 * En rad utan fil visas med texten "Kommer snart" i stället för länken.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HJP_DOK_OPTION', 'hjuviks_dokument' );

/** Startvärden: dokumenten som fanns på sidan när funktionen byggdes. */
function hjp_dok_standard() {
	$rader = array();
	foreach ( array( 'Överlåtelse', 'Uppsägning', 'Andrahandsuthyrning', 'Adressanmälan', 'Ordningsföreskrifter', 'Hamnritning' ) as $titel ) {
		$rader[] = array( 'titel' => $titel, 'fil' => 0 );
	}
	return $rader;
}

/** Alla rader: array( array( 'titel' => string, 'fil' => bilagans id eller 0 ) ). */
function hjp_dok_hamta() {
	$rader = get_option( HJP_DOK_OPTION, null );
	if ( ! is_array( $rader ) ) {
		return hjp_dok_standard();
	}
	return $rader;
}

/** Filens adress, filnamn och typ, eller null om filen saknas eller är borttagen. */
function hjp_dok_fil( $id ) {
	$id = (int) $id;
	if ( ! $id ) {
		return null;
	}
	$url = wp_get_attachment_url( $id );
	if ( ! $url ) {
		return null;
	}
	$sokvag = get_attached_file( $id );
	$typ    = strtoupper( (string) pathinfo( (string) $sokvag, PATHINFO_EXTENSION ) );
	$storlek = ( $sokvag && file_exists( $sokvag ) ) ? size_format( filesize( $sokvag ) ) : '';
	return array(
		'url'     => $url,
		'namn'    => wp_basename( $url ),
		'typ'     => $typ,
		'storlek' => $storlek,
	);
}

/* -------------------------------------------------------------------------
 * Admin
 * ---------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
	add_menu_page(
		'Dokument',
		'Dokument',
		hjp_capability(),
		'hjuviks-dokument',
		'hjp_dok_render_admin',
		'dashicons-media-document',
		22
	);
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'toplevel_page_hjuviks-dokument' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'hjp-admin', HJP_URL . 'assets/admin.css', array(), HJP_VERSION );
	wp_enqueue_style( 'hjp-dok-admin', HJP_URL . 'assets/dokument-admin.css', array( 'hjp-admin' ), HJP_VERSION );
	wp_enqueue_script( 'hjp-dok-admin', HJP_URL . 'assets/dokument-admin.js', array( 'jquery', 'jquery-ui-sortable' ), HJP_VERSION, true );
} );

function hjp_dok_render_rad( $i, $rad ) {
	$fil = hjp_dok_fil( isset( $rad['fil'] ) ? $rad['fil'] : 0 );
	?>
	<div class="hjp-dok-rad" data-rad>
		<span class="hjp-dok-dra dashicons dashicons-menu" title="Dra för att ändra ordning" aria-hidden="true"></span>
		<label class="screen-reader-text" for="hjp-dok-titel-<?php echo esc_attr( $i ); ?>">Rubrik</label>
		<input type="text" class="hjp-dok-titel" id="hjp-dok-titel-<?php echo esc_attr( $i ); ?>" name="dok[<?php echo esc_attr( $i ); ?>][titel]" value="<?php echo esc_attr( $rad['titel'] ); ?>" placeholder="Rubrik, till exempel Överlåtelse">
		<input type="hidden" data-fil name="dok[<?php echo esc_attr( $i ); ?>][fil]" value="<?php echo esc_attr( $fil ? (int) $rad['fil'] : 0 ); ?>">
		<span class="hjp-dok-fil" data-filnamn>
			<?php if ( $fil ) : ?>
				<a href="<?php echo esc_url( $fil['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $fil['namn'] ); ?></a>
				<?php if ( $fil['storlek'] ) : ?><span class="hjp-dok-info"><?php echo esc_html( $fil['storlek'] ); ?></span><?php endif; ?>
			<?php else : ?>
				<span class="hjp-dok-tom">Ingen fil vald</span>
			<?php endif; ?>
		</span>
		<button type="button" class="button" data-valj><?php echo $fil ? 'Byt fil' : 'Välj fil'; ?></button>
		<button type="button" class="button-link hjp-dok-rensa" data-rensa <?php echo $fil ? '' : 'hidden'; ?>>Ta bort filen</button>
		<button type="button" class="button-link hjp-dok-tabort" data-tabort aria-label="Ta bort raden"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
	</div>
	<?php
}

function hjp_dok_render_admin() {
	if ( ! current_user_can( hjp_capability() ) ) {
		return;
	}
	$rader = hjp_dok_hamta();
	?>
	<div class="wrap hjp-wrap">
		<h1 class="hjp-title">Dokument</h1>
		<p class="hjp-intro">Här lägger du upp blanketter och dokument som besökarna kan ladda ned. Skriv en rubrik, klicka på <strong>Välj fil</strong> och ladda upp filen eller välj en som redan finns. Klicka sedan på <strong>Spara dokumenten</strong>. Dra i handtaget till vänster för att ändra ordning. En rad utan fil visas på webbplatsen med texten ”Kommer snart”.</p>

		<?php if ( isset( $_GET['hjp_saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p>Dokumenten är sparade och syns nu på webbplatsen.</p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="hjp-dok-form">
			<input type="hidden" name="action" value="hjp_dok_save">
			<?php wp_nonce_field( 'hjp_dok_save', 'hjp_dok_nonce' ); ?>

			<div class="hjp-dok-lista" data-lista>
				<?php
				foreach ( $rader as $i => $rad ) {
					hjp_dok_render_rad( $i, $rad );
				}
				?>
			</div>

			<p><button type="button" class="button" data-ny>+ Lägg till dokument</button></p>
			<p class="submit"><button type="submit" class="button button-primary button-hero">Spara dokumenten</button></p>
		</form>

		<script type="text/html" id="hjp-dok-mall">
			<?php hjp_dok_render_rad( '__I__', array( 'titel' => '', 'fil' => 0 ) ); ?>
		</script>
	</div>
	<?php
}

add_action( 'admin_post_hjp_dok_save', function () {
	if ( ! current_user_can( hjp_capability() ) ) {
		wp_die( 'Du har inte behörighet att ändra dokumenten.' );
	}
	check_admin_referer( 'hjp_dok_save', 'hjp_dok_nonce' );

	$in    = isset( $_POST['dok'] ) && is_array( $_POST['dok'] ) ? wp_unslash( $_POST['dok'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$rader = array();
	foreach ( $in as $rad ) {
		$titel = isset( $rad['titel'] ) ? sanitize_text_field( $rad['titel'] ) : '';
		$fil   = isset( $rad['fil'] ) ? absint( $rad['fil'] ) : 0;
		if ( $fil && 'attachment' !== get_post_type( $fil ) ) {
			$fil = 0;
		}
		if ( '' === $titel && ! $fil ) {
			continue;
		}
		$rader[] = array( 'titel' => $titel, 'fil' => $fil );
	}
	update_option( HJP_DOK_OPTION, $rader, false );

	wp_safe_redirect( add_query_arg( array( 'page' => 'hjuviks-dokument', 'hjp_saved' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
} );

/* -------------------------------------------------------------------------
 * Webbplats
 * ---------------------------------------------------------------------- */

/** Pilen framför "Ladda ned" (Font Awesome Free 6, arrow-down, CC BY 4.0). */
function hjp_dok_ikon() {
	return '<svg class="hjp-dok-ikon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M169.4 470.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 370.8 224 64c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 306.7L54.6 265.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"/></svg>';
}

function hjp_dok_render( $atts = array() ) {
	$atts = wp_parse_args( $atts, array(
		'lank'  => 'Ladda ned',
		'saknas' => 'Kommer snart',
	) );
	wp_enqueue_style( 'hjp-front' );

	$rader = hjp_dok_hamta();
	if ( ! $rader ) {
		return '';
	}
	$html = '<ul class="hjp-dok">';
	foreach ( $rader as $rad ) {
		$fil   = hjp_dok_fil( isset( $rad['fil'] ) ? $rad['fil'] : 0 );
		$html .= '<li class="hjp-dok-post">';
		$html .= '<p class="hjp-dok-rubrik">' . esc_html( $rad['titel'] ) . '</p>';
		if ( $fil ) {
			$etikett = $atts['lank'] . ' ' . $rad['titel'] . ( $fil['typ'] ? ' (' . $fil['typ'] . ')' : '' );
			$html   .= '<a class="hjp-dok-lank" href="' . esc_url( $fil['url'] ) . '" download aria-label="' . esc_attr( $etikett ) . '">' . hjp_dok_ikon() . '<span>' . esc_html( $atts['lank'] ) . '</span></a>';
		} else {
			$html .= '<span class="hjp-dok-lank hjp-dok-saknas">' . esc_html( $atts['saknas'] ) . '</span>';
		}
		$html .= '</li>';
	}
	$html .= '</ul>';
	return $html;
}

add_shortcode( 'hjuviks_dokument', function ( $atts ) {
	return hjp_dok_render( shortcode_atts( array( 'lank' => 'Ladda ned', 'saknas' => 'Kommer snart' ), $atts ) );
} );

add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	require_once __DIR__ . '/elementor-dokument.php';
	$widgets_manager->register( new HJP_Elementor_Dokument() );
} );
