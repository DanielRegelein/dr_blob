<?php
if ( !defined( 'TYPO3_MODE' ) ) {
	die ( 'Access denied.' );
}
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dr_blob']);

$TCA['tx_drblob_content'] = array (
	'ctrl' => array(
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
		'iconfile' => t3lib_extMgm::extRelPath( 'dr_blob' ).'ico/ext_icon_content.gif',
	),

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
		'sorting' => array(
			
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
		'title' => array (		
			'exclude' => 0,
			'l10n_mode' => 'prefixLangTitle',
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'max' => '255',	
				'eval' => 'required,trim',
			)
		),
		'description' => array (		
			'exclude' => 0,
			'l10n_mode' => 'prefixLangTitle',		
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.description',		
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
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.is_vip',
			'config' => array (
				'type' => 'check',
				'items' => array(
					array( 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.is_vip.desc' , '1' )
				),
				'eval' => 'int'
			)
		),
		'download_count' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.download_count',
			'config' => array (
				'type' => ( $extConf['enableCounterReset'] ? 'user' : 'passthrough' ),
				'userFunc' => 'tx_drblob_FormFields->inputDownloadCounter'
			)
		),
		'blob_name' => array (		
			'exclude' => 0,	
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_name',		
			'config' => array (
				'type' => 'user',	
				'userFunc' => 'tx_drblob_FormFields->inputFileName',
			)
		),
		'blob_size' => array (		
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_size',		
			'config' => array (
				'type' => 'user',
				'userFunc' => 'tx_drblob_FormFields->inputFileSize',
				
			)
		),
		'blob_type' => array (		
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_type',		
			'config' => array (
				'type' => 'user',
				'userFunc' => 'tx_drblob_FormFields->inputFileType'				
			)
		),
		
		'blob_data' => array(
			'exclude' => 0,
			'l10n_mode' => 'mergeIfNotBlank',
			'l10n_display' => 'hideDiff',
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_data',
			'config' => array(
				'type' => 'user',
				'userFunc' => 'tx_drblob_FormFields->inputFile'
			)
		)
	),

	'types' => array (
		'0' => array( 'showitem' => 'hidden;;1;;1-1-1,title;;;;2-2-2,sys_language_uid;;2;;,description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts];3-3-3,is_vip,download_count,blob_name,blob_size,blob_type,blob_data'),
	),

	'palettes' => array (
		'1' => array( 'showitem' => 'starttime,endtime,fe_group' ),
		'2' => array( 'showitem' => 'l18n_parent' ),
	)
);
?>