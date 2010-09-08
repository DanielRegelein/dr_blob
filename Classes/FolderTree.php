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
 * Virtual FolderTree for the dr_blob-Extension
 *
 * @author		Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @category 	Frontend Plugins
 * @copyright 	Copyright &copy; 2005-present Daniel Regelein
 * @package 	TYPO3
 * @subpackage	dr_blob
 * @filesource 	EXT:dr_blob/Classes/FolderTree.php
 * @version 	2.4.0
 */

require_once( PATH_t3lib . 'class.t3lib_treeview.php' );

class Tx_DrBlob_FolderTree extends t3lib_treeview {

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
	
	
	/********************************
	 *
	 * tree data buidling
	 *
	 ********************************/

	/**
	 * Fetches the data for the tree
	 *
	 * @param	integer		item id for which to select subitems (parent id)
	 * @param	integer		Max depth (recursivity limit)
	 * @param	string		HTML-code prefix for recursive calls.
	 * @param	string		? (internal)
	 * @param	string		CSS class to use for <td> sub-elements
	 * @return	integer		The count of items on the level
	 */
	function getTree($uid, $depth=999, $depthData='',$blankLineCode='',$subCSSclass='')	{

			// Buffer for id hierarchy is reset:
		$this->buffer_idH=array();

			// Init vars
		$depth=intval($depth);
		$HTML='';
		$a=0;

		$res = $this->getDataInit($uid,$subCSSclass);
		$c = $this->getDataCount($res);
		$crazyRecursionLimiter = 999;

			// Traverse the records:
		while ($crazyRecursionLimiter>0 && $row = $this->getDataNext($res,$subCSSclass))	{
			$a++;
			$crazyRecursionLimiter--;

			$newID = $row['uid'];

			if ($newID==0)	{
				t3lib_BEfunc::typo3PrintError ('Endless recursion detected', 'TYPO3 has detected an error in the database. Please fix it manually (e.g. using phpMyAdmin) and change the UID of '.$this->table.':0 to a new value.<br /><br />See <a href="http://bugs.typo3.org/view.php?id=3495" target="_blank">bugs.typo3.org/view.php?id=3495</a> to get more information about a possible cause.',0);
				exit;
			}

			$this->tree[]=array();		// Reserve space.
			end($this->tree);
			$treeKey = key($this->tree);	// Get the key for this space
			$LN = ($a==$c)?'blank':'line';

				// If records should be accumulated, do so
			if ($this->setRecs)	{
				$this->recs[$row['uid']] = $row;
			}

				// Accumulate the id of the element in the internal arrays
			$this->ids[] = $idH[$row['uid']]['uid'] = $row['uid'];
			$this->ids_hierarchy[$depth][] = $row['uid'];
			$this->orig_ids_hierarchy[$depth][] = $row['_ORIG_uid'] ? $row['_ORIG_uid'] : $row['uid'];

				// Make a recursive call to the next level
			$HTML_depthData = $depthData.'<img'.t3lib_iconWorks::skinImg($this->backPath,$this->getTreeGfxFolder().$LN.'.gif','width="18" height="16"').' alt="" />';
			if ($depth>1 && $this->expandNext($newID) && !$row['php_tree_stop'])	{
				$nextCount=$this->getTree(
						$newID,
						$depth-1,
						$this->makeHTML ? $HTML_depthData : '',
						$blankLineCode.','.$LN,
						$row['_SUBCSSCLASS']
					);
				if (count($this->buffer_idH))	$idH[$row['uid']]['subrow']=$this->buffer_idH;
				$exp=1;	// Set "did expand" flag
			} else {
				$nextCount=$this->getCount($newID);
				$exp=0;	// Clear "did expand" flag
			}

				// Set HTML-icons, if any:
			if ($this->makeHTML)	{
				$HTML = $depthData.$this->PMicon($row,$a,$c,$nextCount,$exp);
				$HTML.=$this->wrapStop($this->getIcon($row),$row);
				#	$HTML.=$this->wrapStop($this->wrapIcon($this->getIcon($row),$row),$row);
			}

				// Finally, add the row/HTML content to the ->tree array in the reserved key.
			$this->tree[$treeKey] = Array(
				'row'=>$row,
				'HTML'=>$HTML,
				'HTML_depthData' => $this->makeHTML==2 ? $HTML_depthData : '',
				'invertedDepth'=>$depth,
				'blankLineCode'=>$blankLineCode,
				'bank' => $this->bank
			);
		}

		$this->getDataFree($res);
		$this->buffer_idH=$idH;
		return $c;
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
			$icon='<img'.t3lib_iconWorks::skinImg($this->backPath,$this->getTreeGfxFolder().($isOpen?'minus':'plus').'only.gif','width="18" height="16"').' alt="" />';
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
					$depthD='<img'.t3lib_iconWorks::skinImg($this->backPath,$this->getTreeGfxFolder().'blank.gif','width="18" height="16"').' alt="" />';
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
	
	
	/**
	 * Generate the plus/minus icon for the browsable tree.
	 *
	 * @param	array		record for the entry
	 * @param	integer		The current entry number
	 * @param	integer		The total number of entries. If equal to $a, a "bottom" element is returned.
	 * @param	integer		The number of sub-elements to the current element.
	 * @param	boolean		The element was expanded to render subelements if this flag is set.
	 * @return	string		Image tag with the plus/minus icon.
	 * @access private
	 * @see t3lib_pageTree::PMicon()
	 */
	function PMicon($row,$a,$c,$nextCount,$exp)	{
		$PM = $nextCount ? ($exp?'minus':'plus') : 'join';
		$BTM = ($a==$c)?'bottom':'';
		$icon = '<img'.t3lib_iconWorks::skinImg($this->backPath,$this->getTreeGfxFolder().$PM.$BTM.'.gif','width="18" height="16"').' alt="" />';

		if ($nextCount)	{
			$cmd=$this->bank.'_'.($exp?'0_':'1_').$row['uid'].'_'.$this->treeName;
			$bMark=($this->bank.'_'.$row['uid']);
			$icon = $this->PM_ATagWrap($icon,$cmd,$bMark);
		}
		return $icon;
	}
	
	
	function getIcon($row) {
		return $this->cObj->cObjGetSingle( 'IMAGE', $this->conf['listView.']['vFolderIcon.'] );
	}

	
	function getTreeGfxFolder() {
		if( !empty( $this->conf['listView.']['vFolderGfxFolder'] ) ) {
			return $this->conf['listView.']['vFolderGfxFolder'];
		}
		return t3lib_extmgm::siteRelPath( 'dr_blob' ) . 'Resources/Public/FolderTree/';
	}
}


if ( defined( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Classes/FolderTree.php'] ) {
    include_once( $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Classes/FolderTree.php'] );
}
?>