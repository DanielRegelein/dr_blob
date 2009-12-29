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
 * This class provides several methods to be called staticly for the 
 * extension dr_blob
 * 
 * @package EXT:dr_blob
 * @since 2.2.0
 * @version 2.0.0
 * @author Daniel Regelein <daniel.regelein@diehl-informatik.de>
 */
class tx_drblob_div {
	
	/**
	 * This method returns the Folder used for secure files from the filesystem
	 *
	 * @static
	 * @return String Folder
	 */
	public static function getStorageFolder() {
		$storageFolder = 'uploads/tx_drblob/storage/';
		
		$extConf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dr_blob'] );
		if( !empty( $extConf['fileStorageFolder'] ) ) {
			$storageFolder = $extConf['fileStorageFolder'];
		}

		if( strpos( $storageFolder, '/', 0 ) != 0 ) {
			$storageFolder = PATH_site . $storageFolder;
		}

			//ensure there is a trailing slash 
		$storageFolder = t3lib_div::dirname( $storageFolder ) . '/';
		
		return $storageFolder;
	}
	
	
	/**
	 * @name		__toString
	 * Output the class makes when calling <code>echo $obj;</code>
	 * 
	 * @access		public
	 * @return		String		"tx_drblob_div"
	 */
	/*public*/function __toString() {
		return 'tx_drblob_div';
	}
};


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.tx_drblob_div.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.tx_drblob_div.php']);
}
?>