<?php
/**
* La ligne des valeurs des variables de l'évaluation complexe de digirisk
*
* @author Jimmy Latour <jimmy.latour@gmail.com>
* @version 0.1
* @copyright 2015-2016 Eoxia
* @package evaluation_method
* @subpackage view
*/

if ( !defined( 'ABSPATH' ) ) exit; ?>

<ul class="row">
  <li><?php echo $i; ?></li>
  <?php for ( $x = 0; $x < count( $list_evaluation_method_variable ); $x++ ): ?>
    <?php
    $active = '';

    if ( !empty( $risk_evaluation ) && !empty( $risk_evaluation->option['quotation_detail'] ) ):
      foreach( $risk_evaluation->option['quotation_detail'] as $detail ) {
        if( $detail['variable_id'] == $list_evaluation_method_variable[$x]->id && $detail['value'] == $list_evaluation_method_variable[$x]->option['survey']['request'][$i]['seuil'] )
          $active = 'active';
      }
    endif;
    ?>

    <li data-variable-id="<?php echo $list_evaluation_method_variable[$x]->id; ?>" data-seuil-id="<?php echo $list_evaluation_method_variable[$x]->option['survey']['request'][$i]['seuil'] == null ? 'undefined' : $list_evaluation_method_variable[$x]->option['survey']['request'][$i]['seuil']; ?>" class="cell <?php echo $active; ?>">
      <?php echo !empty( $list_evaluation_method_variable[$x]->option['survey']['request'][$i] ) ? $list_evaluation_method_variable[$x]->option['survey']['request'][$i]['question'] : ''; ?>
    </li>
  <?php endfor; ?>
</ul>