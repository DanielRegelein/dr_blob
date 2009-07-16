<?php

########################################################################
# Extension Manager/Repository config file for ext: "dr_blob"
#
# Auto generated 11-11-2007 21:45
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'File list',
	'description' => 'Adds a frontend plugin named \'filelist\'. That plugin contains files that are stored in the database instead of the filesystem to ensure a secure data storage.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => 'uploads/tx_drblob',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Daniel Regelein',
	'author_email' => 'daniel.regelein@diehl-informatik.de',
	'author_company' => 'DIEHL Informatik GmbH',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.3.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '3.8.2-3.8.3',
			'php' => '4.3.5-5.0.5',
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:35:{s:23:"class.ux_SC_alt_doc.php";s:4:"b558";s:12:"ext_icon.gif";s:4:"46ed";s:15:"ext_icon__f.gif";s:4:"2e29";s:16:"ext_icon__fu.gif";s:4:"b3b3";s:15:"ext_icon__h.gif";s:4:"8e13";s:17:"ext_icon__hfu.gif";s:4:"4539";s:16:"ext_icon__ht.gif";s:4:"ddb5";s:17:"ext_icon__htf.gif";s:4:"ddb5";s:17:"ext_icon__htu.gif";s:4:"04a2";s:16:"ext_icon__hu.gif";s:4:"534f";s:15:"ext_icon__t.gif";s:4:"5fb2";s:16:"ext_icon__tf.gif";s:4:"5fb2";s:17:"ext_icon__tfu.gif";s:4:"2a9b";s:16:"ext_icon__tu.gif";s:4:"2a9b";s:15:"ext_icon__u.gif";s:4:"f9cf";s:15:"ext_icon__x.gif";s:4:"3baa";s:17:"ext_localconf.php";s:4:"becc";s:14:"ext_tables.php";s:4:"bb8b";s:14:"ext_tables.sql";s:4:"a59c";s:24:"ext_typoscript_setup.txt";s:4:"de20";s:15:"flexform_ds.xml";s:4:"9c7a";s:13:"locallang.php";s:4:"6a3f";s:16:"locallang_db.php";s:4:"71e0";s:7:"tca.php";s:4:"d411";s:16:"res/dr_blob.tmpl";s:4:"bf0d";s:38:"res/templateSubparts/dr_blob.list.tmpl";s:4:"fd70";s:40:"res/templateSubparts/dr_blob.search.tmpl";s:4:"6133";s:40:"res/templateSubparts/dr_blob.single.tmpl";s:4:"500f";s:37:"res/templateSubparts/dr_blob.top.tmpl";s:4:"9794";s:14:"doc/manual.sxw";s:4:"331e";s:14:"pi1/ce_wiz.gif";s:4:"a6c1";s:27:"pi1/class.tx_drblob_pi1.php";s:4:"f68c";s:35:"pi1/class.tx_drblob_pi1_wizicon.php";s:4:"545d";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.php";s:4:"af2f";}',
);

?>