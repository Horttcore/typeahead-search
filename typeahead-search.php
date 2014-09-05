<?php
/*
Plugin Name: Typeahead Search
Plugin URI: http://horttcore.de
Description: Improve your WordPress search with the typeahead auto completer
Version: 0.1.0
Author: Ralf Hortt
Author URI: http://horttcore.de
License: GPL2
*/

class Typeahead_Search {



	/**
	 * Plugin version number
	 *
	 * @var string
	 **/
	protected $version = '0.1.0';



	/**
	 * Constructor
	 *
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	public function __construct()
	{

		add_action( 'template_redirect', array( $this, 'template_redirect' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_styles' ) );

		add_action( 'wp_ajax_typeahead-search', array( $this, 'bloodhound' ) );
		add_action( 'wp_ajax_nopriv_typeahead-search', array( $this, 'bloodhound' ) );


	} // end __construct



	/**
	 * Build autocomplete index
	 *
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	public function bloodhound()
	{

		if ( FALSE !== ( $autocompletes = get_transient( 'typeahead-search-objects' ) ) /* && 1 == 2 */ )
			die( $autocompletes );

		$query = new WP_Query(array(
			'post_type' => 'page',
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'showposts' => -1,
		));

		if ( $query->have_posts() ) :

			$autocompletes = array();

			$post_types = array();

			while ( $query->have_posts() ) : $query->the_post();

				if ( !isset( $post_types[ get_post_type() ] ) )
					$post_types[get_post_type()] = get_post_type_object( get_post_type() );

				$autocompletes[] = apply_filters( 'typeahead-search-object', array(
					'post_title' => get_the_title(),
					'post_type' => $post_types[get_post_type()]->labels->singular_name,
					'post_date' => get_post_field( 'post_date', get_the_ID() ),
					'permalink' => get_the_permalink(),
				) );

			endwhile;

			set_transient( 'typeahead-search-objects', json_encode( apply_filters( 'typeahead-search-objects', $autocompletes ) ) );

		endif;

		wp_reset_query();

		die( json_encode( apply_filters( 'typeahead-search-objects', $autocompletes ) ) );
	}



	/**
	 * Redirect to page if there is only one result
	 *
	 * @author Ralf Hortt
	 **/
	public function template_redirect()
	{

		if ( !is_search() )
			return;

		global $wp_query;

		if ($wp_query->post_count == 1)
			wp_redirect( get_permalink( $wp_query->posts['0']->ID ) );

	} // end template_redirect



	/**
	 * Register and enqueue script
	 *
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	public function wp_enqueue_scripts()
	{

		// Register
		wp_register_script( 'typeahead-bundle', plugins_url( 'javascript/lib/typeahead.bundle.js', __FILE__ ), array( 'jquery' ), $this->version, TRUE );
		wp_register_script( 'typeahead-search', plugins_url( 'javascript/typeahead.search.js', __FILE__ ), array( 'jquery', 'typeahead-bundle' ), $this->version, TRUE );

		// Adding vars
		wp_localize_script( 'typeahead-search', 'TypeaheadSearch', array(
			'prefetchUrl' => admin_url( 'admin-ajax.php?action=typeahead-search' )
		) );

		// Enqueue script
		wp_enqueue_script( 'typeahead-search' );

	} // end wp_enqueue_scripts



	/**
	 * Register and enqueue styles
	 *
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	public function wp_enqueue_styles()
	{
		// Register
		wp_register_style( 'typeahead-search', plugins_url( 'css/typeahead.search.css', __FILE__ ), FALSE, $this->version, 'screen' );

		// Enqueue style
		wp_enqueue_style( 'typeahead-search' );

	} // end wp_enqueue_styles



}

new Typeahead_Search;
