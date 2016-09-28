<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
 * @version 1.0
 */

class common_util extends singleton_util {
	protected function construct() {}

	/**
	 * Récupération du dernier index unique pour un type
	 *
	 * @param string $wp_type (test: post) Le type de la donnée
	 * @param string $element_type (test: digi-risk) Le type de la donnée
	 *
	 * @return integer Retourne la valeur du dernier index unique pour les éléments digirisk / Return the last index for digirisk element
	 */
	public static function get_last_unique_key( $controller ) {
		$element_type = $controller::g()->get_post_type();
		$wp_type = $controller::g()->get_identifier_helper();
		if ( empty( $wp_type ) || empty( $element_type ) || !is_string( $wp_type ) || !is_string( $element_type ) )
			return false;

		global $wpdb;
		switch ( $wp_type ) {
			case 'post':
				$query = $wpdb->prepare(
					"SELECT max( PM.meta_value + 0 )
					FROM {$wpdb->postmeta} AS PM
						INNER JOIN {$wpdb->posts} AS P ON ( P.ID = PM.post_id )
					WHERE PM.meta_key = %s
						AND P.post_type = %s", '_wpdigi_unique_key', $element_type );
			break;

			case 'comment':
				$query = $wpdb->prepare(
					"SELECT max( CM.meta_value + 0 )
					FROM {$wpdb->commentmeta} AS CM
						INNER JOIN {$wpdb->comments} AS C ON ( C.comment_ID = CM.comment_id )
					WHERE CM.meta_key = %s
						AND C.comment_type = %s", '_wpdigi_unique_key', $element_type );
			break;

			case 'user':
				$query = $wpdb->prepare(
					"SELECT max( UM.meta_value + 0 )
					FROM {$wpdb->usermeta} AS UM
					WHERE UM.meta_key = %s", '_wpdigi_unique_key' );
			break;

			case 'term':
				$query = $wpdb->prepare(
					"SELECT max( TM.meta_value + 0 )
					FROM {$wpdb->term_taxonomy} AS T
						INNER JOIN {$wpdb->termmeta} AS TM ON ( T.term_id = TM.term_id )
					WHERE TM.meta_key = %s AND T.taxonomy=%s", '_wpdigi_unique_key', $element_type );
			break;
		}

		if ( !empty( $query ) ) {
			$last_unique_key = $wpdb->get_var( $query );
		}

		if ( empty( $last_unique_key ) )
			return 0;

		return $last_unique_key;
	}
}
