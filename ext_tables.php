<?php
if ( !defined ('TYPO3_MODE') ) {
	die ('Access denied.');
}


include_once( t3lib_extMgm::extPath( 'dr_blob' ) . 'Classes/Div.php' );


$TCA[Tx_DrBlob_Div::CONTENT_TABLE] = array(
	'ctrl' => array (
		'title' => 'LLL:EXT:dr_blob/Resources/Private/Language/locallang_tca.xml:tx_drblob_content',		
		'label' => 'title',
		'label_alt' => 'blob_name',
		'sortby' => 'sorting',
		'default_sortby' => 'ORDER BY title ASC',	
		'copyAfterDuplFields' => 'sys_language_uid',
		'useColumnsForDefaultValues' => 'sys_language_uid',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'dividers2tabs' => true,
		'versioningWS' => true,
		'versioning_followPages' => true,
		'origUid' => 't3_origuid',
		'setToDefaultOnCopy' => 'download_count',
		'delete' => 'deleted',	
		'type' => 'type',
		'fe_group' => 'fe_group',
		'starttime' => 'starttime',	
		'endtime' => 'endtime',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
			'fe_group' => 'fe_group'
		),
		'typeicon_column' => 'type',
		'typeicons' => array (
			'1' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'Resources/Public/Icons/tx_drblob_content-1.gif',
			'2' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'Resources/Public/Icons/tx_drblob_content-2.gif',
			'3' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'Resources/Public/Icons/tx_drblob_content-3.gif',
		),
		'iconfile' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'Resources/Public/Icons/tx_drblob_content.gif',
		'dynamicConfigFile' => t3lib_extMgm::extPath( $_EXTKEY ) . 'Configuration/TCA/tca.php',
	),
);


$TCA[Tx_DrBlob_Div::CATEGORY_TABLE] = array(
	'ctrl' => array (
		'title' => 'LLL:EXT:dr_blob/Resources/Private/Language/locallang_tca.xml:tx_drblob_category',		
		'label' => 'title',
		'default_sortby' => 'ORDER BY title ASC',	
		'delete' => 'deleted',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'enablecolumns' => array(		
			'disabled' => 'hidden',	
		),
		'iconfile' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'Resources/Public/Icons/tx_drblob_category.gif',
		'dynamicConfigFile' => t3lib_extMgm::extPath( $_EXTKEY ) . 'Configuration/TCA/tca.php',
	),
);


	//Integration into the extension "linkhandler"
if ( t3lib_extMgm::isLoaded( 'linkhandler' ) ) {
	t3lib_extMgm::addPageTSConfig( Tx_DrBlob_Div::getUserOrPageTS( 'PageTS/linkhandler.txt' ) );
}

	//Integration into the extension "css_styled_content"
if ( t3lib_extMgm::isLoaded( 'css_styled_content' ) ) {
	t3lib_extMgm::addPageTSConfig( Tx_DrBlob_Div::getUserOrPageTS( 'PageTS/css_styled_content.txt' ) );
}


if ( TYPO3_MODE == 'BE' ) {
	
		//Adding the folder icon for files
	t3lib_div::loadTCA( 'pages' );
	$TCA['pages']['columns']['module']['config']['items'][] = array( 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_tca.xml:pages.folderIconsDescr', 'files' );
	if ( t3lib_div::int_from_ver( TYPO3_version ) >= 4004000 ) {
		t3lib_SpriteManager::addTcaTypeIcon( 'pages', 'contains-files', '../typo3conf/ext/' . $_EXTKEY . '/Resources/Public/Icons/pages.gif' );
	} else {
		$ICON_TYPES['files'] = array( 'icon' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'Resources/Public/Icons/pages.gif' );
	}

	t3lib_extMgm::addLLrefForTCAdescr( Tx_DrBlob_Div::CONTENT_TABLE, 'EXT:dr_blob/Resources/Private/Language/locallang_csh_txdrblobcontent.xml' );
	t3lib_extMgm::addLLrefForTCAdescr( Tx_DrBlob_Div::CATEGORY_TABLE, 'EXT:dr_blob/Resources/Private/Language/locallang_csh_txdrblobcategory.xml' );
	t3lib_extMgm::allowTableOnStandardPages( Tx_DrBlob_Div::CONTENT_TABLE );
	
		// add the dr_blob record to the insert records content element
	t3lib_extMgm::addToInsertRecords( Tx_DrBlob_Div::CONTENT_TABLE );


		//Including the class containing the nessesary custom input elements
	require_once( t3lib_extMgm::extPath( $_EXTKEY ) . 'Classes/FormFields.php' );
		//Including the class containing the hook for TCEmain
	require_once( t3lib_extMgm::extPath( $_EXTKEY ) . 'Classes/TceMain.php' );

	
	t3lib_div::loadTCA( 'tt_content' );
	
	
		//"old" Plugin architecture (pibase)
	if( Tx_DrBlob_Div::extConf_enablePi1() ) {
			//add wizicon
		$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_DrBlob_WizIcon'] = t3lib_extMgm::extPath( $_EXTKEY ) . 'Classes/WizIcon.php';
		t3lib_extMgm::addPlugin( array( 'LLL:EXT:dr_blob/Resources/Private/Language/locallang_tca.xml:tt_content.list_type_pi1', $_EXTKEY . '_pi1' ), 'list_type');
				
		$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,recursive,pages';
		$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';
		t3lib_extMgm::addPiFlexFormValue( $_EXTKEY.'_pi1', 'FILE:EXT:dr_blob/Configuration/FlexForms/pi1.xml' );

			//Add static templates
		t3lib_extMgm::addStaticFile( $_EXTKEY, 'Configuration/TypoScript/Pi1/', 'File List [classic]' );
		t3lib_extMgm::addStaticFile( $_EXTKEY, 'Configuration/TypoScript/RSS/', 'File List RSS 2.0 Feed' );
	}
	
		//"new" plugin architecture (Extbase / Fluid)
	if( Tx_DrBlob_Div::extConf_enablePi2() ) {
		Tx_Extbase_Utility_Extension::registerPlugin(
			$_EXTKEY,
			'Pi2',
			'LLL:EXT:dr_blob/Resources/Private/Language/locallang_tca.xml:tt_content.list_type_pi2'
		);
		
		t3lib_extMgm::addStaticFile( $_EXTKEY, 'Configuration/TypoScript/Pi2/', 'File List [new]' );
		$TCA['tt_content']['types']['list']['subtypes_excludelist']['drblob_pi2'] = 'layout,select_key';
		$TCA['tt_content']['types']['list']['subtypes_addlist']['drblob_pi2'] = 'pi_flexform';
		t3lib_extMgm::addPiFlexFormValue( 'drblob_pi2', 'FILE:EXT:dr_blob/Configuration/FlexForms/pi2.xml' );
	}
}
?>