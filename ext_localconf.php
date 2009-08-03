<?php
if ( !defined( 'TYPO3_MODE' ) ) {
	die ( 'Access denied.' );
}

t3lib_extMgm::addUserTSConfig( 'options.saveDocNew.tx_drblob_content=1' );
t3lib_extMgm::addPItoST43( $_EXTKEY, 'pi1/class.tx_drblob_pi1.php', '_pi1', 'list_type', 1 );


	//this hook is used to store the file directly into the database-table
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:dr_blob/class.tx_drblob_tcemain.php:tx_drblob_tcemain';

	//Hook for versioning / deleting files stored in the filesystem
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'EXT:dr_blob/class.tx_drblob_tcemain.php:tx_drblob_tcemain';

	//Hook for displaying error messages
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['displayWarningMessages'][] = 'EXT:dr_blob/class.tx_drblob_befunc.php:tx_drblob_befunc';

	//Hook for displaying the list type in the tt_content-object
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['dr_blob_pi1'][] = 'EXT:dr_blob/class.tx_drblob_cms_layout.php:tx_drblob_cms_layout->getExtensionSummary';



$extConf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dr_blob'] );
if( t3lib_extMgm::isLoaded( 'indexed_search' ) && strtolower( $extConf['useIndexedSearchIntegration'] ) == '1' ) {
		// include XCLASS
	$TYPO3_CONF_VARS['FE']['XCLASS']['ext/indexed_search/class.indexer.php'] = t3lib_extMgm::extPath( $_EXTKEY, 'class.ux_tx_indexedsearch_indexer.php' );
}
?>