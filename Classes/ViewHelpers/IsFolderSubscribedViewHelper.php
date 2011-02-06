<?php
/*                                                                        *
 * This script belongs to the TYPO3 package "dr_blob".                    *
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

class Tx_DrBlob_ViewHelpers_IsFolderSubscribedViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractConditionViewHelper {

	/**
	 * Renders <f:then> child if the current folder is already subscribed, otherwise renders <f:else> child.
	 *
	 * @return string the rendered string
	 */
	public function render() {
		$request = $this->controllerContext->getRequest();
		if( $request->getArgument( 'isFolderSubscribed' ) ) {
			return $this->renderThenChild();
		}
		return $this->renderElseChild();
	}
}
?>
