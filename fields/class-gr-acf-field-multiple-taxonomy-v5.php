<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('gr_acf_field_multiple_taxonomy') ) :


class gr_acf_field_multiple_taxonomy extends acf_field {

	// vars
	var $save_post_terms = array();
	var $saved_all_taxonomies = array();
	var $saved_taxonomy_set_choices = array();
	var $saved_taxonomy_choices = array();

	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function __construct( $settings ) {

		$this->name = 'multiple_taxonomy';
		$this->label = __('Multiple Taxonomy', 'acf-multiple-taxonomy');
		$this->category = 'relational';
		$this->defaults = array(
			'taxonomy'      => [],
			'field_type'    => 'multi_select',
			'multiple'      => 0,
			'allow_null'    => 0,
			'return_format' => 'id',
			'load_terms'    => 0,
			'save_terms'    => 0,
		);
		$this->l10n = array(
			'error'	=> __('Error!', 'acf-multiple-taxonomy'),
		);
		$this->settings = $settings;

		// register filter variations
		acf_add_filter_variations( 'acf/fields/multiple_taxonomy/query',  array('name', 'key'), 1 );
		acf_add_filter_variations( 'acf/fields/multiple_taxonomy/result', array('name', 'key'), 2 );

		// ajax
		add_action('wp_ajax_acf/fields/multiple_taxonomy/query',        array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/multiple_taxonomy/query', array($this, 'ajax_query'));

		// actions
		add_action('acf/save_post', array($this, 'save_post'), 15, 1);

		// do not delete!
		parent::__construct();

	}


	/*
	*  ajax_query
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/

	function ajax_query() {

		// validate
		if( !acf_verify_ajax() ) die();


		// get choices
		$response = $this->get_ajax_query( $_POST );


		// return
		acf_send_ajax_results($response);

	}


	/*
	*  get_ajax_query
	*
	*  This function will return an array of data formatted for use in a select2 AJAX response
	*
	*  @type	function
	*  @date	15/10/2014
	*  @since	5.0.9
	*
	*  @param	$options (array)
	*  @return	(array)
	*/

	function get_ajax_query( $options = array() ) {

   	// defaults
   	$options = acf_parse_args( $options, array(
			'post_id'   => 0,
			's'         => '',
			'field_key' => '',
			'paged'     => 0,
		));


		// load field
		$field = acf_get_field( $options['field_key'] );
		if( !$field ) return false;


		// get all taxonomy if no taxonomy
		if( ! $field['taxonomy'] ) {

			$field['taxonomy'] = acf_get_taxonomies();

		}


		// vars
		$results = array();

		foreach( $field['taxonomy'] as $taxonomy ) {

			$is_hierarchical = is_taxonomy_hierarchical( $taxonomy );
			$is_pagination = ( $options['paged'] > 0 );
			$is_search = false;
			$limit = 20;
			$offset = 20 * ( $options['paged'] - 1 );


			// args
			$args = array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			);


			// pagination
			// - don't bother for hierarchial terms, we will need to load all terms anyway
			if( $is_pagination && !$is_hierarchical ) {

				$args['number'] = $limit;
				$args['offset'] = $offset;

			}


			// search
			if( $options['s'] !== '' ) {

				// strip slashes (search may be integer)
				$s = wp_unslash( strval( $options['s'] ) );


				// update vars
				$args['search'] = $s;
				$is_search = true;

			}


			// filters
			$args = apply_filters('acf/fields/multiple_taxonomy/query', $args, $field, $options['post_id'] );


			// get terms
			$terms = acf_get_terms( $args );


			// sort into hierarchical order!
			if( $is_hierarchical ) {

				// update vars
				$limit  = acf_maybe_get( $args, 'number', $limit );
				$offset = acf_maybe_get( $args, 'offset', $offset );


				// get parent
				$parent = acf_maybe_get( $args, 'parent', 0 );
				$parent = acf_maybe_get( $args, 'child_of', $parent );


				// this will fail if a search has taken place because parents wont exist
				if( !$is_search ) {

					// order terms
					$ordered_terms = _get_term_children( $parent, $terms, $taxonomy );


					// check for empty array (possible if parent did not exist within original data)
					if( !empty( $ordered_terms ) ) {

						$terms = $ordered_terms;

					}

				}


				// fake pagination
				if( $is_pagination ) {

					$terms = array_slice( $terms, $offset, $limit );

				}

			}

			if( count( $terms ) === 0 ) continue;


			// get and set taxonomy label
			$taxonomy_label = get_taxonomy( $taxonomy )->label;
			$data = array(
				'text' => $taxonomy_label,
				'children' => array(),
			);

			// append to r
			foreach( $terms as $term ) {

				// add to json
				$data['children'][] = array(
					'id'   => $term->term_id,
					'text' => $this->get_term_title( $term, $field, $options['post_id'] )
				);

			}

			$results[] = $data;

			if( count( $results, 1 ) >= $limit ) break;

		}


		// vars
		$response = array(
			'results' => $results,
			'limit'   => $limit,
		);


		// return
		return $response;

	}


	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field_settings( $field ) {


		// taxonomy
		acf_render_field_setting( $field, array(
			'label'        => __('Taxonomy','acf'),
			'instructions' => __('Select the taxonomy to be displayed','acf'),
			'type'         => 'select',
			'name'         => 'taxonomy',
			'choices'      => acf_get_taxonomy_labels(),
			'multiple'     => 1,
			'ui'           => 1,
			'allow_null'   => 1,
			'placeholder'  => __("All taxonomy",'acf'),
		));


		// field_type
		acf_render_field_setting( $field, array(
			'label'	       => __('Appearance','acf'),
			'instructions' => __('Select the appearance of this field','acf'),
			'type'         => 'select',
			'name'         => 'field_type',
			'optgroup'     => true,
			'choices'      => array(
				__("Multiple Values",'acf') => array(
					'checkbox'     => __('Checkbox', 'acf'),
					'multi_select' => __('Multi Select', 'acf')
				),
				__("Single Value",'acf') => array(
					'radio'        => __('Radio Buttons', 'acf'),
					'select'       => _x('Select', 'noun', 'acf')
				)
			)
		));


		// allow_null
		acf_render_field_setting( $field, array(
			'label'        => __('Allow Null?','acf'),
			'instructions' => '',
			'name'         => 'allow_null',
			'type'         => 'true_false',
			'ui'           => 1,
		));


		// save_terms
		acf_render_field_setting( $field, array(
			'label'        => __('Save Terms','acf'),
			'instructions' => __('Connect selected terms to the post','acf'),
			'name'         => 'save_terms',
			'type'         => 'true_false',
			'ui'           => 1,
		));


		// load_terms
		acf_render_field_setting( $field, array(
			'label'	       => __('Load Terms','acf'),
			'instructions' => __('Load value from posts terms','acf'),
			'name'         => 'load_terms',
			'type'         => 'true_false',
			'ui'           => 1,
		));


		// return_format
		acf_render_field_setting( $field, array(
			'label'	       => __('Return Value','acf'),
			'instructions' => '',
			'type'         => 'radio',
			'name'         => 'return_format',
			'choices'      => array(
				'object'     => __("Term Object",'acf'),
				'id'         => __("Term ID",'acf')
			),
			'layout'       => 'horizontal',
		));

	}



	/*
	*  prepare_field
	*
	*  This function will prepare the field for input
	*
	*  @type	function
	*  @date	14/2/17
	*  @since	5.5.8
	*
	*  @param	$field (array)
	*  @return	(int)
	*/

	function prepare_field( $field ) {

		// get all taxonomy when no taxonomy
		if( ! $field['taxonomy'] ) {

			$field['taxonomy'] = $this->saved_all_taxonomies === [] ? acf_get_taxonomies() : $this->saved_all_taxonomies;

		}

		// prepare choices
		$choices = $this->prepare_choices( $field );

		// Set type
		if( $field['field_type'] === 'select' ) {

			$field['type'] = 'select';
			$field['multiple'] = 0;

		} elseif( $field['field_type'] === 'multi_select' ) {

			$field['type'] = 'select';
			$field['multiple'] = 1;

		} elseif( $field['field_type'] === 'radio' ) {

			$field['type'] = 'radio';
			$field['layout'] = 'vertical';
			$field['other_choice'] = 0;

		} elseif( $field['field_type'] === 'checkbox' ) {

			$field['type'] = 'checkbox';
			$field['layout'] = 'vertical';
			$field['toggle'] = 0;
			$field['allow_custom'] = 0;

		}


    // Add choices and ajax
		$field['choices'] = $choices;
		$field['ajax'] = 1;
		$field['ajax_action'] = 'acf/fields/multiple_taxonomy/query';

		// Always enable "Stylized UI" option
		$field['ui'] = 1;

    // Let ACF handle the rest
		return $field;
	}


	/**
	 * Returns choices
	 *
	 * @param array $field
	 * @return array
	 */
	function prepare_choices( $field ) {

		$taxonomies = (array)$field['taxonomy'];
		if( $taxonomies === [] ) return [];

		$taxonomy_key = implode( '--', $taxonomies );
		if( isset( $this->saved_taxonomy_set_choices[ $taxonomy_key ] ) ) return $this->saved_taxonomy_set_choices[ $taxonomy_key ];

		// prepare choices
		$choices = array();

		foreach( $taxonomies as $taxonomy ) {

			if( isset( $this->saved_taxonomy_choices[ $taxonomy ] ) ) {
				$choices = $choices + $this->saved_taxonomy_choices[ $taxonomy ];
				continue;
			}

			// args
			$args = array(
				'taxonomy'		=> $taxonomy,
				'hide_empty'	=> false
			);

			// get terms
			$terms = acf_get_terms( $args );


			// make choices
			$taxonomy_choices = array();
			foreach( $terms as $term ) {

				if ( is_array( $term ) ) continue;
				$taxonomy_choices[ $term->term_id ] = $this->get_term_title( $term, $field );

			}

			// set choices
			$choices = $choices + $taxonomy_choices;
			$this->saved_taxonomy_choices[ $taxonomy ] = $taxonomy_choices;

		}

		$this->saved_taxonomy_set_choices[ $taxonomy_key ] = $choices;
		return $choices;
	}


	/**
	 * Returns the Term's title displayed in the field UI.
	 *
	 * @date	1/11/2013
	 * @since	5.0.0
	 *
	 * @param	WP_Term $term The term object.
	 * @param	array $field The field settings.
	 * @param	mixed $post_id The post_id being edited.
	 * @return	string
	 */
	function get_term_title( $term, $field, $post_id = 0 ) {
		$title = acf_get_term_title( $term );

		// Default $post_id to current post being edited.
		$post_id = $post_id ? $post_id : acf_get_form_data('post_id');

		/**
		 * Filters the term title.
		 *
		 * @date	1/11/2013
		 * @since	5.0.0
		 *
		 * @param	string $title The term title.
		 * @param	WP_Term $term The term object.
		 * @param	array $field The field settings.
		 * @param	(int|string) $post_id The post_id being edited.
		 */
		 return apply_filters('acf/fields/multiple_taxonomy/result', $title, $term, $field, $post_id);
	}


	/*
	*  get_terms
	*
	*  This function will return an array of terms for a given field value
	*
	*  @type	function
	*  @date	13/06/2014
	*  @since	5.0.0
	*
	*  @param	$value (array)
	*  @return	$value
	*/

	function get_terms( $value, $taxonomy = 'category' ) {

		// load terms in 1 query to save multiple DB calls from following code
		if( count($value) > 1 ) {

			$terms = acf_get_terms(array(
				'taxonomy'		=> $taxonomy,
				'include'		=> $value,
				'hide_empty'	=> false
			));

		}


		// update value to include $post
		foreach( array_keys($value) as $i ) {

			$value[ $i ] = get_term( $value[ $i ] );

		}


		// filter out null values
		$value = array_filter($value);


		// return
		return $value;
	}


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	function load_value( $value, $post_id, $field ) {

		// get valid terms
		foreach( (array)$field['taxonomy'] as $taxonomy ) {

			$value = acf_get_valid_terms( $value, $taxonomy );

		}


		// load_terms
		if( $field['load_terms'] ) {

			// Decode $post_id for $type and $id.
			extract( acf_decode_post_id($post_id) );
			if( $type === 'block' ) {
				// Get parent block...
			}

			// get terms
			$term_ids = wp_get_object_terms( $id, $field['taxonomy'], array('fields' => 'ids', 'orderby' => 'none') );


			// bail early if no terms
			if( empty($term_ids) || is_wp_error($term_ids) ) return false;


			// sort
			if( !empty($value) ) {

				$order = array();

				foreach( $term_ids as $i => $v ) {

					$order[ $i ] = array_search($v, $value);

				}

				array_multisort($order, $term_ids);

			}


			// update value
			$value = $term_ids;

		}


		// convert back from array if necessary
		if( $field['field_type'] == 'select' || $field['field_type'] == 'radio' ) {

			$value = array_shift($value);

		}


		// return
		return $value;

	}


	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	function update_value( $value, $post_id, $field ) {

		// vars
		if( is_array($value) ) {

			$value = array_filter($value);

		}


		// save_terms
		if( $field['save_terms'] ) {

			foreach( (array)$field['taxonomy'] as $taxonomy ) {

				// force value to array
				$term_ids = acf_get_array( $value );


				// convert to int
				$term_ids = array_map('intval', $term_ids);


				// extract term ids to append
				$ids_to_append = array();

				foreach( $term_ids as $term_id ) {

					if( get_term( $term_id )->taxonomy === $taxonomy ) {

						$ids_to_append[] = $term_id;

					}

				}


				// get existing term id's (from a previously saved field)
				$old_term_ids = isset($this->save_post_terms[ $taxonomy ]) ? $this->save_post_terms[ $taxonomy ] : array();


				// append
				$this->save_post_terms[ $taxonomy ] = array_merge($old_term_ids, $ids_to_append);

			}


			// if called directly from frontend update_field()
			if( !did_action('acf/save_post') ) {

				$this->save_post( $post_id );

				return $value;

			}

		}


		// return
		return $value;

	}



	/*
	*  save_post
	*
	*  This function will save any terms in the save_post_terms array
	*
	*  @type	function
	*  @date	26/11/2014
	*  @since	5.0.9
	*
	*  @param	$post_id (int)
	*  @return	n/a
	*/

	function save_post( $post_id ) {

		// Check for saved terms.
		if( !empty($this->save_post_terms) ) {

			// Determine object ID allowing for non "post" $post_id (user, taxonomy, etc).
			// Although not fully supported by WordPress, non "post" objects may use the term relationships table.
			// Sharing taxonomies across object types is discoraged, but unique taxonomies work well.
			// Note: Do not attempt to restrict to "post" only. This has been attempted in 5.8.9 and later reverted.
			extract( acf_decode_post_id($post_id) );
			if( $type === 'block' ) {
				// Get parent block...
			}

			// Loop over taxonomies and save terms.
			foreach( $this->save_post_terms as $taxonomy => $term_ids ){
				wp_set_object_terms( $post_id, $term_ids, $taxonomy, false );
			}

			// Reset storage.
			$this->save_post_terms = array();
		}
	}



	/*
	*  format_value()
	*
	*  This filter is applied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/

	function format_value( $value, $post_id, $field ) {

		// bail early if no value
		if( empty($value) ) return false;


		// force value to array
		$value = acf_get_array( $value );


		// load terms if needed
		if( $field['return_format'] == 'object' ) {

			// get terms
			$value = $this->get_terms( $value, $field["taxonomy"] );

		}


		// convert back from array if necessary
		if( $field['field_type'] == 'select' || $field['field_type'] == 'radio' ) {

			$value = array_shift($value);

		}


		// return
		return $value;

	}


}


// initialize
new gr_acf_field_multiple_taxonomy( $this->settings );


// class_exists check
endif;

?>