<?php

########################################################################
# Extension Manager/Repository config file for ext: "dr_blob"
#
# Auto generated 11-11-2007 21:17
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'File list',
	'description' => 'Add a list of files that are stored in the database instead of the filesystem to grant a secure data storage',
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
	'author_email' => 'Daniel.Regelein@diehl-informatik.de',
	'author_company' => 'DIEHL Informatik GmbH',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '3.5.0-0.0.0',
			'php' => '3.0.0-0.0.0',
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:33:{s:23:"class.ux_SC_alt_doc.php";s:4:"b558";s:12:"ext_icon.gif";s:4:"46ed";s:15:"ext_icon__f.gif";s:4:"2e29";s:16:"ext_icon__fu.gif";s:4:"b3b3";s:15:"ext_icon__h.gif";s:4:"8e13";s:17:"ext_icon__hfu.gif";s:4:"4539";s:16:"ext_icon__ht.gif";s:4:"ddb5";s:17:"ext_icon__htf.gif";s:4:"ddb5";s:17:"ext_icon__htu.gif";s:4:"04a2";s:16:"ext_icon__hu.gif";s:4:"534f";s:15:"ext_icon__t.gif";s:4:"5fb2";s:16:"ext_icon__tf.gif";s:4:"5fb2";s:17:"ext_icon__tfu.gif";s:4:"2a9b";s:16:"ext_icon__tu.gif";s:4:"2a9b";s:15:"ext_icon__u.gif";s:4:"f9cf";s:15:"ext_icon__x.gif";s:4:"3baa";s:17:"ext_localconf.php";s:4:"becc";s:14:"ext_tables.php";s:4:"df28";s:14:"ext_tables.sql";s:4:"805d";s:24:"ext_typoscript_setup.txt";s:4:"4eb9";s:15:"flexform_ds.xml";s:4:"2c2c";s:13:"locallang.php";s:4:"f720";s:16:"locallang_db.php";s:4:"91a3";s:7:"tca.php";s:4:"890b";s:21:"res/dr_blob.list.tmpl";s:4:"f556";s:23:"res/dr_blob.single.tmpl";s:4:"0f92";s:16:"res/dr_blob.tmpl";s:4:"6eea";s:20:"res/dr_blob.top.tmpl";s:4:"ea84";s:14:"pi1/ce_wiz.gif";s:4:"a6c1";s:27:"pi1/class.tx_drblob_pi1.php";s:4:"eb69";s:35:"pi1/class.tx_drblob_pi1_wizicon.php";s:4:"545d";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.php";s:4:"23d2";}',
);

?>