<?php
/**
 * Le bouton "Télécharger le ZIP"
 *
 * @author Jimmy Latour <jimmy@evarisk.com>
 * @version 6.2.1.0
 * @copyright 2015-2016 Evarisk
 * @package zip
 * @subpackage view
 */

namespace digi;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<a href="<?php echo esc_attr( $zip_url ); ?>">
	<i class="fa fa-file-archive-o" aria-hidden="true"></i>
	<?php esc_html_e( 'ZIP', 'digirisk' ); ?>
</a>
