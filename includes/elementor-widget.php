<?php
/**
 * Elementor-widget "Hjuviks prislista": samma flikprislista som [hjuviks_prislista stil="flikar"],
 * men med utseendet styrt från Elementors Stil-flik (typsnitt, färger, storlekar, padding, per enhet).
 * Tomma reglage lämnar tilläggets standardutseende orört.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

class HJP_Elementor_Prislista extends Widget_Base {

	public function get_name() {
		return 'hjuviks_prislista';
	}

	public function get_title() {
		return 'Hjuviks prislista';
	}

	public function get_icon() {
		return 'eicon-price-list';
	}

	public function get_categories() {
		return array( 'general' );
	}

	public function get_keywords() {
		return array( 'prislista', 'priser', 'hjuvik', 'båtplatser', 'flikar' );
	}

	public function get_style_depends() {
		return array( 'hjp-front' );
	}

	public function get_script_depends() {
		return array( 'hjp-flikar' );
	}

	/** Reglage för typsnitt plus färg på samma element. */
	private function text_reglage( $id, $rubrik, $selektor ) {
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $id . '_typo',
				'label'    => $rubrik . ': typsnitt',
				'selector' => $selektor,
			)
		);
		$this->add_control(
			$id . '_farg',
			array(
				'label'     => $rubrik . ': färg',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $selektor => 'color: {{VALUE}};' ),
			)
		);
	}

	protected function register_controls() {
		$data    = function_exists( 'hjp_get_data' ) ? hjp_get_data() : array( 'grupper' => array() );
		$grupper = array( '' => 'Alla grupper (en flik per grupp)' );
		foreach ( $data['grupper'] as $grupp ) {
			if ( ! empty( $grupp['titel'] ) ) {
				$grupper[ $grupp['titel'] ] = $grupp['titel'];
			}
		}

		/* ---------------- Innehåll ---------------- */
		$this->start_controls_section( 'innehall', array( 'label' => 'Prislista' ) );

		$this->add_control(
			'grupp',
			array(
				'label'   => 'Visa',
				'type'    => Controls_Manager::SELECT,
				'options' => $grupper,
				'default' => '',
			)
		);
		$this->add_control(
			'moms',
			array(
				'label'   => 'Moms',
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'bada' => 'Båda priserna i egna kolumner',
					''     => 'Reglage: inkl. eller exkl. moms',
				),
				'default' => 'bada',
			)
		);
		$this->add_control(
			'noter',
			array(
				'label'        => 'Visa noter under listan',
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'info',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => 'Priser, grupper och noter redigeras under <a href="' . esc_url( admin_url( 'admin.php?page=hjuviks-prislista' ) ) . '" target="_blank">Prislista</a> i wp-admin. Utseendet styrs under Stil.',
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->end_controls_section();

		/* ---------------- Stil: Flikar ---------------- */
		$this->start_controls_section(
			'stil_flikar',
			array(
				'label' => 'Flikar',
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'flik_justering',
			array(
				'label'     => 'Placering',
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start'  => array(
						'title' => 'Vänster',
						'icon'  => 'eicon-text-align-left',
					),
					'safe center' => array(
						'title' => 'Mitten',
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'    => array(
						'title' => 'Höger',
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'safe center',
				'selectors' => array(
					'{{WRAPPER}} .hjp-f-topp'   => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .hjp-f-flikar' => 'justify-content: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'flik_typo',
				'label'    => 'Typsnitt',
				'selector' => '{{WRAPPER}} .hjp-f-flik',
			)
		);
		$this->add_responsive_control(
			'flik_padding',
			array(
				'label'      => 'Padding',
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .hjp-f-flik' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'flik_hojd',
			array(
				'label'      => 'Minsta höjd',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 24, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .hjp-f-flik' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'flik_radie',
			array(
				'label'      => 'Rundning',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .hjp-f-flik' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'flik_kant',
			array(
				'label'      => 'Kantlinje',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 4, 'step' => 0.5 ) ),
				'selectors'  => array( '{{WRAPPER}} .hjp-f-flik' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;' ),
			)
		);
		$this->add_responsive_control(
			'flik_mellanrum',
			array(
				'label'      => 'Mellanrum mellan flikar',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors'  => array( '{{WRAPPER}} .hjp-f-flikar' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'flik_under',
			array(
				'label'      => 'Avstånd under flikarna',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .hjp-f-topp' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->start_controls_tabs( 'flik_lagen' );
		foreach ( array(
			'normal' => array( 'Normal', '{{WRAPPER}} .hjp-f-flik' ),
			'hover'  => array( 'Hovring', '{{WRAPPER}} .hjp-f-flik:hover' ),
			'vald'   => array( 'Vald', '{{WRAPPER}} .hjp-f-flik[aria-selected="true"]' ),
		) as $lage => $info ) {
			$this->start_controls_tab( 'flik_' . $lage, array( 'label' => $info[0] ) );
			$this->add_control(
				'flik_' . $lage . '_text',
				array(
					'label'     => 'Textfärg',
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'color: {{VALUE}};' ),
				)
			);
			$this->add_control(
				'flik_' . $lage . '_bg',
				array(
					'label'     => 'Bakgrund',
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'background-color: {{VALUE}};' ),
				)
			);
			$this->add_control(
				'flik_' . $lage . '_kant',
				array(
					'label'     => 'Kantfärg',
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'border-color: {{VALUE}};' ),
				)
			);
			$this->end_controls_tab();
		}
		$this->end_controls_tabs();
		$this->end_controls_section();

		/* ---------------- Stil: Kolumnrubriker ---------------- */
		$this->start_controls_section(
			'stil_kolumner',
			array(
				'label'     => 'Kolumnrubriker',
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'moms' => 'bada' ),
			)
		);
		$this->text_reglage( 'kol', 'Rubriker', '{{WRAPPER}} .hjp-f-kolumner' );
		$this->add_responsive_control(
			'kol_under',
			array(
				'label'      => 'Avstånd till linjen',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors'  => array( '{{WRAPPER}} .hjp-f-kolumner' => 'padding-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'kol_linje_info',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => 'Linjen under rubrikerna följer linjerna mellan raderna (Stil › Rader).',
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->end_controls_section();

		/* ---------------- Stil: Rader ---------------- */
		$this->start_controls_section(
			'stil_rader',
			array(
				'label' => 'Rader',
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->text_reglage( 'namn', 'Benämning', '{{WRAPPER}} .hjp-f-namn' );
		$this->add_control( 'avdelare_period', array( 'type' => Controls_Manager::DIVIDER ) );
		$this->text_reglage( 'period', 'Period', '{{WRAPPER}} .hjp-f-period' );
		$this->add_control( 'avdelare_inkl', array( 'type' => Controls_Manager::DIVIDER ) );
		$this->text_reglage( 'inkl', 'Pris inkl. moms', '{{WRAPPER}} .hjp-f-pris-inkl, {{WRAPPER}} .hjp-f-pris' );
		$this->add_control( 'avdelare_exkl', array( 'type' => Controls_Manager::DIVIDER ) );
		$this->text_reglage( 'exkl', 'Pris exkl. moms', '{{WRAPPER}} .hjp-f-pris-exkl, {{WRAPPER}} .hjp-f-exkl' );
		$this->add_control( 'avdelare_rad', array( 'type' => Controls_Manager::DIVIDER ) );
		$this->add_responsive_control(
			'rad_padding',
			array(
				'label'      => 'Radhöjd (luft över och under)',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors'  => array( '{{WRAPPER}} .hjp-f-rad' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'rad_kolgap',
			array(
				'label'      => 'Mellanrum mellan kolumner',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .hjp-flikar' => '--hjp-f-kolgap: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'moms' => 'bada' ),
			)
		);
		$this->add_responsive_control(
			'rad_priskol',
			array(
				'label'      => 'Priskolumnernas bredd',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array(
					'px'  => array( 'min' => 60, 'max' => 260 ),
					'rem' => array( 'min' => 4, 'max' => 16, 'step' => 0.5 ),
				),
				'selectors'  => array( '{{WRAPPER}} .hjp-flikar' => '--hjp-f-priskol: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'moms' => 'bada' ),
			)
		);
		$this->add_control(
			'rad_linje_farg',
			array(
				'label'     => 'Linjer: färg',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .hjp-flikar' => '--hjp-f-line: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'rad_linje_bredd',
			array(
				'label'      => 'Linjer: tjocklek',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 4, 'step' => 0.5 ) ),
				'selectors'  => array( '{{WRAPPER}} .hjp-flikar' => '--hjp-f-line-w: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'rad_hover',
			array(
				'label'     => 'Bakgrund vid hovring',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .hjp-flikar' => '--hjp-f-hover: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'rad_inset',
			array(
				'label'       => 'Hovringsytans luft i sidled',
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 32 ) ),
				'selectors'   => array( '{{WRAPPER}} .hjp-flikar' => '--hjp-f-rad-inset: {{SIZE}}{{UNIT}};' ),
				'condition'   => array( 'moms' => 'bada' ),
			)
		);
		$this->end_controls_section();

		/* ---------------- Stil: Momsreglage ---------------- */
		$this->start_controls_section(
			'stil_moms',
			array(
				'label'     => 'Momsreglage',
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'moms' => '' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'moms_typo',
				'label'    => 'Typsnitt',
				'selector' => '{{WRAPPER}} .hjp-f-moms button',
			)
		);
		$this->add_control(
			'moms_text',
			array(
				'label'     => 'Textfärg',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .hjp-f-moms button' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'moms_vald_bg',
			array(
				'label'     => 'Valt läge: bakgrund',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .hjp-f-moms button[aria-pressed="true"]' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'moms_vald_text',
			array(
				'label'     => 'Valt läge: textfärg',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .hjp-f-moms button[aria-pressed="true"]' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		/* ---------------- Stil: Noter ---------------- */
		$this->start_controls_section(
			'stil_noter',
			array(
				'label'     => 'Noter',
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'noter' => 'yes' ),
			)
		);
		$this->text_reglage( 'noter', 'Noter', '{{WRAPPER}} .hjp-f-noter' );
		$this->add_responsive_control(
			'noter_over',
			array(
				'label'      => 'Avstånd ovanför',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .hjp-f-noter' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! function_exists( 'hjp_render_flikar' ) ) {
			return;
		}
		$s    = $this->get_settings_for_display();
		$data = hjp_get_data();
		$atts = array(
			'grupp'  => isset( $s['grupp'] ) ? (string) $s['grupp'] : '',
			'rubrik' => 'nej',
			'noter'  => ( isset( $s['noter'] ) && 'yes' === $s['noter'] ) ? 'ja' : 'nej',
			'stil'   => 'flikar',
			'tema'   => '',
			'moms'   => isset( $s['moms'] ) ? (string) $s['moms'] : 'bada',
			'id'     => $this->get_id(),
		);
		echo hjp_render_flikar( $data, hjp_filtrera_grupper( $data['grupper'], $atts['grupp'] ), $atts ); // phpcs:ignore WordPress.Security.EscapeOutput -- byggs med esc_* i hjp_render_flikar.
	}
}
