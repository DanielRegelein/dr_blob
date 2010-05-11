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
 * @version		2.3.0
 * @since 		1.5.0, 2007-04-10
 * @internal 	Record type: $obj->checkValue_currentRecord['type']
 * 					Type 1 -- Database
 * 					Type 2 -- Filesystem
 * 					Type 3 -- Filesystem Unsecure
 */
class tx_drblob_tcemain {
	
	
	/**
	 * This method is called by a hook in the TYPO3 Core Engine (TCEmain) when a record is saved. This is used for two purposes:
	 * 	 - To manipulate the TCA-Array for tx_drblob_content for type=3
	 *   - to preview a record
	 *
	 * @param	array		$fieldArray: The field names and their values to be processed (passed by reference)
	 * @param	string		$table: The table TCEmain is currently processing
	 * @param	string		$id: The records id (if any)
	 * @param	object		$pObj: Reference to the parent object (TCEmain)
	 * @return	void
	 * @access public
	 */
	public function processDatamap_preProcessFieldArray( &$fieldArray, $table, $id, &$pObj ) {
		if( $table == tx_drblob_div::$CONTENT_TABLE ) {
			
				//Probe for the type of the record
			$type = null;
			if( $fieldArray['type'] ) {
				$type = $fieldArray['type'];
			} else {
				if( is_int( $id ) ) {
					$rslt = $GLOBALS['TYPO3_DB']->exec_SELECTquery( 
						'`type`', 
						'`'.$table.'`', 
						'`uid`=' . intval( $id ) 
					);
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt );
					$type = $row['type'];
				}
			}
			
				//type=3, override some fields in TCA
			if( $type == 3 ) {
				t3lib_div::loadTCA( $table );
				$GLOBALS['TCA'][$table]['columns']['blob_data']['config']['type'] = 'group';
			}

				//the "Preview"-Feature...
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
	
	
	/**
	 * This method stores the attributes of a blob file (type, size, name, checksum) to the database.
	 * This method allows to add these attributes to the same database query that stores the other stuff,
	 * like title and description. 
	 * If the values should be resetable using the undo-function, the extraction would have to be done in
	 * processDatamap_preProcessFieldArray. It is implemented here, because these attributes are bound to the 
	 * attached file, and should not be manipulated on their own (execpt for the filename maybe)
	 * 
	 * Therefore the attributes are extracted from the $_FILES-Array.
	 * When submitting a group-record (type=3) the file has already been processed the tcemain. 
	 * Thus we've to use the file from the tcemain mapping array because the file is already moved.
	 * For type=1 and type=2-files we have to do that on our own.
	 * 
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
	public function processDatamap_postProcessFieldArray( $status, $table, $id, &$fieldArray, $obj ) {
		if( $table == tx_drblob_div::$CONTENT_TABLE ) {
			if( $obj->checkValue_currentRecord['type'] == 3 ) {
					//Clear file-related attributes if the file is deleted
				if ( array_key_exists( 'blob_data', $fieldArray ) && $fieldArray['blob_data'] == '' ) {
					$fieldArray['blob_checksum'] = '';
					$fieldArray['blob_name'] = '';
					$fieldArray['blob_size'] = '';
					$fieldArray['blob_type'] = '';
				}
				$fileArray = $obj->uploadedFileArray[$table][$id]['blob_data'];
			} else {
				$fileArray = array(
					'error' => $_FILES['data']['error'][$table][$id]['blob_data'],
					'name' => $_FILES['data']['name'][$table][$id]['blob_data'],
					'tmp_name' => $_FILES['data']['tmp_name'][$table][$id]['blob_data'],
					'size' => $_FILES['data']['size'][$table][$id]['blob_data'],
					'type' => $_FILES['data']['type'][$table][$id]['blob_data']
				);
			}
			
				//Extract nessesary attributes if a file is uploaded
			if ( $fileArray['error'] == 0 && !empty( $fileArray['tmp_name'] ) && $fileArray['tmp_name'] != 'none' ) {
				if( $obj->checkValue_currentRecord['type'] != 3 ) {
					$filePointer = fopen( $fileArray['tmp_name'], 'r' );
						$data = fread( $filePointer, filesize( $fileArray['tmp_name'] ) );
					fclose( $filePointer );
					
					if( $obj->checkValue_currentRecord['type'] == '1' ) {
						$fieldArray['blob_data'] = addslashes( $data );
					} else {
						$fieldArray['blob_data'] = tx_drblob_div::generateStorageFilename();
						move_uploaded_file( $fileArray['tmp_name'], tx_drblob_div::getStorageFolder() . $fieldArray['blob_data'] );
						$fileArray['tmp_name'] = tx_drblob_div::getStorageFolder() . $fieldArray['blob_data'];
					}
				} else {
					$fileArray['tmp_name'] = $obj->copiedFileMap[$fileArray['tmp_name']];
				}
				$fieldArray['blob_checksum'] = tx_drblob_div::calculateFileChecksum( $fileArray['tmp_name'] );
				$fieldArray['blob_name'] = $fileArray['name'];
				$fieldArray['blob_size'] = $fileArray['size'];
				$fieldArray['blob_type'] = tx_drblob_div::overrideMimeType( $fileArray['type'], $fileArray['name'] );
			}
		}
	}
	
	
	/**
	 * 
	 * @param String 	$command 	TCEMAINcommand what to do
	 * @param String 	$table 		The table to operate on.
	 * @param Integer 	$srcId		uid of the record to operate on
	 * @param Integer 	$destId
	 * @param TCEMAIN 	$pObj
	 */
	public function processCmdmap_postProcess( $command, $table, $srcId, $destId, &$pObj ) {
		if( $table == tx_drblob_div::$CONTENT_TABLE ) {
			
			$rslt = $GLOBALS['TYPO3_DB']->exec_SELECTquery( 
				'`type`, `blob_data`', 
				'`'.$table.'`', 
				'`uid`=' . intval( $srcId ) 
			);
			if( $rslt ) {
				if( $GLOBALS['TYPO3_DB']->sql_num_rows( $rslt ) == 1 ) {

					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt );
					
					switch( $command ) {
						case 'delete':
							if( ( !empty( $row['blob_data'] ) ) && intval( $extConf['reallyDeleteFiles'] ) == 1 ) {
								
								if( $row['type'] == 2 ) {
									$target = tx_drblob_div::getStorageFolder() . $row['blob_data'];
									unlink( $target );
								}
								if( $row['type'] == 3 ) {
									$target = tx_drblob_div::getUploadFolder() . 'storage/' . $row['blob_data'];
									unlink( $target );
								}
								
								//Now update the deleted record to ensure integrity
								$rslt = $GLOBALS['TYPO3_DB']->exec_UPDATEquery( 
									'`'.$table.'`',	
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
								
								$newVersionID = $pObj->copyMappingArray[$table][$srcId];
								$newFileName = tx_drblob_div::generateStorageFilename( $newVersionID );
								
								$sourceFile = tx_drblob_div::getStorageFolder() . $row['blob_data'];
								$targetFile = tx_drblob_div::getStorageFolder() . $newFileName;
								
								if( copy( $sourceFile, $targetFile ) ) {
									$rslt = $GLOBALS['TYPO3_DB']->exec_UPDATEquery( 
										'`'.$table.'`',	
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
	 * @name		__toString
	 * Output the class makes when calling <code>echo $obj;</code>
	 * 
	 * @access		public
	 * @return		String		"tx_drblob_tcemain"
	 */
	public function __toString() {
		return 'tx_drblob_tcemain';
	}
};


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.tx_drblob_tcemain.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.tx_drblob_tcemain.php']);
}
?>