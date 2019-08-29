<?php
/**
 * La classe gérant les plans de prévention
 *
 * @author    Evarisk <dev@evarisk.com>
 * @since     6.6.0
 * @version   6.6.0
 * @copyright 2019 Evarisk.
 * @package   DigiRisk
 */

namespace digi;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * La classe gérant les causeries
 */
class Prevention_Class extends \eoxia\Post_Class {

	/**
	 * Le nom du modèle
	 *
	 * @var string
	 */
	protected $model_name = '\digi\Prevention_Model';

	/**
	 * Le post type
	 *
	 * @var string
	 */
	protected $type = 'digi-prevention';

	/**
	 * La route pour accéder à l'objet dans la rest API
	 *
	 * @var string
	 */
	protected $base = 'prevention';

	/**
	 * La version de l'objet
	 *
	 * @var string
	 */
	protected $version = '0.1';

	/**
	 * La clé principale du modèle
	 *
	 * @var string
	 */
	protected $meta_key = '_wpdigi_prevention';

	/**
	 * Le préfixe de l'objet dans DigiRisk
	 *
	 * @var string
	 */
	public $element_prefix = 'C';


	public function add_signature( $prevention, $user_id, $signature_data, $is_former = false ) {
		$upload_dir = wp_upload_dir();

		// Association de la signature.
		if ( ! empty( $signature_data ) ) {
			$encoded_image = explode( ',', $signature_data )[1];
			$decoded_image = base64_decode( $encoded_image );
			file_put_contents( $upload_dir['basedir'] . '/digirisk/tmp/signature.png', $decoded_image );
			$file_id = \eoxia\File_Util::g()->move_file_and_attach( $upload_dir['basedir'] . '/digirisk/tmp/signature.png', $prevention->data['id'] );

			if ( $is_former ) {
				$prevention->data['former']['signature_id']   = $file_id;
				$prevention->data['former']['signature_date'] = current_time( 'mysql' );
			} else {
				$prevention->data['participants'][ $user_id ]['signature_id']   = $file_id;
				$prevention->data['participants'][ $user_id ]['signature_date'] = current_time( 'mysql' );
			}
		}

		return $prevention;
	}

	public function step_maitreoeuvre( $prevention ) {

		// $prevention = $this->add_participant( $prevention, $former_id, true );
		$mo_phone      = ! empty( $_POST['maitre-oeuvre-phone'] ) ? sanitize_text_field( $_POST['maitre-oeuvre-phone'] ) : '';
		$mo_phone_code = ! empty( $_POST['maitre-oeuvre-phone-callingcode'] ) ? sanitize_text_field( $_POST['maitre-oeuvre-phone-callingcode'] ) : '';

		$prevention->data['step'] = \eoxia\Config_Util::$init['digirisk']->prevention_plan->steps->PREVENTION_INFORMATION;

		if( $mo_phone != "" ){
			$mo_phone_code = $mo_phone_code != "" ? '(' . $mo_phone_code . ')' : '';
			$prevention->data[ 'maitre_oeuvre' ][ 'phone' ] = $mo_phone_code . $mo_phone;

			if( $prevention->data[ 'maitre_oeuvre'][ 'user_id' ] ){

				$user_information = get_the_author_meta( 'digirisk_user_information_meta', $prevention->data[ 'maitre_oeuvre'][ 'user_id' ] );
				$user_information = ! empty( $user_information ) ? $user_information : array();
				$user_information[ 'digi_phone_number' ] = $mo_phone;
				$user_information[ 'digi_phone_number_full' ] = $mo_phone_code . $mo_phone;

				update_user_meta( $prevention->data[ 'maitre_oeuvre'][ 'user_id' ], 'digirisk_user_information_meta', $user_information );
			}
		}

		return Prevention_Class::g()->update( $prevention->data );
	}

	// public function all_user_in_prevention_id( $id ){
	// 	$users_clean = array();
	//
	// 	$users = User_Class::g()->get();
	//
	// 	foreach( $users as $key => $user ){
	// 		if( $user->data[ 'prevention_parent' ] == $id ){
	// 			array_push( $users_clean, $users[ $key ] );
	// 		}
	// 	}
	//
	// 	return $users_clean;
	// }

	public function get_link( $prevention, $step_number, $skip = false ) {
		return admin_url( 'admin-post.php?action=change_step_prevention&id=' . $prevention->data['id'] . '&step=' . $step_number );
	}

	public function update_information_prevention( $prevention, $data = array() ){
		if( ! isset( $data[ 'title' ] ) || $data[ 'title' ] == '' ){
			$data[ 'title' ] = esc_html__( 'Aucun titre', 'task-manager' );
		}

		if( ! isset( $data[ 'date_start' ] ) || $data[ 'date_start' ] == '' ){
			$data[ 'date_start' ] = date( 'd-m-Y', strtotime( 'now' ) );
		}

		if( ! isset( $data[ 'date_end' ] ) || $data[ 'date_end' ] == '' ){
			$data[ 'date_end_define' ] = 0;
		}else{
			$data[ 'date_end_define' ] = 1;
			if( strtotime( $data[ 'date_start' ] ) > strtotime( $data[ 'date_end' ] ) ){
				$data[ 'date_end' ] = date( 'd-m-Y', strtotime( $data[ 'date_start' ] ) + 86400 );
			}
		}

		$prevention_data = wp_parse_args( $data, $prevention->data );
		return Prevention_Class::g()->update( $prevention_data );

	}

	public function display_list_intervenant( $id ){
		$prevention = Prevention_Class::g()->get( array( 'id' => $id ), true );

		\eoxia\View_Util::exec( 'digirisk', 'prevention_plan', 'start/step-3-table-users', array(
			'prevention' => $prevention
		) );
	}

	public function display_maitre_oeuvre( $user = array(), $id = 0 ){
		$prevention = Prevention_Class::g()->get( array( 'id' => $id ), true );

		\eoxia\View_Util::exec( 'digirisk', 'prevention_plan', 'start/step-4-maitre-oeuvre', array(
			'user' => $user,
			'prevention' => $this->add_information_to_prevention( $prevention )
		) );
	}

	public function display_intervenant_exterieur( $user = array(), $id = 0 ){
		$prevention = Prevention_Class::g()->get( array( 'id' => $id ), true );

		\eoxia\View_Util::exec( 'digirisk', 'prevention_plan', 'start/step-4-intervenant-exterieur', array(
			'user' => $user,
			'prevention' => $prevention
		) );
	}

	public function add_signature_maitre_oeuvre( $prevention, $signature_data , $slug ) {
		$upload_dir = wp_upload_dir();

		// Association de la signature.
		if ( ! empty( $signature_data ) ) {
			$encoded_image = explode( ',', $signature_data )[1];
			$decoded_image = base64_decode( $encoded_image );
			file_put_contents( $upload_dir['basedir'] . '/digirisk/tmp/signature.png', $decoded_image );
			$file_id = \eoxia\File_Util::g()->move_file_and_attach( $upload_dir['basedir'] . '/digirisk/tmp/signature.png', $prevention->data['id'] );

			$prevention->data[$slug]['signature_id']   = $file_id;
			$prevention->data[$slug]['signature_date'] = current_time( 'mysql' );
		}

		return $prevention;
	}

	public function save_info_maitre_oeuvre(){
		$id   = ! empty( $_POST['id'] ) ? (int) $_POST['id'] : 0;

		$mo_phone      = ! empty( $_POST['maitre-oeuvre-phone'] ) ? sanitize_text_field( $_POST['maitre-oeuvre-phone'] ) : '';
		$mo_phone_code = ! empty( $_POST['maitre-oeuvre-callingcode'] ) ? sanitize_text_field( $_POST['maitre-oeuvre-callingcode'] ) : '';

		$i_name        = ! empty( $_POST['intervenant-name'] ) ? sanitize_text_field( $_POST['intervenant-name'] ) : '';
		$i_lastname    = ! empty( $_POST['intervenant-lastname'] ) ? sanitize_text_field( $_POST['intervenant-lastname'] ) : '';
		$i_phone       = ! empty( $_POST['intervenant-phone'] ) ? sanitize_text_field( $_POST['intervenant-phone'] ) : '';
		$i_phone_code  = ! empty( $_POST['intervenant-phone-callingcode'] ) ? sanitize_text_field( $_POST['intervenant-phone-callingcode'] ) : '';

		if( ! $i_name || ! $i_lastname || ! $i_phone ){
			wp_send_json_error( 'Erreur in intervenant exterieur' );
		}

		$prevention = Prevention_Class::g()->get( array( 'id' => $id ), true );

		if( $mo_phone != ""  ){
			echo '<pre>'; print_r( '----' ); echo '</pre>';
			$prevention->data[ 'maitre_oeuvre' ][ 'phone' ] = '(' . $mo_phone_code . ')' . $mo_phone;
		}

		$data_i = array(
			'firstname' => $i_name,
			'lastname'  => $i_lastname,
			'phone'     => '(' . $i_phone_code . ')' . $i_phone
		);

		$prevention->data[ 'intervenant_exterieur' ] = wp_parse_args( $data_i, $prevention->data[ 'intervenant_exterieur' ] );

		return Prevention_Class::g()->update( $prevention->data );
	}


	public function add_information_to_prevention( $prevention ){
		$prevention->data[ 'intervention' ] = Prevention_Intervention_Class::g()->get( array( 'post_parent' => $prevention->data[ 'id' ] ) ); // Recupere la liste des interventions

		/*$id = $prevention->data[ 'former' ][ 'user_id' ];
		if( $prevention->data[ 'former' ][ 'user_id' ] > 0 ){
			$prevention = $this->get_information_from_user( $id, $prevention, 'former' );
		}*/

		if( $prevention->data[ 'maitre_oeuvre' ][ 'user_id' ] > 0 ){ // Maitre d'oeuvre data
			$id = $prevention->data[ 'maitre_oeuvre' ][ 'user_id' ];
			$prevention = $this->get_information_from_user( $id, $prevention, 'maitre_oeuvre' );
		}
		return $prevention;
	}

	public function get_information_from_user( $id, $prevention, $type_user ){
		$user_info = get_user_by( 'id', $id );
		$prevention->data[ $type_user ] = wp_parse_args( $user_info, $prevention->data[ $type_user ] );

		$avatar_color = array( 'e9ad4f', '50a1ed', 'e05353', 'e454a2', '47e58e', '734fe9' ); // Couleur
		$color = $id % count( $avatar_color );
		$prevention->data[ $type_user ][ 'data' ]->avator_color = $avatar_color[ $color ]; // De l'avatar

		$prevention->data[ $type_user ][ 'data' ]->first_name = $user_info->first_name; // De l'avatar
		$prevention->data[ $type_user ][ 'data' ]->last_name = $user_info->last_name; // De l'avatar

		if( $user_info->first_name != "" || $user_info->last_name != "" ){ // Inital
			$prevention->data[ $type_user ][ 'data' ]->initial = substr( $user_info->first_name, 0, 1 ) . ' ' . substr( $user_info->last_name, 0, 1 );
		}else{
			$prevention->data[ $type_user ][ 'data' ]->initial = substr( $user_info->display_name, 0, 1 );
		}

		$user_information = get_the_author_meta( 'digirisk_user_information_meta', $id );
		$phone_number = ! empty( $user_information['digi_phone_number_full'] ) ? $user_information['digi_phone_number_full'] : '';
		$prevention->data[ $type_user ][ 'data' ]->phone = $phone_number;

		return $prevention;
	}


	public function generate_document_odt_prevention( $prevention ){

		$legal_display = Legal_Display_Class::g()->get( array(
			'posts_per_page' => 1
		), true );

		if ( empty( $legal_display ) ) {
			$legal_display = Legal_Display_Class::g()->get( array(
				'schema' => true,
			), true );
		}

		$society = Society_Class::g()->get( array(
			'posts_per_page' => 1,
		), true );

		$data = array(
			'legal_display' => $legal_display,
			'society' => $society
		);

		$response = Sheet_Prevention_Class::g()->prepare_document( $prevention, $data );
		$response = Sheet_Prevention_Class::g()->create_document( $response['document']->data['id'] );
		return $response;
	}

	public function update_maitre_oeuvre( $id, $user_id ){
		$prevention = Prevention_Class::g()->get( array( 'id' => $id ), true );
		$user_info = get_user_by( 'id', $user_id );

		if( ! empty( $user_info ) ){
			$prevention->data[ 'maitre_oeuvre' ][ 'user_id' ] =  intval( $user_info->data->ID );
			// $prevention->data[ 'maitre_oeuvre' ][ 'firstname' ] = $user_info->first_name;
			// $prevention->data[ 'maitre_oeuvre' ][ 'lastname' ] = $user_info->last_name;

			/*$user_information = get_the_author_meta( 'digirisk_user_information_meta', $user_id );
			$phone_number = ! empty( $user_information['digi_phone_number'] ) ? $user_information['digi_phone_number'] : '';
			$prevention->data[ 'maitre_oeuvre' ][ 'phone' ] = $phone_number;*/
		}
		return Prevention_Class::g()->update( $prevention->data );
	}
}

Prevention_Class::g();
