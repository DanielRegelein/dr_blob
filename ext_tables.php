<?php
if ( !defined ('TYPO3_MODE') ) {
	die ('Access denied.');
}


include_once( t3lib_extMgm::extPath( 'dr_blob' ) . 'class.tx_drblob_div.php' );


$TCA['tx_drblob_content'] = array(
	'ctrl' => array (
		'title' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content',		
		'label' => 'title',
		'label_alt' => 'blob_name',
		'sortby' => 'sorting',
		'default_sortby' => 'ORDER BY title ASC',	
		'copyAfterDuplFields' => 'sys_language_uid',
		'useColumnsForDefaultValues' => 'sys_language_uid',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'dividers2tabs' => t3lib_div::compat_version( '4.3' ) ? $TYPO3_USER_SETTINGS['ctrl']['dividers2tabs'] : true,
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
			'1' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'res/gfx/tx_drblob_content-1.gif',
			'2' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'res/gfx/tx_drblob_content-2.gif',
			'3' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'res/gfx/tx_drblob_content-3.gif',
		),
		'iconfile' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'res/gfx/tx_drblob_content.gif',
		'dynamicConfigFile' => t3lib_extMgm::extPath( $_EXTKEY ) . 'tca.php',
	),
);


$TCA['tx_drblob_category'] = array(
	'ctrl' => array (
		'title' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_category',		
		'label' => 'title',
		'default_sortby' => 'ORDER BY title ASC',	
		'delete' => 'deleted',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'enablecolumns' => array(		
			'disabled' => 'hidden',	
		),
		'iconfile' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'res/gfx/tx_drblob_category.gif',
		'dynamicConfigFile' => t3lib_extMgm::extPath( $_EXTKEY ) . 'tca.php',
	),
);


t3lib_div::loadTCA( 'tt_content' );
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,recursive,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';

t3lib_extMgm::addPlugin( array( 'LLL:EXT:dr_blob/locallang_tca.xml:tt_content.list_type_pi1', $_EXTKEY . '_pi1' ), 'list_type');
t3lib_extMgm::addStaticFile( $_EXTKEY, 'pi1/static/ts/', 'File List' );
t3lib_extMgm::addStaticFile( $_EXTKEY, 'pi1/static/xml/', 'File List RSS 2.0 Feed' );
t3lib_extMgm::addPiFlexFormValue( $_EXTKEY.'_pi1', 'FILE:EXT:dr_blob/flexform_ds.xml' );
t3lib_extMgm::addLLrefForTCAdescr( 'tx_drblob_content','EXT:dr_blob/locallang_csh_txdrblobcontent.xml' );
t3lib_extMgm::addLLrefForTCAdescr( 'tx_drblob_category','EXT:dr_blob/locallang_csh_txdrblobcategory.xml' );
t3lib_extMgm::allowTableOnStandardPages( 'tx_drblob_content' );

// add the dr_blob record to the insert records content element
t3lib_extMgm::addToInsertRecords( 'tx_drblob_content' );

	//Including the class containing the nessesary custom input elements
require_once( t3lib_extMgm::extPath( $_EXTKEY ) . 'class.tx_drblob_FormFields.php' );
	//Including the class containing the hook for TCEmain
require_once( t3lib_extMgm::extPath( $_EXTKEY ) . 'class.tx_drblob_tcemain.php' );


	//Integration into the extension "linkhandler"
if ( t3lib_extMgm::isLoaded( 'linkhandler' ) ) {
	t3lib_extMgm::addPageTSConfig('
		RTE.default.tx_linkhandler.dr_blob.label=Secure File
		RTE.default.tx_linkhandler.dr_blob.listTables=tx_drblob_content 
		mod.tx_linkhandler.dr_blob.label=Secure File
		mod.tx_linkhandler.dr_blob.listTables=tx_drblob_content
	');
}


	//Integration into the extension "css_styled_content"
if ( t3lib_extMgm::isLoaded( 'css_styled_content' ) ) {
	t3lib_extMgm::addPageTSConfig('
		# RTE mode in table "tx_drblob_content"
		RTE.config.tx_drblob_content.description.proc.overruleMode=ts_css
	');
}


t3lib_div::loadTCA( 'pages' );
$TCA['pages']['columns']['module']['config']['items'][] = array( 'LLL:EXT:' . $_EXTKEY . '/locallang_tca.xml:pages.folderIconsDescr', 'files' );
$ICON_TYPES['files'] = array( 'icon' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'res/gfx/' . ( t3lib_extMgm::isLoaded( 't3skin' ) ? 't3skin/' : 'classicskin/' ) . 'pages.gif' );

	
if (TYPO3_MODE=='BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_drblob_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_drblob_pi1_wizicon.php';
}
?>