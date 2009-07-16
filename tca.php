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


$TCA["tx_drblob_content"] = Array (
	"ctrl" => Array(
		"label" => "title",
		"title" => "LLL:EXT:dr_blob/locallang_db.php:tt_content.list_type_pi1",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"delete" => "deleted",
		"default_sortby" => "ORDER BY title ASC",
		"fe_group" => "fe_group",
		"starttime" => "starttime",
		"endtime" => "endtime",
		"enablecolumns" => Array(
			"disabled" => "hidden",
			"starttime" => "starttime",
			"endtime" => "endtime",
			"fe_group" => "fe_group"
		),
		"iconfile" => t3lib_extMgm::extRelPath("dr_blob")."ext_icon.gif"
	),

	"interface" => Array (
		"showRecordFieldList" => "title,crdate,blob_name,blob_size,blob_type,hidden,fe_group,starttime,endtime",
		"maxDBListItems" => 20
	),

	"feInterface" => $TCA["tx_drblob_content"]["feInterface"],

	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		'fe_group' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
			'config' => Array (
				'type' => 'select',	
				'items' => Array (
					Array('', 0),
					Array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),					
					Array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"title" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "255",	
				"eval" => "required,trim",
			)
		),
		"description" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"is_vip" => Array(
			"exclude" => 1, 
			"label" => "LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.is_vip",
			"config" => Array (
				"type" => "check",
				"value" => "1",
				"eval" => "int"
			)
		),
		"blob_name" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_name",		
			"config" => Array (
				"type" => "user",	
				"userFunc" => "user_dispInputFileName"	
			)
		),
		"blob_size" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_size",		
			"config" => Array (
				"type" => "user",	
				"userFunc" => "user_dispInputFileSize"
			)
		),
		"blob_type" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_type",		
			"config" => Array (
				"type" => "user",	
				"userFunc" => "user_dispInputFileType"
			)
		),
		"blob_data" => Array(
			"exclude" => 0,		
			"label" => "LLL:EXT:dr_blob/locallang_db.php:tx_drblob_content.blob_data",
			"config" => Array(
				"type" => "user",
				"userFunc" => "user_dispInputFile"
			)
		)
	),

	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts];3-3-3, is_vip, blob_name, blob_size, blob_type, blob_data")
	),

	"palettes" => Array (
		"1" => Array("showitem" => "starttime,endtime,fe_group")
	)
);

$TYPO3_CONF_VARS['BE']["XCLASS"]["typo3/alt_doc.php"] = PATH_typo3conf.'ext/dr_blob/class.ux_SC_alt_doc.php';

?>