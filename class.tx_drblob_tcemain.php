<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Daniel Regelein (Daniel.Regelein@diehl-informatik.de)
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
 * @name 		tx_drblob_tcemain
 * Class being included by TCEmain using the hook processDatamap_postProcessFieldArray
 * 
 * @author		Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @package 	dr_blob
 * @filesource	class.tx_drblob_tcemain.php
 * @version		1.5.1
 * @since 		1.5.0, 2007-04-10
 */
class tx_drblob_tcemain {
	
	var $dbVars = array( 'table' => 'tx_drblob_content' );

	
	/**
	 * @name		processDatamap_afterDatabaseOperations
	 * Hook <code>processDatamap</code> in TCEmain
	 * 
	 * @param		String	$status		Status
	 * @param		String	$table		Databasetable where to write the data
	 * @param		Mixed	$id			ID of the record; In case of a new record it has the value NEW[HASH_VALUE]
	 * @param		Array	$fieldArray	FieldArray
	 * @param		Object	$obj		Reference 
	 * @access		public
	 * @return		void
	 */
	/*public*/function processDatamap_afterDatabaseOperations( $status, $table, $id, $fieldArray, $obj ){

		if( $table == $this->dbVars['table'] ) {
			
			if ( !is_int( $id ) ) {
				$item = $obj->substNEWwithIDs[$id];
			} else {
				$item = $id;
			}
			if ( is_array( $_FILES['data']['tmp_name'][$this->dbVars['table']] ) ) {
				$fileName = $_FILES['data']['tmp_name'][$this->dbVars['table']][$id]['blob_data'];
				if ( ( !empty( $fileName ) ) && ( $fileName != 'none' ) ) {
						//Open File and Quote it
					$filePointer = fopen( $fileName, 'r' );
						$data = addslashes( fread( $filePointer, filesize( $fileName ) ) );
					fclose( $filePointer );
					
						//Prepare UPDATE-Array. Quoting the values is not nessesary, because this is done by 
						//the method $GLOBALS['TYPO3_DB']->UPDATEquery that is called from $GLOBALS['TYPO3_DB']->exec_UPDATEquery
					$arrValues = array(
						'blob_data' => $data,
						'blob_name' => $_FILES['data']['name'][$this->dbVars['table']][$id]['blob_data'],
						'blob_size' => $_FILES['data']['size'][$this->dbVars['table']][$id]['blob_data'],
						'blob_type' => $_FILES['data']['type'][$this->dbVars['table']][$id]['blob_data']
					);
					$rslt = $GLOBALS['TYPO3_DB']->exec_UPDATEquery( $this->dbVars['table'], 'uid='.$item, $arrValues );
				}
			}
		}
	}

	
	/**
	 * @name		__toString
	 * Output the class makes when calling <code>echo $obj;</code>
	 * 
	 * @access		public
	 * @return		String		"tx_drblob_tcemain"
	 */
	/*public*/function __toString() {
		return 'tx_drblob_tcemain';
	}
};


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.tx_drblob_tcemain.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.tx_drblob_tcemain.php']);
}
?>