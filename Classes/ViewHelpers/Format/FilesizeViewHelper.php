<?php

/*                                                                        *
 * This script belongs to the TYPO3 package "vhc".                        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Formats a filesize
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <vhc:format.filesize>724766</vhc:format.filesize>
 * </code>
 *
 * Output:
 * 708 K
 *
 * <code title="With all parameters">
 * <vhc:format.filesize label=" | Kilo| Mega| Giga">724766</vhc:format.filesize>
 * </code>
 *
 * Output:
 * 708 Kilo
 *
 * @package vhc
 * @subpackage ViewHelpers\Format
 * @api
 * @scope prototype
 */
class Tx_DrBlob_ViewHelpers_Format_FilesizeViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Format the numeric value as a number with grouped thousands, decimal point and
	 * precision.
	 *
	 * @param int $bytes The filesize to format
	 * @param string $label Rule how to format the size (default  | K| M| G)
	 * @return string The formatted file size
	 * @author Daniel Regelein <daniel.regelein@diehl-informatik.de>
	 * @api
	 */
	public function render( $bytes = 0, $label = ' | K| M| G' ) {
		if ( $bytes === 0 ) {
			$bytes = $this->renderChildren();
			if ( $bytes === NULL ) {
				return '';
			}
		}
		return t3lib_div::formatSize( $bytes, $label );
	}
}
?>