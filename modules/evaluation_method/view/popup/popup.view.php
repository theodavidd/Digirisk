<?php namespace digi;
/**
* La popup qui contient les données de l'évaluation complexe de digirisk
*
* @author Jimmy Latour <jimmy@evarisk.com>
* @version 0.1
* @copyright 2015-2016 Eoxia
* @package evaluation_method
* @subpackage view
*/

if ( !defined( 'ABSPATH' ) ) exit; ?>

<div class="wpdigi-method-evaluation-render digi-popup hidden">
  <!-- Utile pour retenir la méthode d'evaluation utilisée -->
  <input type="hidden" class="digi-method-evaluation-id" value="<?php echo !empty( $term_evarisk->term_id ) ? $term_evarisk->term_id : 0; ?>" />

  <section class="wp-digi-eval-evarisk">
    <div class="digi-popup-propagation wp-digi-bloc-loader">
			<a href="#" class="close"><i class="dashicons dashicons-no-alt"></i></a>
			<div class="wp-digi-eval-table">
				<?php if ( !empty( $list_evaluation_method_variable ) ): ?>
					<?php view_util::exec( 'evaluation_method', 'popup/header', array( 'term_evarisk' => $term_evarisk, 'risk_id' => $risk_id, 'risk' => $risk, 'list_evaluation_method_variable' => $list_evaluation_method_variable, 'evarisk_evaluation_method' => $evarisk_evaluation_method ) ); ?>

					<?php for( $i = 0; $i < count( $list_evaluation_method_variable ); $i++ ): ?>
						<?php view_util::exec( 'evaluation_method', 'popup/row', array( 'i' => $i, 'term_evarisk' => $term_evarisk, 'risk_id' => $risk_id, 'risk' => $risk, 'list_evaluation_method_variable' => $list_evaluation_method_variable, 'evarisk_evaluation_method' => $evarisk_evaluation_method ) ); ?>
					<?php endfor; ?>
				<?php endif;?>
				<button type="button" data-nonce="<?php echo wp_create_nonce( 'get_scale' ); ?>" class="float right wp-digi-bton-fourth"><?php _e( 'Evaluate risk', 'digirisk' ); ?></button>
			</div>
    </div>
  </section>
</div>
