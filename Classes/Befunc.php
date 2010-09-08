<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-present Daniel Regelein (Daniel.Regelein@diehl-informatik.de)
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
 * Class being included by t3lib_befunc using the hook displayWarningMessages_postProcess
 * 
 * @author		Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @package 	TYPO3
 * @subpackage  dr_blob
 * @filesource	EXT:dr_blob/Classes/Befunc.php
 * @version		2.4.0
 * @since 		2.0.1, 2009-02-24
 */
class Tx_DrBlob_Befunc {
	
	/**
	 * Display some warning messages if this installation is obviously insecure!!
	 * These warnings are only displayed to admin users
	 *
	 * @return	void
	 */
	function displayWarningMessages_postProcess( &$warning ) {

		if( tx_drblob_div::extConf_isStorageTypeAllowed( 'fs' ) ) {
			$folder = tx_drblob_div::getStorageFolder();
			
			$warning['tx_drblob_uploadFolderNotWriteable'] = sprintf(
				$GLOBALS['LANG']->sL( 'LLL:EXT:dr_blob/Resources/Private/Language/locallang_wiz.xml:err_uploadFolderNotWriteable' ),
				$folder
			);
			
			//Check whether the storage folder exists...
			if( @is_dir( $folder ) ) {
				
				//... and is writeable
				if( @is_writeable( $folder ) ) {
					unset( $warning['tx_drblob_uploadFolderNotWriteable'] );
				}
				
				//Check whether the storage folder is accessible via web
				if( t3lib_div::isFirstPartOfStr( $folder, PATH_site ) ) {
					$warning['tx_drblob_uploadFolderAccesibleViaWeb'] = sprintf(
						$GLOBALS['LANG']->sL( 'LLL:EXT:dr_blob/Resources/Private/Language/locallang_wiz.xml:err_uploadFolderAccesibleViaWeb' ),
						$folder
					);
				}
			}
			
		}
	}
};


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Classes/Befunc.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Classes/Befunc.php']);
}
?>