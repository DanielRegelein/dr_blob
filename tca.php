<?php
if ( !defined( 'TYPO3_MODE' ) ) {
	die ( 'Access denied.' );
}

$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dr_blob']);
if( strtolower( $extConf['categoryStorage'] ) == 'idlist' ) {
	$extConf['categoryStorageWhere'] = ' AND tx_drblob_category.pid IN( ###PAGE_TSCONFIG_IDLIST### )';
} else {
	$extConf['categoryStorageWhere'] = ' AND tx_drblob_category.pid = ###STORAGE_PID###';
}

if( strtolower( $extConf['fileStorageLocation'] ) == 'both' || strtolower( $extConf['fileStorageLocation'] ) == 'database' ) {
	$extConf['defaultTypeValue'] = 1;
} else {
	$extConf['defaultTypeValue'] = 2;
}


$TCA['tx_drblob_content'] = array (
	'ctrl' => $TCA['tx_drblob_content']['ctrl'],

	'interface' => array (
		'showRecordFieldList' => 'title,crdate,blob_name,blob_size,blob_type,hidden,fe_group,starttime,endtime,download_count',
		'maxDBListItems' => 20
	),

	'feInterface' => $TCA['tx_drblob_content']['feInterface'],

	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0',
				'range' => array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
			'config' => array (
				'type' => 'select',    
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),                    
					array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'sys_language_uid' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
		 		),
			)
		),
		'l18n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table' => 'tx_drblob_content',
				'foreign_table_where' => 'AND tx_drblob_content.pid=###CURRENT_PID### AND tx_drblob_content.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array(
			'config'=> array( 
				'type'=>'passthrough' 
			) 
		),
		't3ver_label' => array(
			'displayCond' => 'FIELD:t3ver_label:REQ:true',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => array(
				'type'=>'none',
				'cols' => 27
			)
		),
		'cruser_id' => array(
			'displayCond' => 'HIDE_FOR_NON_ADMINS',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.author',
			'config' => array(
				'type' => 'select',
				'items' => array (
					array('', 0),
				),
				'iconsInOptionTags' => true,
				'foreign_table' => 'be_users',
				'foreign_table_where' => '',
				'foreign_table_loadIcons' => true,
				'rootlevel' => true,
			)
		),
		'title' => array (		
			'exclude' => 0,
			'l10n_mode' => 'prefixLangTitle',
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'max' => '255',	
				'eval' => 'required,trim',
			)
		),
		'type' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.type',
			'displayCond' => 'REC:NEW:true',
			'config' => array (
				'type' => ( ( strtolower( $extConf['fileStorageLocation'] ) == 'both' ) ? 'radio' : 'passthrough' ),
				'items' => array (
					array( 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.type.db', 1 ),
					array( 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.type.fs', 2 ),
					array( 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.type.fsus', 3 ),
					array( 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.type.dam', 4 ),
				),
				'default' => $extConf['defaultTypeValue'],
				'eval' => 'required',
			)
		),
		'description' => array (		
			'exclude' => 0,
			'l10n_mode' => 'prefixLangTitle',		
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
				'wizards' => array(
					'_PADDING' => 2,
					'RTE' => array(
						'notNewRecords' => 1,
						'RTEonly' => 1,
						'type' => 'script',
						'title' => 'LLL:EXT:cms/locallang_ttc.php:bodytext.W.RTE',
						'icon' => 'wizard_rte2.gif',
						'script' => 'wizard_rte.php',
					),
				),
			)
		),
		'is_vip' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.is_vip',
			'config' => array (
				'type' => 'check',
				'items' => array(
					array( 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.is_vip.desc' , '1' )
				),
				'eval' => 'int'
			)
		),
		'download_count' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.download_count',
			'config' => array (
				'type' => ( $extConf['enableCounterReset'] ? 'user' : 'passthrough' ),
				'userFunc' => 'tx_drblob_FormFields->inputDownloadCounter'
			)
		),
		'category' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.category',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_drblob_category',
				'foreign_table_where' => $extConf['categoryStorageWhere'],
				'MM' => 'tx_drblob_category_mm',
				'minitems' => 0,
				'maxitems' => 500,
				'size' => 3,
				'default' => ''
			)
		),
		'blob_name' => array (		
			'exclude' => 0,	
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.blob_name',		
			'config' => array (
				'type' => 'user',	
				'userFunc' => 'tx_drblob_FormFields->inputFileName',
			)
		),
		'blob_size' => array (		
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.blob_size',		
			'config' => array (
				'type' => 'user',
				'userFunc' => 'tx_drblob_FormFields->inputFileSize',
				
			)
		),
		'blob_type' => array (		
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.blob_type',		
			'config' => array (
				'type' => 'user',
				'userFunc' => 'tx_drblob_FormFields->inputFileType'				
			)
		),
		'blob_checksum' => array (		
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.blob_checksum',		
			'config' => array (
				'type' => 'user',
				'userFunc' => 'tx_drblob_FormFields->inputFileChecksum'	
			)
		),
		'blob_data' => array(
			'exclude' => 0,
			'l10n_mode' => 'mergeIfNotBlank',
			'l10n_display' => 'hideDiff',
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.blob_data',
			'config' => array(
				'type' => 'user',
				'userFunc' => 'tx_drblob_FormFields->inputFile',
				'internal_type' => 'file',
				'size' => '1',
				'uploadfolder' => 'uploads/tx_drblob/storage/'
			)
		)
	),

	'types' => array (
		'1' => array( 'showitem' => 'hidden;;1;;1-1-1,title;;;;2-2-2,type,sys_language_uid;;2;;,description;;;richtext:rte_transform[mode=ts],category,is_vip,download_count;;;;2-2-2, --div--,blob_name;;3;;,blob_data'),
	),

	'palettes' => array (
		'1' => array( 'showitem' => 'starttime,endtime,fe_group' ),
		'2' => array( 'showitem' => 'l18n_parent,t3ver_label,cruser_id' ),
		'3' => array( 'showitem' => 'blob_size,blob_type,blob_checksum'  )
	)
);

$TCA['tx_drblob_category'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_category',
		'label' => 'title',
		'default_sortby' => 'ORDER BY title ASC',
		'delete' => 'deleted',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
		),
		'iconfile' => t3lib_extMgm::extRelPath( 'dr_blob' ).'ico/ext_icon_category.gif',
	),
	'interface' => array(
		'showRecordFieldList' => 'title,crdate,hidden',
		'maxDBListItems' => 10
	),
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'title' => array (		
			'exclude' => 0,
			'l10n_mode' => 'prefixLangTitle',
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_category.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'max' => '255',	
				'eval' => 'required,trim',
			)
		),
	),
	'types' => array (
		'0' => array( 'showitem' => 'hidden,title' )
	),
	'palettes' => array()
);
?>