<?php

namespace digi;

if ( !defined( 'ABSPATH' ) ) exit;

class user_action extends \singleton_util {
	protected function construct() {
		add_action( 'admin_menu', array( $this, 'callback_admin_menu' ) );

		// Quand on affecte un utilisateur
		add_action( 'wp_ajax_edit_user_assign', array( $this, 'callback_edit_user_assign' ) );

		// Quand on désaffecte un utilisateur
		add_action( 'wp_ajax_detach_user', array( $this, 'callback_detach_user' ) );

		add_action( 'wp_ajax_paginate_user', array( $this, 'callback_paginate_user' ) );

		// Recherche d'un utilisateur affecté
		add_action( 'wp_ajax_search_user_affected', array( $this, 'ajax_search_user_affected' ) );
	}

	public function callback_admin_menu() {
		add_users_page( __( 'Create or import user easyly with a form ', 'digirisk'), __( 'Digirisk : import', 'digirisk'), 'read', 'digirisk-users', array( $this, 'display_page_staff' ) );
	}

	public function display_page_staff( $hidden ) {
		$list_user = user_class::get()->index();
		array_shift( $list_user );

		require( USERS_VIEW . 'page-staff.php' );
	}

	public function callback_edit_user_assign() {
		if ( 0 === (int)$_POST['workunit_id'] )
			wp_send_json_error( array( 'error' => __LINE__, ) );
		else
			$workunit_id = (int)$_POST['workunit_id'];

		if ( 0 === (int)$_POST['group_id'] )
			wp_send_json_error( array( 'error' => __LINE__, ) );
		else
			$group_id = (int)$_POST['group_id'];

		if( !is_array( $_POST['list_user'] ) )
			wp_send_json_error();

		$workunit = \workunit_class::get()->show( $workunit_id );

		if ( empty( $workunit ) )
			wp_send_json_error();

		foreach ( $_POST['list_user'] as $user_id => $list_value ) {
			if ( !empty( $list_value['affect'] ) ) {
				$list_value['on'] = str_replace( '/', '-', $list_value['on'] );
				$workunit->option['user_info']['affected_id']['user'][$user_id][] = array(
					'status' => 'valid',
					'start' => array(
						'date' 	=> sanitize_text_field( date( 'Y-m-d', strtotime( $list_value['on'] ) ) ),
						'by'	=> get_current_user_id(),
						'on'	=> current_time( 'Y-m-d' ),
					),
					'end' => array(
						'date' 	=> '0000-00-00 00:00:00',
						'by'	=> get_current_user_id(),
						'on'	=> '0000-00-00 00:00:00',
					),

				);
			}
		}

		// On met à jour si au moins un utilisateur à été affecté
		if( count( $_POST['list_user'] ) > 0 )
			\workunit_class::get()->update( $workunit );

		$list_affected_user = user_class::get()->list_affected_user( $workunit, $list_affected_id );
		ob_start();
		require( USERS_VIEW . 'list-affected-user.php' );
		$template = ob_get_clean();

		$current_page = !empty( $_REQUEST['current_page'] ) ? (int) $_REQUEST['current_page'] : 1;
		$args_where_user = array(
			'offset' => ( $current_page - 1 ) * $this->limit_user,
			'number' => user_class::get()->limit_user,
			'exclude' => array( 1 ),
			'meta_query' => array(
				'relation' => 'OR',
			),
		);
		$list_user_to_assign = user_class::get()->index( $args_where_user );

		// Pour compter le nombre d'utilisateur en enlevant la limit et l'offset
		unset( $args_where_user['offset'] );
		unset( $args_where_user['number'] );
		$args_where_user['fields'] = array( 'ID' );
		$count_user = count( user_class::get()->index( $args_where_user ) );
		$number_page = ceil( $count_user / user_class::get()->limit_user );

		ob_start();
		require( USERS_VIEW . 'list-user-to-assign.php' );
		wp_send_json_success( array( 'template' => $template, 'template_form' => ob_get_clean() ) );
	}

	public function callback_detach_user() {
		$id = !empty( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$user_id = !empty( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;

		$workunit = \workunit_class::get()->show( $id );
		$index_valid_key = user_class::get()->get_valid_in_workunit_by_user_id( $workunit, $user_id );

		$workunit->option['user_info']['affected_id']['user'][$user_id][$index_valid_key]['status'] = 'delete';
		$workunit->option['user_info']['affected_id']['user'][$user_id][$index_valid_key]['end'] = array(
			'date'  => current_time( 'Y-m-d' ),
			'by'	=> get_current_user_id(),
			'on'	=> current_time( 'Y-m-d' ),
		);


		\workunit_class::get()->update( $workunit );

		$list_affected_user = user_class::get()->list_affected_user( $workunit, $list_affected_id );
		ob_start();
		require( USERS_VIEW . 'list-affected-user.php' );
		$template = ob_get_clean();

		$current_page = !empty( $_REQUEST['current_page'] ) ? (int) $_REQUEST['current_page'] : 1;
		$args_where_user = array(
			'offset' => ( $current_page - 1 ) * user_class::get()->limit_user,
			'number' => user_class::get()->limit_user,
			'exclude' => array( 1 ),
			'meta_query' => array(
				'relation' => 'OR',
			),
		);
		$list_user_to_assign = user_class::get()->index( $args_where_user );

		// Pour compter le nombre d'utilisateur en enlevant la limit et l'offset
		unset( $args_where_user['offset'] );
		unset( $args_where_user['number'] );
		$args_where_user['fields'] = array( 'ID' );
		$count_user = count( user_class::get()->index( $args_where_user ) );
		$number_page = ceil( $count_user / user_class::get()->limit_user );

		ob_start();
		require( USERS_VIEW . 'list-user-to-assign.php' );
		wp_send_json_success( array( 'template' => $template, 'template_form' => ob_get_clean() ) );
	}

	public function ajax_search_user_affected() {
		// wpdigi_utils::check( 'ajax_search_user_affected' );

		global $wpdb;
		$user_name_affected = sanitize_text_field( $_POST['user_name_affected'] );

		$keyword = '%' . $user_name_affected . '%';

		$query = "SELECT u.ID FROM {$wpdb->users} as u
							WHERE u.user_email LIKE %s";

		$list_user_result = $wpdb->get_results( $wpdb->prepare( $query, array( $keyword ) ), 'ARRAY_N' );
		$list_user_result = array_map( 'current', $list_user_result );


		ob_start();
		require( USERS_VIEW . 'list-affecetd-user.php' );
		wp_send_json_success( array( 'template' => ob_get_clean() ) );
	}

	public function callback_paginate_user() {
		$element_id = !empty( $_POST['element_id'] ) ? (int) $_POST['element_id'] : 0;

		if ( $element_id === 0 ) {
			wp_send_json_error();
		}

		$element = \workunit_class::get()->show( $element_id );
		user_class::get()->render_list( $element );
		wp_die();
	}
}

user_action::get();