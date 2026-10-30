<?php
/**
 * Plugin Name: Hjuviks Marina – Prislista
 * Description: Enkel prislista för båtplatser, sjöbodar, parkering och avgifter. Redigera under menyn "Prislista" och visa listan med kortkoden [hjuviks_prislista].
 * Version: 1.1.0
 * Update URI: https://github.com/daystudioab/hjuviks-prislista
 * Author: Studio Day
 * Text Domain: hjuviks-prislista
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uppdateringar från GitHub (daystudioab/hjuviks-prislista).
 * Nya versioner publiceras som GitHub-releaser och visas under Tillägg som vanliga uppdateringar.
 * Privat repo: lägg in en läsnyckel i wp-config.php:
 *   define( 'STUDIODAY_GITHUB_TOKEN', 'github_pat_…' );
 */
require_once __DIR__ . '/lib/plugin-update-checker/plugin-update-checker.php';

$hjp_uppdaterare = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/daystudioab/hjuviks-prislista/',
	__FILE__,
	'hjuviks-prislista'
);
$hjp_uppdaterare->getVcsApi()->enableReleaseAssets( '/hjuviks-prislista\\.zip$/' );
if ( defined( 'STUDIODAY_GITHUB_TOKEN' ) && STUDIODAY_GITHUB_TOKEN ) {
	$hjp_uppdaterare->setAuthentication( STUDIODAY_GITHUB_TOKEN );
}

define( 'HJP_VERSION', '1.1.0' );
define( 'HJP_OPTION', 'hjuviks_prislista' );
define( 'HJP_URL', plugin_dir_url( __FILE__ ) );

/**
 * Startvärden – prislistan från gamla hjuviksmarina.se.
 */
function hjp_default_data() {
	$season = '1 april – 31 oktober';
	return array(
		'rubrik'      => 'Prislista',
		'sasong'      => '',
		'moms'        => 25,
		'visa_exmoms' => 1,
		'uppdaterad'  => '',
		'grupper'     => array(
			array(
				'titel' => 'Båtplatser',
				'rader' => array(
					array( 'namn' => 'Y-bom 2,0 m', 'period' => $season, 'pris' => 3546 ),
					array( 'namn' => 'Y-bom 2,5 m', 'period' => $season, 'pris' => 5239 ),
					array( 'namn' => 'Y-bom 3,0 m', 'period' => $season, 'pris' => 6151 ),
					array( 'namn' => 'Y-bom 3,5 m', 'period' => $season, 'pris' => 7246 ),
					array( 'namn' => 'M-bom 4,0 m', 'period' => $season, 'pris' => 10895 ),
					array( 'namn' => 'Längs kaj 31 fot', 'period' => $season, 'pris' => 11759 ),
					array( 'namn' => 'Längs kaj 34 fot', 'period' => $season, 'pris' => 13070 ),
					array( 'namn' => 'Längs kaj 37 fot', 'period' => $season, 'pris' => 14383 ),
					array( 'namn' => 'Längs kaj 40 fot', 'period' => $season, 'pris' => 15696 ),
					array( 'namn' => 'Längs kaj 44 fot', 'period' => $season, 'pris' => 17300 ),
					array( 'namn' => 'Längs ponton 50 fot', 'period' => $season, 'pris' => 18101 ),
				),
			),
			array(
				'titel' => 'Sjöbod och parkering',
				'rader' => array(
					array( 'namn' => 'Sjöbod', 'period' => '1 april – 31 mars', 'pris' => 5892 ),
					array( 'namn' => 'Parkeringsplats', 'period' => $season, 'pris' => 2734 ),
				),
			),
			array(
				'titel' => 'Administrationsavgifter',
				'rader' => array(
					array( 'namn' => 'Andrahandsuthyrning', 'period' => 'Per kontrakt och säsong', 'pris' => 240 ),
					array( 'namn' => 'Överlåtelse', 'period' => 'Per kontrakt', 'pris' => 400 ),
				),
			),
		),
		'noter'       => "Årliga säsongsavgifter är knutna till nyttjanderättsavtal med insatslån.\nFör vinterbåtplats och/eller vinterparkering 1 november – 31 mars tillkommer 50 % av ovanstående priser.",
	);
}

function hjp_get_data() {
	$data = get_option( HJP_OPTION );
	if ( ! is_array( $data ) || empty( $data['grupper'] ) ) {
		$data = hjp_default_data();
	}
	return wp_parse_args( $data, hjp_default_data() );
}

register_activation_hook( __FILE__, function () {
	if ( false === get_option( HJP_OPTION ) ) {
		add_option( HJP_OPTION, hjp_default_data(), '', false );
	}
} );

/**
 * Behörighet: redaktörer och administratörer.
 */
function hjp_capability() {
	return apply_filters( 'hjp_capability', 'edit_pages' );
}

/* -------------------------------------------------------------------------
 * Admin
 * ---------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
	add_menu_page(
		'Prislista',
		'Prislista',
		hjp_capability(),
		'hjuviks-prislista',
		'hjp_render_admin',
		'dashicons-tag',
		21
	);
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'toplevel_page_hjuviks-prislista' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'hjp-admin', HJP_URL . 'assets/admin.css', array(), HJP_VERSION );
	wp_enqueue_script( 'hjp-admin', HJP_URL . 'assets/admin.js', array( 'jquery', 'jquery-ui-sortable' ), HJP_VERSION, true );
} );

function hjp_format_kr( $value ) {
	return number_format( (float) $value, 0, ',', "\xC2\xA0" ) . "\xC2\xA0kr";
}

function hjp_render_row( $g, $r, $row, $moms ) {
	$pris = isset( $row['pris'] ) ? $row['pris'] : '';
	$inkl = ( '' === $pris ) ? '' : hjp_format_kr( round( (float) $pris * ( 1 + $moms / 100 ) ) );
	?>
	<tr class="hjp-row">
		<td class="hjp-handle" title="Dra för att flytta"><span class="dashicons dashicons-menu"></span></td>
		<td><input type="text" class="hjp-namn" name="grupper[<?php echo esc_attr( $g ); ?>][rader][<?php echo esc_attr( $r ); ?>][namn]" value="<?php echo esc_attr( isset( $row['namn'] ) ? $row['namn'] : '' ); ?>" placeholder="T.ex. Y-bom 2,0 m"></td>
		<td><input type="text" class="hjp-period" name="grupper[<?php echo esc_attr( $g ); ?>][rader][<?php echo esc_attr( $r ); ?>][period]" value="<?php echo esc_attr( isset( $row['period'] ) ? $row['period'] : '' ); ?>" placeholder="T.ex. 1 april – 31 oktober"></td>
		<td class="hjp-pris-cell"><input type="number" min="0" step="1" class="hjp-pris" name="grupper[<?php echo esc_attr( $g ); ?>][rader][<?php echo esc_attr( $r ); ?>][pris]" value="<?php echo esc_attr( $pris ); ?>"><span class="hjp-suffix">kr</span></td>
		<td class="hjp-inkl"><?php echo esc_html( $inkl ); ?></td>
		<td class="hjp-actions"><button type="button" class="button-link hjp-remove-row" aria-label="Ta bort raden"><span class="dashicons dashicons-trash"></span></button></td>
	</tr>
	<?php
}

function hjp_render_group( $g, $grupp, $moms ) {
	?>
	<div class="hjp-group" data-g="<?php echo esc_attr( $g ); ?>">
		<div class="hjp-group-head">
			<span class="hjp-group-handle dashicons dashicons-move" title="Dra för att flytta gruppen"></span>
			<input type="text" class="hjp-group-title" name="grupper[<?php echo esc_attr( $g ); ?>][titel]" value="<?php echo esc_attr( isset( $grupp['titel'] ) ? $grupp['titel'] : '' ); ?>" placeholder="Gruppens rubrik">
			<button type="button" class="button-link hjp-remove-group">Ta bort grupp</button>
		</div>
		<table class="hjp-table">
			<thead>
				<tr>
					<th class="hjp-handle"></th>
					<th>Benämning</th>
					<th>Period</th>
					<th>Pris exkl. moms</th>
					<th>Inkl. moms</th>
					<th></th>
				</tr>
			</thead>
			<tbody class="hjp-rows">
				<?php
				$rader = isset( $grupp['rader'] ) ? $grupp['rader'] : array();
				foreach ( array_values( $rader ) as $r => $row ) {
					hjp_render_row( $g, $r, $row, $moms );
				}
				?>
			</tbody>
		</table>
		<button type="button" class="button hjp-add-row"><span class="dashicons dashicons-plus-alt2"></span> Lägg till rad</button>
	</div>
	<?php
}

function hjp_render_admin() {
	if ( ! current_user_can( hjp_capability() ) ) {
		return;
	}
	$data = hjp_get_data();
	$moms = (float) $data['moms'];
	?>
	<div class="wrap hjp-wrap">
		<h1 class="hjp-title">Prislista</h1>
		<p class="hjp-intro">Ändra priserna nedan och klicka på <strong>Spara prislistan</strong>. Ange priser exklusive moms – priset inklusive moms räknas ut automatiskt. Listan visas på webbplatsen där kortkoden <code>[hjuviks_prislista]</code> är placerad.</p>

		<?php if ( isset( $_GET['hjp_saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p>Prislistan är sparad och syns nu på webbplatsen.</p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="hjp-form" data-moms="<?php echo esc_attr( $moms ); ?>">
			<input type="hidden" name="action" value="hjp_save">
			<?php wp_nonce_field( 'hjp_save', 'hjp_nonce' ); ?>

			<div class="hjp-card hjp-settings">
				<div class="hjp-field">
					<label for="hjp-rubrik">Rubrik</label>
					<input type="text" id="hjp-rubrik" name="rubrik" value="<?php echo esc_attr( $data['rubrik'] ); ?>">
				</div>
				<div class="hjp-field">
					<label for="hjp-sasong">Gäller för säsong</label>
					<input type="text" id="hjp-sasong" name="sasong" value="<?php echo esc_attr( $data['sasong'] ); ?>" placeholder="T.ex. 2027">
				</div>
				<div class="hjp-field hjp-field-small">
					<label for="hjp-moms">Moms</label>
					<span class="hjp-inline"><input type="number" id="hjp-moms" name="moms" min="0" max="100" step="1" value="<?php echo esc_attr( $moms ); ?>"> %</span>
				</div>
				<div class="hjp-field hjp-field-check">
					<label><input type="checkbox" name="visa_exmoms" value="1" <?php checked( ! empty( $data['visa_exmoms'] ) ); ?>> Visa även pris exkl. moms på webbplatsen</label>
				</div>
			</div>

			<div id="hjp-groups">
				<?php
				foreach ( array_values( $data['grupper'] ) as $g => $grupp ) {
					hjp_render_group( $g, $grupp, $moms );
				}
				?>
			</div>
			<button type="button" class="button hjp-add-group"><span class="dashicons dashicons-plus-alt2"></span> Lägg till grupp</button>

			<div class="hjp-card">
				<div class="hjp-field">
					<label for="hjp-noter">Villkor och noter</label>
					<textarea id="hjp-noter" name="noter" rows="4"><?php echo esc_textarea( $data['noter'] ); ?></textarea>
					<p class="description">En not per rad. Visas under prislistan.</p>
				</div>
			</div>

			<div class="hjp-savebar">
				<?php submit_button( 'Spara prislistan', 'primary large', 'submit', false ); ?>
				<?php if ( ! empty( $data['uppdaterad'] ) ) : ?>
					<span class="hjp-updated">Senast sparad <?php echo esc_html( date_i18n( 'j F Y \k\l. H:i', (int) $data['uppdaterad'] ) ); ?></span>
				<?php endif; ?>
			</div>
		</form>

		<script type="text/template" id="hjp-row-template"><?php hjp_render_row( '__G__', '__R__', array( 'namn' => '', 'period' => '', 'pris' => '' ), $moms ); ?></script>
		<script type="text/template" id="hjp-group-template"><?php hjp_render_group( '__G__', array( 'titel' => '', 'rader' => array( array( 'namn' => '', 'period' => '', 'pris' => '' ) ) ), $moms ); ?></script>
	</div>
	<?php
}

add_action( 'admin_post_hjp_save', function () {
	if ( ! current_user_can( hjp_capability() ) ) {
		wp_die( 'Du har inte behörighet att ändra prislistan.' );
	}
	check_admin_referer( 'hjp_save', 'hjp_nonce' );

	$in      = wp_unslash( $_POST );
	$grupper = array();

	if ( ! empty( $in['grupper'] ) && is_array( $in['grupper'] ) ) {
		foreach ( $in['grupper'] as $grupp ) {
			$titel = isset( $grupp['titel'] ) ? sanitize_text_field( $grupp['titel'] ) : '';
			$rader = array();
			if ( ! empty( $grupp['rader'] ) && is_array( $grupp['rader'] ) ) {
				foreach ( $grupp['rader'] as $row ) {
					$namn = isset( $row['namn'] ) ? sanitize_text_field( $row['namn'] ) : '';
					if ( '' === $namn ) {
						continue;
					}
					$pris    = isset( $row['pris'] ) && '' !== $row['pris'] ? max( 0, round( (float) str_replace( array( ' ', ',' ), array( '', '.' ), $row['pris'] ) ) ) : '';
					$rader[] = array(
						'namn'   => $namn,
						'period' => isset( $row['period'] ) ? sanitize_text_field( $row['period'] ) : '',
						'pris'   => $pris,
					);
				}
			}
			if ( '' === $titel && empty( $rader ) ) {
				continue;
			}
			$grupper[] = array(
				'titel' => $titel,
				'rader' => $rader,
			);
		}
	}

	$data = array(
		'rubrik'      => isset( $in['rubrik'] ) ? sanitize_text_field( $in['rubrik'] ) : '',
		'sasong'      => isset( $in['sasong'] ) ? sanitize_text_field( $in['sasong'] ) : '',
		'moms'        => isset( $in['moms'] ) ? min( 100, max( 0, (float) $in['moms'] ) ) : 25,
		'visa_exmoms' => empty( $in['visa_exmoms'] ) ? 0 : 1,
		'uppdaterad'  => current_time( 'timestamp' ),
		'grupper'     => $grupper,
		'noter'       => isset( $in['noter'] ) ? sanitize_textarea_field( $in['noter'] ) : '',
	);

	update_option( HJP_OPTION, $data, false );

	wp_safe_redirect( add_query_arg( 'hjp_saved', '1', admin_url( 'admin.php?page=hjuviks-prislista' ) ) );
	exit;
} );

/* -------------------------------------------------------------------------
 * Webbplats – kortkod
 * ---------------------------------------------------------------------- */

add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'hjp-front', HJP_URL . 'assets/front.css', array(), HJP_VERSION );
} );

/**
 * [hjuviks_prislista] – hela listan.
 * [hjuviks_prislista grupp="Båtplatser"] – bara en grupp.
 * [hjuviks_prislista rubrik="nej"] – utan rubrik.
 */
add_shortcode( 'hjuviks_prislista', function ( $atts ) {
	$atts = shortcode_atts(
		array(
			'grupp'  => '',
			'rubrik' => 'ja',
			'noter'  => 'ja',
		),
		$atts,
		'hjuviks_prislista'
	);

	wp_enqueue_style( 'hjp-front' );

	$data    = hjp_get_data();
	$moms    = (float) $data['moms'];
	$exmoms  = ! empty( $data['visa_exmoms'] );
	$grupper = $data['grupper'];

	if ( '' !== $atts['grupp'] ) {
		$grupper = array_filter( $grupper, function ( $grupp ) use ( $atts ) {
			return 0 === strcasecmp( trim( $grupp['titel'] ), trim( $atts['grupp'] ) );
		} );
	}

	ob_start();
	?>
	<div class="hjp-prislista">
		<?php if ( 'nej' !== $atts['rubrik'] && ( $data['rubrik'] || $data['sasong'] ) ) : ?>
			<div class="hjp-head">
				<?php if ( $data['rubrik'] ) : ?>
					<span class="hjp-head-title"><?php echo esc_html( $data['rubrik'] ); ?></span>
				<?php endif; ?>
				<?php if ( $data['sasong'] ) : ?>
					<span class="hjp-head-season">Säsong <?php echo esc_html( $data['sasong'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php foreach ( $grupper as $grupp ) : ?>
			<section class="hjp-grupp">
				<?php if ( ! empty( $grupp['titel'] ) ) : ?>
					<span class="hjp-grupp-titel"><?php echo esc_html( $grupp['titel'] ); ?></span>
				<?php endif; ?>
				<table class="hjp-tabell">
					<thead class="hjp-sr">
						<tr><th scope="col">Benämning</th><th scope="col">Period</th><th scope="col">Pris</th></tr>
					</thead>
					<tbody>
						<?php foreach ( $grupp['rader'] as $row ) : ?>
							<tr>
								<th scope="row" class="hjp-namn"><?php echo esc_html( $row['namn'] ); ?></th>
								<td class="hjp-period"><?php echo esc_html( $row['period'] ); ?></td>
								<td class="hjp-pris">
									<?php if ( '' !== $row['pris'] ) : ?>
										<span class="hjp-inkl"><?php echo esc_html( hjp_format_kr( round( (float) $row['pris'] * ( 1 + $moms / 100 ) ) ) ); ?></span>
										<?php if ( $exmoms ) : ?>
											<span class="hjp-ex"><?php echo esc_html( hjp_format_kr( $row['pris'] ) ); ?> exkl. moms</span>
										<?php endif; ?>
									<?php else : ?>
										<span class="hjp-inkl">Pris på förfrågan</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</section>
		<?php endforeach; ?>

		<?php
		$noter = array_filter( array_map( 'trim', explode( "\n", (string) $data['noter'] ) ) );
		if ( 'nej' !== $atts['noter'] && $noter ) :
			?>
			<ul class="hjp-noter">
				<li>Alla priser anges inklusive <?php echo esc_html( rtrim( rtrim( number_format( $moms, 2, ',', '' ), '0' ), ',' ) ); ?>&nbsp;% moms.</li>
				<?php foreach ( $noter as $not ) : ?>
					<li><?php echo esc_html( $not ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
	return trim( ob_get_clean() );
} );

/**
 * Elementors atomära text-widgetar rensar bort tabeller och klasser ur
 * kortkoders utdata. Därför ersätts [hjuviks_prislista] i den färdiga
 * sidans HTML i stället – skriv kortkoden som vanlig text i valfri widget.
 */
function hjp_replace_in_html( $html ) {
	if ( ! is_string( $html ) || false === strpos( $html, '[hjuviks_prislista' ) ) {
		return $html;
	}
	return preg_replace_callback(
		'/\[hjuviks_prislista([^\]]*)\]/',
		function ( $m ) {
			$attrs = html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' );
			$attrs = str_replace( array( '”', '“', '″', '„' ), '"', $attrs );
			$attrs = wp_strip_all_tags( $attrs );
			return do_shortcode( '[hjuviks_prislista' . $attrs . ']' );
		},
		$html
	);
}
add_filter( 'elementor/frontend/the_content', 'hjp_replace_in_html', 20 );
add_filter( 'the_content', 'hjp_replace_in_html', 99 );

/**
 * Länk till prislistan direkt från tilläggslistan.
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=hjuviks-prislista' ) ) . '">Redigera prislistan</a>' );
	return $links;
} );
