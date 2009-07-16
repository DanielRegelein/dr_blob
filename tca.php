<?php
if (!defined ("TYPO3_MODE")) {
	die ("Access denied.");
}
function user_dispInputFileName( $PA, $fobj ) {
	return 	'<input ' .
				'type="text" ' .
				'name="' . $PA['itemFormElName'] . '" ' .
				'value="' . $PA['itemFormElValue'] . '" ' .
				'size="48" ' .
				' / >';
}
function user_dispInputFileType( $PA, $fobj ) {
	return 	'<input ' .
				'type="text" ' .
				'name="' . $PA['itemFormElName'] . '" ' .
				'value="' . $PA['itemFormElValue'] . '" ' .
				'size="48" ' .
				'readonly / >';
}
function user_dispInputFileSize( $PA, $fobj ) {
	return 	'<input ' .
				'type="text" ' .
				'name="' . $PA['itemFormElName'] . '" ' .
				'value="' . $PA['itemFormElValue'] . '" ' .
				'size="20" ' .
				'readonly / > Bytes';
}
function user_dispInputFile( $PA, $fobj ) {
	return 	'<input ' .
				'type="file" ' .
				'name="' . $PA['itemFormElName'] . '" ' .
				'size="48" ' .
				'onChange="" ' .
			'/ >';
}


$TCA['tx_drblob_content'] = Array (
	'ctrl' => Array(
		'label' => 'title',
		'title' => 'LLL:EXT:dr_blob/locallang_db.php:tt_content.list_type_pi1',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'delete' => 'deleted',
		'default_sortby' => 'ORDER BY title ASC',
		'fe_group' => 'fe_group',
		'copyAfterDuplFields' => 'sys_language_uid',
		'useColumnsForDefaultValues' => 'sys_language_uid',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'starttime' => 'starttime',
		'endtime' => 'endtime',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group'
		),
		'iconfile' => t3lib_extMgm::extRelPath('dr_blob').'ext_icon.gif'
	),

	'interface' => array (
		'showRecordFieldList' => 'title,crdate,blob_name,blob_size,blob_type,hidden,fe_group,starttime,endtime',
		'maxDBListItems' => 20
	),

	'feInterface' => $TCA['tx_drblob_content']['feInterface'],

	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
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
			'l10n_mode' => 'mergeIfNotBlank',
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
		 		)
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
				'foreign_table' => 'tt_news',
				'foreign_table_where' => 'AND tt_news.uid=###REC_FIELD_l18n_parent### AND tt_news.sys_language_uid IN (-1,0)',
				'wizards' => array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
			 		'edit' => array(
						'type' => 'popup',
						'title' => 'edit default language version of this record ',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=600,width=700,status=0,menubar=0,scrollbars=1,resizable=1',
					),
				),
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
						'title' => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
						'icon' => 'wizard_rte2.gif',
						'script' => 'wizard_rte.php',
					),
				),
			)
		),
		'is_vip' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.is_vip',
			'config' => array (
				'type' => 'check',
				'value' => '1',
				'eval' => 'int'
			)
		),
		'blob_name' => array (		
			'exclude' => 0,	
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_name',		
			'config' => array (
				'type' => 'user',	
				'userFunc' => 'user_dispInputFileName'	
			)
		),
		'blob_size' => array (		
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_size',		
			'config' => array (
				'type' => 'user',	
				'userFunc' => 'user_dispInputFileSize'
			)
		),
		'blob_type' => array (		
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_type',		
			'config' => array (
				'type' => 'user',	
				'userFunc' => 'user_dispInputFileType'
			)
		),
		'blob_data' => array(
			'exclude' => 0,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_data',
			'config' => array(
				'type' => 'user',
				'userFunc' => 'user_dispInputFile'
			)
		)
	),

	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts];3-3-3, is_vip, blob_name, blob_size, blob_type, blob_data')
	),

	'palettes' => array (
		'1' => array('showitem' => 'starttime,endtime,fe_group')
	)
);

$TYPO3_CONF_VARS['BE']['XCLASS']['typo3/alt_doc.php'] = PATH_typo3conf.'ext/dr_blob/class.ux_SC_alt_doc.php';

?>