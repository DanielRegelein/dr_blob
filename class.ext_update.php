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

	var $update = array(
		'fixEmptyChecksum' => array(),
		'fixEmptyAuthor' => array(),
		'fixStaticTemplates' => array()
	);
	
	
	var $ll = 'LLL:EXT:dr_blob/Resources/Private/Language/locallang_wiz.xml:updater.';


	/**
	 * Main function, returning the HTML content of the module
	 *
	 * @return	string		HTML
	 */
	function main() {
		
			//Query for updateable records for rows without a checksum
		$resChecksum = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, title, type, blob_data', 
			Tx_DrBlob_Div::CONTENT_TABLE, 
			'`deleted` = 0 AND `type` IN (1,2) AND `blob_size` > 0 AND `blob_checksum` = \'\' '
		);
		if ( $resChecksum && $GLOBALS['TYPO3_DB']->sql_num_rows( $resChecksum ) ) {
			while( $rowChecksum = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $resChecksum ) ) {
				$this->update['fixEmptyChecksum'][] = $rowChecksum;
			}
		}

			//Query for updateable records for rows without a value for the author-field
		$resAuthor = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid,title,cruser_id', 
			Tx_DrBlob_Div::CONTENT_TABLE, 
			'`deleted` = 0 AND `author` = \'\' '
		);
		if ( $resAuthor && $GLOBALS['TYPO3_DB']->sql_num_rows( $resAuthor ) ) {
			while( $rowAuthor = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $resAuthor ) ) {
				$this->update['fixEmptyAuthor'][] = $rowAuthor;
			}
		}
		
			//Query for updateable records for TS-templates that include the old static tmpl path
		$resTStmpl = $GLOBALS['TYPO3_DB']->exec_SELECTquery( 
			'uid,pid,title,include_static_file', 
			'sys_template',
			'deleted=0 AND (
				include_static_file LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr( '%EXT:dr_blob/static/%', 'sys_template' ) . ' OR
				include_static_file LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr( '%EXT:dr_blob/pi1/static/ts/%', 'sys_template' ) . ' OR
				include_static_file LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr( '%EXT:dr_blob/pi1/static/xml/%', 'sys_template' ) . '
			)'
		);
		if( $resTStmpl && $GLOBALS['TYPO3_DB']->sql_num_rows( $resTStmpl ) ) {
			while( $rowTStmpl = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $resTStmpl ) ) {
				$this->update['fixStaticTemplates'][] = $rowTStmpl;
			}
		}
		
		$out = '';
		if (t3lib_div::_GP('do_update')) {
			$out .= '<a href="' . t3lib_div::linkThisScript(array('do_update' => '', 'func' => '')) . '">' . $GLOBALS['LANG']->sL($this->ll . 'back') . '</a><br>';

			$func = trim(t3lib_div::_GP('func'));
			if (method_exists($this, $func)) {
				$out .= '
				<div style="padding:15px 15px 20px 0;">
				<div class="typo3-message message-ok">
   				<div class="message-header">' . $GLOBALS['LANG']->sL('LLL:EXT:dr_blob/Resources/Private/Language/locallang_wiz.xml:updater.updateresults') . '</div>
  				<div class="message-body">
				' . $this->$func() . '
				</div>
				</div></div>';
			} else {
				$out .= '
				<div style="padding:15px 15px 20px 0;">
				<div class="typo3-message message-error">
   					<div class="message-body">ERROR: ' . $func . '() not found</div>
   				</div>
   				</div>';
			}
		} else {
			$out .= '<a href="' . t3lib_div::linkThisScript(array('do_update' => '', 'func' => '')) . '">' . $GLOBALS['LANG']->sL($this->ll . 'reload') . '
			<img style="vertical-align:bottom;" ' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/refresh_n.gif', 'width="18" height="16"') . '></a><br>';

			$out .= $this->displayWarning();

			$out .= '<h3>' . $GLOBALS['LANG']->sL($this->ll . 'actions') . '</h3>';

				// fixEmptyChecksum
			$out .= $this->displayUpdateOption( 'fixEmptyChecksum', count( $this->update['fixEmptyChecksum'] ), 'update_fixEmptyChecksum' );

				// fixEmptyAuthor
			$out .= $this->displayUpdateOption( 'fixEmptyAuthor', count( $this->update['fixEmptyAuthor'] ),'update_fixEmptyAuthor' );

				// fixStaticTemplates
			$out .= $this->displayUpdateOption( 'fixStaticTemplates', count( $this->update['fixStaticTemplates'] ),'update_fixStaticTemplates' );
			
		}

		return $out;
	}


	function displayUpdateOption( $k, $count, $func ) {

		$msg = $GLOBALS['LANG']->sL( $this->ll . 'msg_' . $k ) . ' ';
		$msg .= '<br /><strong>' . sprintf( $GLOBALS['LANG']->sL( $this->ll . 'foundMsg_' . $k ), $count ) . '</strong>';
		
		
		if ($count == 0) {
			$i = 'ok';

		} else {
			$i = 'warning2';
		}
		$msg .= ' <img ' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/icon_' . $i . '.gif', 'width="18" height="16"') . ' />';

		if ($count) {
			$msg .= '<p style="margin:5px 0;">' . $GLOBALS['LANG']->sL($this->ll . 'question_' . $k) . '<p>';
			$msg .=  '<p style="margin-bottom:10px;"><em>'.$GLOBALS['LANG']->sL($this->ll . 'questionInfo_' . $k) . '</em><p>';
			$msg .= $this->getButton($func);
		} else {
			$msg .= '<br />' . $GLOBALS['LANG']->sL('LLL:EXT:dr_blob/Resources/Private/Language/locallang_wiz.xml:updater.nothingtodo');

		}

		$out = $this->wrapForm($msg,$GLOBALS['LANG']->sL($this->ll . 'lbl_' . $k));
		$out .= '<br /><br />';

		return $out;
	}


	function displayWarning() {
		$out = '
		<div style="padding:15px 15px 20px 0;">
			<div class="typo3-message message-warning">
   				<div class="message-header">' . $GLOBALS['LANG']->sL('LLL:EXT:dr_blob/Resources/Private/Language/locallang_wiz.xml:updater.warningHeader') . '</div>
  				<div class="message-body">
					' . $GLOBALS['LANG']->sL('LLL:EXT:dr_blob/Resources/Private/Language/locallang_wiz.xml:updater.warningMsg') . '
				</div>
			</div>
		</div>';

		return $out;
	}


	function wrapForm($content, $fsLabel) {
		$out = '<form action="">
			<fieldset style="background:#f4f4f4;margin-right:15px;">
			<legend>' . $fsLabel . '</legend>
			' . $content . '

			</fieldset>
			</form>';
		return $out;
	}


	function getButton($func, $lbl = 'DO IT') {

		$params = array('do_update' => 1, 'func' => $func);

		$onClick = "document.location='" . t3lib_div::linkThisScript($params) . "'; return false;";
		$button = '<input type="submit" value="' . $lbl . '" onclick="' . htmlspecialchars($onClick) . '">';

		return $button;
	}


	
	/*
	 * 
	 * Update procedures
	 * 
	 */

	
	/**
	 * This method updates sys_template-records that try to include static templates from
	 * the old path.
	 *
	 * @return String Successmessage
	 * @access private
	 */
	private function update_fixStaticTemplates() {
		$msg = array();
		foreach( $this->update['fixStaticTemplates'] as $ts) {
			$oldincFile = $ts['include_static_file'];

			$s = array( 'EXT:dr_blob/static', 'EXT:dr_blob/pi1/static/ts', 'EXT:dr_blob/pi1/static/xml' );
			$r = array( 'EXT:dr_blob/Configuration/TypoScript/Pi1', 'EXT:dr_blob/Configuration/TypoScript/Pi1', 'EXT:dr_blob/Configuration/TypoScript/RSS' );
			$newincfile = str_replace( $s, $r, $oldincFile );
			$fields_values = array( 'include_static_file' => $newincfile );
			if ( $GLOBALS['TYPO3_DB']->exec_UPDATEquery( 'sys_template', 'uid=' . $ts['uid'], $fields_values ) ) {
				$msg[] = 'Updated template "' . $ts['title'] . '" uid: ' . $ts['uid'] . ', page: ' . $ts['pid'] . '<br /><br />' . $newincfile;
			}
		}
		return implode( '<br />', $msg );
	}
	

	function update_fixEmptyChecksum() {
		$msg = array();
		foreach( $this->update['fixEmptyChecksum'] as $row ) {
			
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
			
			//Calculating hash
			$hash = md5( $blobData );
			
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				Tx_DrBlob_Div::CONTENT_TABLE, 
				'uid=' . $row['uid'], 
				array( 
					'blob_checksum' => $hash 
				)
			) ;
			if ( $res ) {
				$msg[] = 'Updated record [ uid=' . $row['uid'] . ', title=' . $row['title'] . ', type=' . $row['type'] . ']';
			}
		}
		return implode('<br />', $msg);
	}

	
	function update_fixEmptyAuthor() {
		$msg = array();
		foreach ( $this->update['fixEmptyAuthor'] as $row ) {
			
				$rsltUser = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid,realName,email',
					'be_users',
					'uid=\'' . $row['cruser_id'] . '\''
				);
				if( $rsltUser && $GLOBALS['TYPO3_DB']->sql_num_rows( $rsltUser ) ) {
					$rowUser = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rsltUser );
					
					$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
						Tx_DrBlob_Div::CONTENT_TABLE, 
						'uid=' . $row['uid'], 
						array( 
							'author' => $rowUser['realName'],
							'author_email' => $rowUser['email']
						)
					) ;
					if ( $res ) {
						$msg[] = 'Updated record [ uid=' . $row['uid'] . ', title=' . $row['title'] . ', author=' . $rowUser['realName'] . ' ]';
					}
			} else {
				$msg[] = 'Skipped record [ uid=' . $row['uid'] . ', title=' . $row['title'] . ' ]: no author found';
			}
		}
		return implode( '<br />', $msg );
	}
	
	
	/**
	 * Checks how many rows are found and returns true if there are any
	 * (this function is called from the extension manager)
	 *
	 * @param	string		$what: what should be updated
	 * @return	boolean
	 */
	function access($what = 'all') {
		return true;
		
		if ($what == 'all') {
			if( is_object( $GLOBALS['TYPO3_DB'] ) ) {
				
				//Testcase 01 - Check for records without checksum
				$resChecksum = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid', 
					Tx_DrBlob_Div::CONTENT_TABLE, 
					'`deleted` = 0 AND `blob_size` > 0 AND `blob_checksum` = \'\' '
				);

				if ( $resChecksum && $GLOBALS['TYPO3_DB']->sql_num_rows( $resChecksum ) ) {
					return true;
				}
				
				//Testcase 02 - Check for records without author
				$resAuthor = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid', 
					Tx_DrBlob_Div::CONTENT_TABLE, 
					'`author` <> \'\' '
				);

				if ( $resAuthor && !$GLOBALS['TYPO3_DB']->sql_num_rows( $resAuthor ) ) {
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