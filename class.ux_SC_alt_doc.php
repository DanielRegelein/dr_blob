<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Daniel Regelein (Daniel.Regelein@diehl-informatik.de)
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
 * @name ux_SC_alt_doc
 * @extends SC_alt_doc
 * 
 * @author Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @package Typo3
 * @subpackage dr_blob
 */
class ux_SC_alt_doc extends SC_alt_doc {
	
	/**
	 * Do processing of data, submitting it to TCEmain.
	 *
	 * @return	void
	 */
	function processData()	{
		global $BE_USER,$TYPO3_CONF_VARS;

			// GPvars specifically for processing:
		$this->data = t3lib_div::_GP('data');
		$this->mirror = t3lib_div::_GP('mirror');
		$this->cacheCmd = t3lib_div::_GP('cacheCmd');
		$this->redirect = t3lib_div::_GP('redirect');
		$this->disableRTE = t3lib_div::_GP('_disableRTE');
		$this->returnNewPageId = t3lib_div::_GP('returnNewPageId');
		$this->vC = t3lib_div::_GP('vC');

			// See tce_db.php for relevate options here:
			// Only options related to $this->data submission are included here.
		$tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$tce->stripslashes_values=0;

			// Setting default values specific for the user:
		$TCAdefaultOverride = $BE_USER->getTSConfigProp('TCAdefaults');
		if (is_array($TCAdefaultOverride))	{
			$tce->setDefaultsFromUserTS($TCAdefaultOverride);
		}

			// Setting internal vars:
		if ($BE_USER->uc['neverHideAtCopy'])	{	$tce->neverHideAtCopy = 1;	}
		$tce->debug=0;
		$tce->disableRTE = $this->disableRTE;

			// Loading TCEmain with data:
		$tce->start($this->data,array());
		if (is_array($this->mirror))	{	$tce->setMirror($this->mirror);	}

			// If pages are being edited, we set an instruction about updating the page tree after this operation.
		if (isset($this->data['pages']))	{
			t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
		}


			// Checking referer / executing
		$refInfo=parse_url(t3lib_div::getIndpEnv('HTTP_REFERER'));
		$httpHost = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
		if ($httpHost!=$refInfo['host'] && $this->vC!=$BE_USER->veriCode() && !$TYPO3_CONF_VARS['SYS']['doNotCheckReferer'])	{
			$tce->log('',0,0,0,1,"Referer host '%s' and server host '%s' did not match and veriCode was not valid either!",1,array($refInfo['host'],$httpHost));
			debug('Error: Referer host did not match with server host.');
		} else {

				// Perform the saving operation with TCEmain:
			$tce->process_uploads($_FILES);
			$tce->process_datamap();

				// If there was saved any new items, load them:
			if (count($tce->substNEWwithIDs_table))	{

					// Resetting editconf:
				$this->editconf = array();

					// Traverse all new records and forge the content of ->editconf so we can continue to EDIT these records!
				foreach($tce->substNEWwithIDs_table as $nKey => $nTable)	{
					$this->editconf[$nTable][$tce->substNEWwithIDs[$nKey]]='edit';
					if ($nTable=='pages' && $this->retUrl!='dummy.php' && $this->returnNewPageId)	{
						$this->retUrl.='&id='.$tce->substNEWwithIDs[$nKey];
					}
				}

					// Finally, set the editconf array in the "getvars" so they will be passed along in URLs as needed.
				$this->R_URL_getvars['edit']=$this->editconf;

					// Unsetting default values since we don't need them anymore.
				unset($this->R_URL_getvars['defVals']);

					// Re-compile the store* values since editconf changed...
				$this->compileStoreDat();
			}


				// If a document is saved and a new one is created right after.
			if (isset($_POST['_savedoknew_x']) && is_array($this->editconf))	{

					// Finding the current table:
				reset($this->editconf);
				$nTable=key($this->editconf);

					// Finding the first id, getting the records pid+uid
				reset($this->editconf[$nTable]);
				$nUid=key($this->editconf[$nTable]);
				$nRec = t3lib_BEfunc::getRecord($nTable,$nUid,'pid,uid');

					// Setting a blank editconf array for a new record:
				$this->editconf=array();
				if ($this->getNewIconMode($nTable)=='top')	{
					$this->editconf[$nTable][$nRec['pid']]='new';
				} else {
					$this->editconf[$nTable][-$nRec['uid']]='new';
				}

					// Finally, set the editconf array in the "getvars" so they will be passed along in URLs as needed.
				$this->R_URL_getvars['edit']=$this->editconf;

					// Re-compile the store* values since editconf changed...
				$this->compileStoreDat();
			}

			$tce->printLogErrorMessages(
				isset($_POST['_saveandclosedok_x']) ?
				$this->retUrl :
				$this->R_URL_parts['path'].'?'.t3lib_div::implodeArrayForUrl('',$this->R_URL_getvars)	// popView will not be invoked here, because the information from the submit button for save/view will be lost .... But does it matter if there is an error anyways?
			);
			
			//----------------------------------------------------------------------------------
			// BO EXT: dr_blob
			//----------------------------------------------------------------------------------
			if ( key($this->editconf) == 'tx_drblob_content' ) {
				$rsID = key( $this->editconf['tx_drblob_content'] );

				if ( isset( $_POST['_savedoknew_x'] ) ) {
					$rsID = $rsID * (-1);
				}

				if ( is_array( $_FILES['data']['tmp_name']['tx_drblob_content'] ) ) {

					$rsHashID = key( $_FILES['data']['tmp_name']['tx_drblob_content'] );
					$fileName = $_FILES['data']['tmp_name']['tx_drblob_content'][$rsHashID]['blob_data'];

					if ( ( !empty( $fileName ) ) && ( $fileName != 'none' ) ) {
						//Open File
						$filePointer = fopen( $fileName, 'r' );
							$data = addslashes( fread( $filePointer, filesize( $fileName ) ) );
						fclose( $filePointer );

						//Prepare UPDATE-Array. Quoting the values is not nessesary, because this is done by 
						//the method $GLOBALS['TYPO3_DB']->UPDATEquery that is called from $GLOBALS['TYPO3_DB']->exec_UPDATEquery
						$arrValues = array(
							'blob_data' => $data,
							'blob_name' => $_FILES['data']['name']['tx_drblob_content'][$rsHashID]['blob_data'],
							'blob_size' => $_FILES['data']['size']['tx_drblob_content'][$rsHashID]['blob_data'],
							'blob_type' => $_FILES['data']['type']['tx_drblob_content'][$rsHashID]['blob_data']
						);
						$rslt = $GLOBALS['TYPO3_DB']->exec_UPDATEquery( 'tx_drblob_content', 'uid='.$rsID, $arrValues );
					}
				}
			}
			//----------------------------------------------------------------------------------
			// EO EXT: dr_blob
			//----------------------------------------------------------------------------------


			
		}
		if (isset($_POST['_saveandclosedok_x']) || $this->closeDoc<0)	{	//  || count($tce->substNEWwithIDs)... If any new items has been save, the document is CLOSED because if not, we just get that element re-listed as new. And we don't want that!
			$this->closeDocument(abs($this->closeDoc));
		}
	}
};
?>
