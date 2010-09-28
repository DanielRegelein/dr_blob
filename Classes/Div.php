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
 * @package TYPO3
 * @subpackage dr_blob
 * @since 2.2.0
 * @version 2.4.0
 * @author Daniel Regelein <daniel.regelein@diehl-informatik.de>
 * @filesource EXT:dr_blob/Classes/Div.php
 */
abstract class Tx_DrBlob_Div {
	
	const CONTENT_TABLE = 'tx_drblob_content';
	const CATEGORY_TABLE = 'tx_drblob_category';
	
	private static $EXTCONF = null;

	
	/**
	 * This method returns one of the three given parameter depending on its priority
	 *
	 * @param Mixed $p1 (Highest Priority)
	 * @param Mixed $p2 (Medium Priority)
	 * @param Mixed $p3 (Lowest Priority) -- Default
	 * @return Mixed
	 * @access public
	 * @static
	 */
	public static function getPrioParam( $p1, $p2, $p3 ) {
		if( $p1 && $p1 != 'ts' ) { return $p1; }
		if( $p2 ) { return $p2; }
		if( $p3 ) { return $p3; }
		return false; 
	}
	
	
	/**
	 * This method returns the Folder used for secure files from the filesystem
	 *
	 * @static
	 * @return String Folder
	 */
	public static function getStorageFolder() {
		self::extConf_initialize();
		
		$storageFolder = 'uploads/tx_drblob/storage/';
		if( !empty( self::$EXTCONF['fileStorageFolder'] ) ) {
			$storageFolder = self::$EXTCONF['fileStorageFolder'];
		}

		if( !t3lib_div::isAbsPath( $storageFolder ) ) {
			$storageFolder = PATH_site . $storageFolder;
		}

			//ensure there is a trailing slash 
		$storageFolder = t3lib_div::dirname( $storageFolder ) . '/';
		
		return $storageFolder;
	}
	
	
	/**
	 * This method returns the Folder used to store templates
	 * (Typically /uploads/tx_drblob/)
	 *
	 * @static
	 * @return String Folder
	 */
	public static function getUploadFolder() {
		return 'uploads/tx_drblob/';
	}
	
	
	/**
	 * This method calculates the checksum of the file given as first parameter.
	 * Therefore it calls the hash-method configured (currently only "md5_file")
	 *  
	 * @param String $file Path to file to generate the checksum for
	 * @return String
	 * @access public
	 * @static
	 */
	public static function calculateFileChecksum( $file ) {
		$checksum = md5_file( $file );
		return $checksum;
	}
	
	
	/**
	 * This method generates an techical filename with the suffix .blob for the use in filesystem stored records
	 * 
	 * @return String Filename to use
	 * @access public
	 * @static
	 */
	public static function generateStorageFilename() {
		return md5(time().rand()).'.blob';
	}
	
	
	/**
	 * Returns the fileextension of the given Filename.
	 * 
	 * @param 	String 	$filename
	 * @return 	String 	Extension
	 * @access 	public
	 * @static 
	 */
	public static function getFileExtension( $fileName ) {
		if ( !empty( $fileName ) ) {
			$tmp = t3lib_div::split_fileref( $fileName );
			return $tmp['realFileext'];
		} else {
			return '';
		}
	}
	
	
	/**
	 * Returns the content of a User- or Page TS .txt-file found in the Configuration-folder
	 *
	 * @param string $file Filename to read out
	 * @return string UserTS / PageTS
	 * @see t3lib_div::getURL
	 */
	public static function getUserOrPageTS( $file ) {
		return t3lib_div::getURL( t3lib_extMgm::extPath( 'dr_blob' ) . 'Configuration/' . $file, 0, false );
	}
	
	
	/**
	 * Ensure a initialized self::$EXTCONF variable
	 *
	 * @return void
	 * @static 
	 * @access private
	 */
	private static function extConf_initialize() {
		if( !sizeof( self::$EXTCONF )){
			self::$EXTCONF = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dr_blob'] );
		}
		return void;
	}
	
	
	/**
	 * This method returns the list of valid storage types
	 * If no valid storage type is selected, the database-type (type=1) will be used.
	 *
	 * @return Array
	 * @static 
	 * @access public
	 */
	public static function extConf_getAllowedStorageTypes() {
		self::extConf_initialize();

		$rtnArr = array();
		foreach( self::$EXTCONF['storageType.'] as $key => $value ) {
			if( $value == 1 ) {
				$rtnArr[$key] = 1;
			}
		}
		if( !sizeof( $rtnArr ) ) { 
			$rtnArr['db'] = 1;
		}
		return $rtnArr;
	}
	
	
	/**
	 * This method tests whether the given storage type is allowed in the extension configuration
	 * 
	 * @see extConf_getAllowedStorageTypes
	 * @param String $type Type to probe for
	 * @return unknown
	 * @static 
	 * @access public
	 */
	public static function extConf_isStorageTypeAllowed( $type ) {
		$validTypes = self::extConf_getAllowedStorageTypes();
		if( array_key_exists( $type, $validTypes ) ) {
			if( $validTypes[$type] == 1 ) {
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * Returns the Integer-key of the default storage type
	 * 	1 = db
	 *  2 = fs
	 *  3 = fsus
	 * 
	 * @see extConf_getAllowedStorageTypes
	 * @return Int Default storage type
	 * @static 
	 * @access public
	 */
	public static function extConf_defaultStorageTypes() {
		$validTypes = self::extConf_getAllowedStorageTypes();
		if( $validTypes['db'] == 1 ) {
			return 1;
		}
		if( $validTypes['fs'] == 1 ) {
			return 2;
		}
		if( $validTypes['fsus'] == 1 ) {
			return 3;
		}
	}
	
	
	/**
	 * Returns the SQL where statement for the category selection in the TCA
	 *
	 * @return String SQL-String for the category-selection
	 * @access public
	 * @static
	 */
	public static function extConf_getCategoryWhereForTCA() {
		self::extConf_initialize();
		
		if( strtolower( self::$EXTCONF['categoryStorage'] ) == 'idlist' ) {
			return ' AND tx_drblob_category.pid IN( ###PAGE_TSCONFIG_IDLIST### )';
		} else if ( strtolower( self::$EXTCONF['categoryStorage'] ) == 'storagepid' ) {
			return ' AND tx_drblob_category.pid = ###STORAGE_PID###';
		}
		return null;
	}
	
	
	/**
	 * Returns whether the "reset download couter"-funciton is enabled- or not.
	 *
	 * @return Bool
	 * @access public
	 * @static
	 */
	public static function extConf_enableDownloadCounterReset() {
		self::extConf_initialize();
		return self::$EXTCONF['enableCounterReset'] ? true : false;
	}

	
	/**
	 * Returns whether the "indexed_search"-integration is enabled- or not.
	 *
	 * @return Bool
	 * @access public
	 * @static
	 */
	public static function extConf_useIndexedSearchIntegration() {
		self::extConf_initialize();
		if( t3lib_extMgm::isLoaded( 'indexed_search' ) ) {
			if( self::$EXTCONF['integration.']['indexed_search'] == 1 ) {
				return true;
			}
		}
		return false;
	}

	
	/**
	 * @return Bool
	 * @access public
	 * @static
	 */
	public static function extConf_enablePi1() {
		self::extConf_initialize();
		if( t3lib_extMgm::isLoaded( 'cms' ) ) {
			if( self::$EXTCONF['enable.']['pi1'] == 1 ) {
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * @return Bool
	 * @access public
	 * @static
	 */
	public static function extConf_enablePi2() {
		self::extConf_initialize();
		if( t3lib_extMgm::isLoaded( 'extbase' ) && t3lib_extMgm::isLoaded( 'fluid' ) ) {
			if( self::$EXTCONF['enable.']['pi2'] == 1 ) {
				return true;
			}
		}
		return false;
	}
};


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Classes/Div.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Classes/Div.php']);
}
?>