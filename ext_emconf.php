<?php

########################################################################
# Extension Manager/Repository config file for ext: "dr_blob"
#
# Auto generated 05-03-2009 10:11
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'File list',
	'description' => 'This plugin allows to generate a list of files that are stored in the database, or in a folder in- or outside the document root directory of typo3 to ensure a secure data storage.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '2.0.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 1,
	'createDirs' => 'uploads/tx_drblob/storage',
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
			'typo3' => '4.0.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:92:{s:9:"Changelog";s:4:"ff72";s:30:"class.tx_drblob_FormFields.php";s:4:"a900";s:27:"class.tx_drblob_tcemain.php";s:4:"4af3";s:37:"class.ux_tx_indexedsearch_indexer.php";s:4:"96a6";s:21:"ext_conf_template.txt";s:4:"e0b8";s:12:"ext_icon.gif";s:4:"46ed";s:17:"ext_localconf.php";s:4:"112d";s:14:"ext_tables.php";s:4:"b88b";s:14:"ext_tables.sql";s:4:"40db";s:24:"ext_typoscript_setup.txt";s:4:"ae00";s:15:"flexform_ds.xml";s:4:"ae0b";s:34:"locallang_csh_txdrblobcategory.xml";s:4:"faaf";s:33:"locallang_csh_txdrblobcontent.xml";s:4:"c72b";s:17:"locallang_tca.xml";s:4:"e221";s:17:"locallang_wiz.xml";s:4:"ff94";s:7:"tca.php";s:4:"bb8e";s:16:"res/dr_blob.tmpl";s:4:"de9d";s:20:"static/constants.txt";s:4:"9034";s:16:"static/setup.txt";s:4:"ea4b";s:25:"ico/ext_icon_category.gif";s:4:"85a9";s:32:"ico/ext_icon_category__h.gif.gif";s:4:"f18b";s:24:"ico/ext_icon_content.gif";s:4:"46ed";s:27:"ico/ext_icon_content_db.gif";s:4:"4904";s:30:"ico/ext_icon_content_db__f.gif";s:4:"8d82";s:31:"ico/ext_icon_content_db__fu.gif";s:4:"556a";s:30:"ico/ext_icon_content_db__h.gif";s:4:"5e02";s:31:"ico/ext_icon_content_db__hf.gif";s:4:"5e02";s:32:"ico/ext_icon_content_db__hfu.gif";s:4:"3391";s:31:"ico/ext_icon_content_db__ht.gif";s:4:"ef7c";s:32:"ico/ext_icon_content_db__htf.gif";s:4:"ddb5";s:33:"ico/ext_icon_content_db__htfu.gif";s:4:"5db1";s:32:"ico/ext_icon_content_db__htu.gif";s:4:"3b13";s:31:"ico/ext_icon_content_db__hu.gif";s:4:"3391";s:30:"ico/ext_icon_content_db__t.gif";s:4:"4bff";s:31:"ico/ext_icon_content_db__tf.gif";s:4:"5fb2";s:32:"ico/ext_icon_content_db__tfu.gif";s:4:"2a9b";s:31:"ico/ext_icon_content_db__tu.gif";s:4:"6702";s:30:"ico/ext_icon_content_db__u.gif";s:4:"68b8";s:30:"ico/ext_icon_content_db__x.gif";s:4:"3baa";s:27:"ico/ext_icon_content_fs.gif";s:4:"654b";s:30:"ico/ext_icon_content_fs__f.gif";s:4:"36be";s:31:"ico/ext_icon_content_fs__fu.gif";s:4:"4b3a";s:30:"ico/ext_icon_content_fs__h.gif";s:4:"33c1";s:31:"ico/ext_icon_content_fs__hf.gif";s:4:"33c1";s:32:"ico/ext_icon_content_fs__hfu.gif";s:4:"4c18";s:31:"ico/ext_icon_content_fs__ht.gif";s:4:"ce40";s:33:"ico/ext_icon_content_fs__htfu.gif";s:4:"361e";s:32:"ico/ext_icon_content_fs__htu.gif";s:4:"361e";s:31:"ico/ext_icon_content_fs__hu.gif";s:4:"4c18";s:30:"ico/ext_icon_content_fs__t.gif";s:4:"4eb1";s:31:"ico/ext_icon_content_fs__tf.gif";s:4:"4eb1";s:32:"ico/ext_icon_content_fs__tfu.gif";s:4:"ade8";s:31:"ico/ext_icon_content_fs__tu.gif";s:4:"ade8";s:30:"ico/ext_icon_content_fs__u.gif";s:4:"d0f3";s:30:"ico/ext_icon_content_fs__x.gif";s:4:"3baa";s:23:"ico/ext_icon_folder.gif";s:4:"60be";s:26:"ico/ext_icon_folder__f.gif";s:4:"d180";s:27:"ico/ext_icon_folder__fp.gif";s:4:"405d";s:27:"ico/ext_icon_folder__fu.gif";s:4:"221e";s:28:"ico/ext_icon_folder__fup.gif";s:4:"812c";s:26:"ico/ext_icon_folder__h.gif";s:4:"b002";s:28:"ico/ext_icon_folder__hfu.gif";s:4:"803d";s:29:"ico/ext_icon_folder__hfup.gif";s:4:"c6de";s:27:"ico/ext_icon_folder__hp.gif";s:4:"eb40";s:27:"ico/ext_icon_folder__ht.gif";s:4:"7565";s:28:"ico/ext_icon_folder__htf.gif";s:4:"5213";s:29:"ico/ext_icon_folder__htfp.gif";s:4:"c42e";s:30:"ico/ext_icon_folder__htfup.gif";s:4:"2540";s:28:"ico/ext_icon_folder__htp.gif";s:4:"c42e";s:28:"ico/ext_icon_folder__htu.gif";s:4:"c4bb";s:29:"ico/ext_icon_folder__htup.gif";s:4:"2540";s:27:"ico/ext_icon_folder__hu.gif";s:4:"803d";s:28:"ico/ext_icon_folder__hup.gif";s:4:"12dd";s:26:"ico/ext_icon_folder__t.gif";s:4:"3307";s:27:"ico/ext_icon_folder__tf.gif";s:4:"6b17";s:28:"ico/ext_icon_folder__tfp.gif";s:4:"86ef";s:28:"ico/ext_icon_folder__tfu.gif";s:4:"d0ec";s:29:"ico/ext_icon_folder__tfup.gif";s:4:"4012";s:27:"ico/ext_icon_folder__tp.gif";s:4:"f434";s:27:"ico/ext_icon_folder__tu.gif";s:4:"d0ec";s:28:"ico/ext_icon_folder__tup.gif";s:4:"4012";s:26:"ico/ext_icon_folder__u.gif";s:4:"7c54";s:27:"ico/ext_icon_folder__up.gif";s:4:"555b";s:30:"ico/t3skin/ext_icon_folder.gif";s:4:"c074";s:14:"doc/manual.sxw";s:4:"ac67";s:14:"pi1/ce_wiz.gif";s:4:"40de";s:22:"pi1/ce_wiz_oldskin.gif";s:4:"a6c1";s:27:"pi1/class.tx_drblob_pi1.php";s:4:"dc0f";s:39:"pi1/class.tx_drblob_pi1_vFolderTree.php";s:4:"8854";s:35:"pi1/class.tx_drblob_pi1_wizicon.php";s:4:"b5b4";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"1183";}',
);

?>