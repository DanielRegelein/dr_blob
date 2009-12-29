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
 * @name		tx_drblob_FormFields
 * Class provides methods to generate the extension's custom input fields.
 * A file's content cannot be displayed here, so the user needs some information about the stored file.
 * To avoid changes to these information some of these fields are hidden, and a simple output is returned. 
 * 
 * @author		Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @category 	Frontend Plugins
 * @copyright 	Copyright &copy; 2005-present Daniel Regelein
 * @package 	dr_blob
 * @filesource	class.tx_drblob_FormFields.php
 * @since		Version 1.5.0, 2007-04-10
 * @version 	2.2.0
 */
class tx_drblob_FormFields {

	function inputFileName( $PA, $fobj ) {
		return 	'<input ' .
			'type="text" ' .
			'name="' . $PA['itemFormElName'] . '" ' .
			'value="' . htmlspecialchars( $PA['itemFormElValue'] ) . '" ' .
			'onchange="' . htmlspecialchars( implode( '',$PA['fieldChangeFunc'] ) ).'" ' .
			'size="48" ' .
			' / >';
	}


	function inputFileType( $PA, $fobj ) {
		return 	$PA['itemFormElValue'] . '<input ' .
			'type="hidden" ' .
			'name="' . $PA['itemFormElName'] . '" ' .
			'value="' . $PA['itemFormElValue'] . '" / >';
	}


	function inputFileSize( $PA, $fobj ) {
		if ( $PA['itemFormElValue'] ) {
			return t3lib_div::formatSize( $PA['itemFormElValue'], (' B| KB| MB| GB' ) ) . '<input ' .
				'type="hidden" ' .
				'name="' . $PA['itemFormElName'] . '" ' .
				'value="' . $PA['itemFormElValue'] . '" / >';
		} else {
			return '0 B';
		}
	}
	
	
	function inputFileChecksum( $PA, $fobj ) {
		return $PA['itemFormElValue'] . '<input ' .
			'type="hidden" ' .
			'name="' . $PA['itemFormElName'] . '" ' .
			'value="' . $PA['itemFormElValue'] . '" / >';
	}


	function inputFile( $PA, $fobj ) {
		return '<input ' .
			'type="file" ' .
			'name="' . $PA['itemFormElName'] . '" ' .
			'size="48" ' .
			'onChange="' . implode( '',$PA['fieldChangeFunc'] ) . ';" / >';
		
		
		#return 'TEST'.$PA['pObj']->getSingleField_typeGroup( 'tx_drblob_content', 'blob_data', $PA['row'], $PA );
		return 'Type: ' . $PA['row']['type'] . '<hr /><input ' .
			'type="file" ' .
			'name="' . $PA['itemFormElName'] . '" ' .
			'size="48" ' .
			'onChange="' . implode( '',$PA['fieldChangeFunc'] ) . ';" / >';
	}


	function inputDownloadCounter( $PA, $fobj ) {
		$temp = preg_replace( '/\#\#\#DOWNLOAD\_COUNT\#\#\#/', '%s', $GLOBALS['LANG']->sL( 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.download_count.desc' ), 1 );
		return (
			sprintf(
				$temp,
				'<input ' .
					'type="text" ' .
					'name="' . $PA['itemFormElName'] . '" ' . 
					'value="' . $PA['itemFormElValue'] . '" ' . 
					'size="3" ' .
					'readonly="readonly" /> '
			) . ' ' . 
			'<input type="button" ' . 
				'value="' . $GLOBALS['LANG']->sL('LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.download_count.reset') . '" ' . 
				'onClick="document.getElementsByName(\'' . $PA['itemFormElName'] . '\')[0].value = \'0\'; ' . implode( '',$PA['fieldChangeFunc'] ) . '" />'
		);
	}
};


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.tx_drblob_FormFields.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/class.tx_drblob_FormFields.php']);
}
?>