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
 * @name 		tx_drblob_tcemain
 * Class being included by TCEmain using the hook processDatamap_postProcessFieldArray
 * 
 * @author		Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @package 	dr_blob
 * @filesource	class.tx_drblob_tcemain.php
 * @version		2.1.0
 * @since 		1.5.0, 2007-04-10
 */
class tx_drblob_tcemain {
	
	var $dbVars = array( 'table' => 'tx_drblob_content' );
	var $defaultUploadFolder = 'uploads/tx_drblob/storage';

	
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
		$this->defaultUploadFolder = PATH_site . $this->defaultUploadFolder;
		
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
						$data = fread( $filePointer, filesize( $fileName ) );
						$md5_checksum = md5( $data );
						$data = addslashes( $data );
					fclose( $filePointer );
					
					/* print( $obj->checkValue_currentRecord['type'] );
					 * 
					 * Type 1 -- Database
					 * Type 2 -- Filesystem
					 */

					//Store file in the filesystem
					if( $obj->checkValue_currentRecord['type'] == '2' ) {
						$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dr_blob']);
						
						//Generate Filename
						$folder = $extConf['fileStorageFolder'] ? $extConf['fileStorageFolder'] : $this->defaultUploadFolder;
						
						//The target filename has a random number in it's name to ensure unique filenames when versioning records.
						$targetFileName = $this->generateFileName( $item );
						t3lib_div::writeFile( t3lib_div::dirname( $folder ) . '/' . $targetFileName, $data );
						$data = $targetFileName;
					}
					
						//Prepare UPDATE-Array. Quoting the values is not nessesary, because this is done by 
						//the method $GLOBALS['TYPO3_DB']->UPDATEquery that is called from $GLOBALS['TYPO3_DB']->exec_UPDATEquery
					$arrValues = array(
						'blob_data' => $data,
						'blob_checksum' => $md5_checksum,
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
	 * This method is called by a hook in the TYPO3 Core Engine (TCEmain) when a record is saved. This is used to preview a record
	 *
	 * @param	array		$fieldArray: The field names and their values to be processed (passed by reference)
	 * @param	string		$table: The table TCEmain is currently processing
	 * @param	string		$id: The records id (if any)
	 * @param	object		$pObj: Reference to the parent object (TCEmain)
	 * @return	void
	 * @access public
	 */
	function processDatamap_preProcessFieldArray( &$fieldArray, $table, $id, &$pObj ) {
		
		if( $table == $this->dbVars['table'] ) {
			if ( isset( $GLOBALS['_POST']['_savedokview_x'] ) ) {
				$pagesTSconf = t3lib_BEfunc::getPagesTSconfig( $GLOBALS['_POST']['popViewId'] );
				if ( $pagesTSconf['tx_drblob.']['previewPid'] ) {
					if( empty( $pagesTSconf['tx_drblob.']['previewMode'] ) || !array_key_exists( trim( $pagesTSconf['tx_drblob.']['previewMode'] ), array( 'list' => '', 'single' => '' ) ) ) {
						$pagesTSconf['tx_drblob.']['previewMode']  = 'single';
					}
					$GLOBALS['_POST']['popViewId_addParams'] = ($fieldArray['sys_language_uid']>0?'&L='.$fieldArray['sys_language_uid']:'').'&no_cache=1' . ( ( $pagesTSconf['tx_drblob.']['previewMode']  == 'single' ) ? '&tx_drblob_pi1[showUid]='.$id : '' );
					$GLOBALS['_POST']['popViewId'] = $pagesTSconf['tx_drblob.']['previewPid'];
				}	
			}
		}
	}
	
	
	function processCmdmap_postProcess( $command, $table, $srcId, $destId, &$pObj ) {

		if( $table == $this->dbVars['table'] ) {
			
			$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
			$rslt = $GLOBALS['TYPO3_DB']->exec_SELECTquery( 
				'`type`, `blob_data`', 
				'`'.$this->dbVars['table'].'`', 
				'`uid`=' . intval( $srcId ) 
			);
			
			if( $rslt ) {
				if( $GLOBALS['TYPO3_DB']->sql_num_rows( $rslt ) == 1 ) {

					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt );
					
					$extConf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dr_blob'] );
					$folder = $extConf['fileStorageFolder'] ? $extConf['fileStorageFolder'] : $this->defaultUploadFolder;					
					
					switch( $command ) {
						case 'delete':
							if( ( !empty( $row['blob_data'] ) ) && intval( $extConf['reallyDeleteFiles'] ) == 1 ) {
								
								if( $row['type'] == 2 ) {
									$target = t3lib_div::dirname( $folder ) . '/' . $row['blob_data'];
									unlink( $target );
								}
								
								//Now update the deleted record to ensure integrity
								$rslt = $GLOBALS['TYPO3_DB']->exec_UPDATEquery( 
									'`'.$this->dbVars['table'].'`',	
									'`uid`=' . $srcId,
								 	array(
								 		'blob_name' => null,
								 		'blob_checksum ' => null,
								 		'blob_size' => 0,
								 		'blob_type' => null,
								 		'blob_data' => null,
								 	)
								);
							}
						break;
						
						case 'version':

							if( $row['type'] == 2 && $destId['action'] == 'new' ) {
								//Duplicate File on versioning and store it using a new name...
								
								$newVersionID = $pObj->copyMappingArray[$this->dbVars['table']][$srcId];
								$newFileName = $this->generateFileName( $newVersionID );
								
								$sourceFile = t3lib_div::dirname( $folder ) . '/' . $row['blob_data'];
								$targetFile = t3lib_div::dirname( $folder ) . '/' . $newFileName;
								
								if( copy( $sourceFile, $targetFile ) ) {
									$rslt = $GLOBALS['TYPO3_DB']->exec_UPDATEquery( 
										'`'.$this->dbVars['table'].'`',	
										'`uid`=' . $newVersionID,
									 	array(
									 		'blob_data' => $newFileName,
									 	)
									);
								}
								
							} else if ( $destId['action'] == 'swap' ) {

								//Currently there is no need to swap the files
								//The filename in the blob_data-field is still unique

							}
							
						break;
						
						default:
						break;
					}
				}
			}
		}
	}
	
	
	/**
	 * @name	generateFileName
	 * This method generates the filename for files stored in the filesystem
	 *
	 * @param Integer $item
	 * @return String
	 */
	function generateFileName( $item ) {
		return $item.'_'.rand().'.blob';
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