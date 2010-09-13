<?php
if ( !defined( 'TYPO3_MODE' ) ) {
	die ( 'Access denied.' );
}

include_once( t3lib_extMgm::extPath( 'dr_blob' ) . 'Classes/Div.php' );

if ( TYPO3_MODE == 'BE' ) {

	t3lib_extMgm::addUserTSConfig( Tx_DrBlob_Div::getUserOrPageTS( 'UserTS/default.txt' ) );
	
		//this hook is used to store the file directly into the database-table
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:dr_blob/Classes/TceMain.php:tx_DrBlob_TceMain';
	
		//Hook for versioning / deleting files stored in the filesystem
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'EXT:dr_blob/Classes/TceMain.php:tx_DrBlob_TceMain';
	
		//Hook for displaying error messages
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['displayWarningMessages'][] = 'EXT:dr_blob/Classes/Befunc.php:tx_DrBlob_Befunc';
}



if( Tx_DrBlob_Div::extConf_usePi1() ) {
	t3lib_extMgm::addPItoST43( $_EXTKEY, 'Classes/Pi1.php', '_pi1', 'list_type', 1 );

		//Hook for displaying the list type in the tt_content-object
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['dr_blob_pi1'][] = 'EXT:dr_blob/Classes/CmsLayout.php:tx_DrBlob_CmsLayout->getExtensionSummary';
}

if( Tx_DrBlob_Div::extConf_usePi2() ) {
	
	Tx_Extbase_Utility_Extension::configurePlugin(
		$_EXTKEY,
		'Pi2',
		array(
			'File' => 'index,details,download',
		),
		array(
			'File' => 'download'
		)
	);
	
		//Hook for displaying the list type in the tt_content-object		
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['drblob_pi2'][] = 'EXT:dr_blob/Classes/CmsLayout.php:tx_DrBlob_CmsLayout->getExtensionSummary';
}


	// include XCLASS
if( Tx_DrBlob_Div::extConf_useIndexedSearchIntegration() ) {
	$TYPO3_CONF_VARS['FE']['XCLASS']['ext/indexed_search/class.indexer.php'] = t3lib_extMgm::extPath( $_EXTKEY, 'Resources/Private/PHP/class.ux_tx_indexedsearch_indexer.php' );
}

?>