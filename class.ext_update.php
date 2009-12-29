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
class ext_update {

	/**
	 * Main function, returning the HTML content of the module
	 *
	 * @return	string		HTML
	 */
	function main() {
		$content = null;
		$content = $this->update_EmptyChecksum();
		return $content;
	}
	

	/**
	 * Add the checksum for all records
	 *
	 * @return String Content of the update interface
	 */
	function update_EmptyChecksum() {
		
		$content = null;
		$rslt = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, type, blob_data', 
			'tx_drblob_content', 
			'`deleted` = 0 AND `blob_size` > 0 AND `blob_checksum` = \'\' '
		);
		if ( $rslt && ( $numRows = $GLOBALS['TYPO3_DB']->sql_num_rows( $rslt ) ) ) {
		
			if (!t3lib_div::_GP('do_update')) {
				$content .= '<p>Found ' . $numRows . ' record(s) containing a file but no checksum</p><br /><br /><br />';
				
				$content .= '<fieldset>';
					$content .= '<legend>Do you really want to update there records?</legend>';
					$onClick = "document.location='".t3lib_div::linkThisScript(array('do_update' => 1))."'; return false;";
					$content .= '<form action=""><input type="submit" value="Perform update" onclick="'.htmlspecialchars($onClick).'" /></form>';
				$content .= '</fieldset>';
			} else {
				//update code here
				
				while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) ) {
					$content .= '<br />Updating record [ uid=' . $row['uid'] . ', type=' . $row['type'] . ' ] ... ';
					$hash = null;
					$blobData = null;
					
					switch( $row['type'] ) {
						case '1':
							$blobData = stripslashes( $row['blob_data'] );
						break;
						case '2':
							$file = tx_drblob_div::getStorageFolder() . $row['blob_data'];
							$fp = fopen( $file, 'r' );
								$blobData = fread( $fp, filesize ( $file ) );
							fclose( $fp );
							$blobData = stripslashes( $blobData );
						break;
						default:
							$content .= 'Error: wrong type';
						break;
					}
					
					if( $blobData ){
						$hash = md5( $blobData );
						$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
							'tx_drblob_content',
							'uid=\'' . $row['uid'] . '\'',
							array( 'blob_checksum' => $hash )
						);
					} else {
						$res = false;
					}
					$content .= ( $res ? 'success [ hash=' . $hash . ' ]' : 'Error while updating record' );
				}
				
			}
		
		} else {
			$content .= 'Nothing to update';
		}
		
		return $content;
	}
	

	/**
	 * Checks how many rows are found and returns true if there are any
	 * (this function is called from the extension manager)
	 *
	 * @param	string		$what: what should be updated
	 * @return	boolean
	 */
	function access($what = 'all') {
		if ($what == 'all') {
			if( is_object( $GLOBALS['TYPO3_DB'] ) ) {
				$testres = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid', 
					'tx_drblob_content', 
					'`deleted` = 0 AND `blob_size` > 0 AND `blob_checksum` = \'\' '
				);

				if ( $testres && $GLOBALS['TYPO3_DB']->sql_num_rows( $testres ) ) {
					return true;
				}
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.ext_update.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.ext_update.php']);
}
?>