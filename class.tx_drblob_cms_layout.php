<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-present Daniel Regelein (Daniel.Regelein@diehl-informatik.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * Hook to display verbose information about pi1 plugin in Web>Page module
 *
 * @author		Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @coauthor	Dmitry Dulepov <dmitry@typo3.org>
 * @package 	dr_blob
 * @filesource	class.tx_drblob_cms_layout.php
 * @version		2.0.1
 * @since 		2.0.1, 2009-02-24
 */
class tx_drblob_cms_layout {

	/**
	 * Returns information about this extension's pi1 plugin
	 *
	 * @param	array		$params	Parameters to the hook
	 * @param	object		$pObj	A reference to calling object
	 * @return	string		Information about pi1 plugin
	 */
	function getExtensionSummary( $params, &$pObj ) {
		if ( $params['row']['list_type'] == 'dr_blob_pi1' ) {
			
			$data = t3lib_div::xml2array( $params['row']['pi_flexform'] );
			$listType = $data['data']['sSettings']['lDEF']['xmlWhatToDisplay']['vDEF'];
			$listType = $listType ? $listType : 'list';
			
			$result = sprintf(
				$GLOBALS['LANG']->sL( 'LLL:EXT:dr_blob/locallang_wiz.xml:cms_layout.mode' ),
				$GLOBALS['LANG']->sL( 'LLL:EXT:dr_blob/locallang_tca.xml:tt_content.pi_flexform.whatToDisplay.' . $listType ) 
			);
			
			if( $listType == 'list' ) {
				if( $data['data']['sVFolderTree']['lDEF']['xmlShowVFolderTree']['vDEF'] == '1' ) {
					$result .= ' ' . $GLOBALS['LANG']->sL( 'LLL:EXT:dr_blob/locallang_wiz.xml:cms_layout.mode.usingVFolderTree' );
				}
				
				if( $data['data']['sSettings']['lDEF']['xmlAdd2Fav']['vDEF'] == '1' ) {
					$result .= ' ' . $GLOBALS['LANG']->sL( 'LLL:EXT:dr_blob/locallang_wiz.xml:cms_layout.mode.usingAdd2Fav' );					
				}
			}
			
		}
		return $result;
	}
	
	
	/**
	 * @name		__toString
	 * Output the class makes when calling <code>echo $obj;</code>
	 * 
	 * @access		public
	 * @return		String		"tx_drblob_cms_layout"
	 */
	/*public*/function __toString() {
		return 'tx_drblob_cms_layout';
	}
};


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.tx_drblob_cms_layout.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.tx_drblob_cms_layout.php']);
}
?>