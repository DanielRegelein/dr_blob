<?php

########################################################################
# Extension Manager/Repository config file for ext "dr_blob".
#
# Auto generated 27-05-2010 23:32
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'File list',
	'description' => 'This plugin generates a list of downloads that are stored in the database, or in a folder in- or outside the document root directory of typo3 to ensure a secure data storage.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '2.3.1',
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
			'php' => '5.0.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:62:{s:9:"Changelog";s:4:"afcc";s:20:"class.ext_update.php";s:4:"65da";s:30:"class.tx_drblob_FormFields.php";s:4:"140e";s:26:"class.tx_drblob_befunc.php";s:4:"8353";s:30:"class.tx_drblob_cms_layout.php";s:4:"7062";s:23:"class.tx_drblob_div.php";s:4:"5c57";s:27:"class.tx_drblob_tcemain.php";s:4:"26a7";s:37:"class.ux_tx_indexedsearch_indexer.php";s:4:"341e";s:21:"ext_conf_template.txt";s:4:"e262";s:12:"ext_icon.gif";s:4:"46ed";s:17:"ext_localconf.php";s:4:"bdc3";s:14:"ext_tables.php";s:4:"6aab";s:14:"ext_tables.sql";s:4:"7ace";s:15:"flexform_ds.xml";s:4:"16dd";s:34:"locallang_csh_txdrblobcategory.xml";s:4:"194b";s:33:"locallang_csh_txdrblobcontent.xml";s:4:"cf02";s:17:"locallang_tca.xml";s:4:"fd66";s:17:"locallang_wiz.xml";s:4:"8cc3";s:7:"tca.php";s:4:"ac05";s:17:"doc/developer.sxw";s:4:"1d34";s:14:"doc/manual.sxw";s:4:"90f5";s:14:"pi1/ce_wiz.gif";s:4:"40de";s:22:"pi1/ce_wiz_oldskin.gif";s:4:"a6c1";s:27:"pi1/class.tx_drblob_pi1.php";s:4:"b8ad";s:39:"pi1/class.tx_drblob_pi1_vFolderTree.php";s:4:"3551";s:35:"pi1/class.tx_drblob_pi1_wizicon.php";s:4:"8968";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"73ff";s:27:"pi1/static/ts/constants.txt";s:4:"92cd";s:23:"pi1/static/ts/setup.txt";s:4:"e0ab";s:28:"pi1/static/xml/constants.txt";s:4:"3fb7";s:24:"pi1/static/xml/setup.txt";s:4:"3cd6";s:16:"res/dr_blob.tmpl";s:4:"33f1";s:19:"res/dr_blob_v2.tmpl";s:4:"a320";s:14:"res/folder.gif";s:4:"705b";s:14:"res/rss_2.tmpl";s:4:"4b7e";s:30:"res/gfx/tx_drblob_category.gif";s:4:"85a9";s:31:"res/gfx/tx_drblob_content-1.gif";s:4:"4904";s:31:"res/gfx/tx_drblob_content-2.gif";s:4:"654b";s:31:"res/gfx/tx_drblob_content-3.gif";s:4:"43fa";s:29:"res/gfx/tx_drblob_content.gif";s:4:"46ed";s:29:"res/gfx/classicskin/pages.gif";s:4:"60be";s:24:"res/gfx/t3skin/pages.gif";s:4:"d7af";s:31:"res/vFolderTree/arrowbullet.gif";s:4:"dd1b";s:25:"res/vFolderTree/blank.gif";s:4:"9f3a";s:28:"res/vFolderTree/halfline.gif";s:4:"6a75";s:24:"res/vFolderTree/join.gif";s:4:"86ea";s:30:"res/vFolderTree/joinbottom.gif";s:4:"3822";s:27:"res/vFolderTree/jointop.gif";s:4:"211c";s:24:"res/vFolderTree/line.gif";s:4:"d3d7";s:25:"res/vFolderTree/minus.gif";s:4:"dd7a";s:31:"res/vFolderTree/minusbottom.gif";s:4:"a1b6";s:31:"res/vFolderTree/minusbullet.gif";s:4:"8336";s:29:"res/vFolderTree/minusonly.gif";s:4:"1c3b";s:28:"res/vFolderTree/minustop.gif";s:4:"2218";s:24:"res/vFolderTree/plus.gif";s:4:"86da";s:30:"res/vFolderTree/plusbottom.gif";s:4:"6ac4";s:30:"res/vFolderTree/plusbullet.gif";s:4:"d11c";s:28:"res/vFolderTree/plusonly.gif";s:4:"8cdc";s:27:"res/vFolderTree/plustop.gif";s:4:"e7ae";s:28:"res/vFolderTree/quadline.gif";s:4:"22bb";s:27:"res/vFolderTree/stopper.gif";s:4:"1424";}',
	'suggests' => array(
	),
);

?>