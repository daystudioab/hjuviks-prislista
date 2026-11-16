<?php
/**
 * Elementor-widget "Hjuviks dokument": listan med blanketter och dokument från menyn Dokument,
 * med utseendet styrt från Stil-fliken. Tomma reglage lämnar tilläggets standardutseende orört.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

class HJP_Elementor_Dokument extends Widget_Base {

	public function get_name() {
		return 'hjuviks_dokument';
	}

	public function get_title() {
		return 'Hjuviks dokument';
	}

	public function get_icon() {
		return 'eicon-document-file';
	}

	public function get_categories() {
		return array( 'general' );
	}

	public function get_keywords() {
		return array( 'dokument', 'blanketter', 'ladda ned', 'filer', 'hjuvik' );
	}

	public function get_style_depends() {
		return array( 'hjp-front' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'innehall', array( 'label' => 'Innehåll' ) );
		$this->add_control( 'info', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => 'Rubriker och filer ändras under <strong>Dokument</strong> i WordPress-menyn.',
			'content_classes' => 'elementor-descriptor',
		) );
		$this->add_control( 'lank', array(
			'label'   => 'Länktext',
			'type'    => Controls_Manager::TEXT,
			'default' => 'Ladda ned',
		) );
		$this->add_control( 'saknas', array(
			'label'   => 'Text när filen saknas',
			'type'    => Controls_Manager::TEXT,
			'default' => 'Kommer snart',
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'stil_lista', array( 'label' => 'Lista', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'kolumner', array(
			'label'     => 'Kolumner',
			'type'      => Controls_Manager::NUMBER,
			'min'       => 1,
			'max'       => 6,
			'selectors' => array( '{{WRAPPER}} .hjp-dok' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
		) );
		$this->add_responsive_control( 'kolgap', array(
			'label'      => 'Avstånd mellan kolumner',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 120 ) ),
			'selectors'  => array( '{{WRAPPER}} .hjp-dok' => 'column-gap: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'padding', array(
			'label'      => 'Padding i raden',
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px' ),
			'selectors'  => array( '{{WRAPPER}} .hjp-dok-post' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'radgap', array(
			'label'      => 'Avstånd mellan rubrik och länk',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
			'selectors'  => array( '{{WRAPPER}} .hjp-dok-post' => 'gap: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_control( 'linje', array(
			'label'     => 'Linjefärg',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .hjp-dok' => '--hjp-dok-linje: {{VALUE}};' ),
		) );
		$this->add_control( 'linje_w', array(
			'label'      => 'Linjens tjocklek',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 6 ) ),
			'selectors'  => array( '{{WRAPPER}} .hjp-dok' => '--hjp-dok-linje-w: {{SIZE}}{{UNIT}};' ),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'stil_rubrik', array( 'label' => 'Rubrik', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'rubrik_typo',
			'selector' => '{{WRAPPER}} .hjp-dok-rubrik',
		) );
		$this->add_control( 'rubrik_farg', array(
			'label'     => 'Färg',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .hjp-dok-rubrik' => 'color: {{VALUE}};' ),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'stil_lank', array( 'label' => 'Länk och ikon', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'lank_typo',
			'selector' => '{{WRAPPER}} .hjp-dok-lank',
		) );
		$this->add_control( 'lank_farg', array(
			'label'     => 'Färg',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .hjp-dok-lank' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'lank_farg_hover', array(
			'label'     => 'Färg vid hovring',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} a.hjp-dok-lank:hover, {{WRAPPER}} a.hjp-dok-lank:focus-visible' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'saknas_farg', array(
			'label'     => 'Färg när filen saknas',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .hjp-dok-saknas' => 'color: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'ikon_storlek', array(
			'label'      => 'Ikonens storlek',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 8, 'max' => 40 ) ),
			'selectors'  => array( '{{WRAPPER}} .hjp-dok-ikon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'ikon_gap', array(
			'label'      => 'Avstånd mellan ikon och text',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
			'selectors'  => array( '{{WRAPPER}} .hjp-dok-lank' => 'gap: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'ikon_lyft', array(
			'label'      => 'Flytta ikonen i höjdled',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => -10, 'max' => 10 ) ),
			'selectors'  => array( '{{WRAPPER}} .hjp-dok-ikon' => 'transform: translateY({{SIZE}}{{UNIT}});' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		// phpcs:ignore WordPress.Security.EscapeOutput -- hjp_dok_render escapar själv.
		echo hjp_dok_render( array(
			'lank'   => '' !== trim( (string) $s['lank'] ) ? $s['lank'] : 'Ladda ned',
			'saknas' => '' !== trim( (string) $s['saknas'] ) ? $s['saknas'] : 'Kommer snart',
		) );
	}
}
