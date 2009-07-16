<?php
if ( !defined( 'TYPO3_MODE' ) ) {
	die ( 'Access denied.' );
}

t3lib_extMgm::addUserTSConfig( 'options.saveDocNew.tx_drblob_content=1' );
t3lib_extMgm::addUserTSConfig( 'options.saveDocNew.tx_drblob_category=1' );
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_drblob_pi1.php','_pi1','list_type',1);

	//this hook is used to store the file directly into the database-table
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 
	'EXT:dr_blob/class.tx_drblob_tcemain.php:tx_drblob_tcemain';
?>