<?php
if ( !defined( 'TYPO3_MODE' ) ) {
	die ( 'Access denied.' );
}


$TCA['tx_drblob_content'] = array (
	'ctrl' => $TCA['tx_drblob_content']['ctrl'],

	'interface' => array (
		'showRecordFieldList' => 'title,author,author_email,category,blob_name,blob_size,blob_type,hidden,fe_group,starttime,endtime,download_count',
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
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
			'config' => Array (
				'type' => 'select',
				'size' => 5,
				'maxitems' => 20,
				'items' => array (
					array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
				),
				'exclusiveKeys' => '-1,-2',
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
		'author' => Array (
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.author',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80',
				'default' => $GLOBALS['BE_USER']->user['realName']
			)
		),
		'author_email' => Array (
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.email',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80',
				'default' => $GLOBALS['BE_USER']->user['email']
			)
		),
		'type' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.type',
			'displayCond' => 'REC:NEW:true',
			'config' => array (
				'type' => ( sizeof( tx_drblob_div::extConf_getAllowedStorageTypes() ) > 1 ) ? 'radio' : 'passthrough',
				'items' => array (
					//This will be filled later
				),
				'default' => tx_drblob_div::extConf_defaultStorageTypes(),
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
		'images' => array (
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.images',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
				'max_size' => '10000',
				'uploadfolder' => 'uploads/pics',
				'show_thumbs' => '1',
				'size' => 3,
				'autoSizeMax' => 15,
				'maxitems' => '99',
				'minitems' => '0'
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
			'l10n_mode' => 'noCopy',
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.download_count',
			'config' => array (
				'type' => ( tx_drblob_div::extConf_enableDownloadCounterReset() ? 'user' : 'passthrough' ),
				'userFunc' => 'tx_drblob_FormFields->inputDownloadCounter'
			)
		),
		'category' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.category',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_drblob_category',
				'foreign_table_where' => tx_drblob_div::extConf_getCategoryWhereForTCA(),
				'MM' => 'tx_drblob_category_mm',
				'minitems' => 0,
				'maxitems' => 500,
				'size' => 6,
				'default' => ''
			)
		),
		'blob_name' => array (		
			'exclude' => 0,	
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.blob_name',
			//'displayCond' => 'FIELD:type!=3',
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
				'maxitems' => 1,
				'maxsize' => 1000000,
				'size' => 1,
				'allowed' => '*',
				'disallowed' => 'php,php3,php4,php5,php6,phtml,inc',
				'disable_controls' => 'browser',
				'uploadfolder' => 'uploads/tx_drblob/storage'
			)
		)
	),

	'types' => array (
		'1' => array( 'showitem' => '
			--div--;LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.tab.general,title;;1;;2-2-2,type,sys_language_uid;;2;;,description;;;richtext:rte_transform[mode=ts],images,category,download_count;;;;2-2-2,,blob_name;;3;;,blob_data,
			--div--;LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.tab.visibility,hidden,starttime,endtime,fe_group,is_vip
		'),
	
	),

	'palettes' => array (
		'1' => array( 'showitem' => 'author,author_email' ),
		'2' => array( 'showitem' => 'l18n_parent,t3ver_label' ),
		'3' => array( 'showitem' => 'blob_size,blob_type,blob_checksum'  ),
	)
);

if( tx_drblob_div::extConf_isStorageTypeAllowed( 'db' ) ) {
	$TCA['tx_drblob_content']['columns']['type']['config']['items'][] = array( 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.type.db', 1 );
}
if( tx_drblob_div::extConf_isStorageTypeAllowed( 'fs' ) ) {
	$TCA['tx_drblob_content']['columns']['type']['config']['items'][] = array( 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.type.fs', 2 );
}
if( tx_drblob_div::extConf_isStorageTypeAllowed( 'fsus' ) ) {
	$TCA['tx_drblob_content']['columns']['type']['config']['items'][] = array( 'LLL:EXT:dr_blob/locallang_tca.xml:tx_drblob_content.type.fsus', 3 );
}



$TCA['tx_drblob_category'] = array(
	'ctrl' => $TCA['tx_drblob_category']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,crdate,hidden',
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


/*
$TCA['tx_drblob_personal'] = array(
	'ctrl' => $TCA['tx_drblob_personal']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => '',
	),
	'columns' => array (
		'uid_feusers' => array (		
			'exclude' => 0,
			'label' => 'user',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'fe_users',
				'foreign_table_where' => 'ORDER BY fe_users.name',
			)
		),
		'uid_pages' => array (		
			'exclude' => 0,
			'label' => 'pagese',		
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'pages',
				'foreign_table_where' => 'ORDER BY pages.title',
			)
		),
	),
	'types' => array (
		'0' => array( 'showitem' => 'uid_feusers,uid_pages' )
	),
	'palettes' => array()
);
*/
?>