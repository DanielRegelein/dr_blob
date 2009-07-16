<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
t3lib_extMgm::addToInsertRecords('tx_drblob_content');

$TCA['tx_drblob_content'] = array (
	'ctrl' => array (
		'title' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content',		
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
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ico/ext_icon_content.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'hidden, starttime, endtime, title, description, blob_name'
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime,endtime,fe_group'),
		'1' => array('showitem' => 'sys_language_uid,l18n_parent')
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,recursive,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

t3lib_extMgm::addPlugin(array('LLL:EXT:dr_blob/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:dr_blob/flexform_ds.xml');


	//Including the class containing the nessesary custom input elements
require_once( t3lib_extMgm::extPath($_EXTKEY) . 'class.tx_drblob_FormFields.php' );
	//Including the class containing the hook for TCEmain
require_once(t3lib_extMgm::extPath( $_EXTKEY ).'class.tx_drblob_tcemain.php');
	//Registing hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamap_postProcessFieldArray'][] = 'EXT:dr_blob/class.tx_drblob_tcemain.php:tx_drblob_tcemain';

	//Adding Folder Icons
t3lib_div::loadTCA('pages');
$TCA['pages']['columns']['module']['config']['items'][] = Array('LLL:EXT:dr_blob/locallang_db.php:pages.folderIconsDescr', 'files');
$ICON_TYPES['files'] = array('icon' => t3lib_extMgm::extRelPath($_EXTKEY).'ico/ext_icon_folder.gif');

	
	
if (TYPO3_MODE=='BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_drblob_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_drblob_pi1_wizicon.php';
}
?>