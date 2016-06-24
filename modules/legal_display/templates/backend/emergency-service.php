<?php
/**
* Service d'urgence
*
* @author Jimmy Latour <jimmy.latour@gmail.com>
* @version 0.1
* @copyright 2015-2016 Eoxia
* @package society
* @subpackage templates
*/

if ( !defined( 'ABSPATH' ) ) exit; ?>

<ul class="wp-digi-form">
  <li><h2><?php _e( 'Emergency service', 'wpdigi-i18n' ); ?></h2></li>
  <li>
    <label>
      <?php _e( 'Samu', 'wpdigi-i18n' ); ?>
      <input name="emergency_service[samu]" type="text" value="15" />
    </label>
  </li>
  <li>
    <label>
      <?php _e( 'Police/Gendarmerie', 'wpdigi-i18n' ); ?>
      <input name="emergency_service[police]" type="text" value="17" />
    </label>
  </li>
  <li>
    <label>
      <?php _e( 'Pompiers', 'wpdigi-i18n' ); ?>
      <input name="emergency_service[pompier]" type="text" value="18" />
    </label>
  </li>
  <li>
    <label>
      <?php _e( 'Emergency', 'wpdigi-i18n' ); ?>
      <input name="emergency_service[emergency]" type="text" value="112" />
    </label>
  </li>
  <li>
    <label>
      <?php _e( 'Rights defender', 'wpdigi-i18n' ); ?>
      <input name="emergency_service[right_defender]" type="text" value="09 69 39 00 00" />
    </label>
  </li>
  <li>
    <label>
      <?php _e( 'Poison control center', 'wpdigi-i18n' ); ?>
      <input name="emergency_service[poison_control_center]" type="text" />
    </label>
  </li>
</ul>