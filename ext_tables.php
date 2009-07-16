<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addToInsertRecords('tx_drblob_content');

$TCA['tx_drblob_content'] = array (
	'ctrl' => array (
		'title' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content',		
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title ASC',	
		'delete' => 'deleted',	
		'fe_group' => 'fe_group',
		'starttime' => 'starttime',	
		'endtime' => 'endtime',
		'copyAfterDuplFields' => 'sys_language_uid',
		'useColumnsForDefaultValues' => 'sys_language_uid',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'versioning' => true, //Compatibilty with T3.7 / T3.8 
		'versioningWS' => true,
		'versioning_followPages' => true,
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
			'fe_group' => 'fe_group'
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'hidden, starttime, endtime, title, description, blob_name'
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime,endtime,fe_group')
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,recursive,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

t3lib_extMgm::addPlugin(array('LLL:EXT:dr_blob/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:dr_blob/flexform_ds.xml');

if (TYPO3_MODE=='BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_drblob_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_drblob_pi1_wizicon.php';
}
?>