<?php
if ( !defined ('TYPO3_MODE') ) {
	die ('Access denied.');
}
#t3lib_extMgm::addToInsertRecords('tx_drblob_content');

$TCA['tx_drblob_content'] = array(
	'ctrl' => array (
		'title' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content',		
		'label' => 'title',
		'sortby' => 'sorting',
		'default_sortby' => 'ORDER BY title ASC',	
		'copyAfterDuplFields' => 'sys_language_uid',
		'useColumnsForDefaultValues' => 'sys_language_uid',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'versioningWS' => true,
		'versioning_followPages' => true,
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
		'dynamicConfigFile' => t3lib_extMgm::extPath( $_EXTKEY ) . 'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'ico/ext_icon_content.gif',
		'typeicon_column' => 'type',
		'typeicons' => array (
			'1' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'ico/ext_icon_content_db.gif',
			'2' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'ico/ext_icon_content_fs.gif',
			'3' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'ico/ext_icon_content_fsus.gif',
			'4' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'ico/ext_icon_content_dam.gif',
		),
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
		'dynamicConfigFile' => t3lib_extMgm::extPath( $_EXTKEY ) . 'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath( $_EXTKEY ) . 'ico/ext_icon_category.gif',
	),
);



t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,recursive,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

t3lib_extMgm::addPlugin( array( 'LLL:EXT:dr_blob/locallang_tca.xml:tt_content.list_type_pi1', $_EXTKEY . '_pi1' ), 'list_type');
t3lib_extMgm::addStaticFile( $_EXTKEY, 'static/', 'File List (dr_blob)' );
t3lib_extMgm::addPiFlexFormValue( $_EXTKEY.'_pi1', 'FILE:EXT:dr_blob/flexform_ds.xml' );
t3lib_extMgm::addLLrefForTCAdescr( 'tx_drblob_content','EXT:dr_blob/locallang_csh_txdrblobcontent.xml' );
t3lib_extMgm::addLLrefForTCAdescr( 'tx_drblob_category','EXT:dr_blob/locallang_csh_txdrblobcategory.xml' );


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


	//Integration into the extension "t3skin"
if( t3lib_extMgm::isLoaded( 't3skin' ) ) {
	$skinPath = 't3skin/';
} else {
	$skinPath = '';
}


t3lib_div::loadTCA('pages');
$TCA['pages']['columns']['module']['config']['items'][] = array('LLL:EXT:' . $_EXTKEY . '/locallang_tca.xml:pages.folderIconsDescr', 'files');
$ICON_TYPES['files'] = array('icon' => t3lib_extMgm::extRelPath( $_EXTKEY ).'ico/' . $skinPath . 'ext_icon_folder.gif');

	
if (TYPO3_MODE=='BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_drblob_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_drblob_pi1_wizicon.php';
}
?>