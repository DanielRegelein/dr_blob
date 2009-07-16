<?php

########################################################################
# Extension Manager/Repository config file for ext: "dr_blob"
#
# Auto generated 11-11-2007 21:53
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'File list',
	'description' => 'Adds a frontend plugin named "filelist". That plugin allows to generate a list of files that are stored in the database instead of the file system to ensure a secure data storage.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '1.5.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 1,
	'createDirs' => 'uploads/tx_drblob',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Daniel Regelein',
	'author_email' => 'daniel.regelein@diehl-informatik.de',
	'author_company' => 'DIEHL Informatik GmbH',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:34:{s:9:"Changelog";s:4:"724b";s:30:"class.tx_drblob_FormFields.php";s:4:"c3bb";s:27:"class.tx_drblob_tcemain.php";s:4:"b348";s:21:"ext_conf_template.txt";s:4:"20aa";s:12:"ext_icon.gif";s:4:"46ed";s:15:"ext_icon__f.gif";s:4:"2e29";s:16:"ext_icon__fu.gif";s:4:"b3b3";s:15:"ext_icon__h.gif";s:4:"8e13";s:17:"ext_icon__hfu.gif";s:4:"4539";s:16:"ext_icon__ht.gif";s:4:"ddb5";s:17:"ext_icon__htf.gif";s:4:"ddb5";s:17:"ext_icon__htu.gif";s:4:"04a2";s:16:"ext_icon__hu.gif";s:4:"534f";s:15:"ext_icon__t.gif";s:4:"5fb2";s:16:"ext_icon__tf.gif";s:4:"5fb2";s:17:"ext_icon__tfu.gif";s:4:"2a9b";s:16:"ext_icon__tu.gif";s:4:"2a9b";s:15:"ext_icon__u.gif";s:4:"f9cf";s:15:"ext_icon__x.gif";s:4:"3baa";s:17:"ext_localconf.php";s:4:"7a1f";s:14:"ext_tables.php";s:4:"7dce";s:14:"ext_tables.sql";s:4:"ec49";s:24:"ext_typoscript_setup.txt";s:4:"cb78";s:15:"flexform_ds.xml";s:4:"bd01";s:13:"locallang.xml";s:4:"82dd";s:16:"locallang_db.xml";s:4:"f8f5";s:7:"tca.php";s:4:"036d";s:16:"res/dr_blob.tmpl";s:4:"1e82";s:14:"doc/manual.sxw";s:4:"9f75";s:14:"pi1/ce_wiz.gif";s:4:"a6c1";s:27:"pi1/class.tx_drblob_pi1.php";s:4:"de5e";s:35:"pi1/class.tx_drblob_pi1_wizicon.php";s:4:"d418";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"6388";}',
);

?>