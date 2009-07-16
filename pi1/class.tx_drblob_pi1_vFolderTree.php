<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-present Daniel Regelein (Daniel.Regelein@diehl-informatik.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * @name		tx_drblob_pi1_vFolderTree
 * Virtual Folder Tree for the dr_blob-Extension
 *
 * @author		Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @category 	Frontend Plugins
 * @copyright 	Copyright &copy; 2005-present Daniel Regelein
 * @package 	dr_blob
 * @filesource 	pi1/class.tx_drblob_pi1_vFolderTree.php
 * @version 	2.0.0
 */

require_once( PATH_t3lib.'class.t3lib_treeview.php' );

class tx_drblob_pi1_vFolderTree extends t3lib_treeview {

	var $recursive = array();
	var $cObj = null;
	var $piVars = null;
	var $conf = array();
	
	var $tmp_pageSelect = null;
	
	function init( $conf, $cObj, $piVars ) {

		$this->recursive = $conf['recursive'];
		$this->cObj = $cObj;
		$this->piVars = $piVars;
		$this->conf = $conf;
		
		$pidList = explode( ',', $conf['pidList'] );
		$this->MOUNTS = array();
		for( $i=0; $i < count( $pidList ); $i++ ) {
			$this->MOUNTS[$i] = $pidList[$i];
		}
		
		$this->table = 'pages';
		$this->titleAttrib = 'title';
		
		t3lib_div::loadTCA( $this->table );
		if( $GLOBALS['TSFE']->sys_language_content != 0 ) {
			$this->tmp_pageSelect = t3lib_div::makeInstance( 't3lib_pageSelect' );
		}
		
		
		$this->setTreeName();
		
		
		//Filter out unwanted pages (like hidden- or access-protected pages/sysfolder)
		$this->clause = $cObj->enableFields( $this->table );
		$this->orderByFields = 'sorting';
		
			// setting this to false disables the use of array-trees by default
		$this->data = false;
		$this->dataLookup = false;
		
	}
	
	
	/**
	 * Will create and return the HTML code for a browsable tree
	 * Is based on the mounts found in the internal array ->MOUNTS (set in the constructor)
	 *
	 * @return	string		HTML code for the browsable tree
	 */
	function getBrowsableTree()	{

			// Get stored tree structure AND updating it if needed according to incoming PM GET var.
		$this->initializePositionSaving();

			// Init done:
		$titleLen=150;
		$treeArr=array();

			// Traverse mounts:
		foreach($this->MOUNTS as $idx => $uid)	{

				// Set first:
			$this->bank=$idx;
			$isOpen = $this->stored[$idx][$uid] || $this->expandFirst;

				// Save ids while resetting everything else.
			$curIds = $this->ids;
			$this->reset();
			$this->ids = $curIds;

				// Set PM icon for root of mount:
			$cmd=$this->bank.'_'.($isOpen?"0_":"1_").$uid.'_'.$this->treeName;
			$icon='<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/'.($isOpen?'minus':'plus').'only.gif','width="18" height="16"').' alt="" />';
			$firstHtml= $this->PM_ATagWrap($icon,$cmd);

				// Preparing rootRec for the mount
			if ($uid)	{
				$rootRec = $this->getRecord($uid);
				$firstHtml.=$this->getIcon($rootRec);
			} else {
					// Artificial record for the tree root, id=0
				$rootRec = $this->getRootRecord($uid);
				$firstHtml.=$this->getRootIcon($rootRec);
			}

			if (is_array($rootRec))	{
				$uid = $rootRec['uid'];		// In case it was swapped inside getRecord due to workspaces.

					// Add the root of the mount to ->tree
				$this->tree[]=array('HTML'=>$firstHtml, 'row'=>$rootRec, 'bank'=>$this->bank);

					// If the mount is expanded, go down:
				if ($isOpen && ( $this->recursive > 0 ) ) {
						// Set depth:
					$depthD='<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/blank.gif','width="18" height="16"').' alt="" />';
					if ($this->addSelfId)	$this->ids[] = $uid;
					
					$this->getTree($uid, $this->recursive, $depthD,'',$rootRec['_SUBCSSCLASS']);
					
				}

					// Add tree:
				$treeArr=array_merge($treeArr,$this->tree);
			}
		}
		return $this->printTree($treeArr);
	}


	function wrapTitle($title,$row,$bank=0)	{
		$stdWrap = $this->conf['listView.']['vFolderTitle' . ( ( $this->piVars['pid'] != $row['uid'] ) ? 'NO' : 'ACT' ) . '_stdWrap.'];
		$stdWrap['typolink.']['parameter'] = $GLOBALS['TSFE']->id;
		$stdWrap['typolink.']['useCacheHash'] = 1;
		$stdWrap['typolink.']['additionalParams'] = '&tx_drblob_pi1[pid]=' . $row['uid'];
		
		//Getting a localized label for the page if exists
		if( $GLOBALS['TSFE']->sys_language_content != 0 ) {
			$pgOverlay = $this->tmp_pageSelect->getPageOverlay( $row['uid'], $GLOBALS['TSFE']->sys_language_content );
			if( !empty( $pgOverlay['title'] ) ) {
				$title = $pgOverlay['title'];
			}
		}
		return $this->cObj->stdWrap( $title, $stdWrap );
	}
	
	
	function getIcon($row) {
		return $this->cObj->cObjGetSingle( 'IMAGE', $this->conf['listView.']['vFolderIcon.'] );
	}
	
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/pi1/class.tx_drblob_pi1_vFolderTree.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/pi1/class.tx_drblob_pi1_vFolderTree.php']);
}
?>