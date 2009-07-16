<?php
if ( !defined( 'TYPO3_MODE' ) ) {
	die ( 'Access denied.' );
}

t3lib_extMgm::addUserTSConfig( 'options.saveDocNew.tx_drblob_content=1' );
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_drblob_pi1.php','_pi1','list_type',1);
t3lib_extMgm::addTypoScript($_EXTKEY, 'setup', 'tt_content.shortcut.20.0.conf.tx_drblob_content = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi1.CMD = singleView',43);
t3lib_extMgm::addTypoScript($_EXTKEY, 'editorcfg', 'tt_content.CSS_editor.ch.tx_drblob_pi1 = < plugin.tx_drblob_pi1.CSS_editor', 43);


	//this hook is used to store the file directly into the database-table
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 
	'EXT:dr_blob/class.tx_drblob_tcemain.php:tx_drblob_tcemain';
?>