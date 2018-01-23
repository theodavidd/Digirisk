<?php
/**
 * Gestion de l'affichage d'une diffusion d'information.
 *
 * @author Jimmy Latour <jimmy@evarisk.com>
 * @since 6.2.10
 * @version 6.4.4
 * @copyright 2015-2017 Evarisk
 * @package DigiRisk
 */

namespace digi;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<tr>
	<td class="padding"><strong><?php echo esc_html( $element->unique_identifier ); ?></strong></td>
	<td class="padding"><?php echo esc_html( $element->title ); ?></td>
	<td>
		<div class="action">
			<?php if ( ! empty( Document_Class::g()->get_document_path( $element ) ) ) : ?>
			<a class="button purple h50" href="<?php echo esc_attr( Document_Class::g()->get_document_path( $element ) ); ?>">
				<i class="icon fa fa-download" aria-hidden="true"></i>
			</a>
			<?php else : ?>
				<span class="button grey h50 tooltip hover red" aria-label="<?php echo esc_attr_e( 'Corrompu', 'digirisk' ); ?>" href="<?php echo esc_attr( Document_Class::g()->get_document_path( $element ) ); ?>">
					<i class="fa fa-times icon" aria-hidden="true"></i>
				</span>
			<?php endif; ?>
		</div>
	</td>
</tr>