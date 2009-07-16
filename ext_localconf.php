<?php
if ( !defined( 'TYPO3_MODE' ) ) {
	die ( 'Access denied.' );
}

t3lib_extMgm::addUserTSConfig( 'options.saveDocNew.tx_drblob_content=1' );
$TYPO3_CONF_VARS['BE']['XCLASS']['typo3/alt_doc.php'] = PATH_typo3conf . 'ext/dr_blob/class.ux_SC_alt_doc.php';



## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,"editorcfg", "tt_content.CSS_editor.ch.tx_drblob_pi1 = < plugin.tx_drblob_pi1.CSS_editor",43);


t3lib_extMgm::addPItoST43($_EXTKEY,"pi1/class.tx_drblob_pi1.php","_pi1","list_type",1);


t3lib_extMgm::addTypoScript($_EXTKEY,"setup","
	tt_content.shortcut.20.0.conf.tx_drblob_content = < plugin.".t3lib_extMgm::getCN($_EXTKEY)."_pi1
	tt_content.shortcut.20.0.conf.tx_drblob_content.CMD = singleView
",43);
?>