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
 * Backend configuration wizard for the file list plugin (dr_blob)
 *
 * @author		Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @copyright 	Copyright &copy; 2005-present Daniel Regelein
 * @package 	TYPO3
 * @subpackage  dr_blob
 * @filesource 	EXT:dr_blob/Classes/WizIcon.php
 * @version 	2.4.0
 */
class Tx_DrBlob_WizIcon {

	public function proc( $wizardItems ) {
		global $LANG;
		$LL = $this->includeLocalLang();
		
		if( Tx_DrBlob_Div::extConf_enablePi1() ) {
			$wizardItems['plugins_tx_drblob_pi1'] = array(
				'icon' => t3lib_extMgm::extRelPath( 'dr_blob' ) . 'Resources/Public/Backend/' . 'ce_wiz.gif',
				'title' => $LANG->getLLL( 'pi1_title', $LL ),
				'description' => $LANG->getLLL( 'pi1_plus_wiz_description', $LL ),
				'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=dr_blob_pi1'
			);
		}
		
		if( Tx_DrBlob_Div::extConf_enablePi2() ) {
			$wizardItems['plugins_tx_drblob_pi2'] = array(
				'icon' => t3lib_extMgm::extRelPath( 'dr_blob' ) . 'Resources/Public/Backend/' . 'ce_wiz.gif',
				'title' => $LANG->getLLL( 'pi2_title', $LL ),
				'description' => $LANG->getLLL( 'pi2_plus_wiz_description', $LL ),
				'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=drblob_pi2'
			);
		}
		
		return $wizardItems;
	}
	
	
	private function includeLocalLang()	{
		$llFile = t3lib_extMgm::extPath( 'dr_blob' ) . 'Resources/Private/Language/locallang_wiz.xml';
		$LOCAL_LANG = t3lib_div::readLLXMLfile( $llFile, $GLOBALS['LANG']->lang );
		return $LOCAL_LANG;
	}
}

if ( defined( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Classes/WizIcon.php'] )	{
	include_once( $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Classes/WizIcon.php'] );
}
?>