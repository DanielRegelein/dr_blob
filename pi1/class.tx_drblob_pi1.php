<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Daniel Regelein (Daniel.Regelein@diehl-informatik.de)
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
 * Plugin 'Binary Object List' for the 'dr_blob' extension.
 *
 * @author	Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_drblob_pi1 extends tslib_pibase {
	var $prefixId = "tx_drblob_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_drblob_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "dr_blob";	// The extension key.


	/**
	 * @name		main
	 * -----------------
	 * @internal	loads the plugin
	 */
	function main( $content, $conf ) {
		switch( $this->cObj->data["select_key"] ) {
			case "TOP":
				$conf["pidList"] = $this->cObj->data["pages"];
				$conf["recursive"] = $this->cObj->data["recursive"];
				return $this->pi_wrapInBaseClass( $this->modeTopView($content,$conf) );
			break;
			
			case "SINGLE":
				list( $t ) = explode( ":",$this->cObj->currentRecord );
				$this->internal["currentTable"]=$t;
				$this->internal["currentRow"]=$this->cObj->data;
				return $this->pi_wrapInBaseClass( $this->modeSingleView($content,$conf) );
			break;
			
			case "DOWNLOAD":
				$this->download();
			break;
			
			case "LIST":
			default:
				if (strstr($this->cObj->currentRecord,"tt_content"))	{
					$conf["pidList"] = $this->cObj->data["pages"];
					$conf["recursive"] = $this->cObj->data["recursive"];
				}
				return $this->pi_wrapInBaseClass( $this->modeListView( $content,$conf ) );
			break;
		}
	}
	

	/**
	 * @name		modeTopView
	 * ------------------------
	 * @internal	Generates a selection of Content Elements
	 */
	function modeTopView( $content, $conf ) {
		$this->conf=$conf;					// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults(); 
		$this->pi_loadLL();					// Loading the LOCAL_LANG values

		$this->internal['results_at_a_time'] = t3lib_div::intInRange($this->conf['topView.']['numDisplayedItems'],1,1000,50);
		$this->pi_listFields = 'uid, title, description, crdate';

		$rslt = $this->pi_exec_query( 'tx_drblob_content', 0, 'AND is_vip=\'1\'', '', '', 'crdate DESC, title ASC', '' );

		$rtnValue = null;		
		if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rslt ) > 0 ) {

			$this->conf['topView']['alternatingLayouts'] = intval( $this->conf['topView']['alternatingLayouts'] ) > 0 ? intval( $this->conf['topView']['alternatingLayouts'] ) : 2;

			$tmpl['total'] = $this->cObj->fileResource( 'typo3conf/ext/dr_blob/res/dr_blob.top.tmpl' );
			$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], '###TEMPLATE_TOP###' );
			$tmpl['item'] = $this->getLayouts( $tmpl['total'], $this->conf['topView']['alternatingLayouts'], 'BLOBITEM' );

			$arrItems = array();
			$count = 0;
			while( $this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) )	{
				$arrItems[] = $this->cObj->substituteMarkerArrayCached( 
						$tmpl['item'][$count%count( $tmpl['item'] )],
						array_merge( 
							$this->getMarkerArray( 'top' ),
							array(
								'###BLOB_TITLE_VAL_LINK###' => $this->getLink( 'show', $this->getFieldContent('title'), $this->conf['topView.']['showSingle.']['showPid'] ),
								'###BLOB_MORE_LINK###' => $this->getLink( 'show', $this->pi_getLL('topButtonShow'), $this->conf['topView.']['showSingle.']['showPid'] )
							)
						)
				);
				$count++;
			}

			$subpartArray = array();
			$wrappedSubpartArray = array();
			$markerArray = array(  );
			$subpartArray['###CONTENT###'] = implode( '', $arrItems );
			return $this->cObj->substituteMarkerArrayCached( 
				$tmpl['total'],
				$markerArray,
				$subpartArray,
				$wrappedSubpartArray
			);

		} else {

			$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], '###TEMPLATE_TOP_NOITEMS###' );
			$rtnValue = $this->cObj->substituteMarkerArrayCached( $tmpl['total'] );

		}
		return $rtnValue;
	}



	/**
	 * @name modeListView
	 * Generates a listView of the records in the selected Pages / Sysfolders
	 * 
	 * @param String $content 
	 * @param Array $conf TypoScript Connector
	 * @return String $content
	 */
	function modeListView( $content, $conf ) {
		$this->conf = $conf;
		$lConf = $conf["listView."];
		
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();

		$qrySortBy = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlListOrderBy' );
		$qrySortDirection = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlListOrderDirection' );
		$qryLimit = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlListLimitCount' );


		// If a single element should be displayed:
		if ( $this->piVars["showUid"] )	{

			return $this->modeSingleView($content,$conf);
		
		// If a single element should be downloaded:
		} else if ( $this->piVars["downloadUid"] ) {
			$this->download();
		} else {
			
			//Initializing the Sort parameters:
			if ( EMPTY( $this->piVars['sort'] ) ) {
				//default: title ASC
				$qrySortDirection = $qrySortDirection ? $qrySortDirection : '0';
				$this->piVars['sort'] = $qrySortBy ? $qrySortBy.':'.$qrySortDirection : 'title'.':'.$qrySortDirection; 
			}
			list($this->internal['orderBy'], $this->internal['descFlag']) = explode(':',$this->piVars['sort']);
			$this->internal['orderByList'] = 'title,sort,crdate,tstamp,cruser_id';


			//Number of results to show in a listing
			$this->internal['results_at_a_time'] = t3lib_div::intInRange( $qryLimit, 0, 1000, 50);
			
			
			//Get number of records
			$rsltNumRows = $this->pi_exec_query( 'tx_drblob_content', 1 );
			list( $this->internal['res_count'] ) = $GLOBALS['TYPO3_DB']->sql_fetch_row( $rsltNumRows );


			return $this->makelist( $this->pi_exec_query( 'tx_drblob_content', 0 ) );
		}
	}


	/**
	 * @name modeSingleView
	 * Generate the view for a single item
	 * 
	 * @param String $content
	 * @param Array $conf TypoScript Connector
	 * @return String $content
	 */
	function modeSingleView( $content, $conf ) {
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->internal['currentTable'] = 'tx_drblob_content';
		$this->internal['currentRow'] = $this->pi_getRecord( 'tx_drblob_content', $this->piVars['showUid'] );

		$tmpl = $this->cObj->fileResource( 'typo3conf/ext/dr_blob/res/dr_blob.single.tmpl' );
		$tmpl = $this->cObj->getSubpart( $tmpl, '###TEMPLATE_SINGLE###' );
		
		return $this->cObj->substituteMarkerArrayCached( $tmpl, $this->getMarkerArray( 'single' ) ) . $this->pi_getEditPanel();
	}


	/**
	 * @name download
	 * Sends an HTTP-Header with blob_type as contentType as Attachment
	 * 
	 * @see	RfC 2045, RfC 2046, RfC 2077 for Content Disposition
	 * @access private
	 */
	function download() {
		$this->internal['currentTable'] = 'tx_drblob_content';
		$this->internal['currentRow'] = $this->pi_getRecord( 'tx_drblob_content', $this->piVars['downloadUid'] );
	    
	    $contentType = $this->getFieldContent("blob_type");
	    if (EMPTY($contentType)) {
	    	$contentType = 'text/plain';
	    }
	    
	    Header('Content-type: '.$contentType);
	    Header('Content-disposition: attachment; filename='.$this->getFieldContent('blob_name'));
	    Header('Pragma: no-cache');
	    Header('Expires: 0');
		
		echo stripslashes( $this->getFieldContent('blob_data') );

		//Avoid Typo from displaying the page
		exit();
	}
	

	/**
	 * @name		makeList
	 * ---------------------
	 * @internal	Generates a List
	 */
	function makelist($res)	{
		$items=Array();
		while($this->internal["currentRow"] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$items[]=$this->makeListItem();
		}
				
		
		$out = '<table border="'.$this->conf["listView."]["tableProperties."]["border"].'"'.
					 ' cellspacing="'.$this->conf["listView."]["tableProperties."]["cellspacing"].'"'.
					 ' cellpadding="'.$this->conf["listView."]["tableProperties."]["cellpadding"].'"'.
					 ' class="'.$this->conf["listView."]["tableProperties."]["class"].'"'.
					 ' width="'.$this->conf["listView."]["tableProperties."]["width"].'">'.
					$this->makeListHeader() . 
					implode( chr(10), $items ) .
					$this->makeListFooter() .
			   '</table>';
		return $out;
	}


	/**
	 * @name		makeListHeader
	 * ---------------------------
	 * @internal	Generates a listHeader
	 */
	function makeListHeader() {
		$out='<tr>
				<th class="kopfzeile" align="left" width="84%">'.$this->getFieldHeader_sortLink("title").'</th>
				<th class="kopfzeile" align="left" width="15%">'.$this->getFieldHeader_sortLink("crdate").'</th>
				<th class="kopfzeile" align="left" width="1">&nbsp;</th>
			</tr>';
		return $out;
	}


	/**
	 * @name		makeListItem
	 * -------------------------
	 * @internal	Generates a listRow
	 */
	function makeListItem()	{
		$out='<tr class="inhalt_liste2">
				<td>'.$this->getFieldContent("title").'</td>
				<td>'.date( $this->conf["listView."]["date_stdWrap"], $this->getFieldContent("crdate") ).'</td>
				<td>'.$this->getLink("show", $this->conf["listView."]["showButtonValue"]).'&nbsp;'.$this->getLink("download", $this->conf["listView."]["downloadButtonValue"]).'</td>
			</tr>';
		return $out;
	}


	/**
	 * @name		makeListFooter
	 * ---------------------------
	 * @internal	Generiert eine Listenunterschrift
	 */
	function makeListFooter() {
		return null;
	}


	/**
	 * @name getFieldContent
	 * displays a field's content identified by $fN
	 * 
	 * @access private
	 * @param String $fn FieldName of the field to query
	 * @return String
	 */
	function getFieldContent( $fN ) {
		return $this->internal['currentRow'][$fN];
	}
	

	/**
	 * @name			getLink
	 * --------------------------------
	 * @internal		Displays a Link
	 * @access 			public
	 * @param			String		Link
	 * @param			String		String to wrap the link around
	 * @param			Int			PID to return to
	 * @return 			String
	 */
	function getLink( $link='show', $linkWrap=null, $pid=0 ) {
		$uid=$this->internal['currentRow']['uid'];
		switch( $link ) {
			case 'download':
				if ( $this->blobExists($uid) ) {
					return $this->pi_linkTP($linkWrap, array($this->prefixId=>array('downloadUid'=>$uid)), false, 0);
				}
			break;

			case 'show':
			default:
				return $this->pi_list_linkSingle($linkWrap, $uid, 1, array(),false, $pid );
		}//End of switch( $link )
	}


	/**
	 * @name getFieldHeader
	 * Displays a field's Title in LocalLang
	 * 
	 * @access private
	 * @param String $fieldName Name of the field to display
	 * @return String
	 */
	function getFieldHeader( $fieldName ) {
		switch( $fieldName ) {
			case 'crdate':
				return $this->pi_getLL('listFieldHeader_date','Date');

			case 'tstamp':
				return $this->pi_getLL('listFieldHeader_date','Last Change');

			default:
				return $this->pi_getLL('listFieldHeader_'.$fieldName, $fieldName ); 
		}//End of switch( $fieldName )
	}


	/**
	 * @name getFieldHeader_sortLink
	 * Displays a field's title in LocalLang wrapped in a sortlink
	 * 
	 * @access (should be) private
	 * @param String Field
	 * @return String Field with sortLink
	 */
	function getFieldHeader_sortLink( $fieldName ) {
		return $this->pi_linkTP_keepPIvars( 
					$this->getFieldHeader( $fieldName ),
					array('sort' => $fieldName . ':' . ($this->internal['descFlag'] ? 0 : 1 ) ),
					0,
					$GLOBALS['TSFE']->id
		);
	}
	
	
	
	/**
	 * @name getMarkerArray
	 * 
	 */
	function getMarkerArray( $mode ) {
		switch ( $mode ) {
			case 'single':
				$dateWrap = $this->conf['singleView.']['date_stdWrap'] ? $this->conf['singleView.']['date_stdWrap'] : 'm/d/Y';
			break;
			case 'top':
				$dateWrap = $this->conf['topView.']['date_stdWrap'] ? $this->conf['topView.']['date_stdWrap'] : 'm/d/Y h:i';
			break;
			case 'list':
				$dateWrap = $this->conf['listView.']['date_stdWrap'] ? $this->conf['listView.']['date_stdWrap'] : 'm/d/Y';
			break;
		}

		$arrMarker = array(
			'###BLOB_TITLE_LBL###' => $this->pi_getLL('listFieldHeader_title'),
			'###BLOB_TITLE_VAL###' => $this->getFieldContent('title'),
			'###BLOB_DESCRIPTION_LBL###' => $this->pi_getLL('listFieldHeader_description'),
			'###BLOB_DESCRIPTION_VAL###' => $this->getFieldContent('description'),
			'###BLOB_AUTHOR_LBL###' => $this->pi_getLL('listFieldHeader_author'),
			'###BLOB_AUTHOR_VAL###' => $this->getFieldContent('author'),
			'###BLOB_SORT_LBL###' => $this->pi_getLL('listFieldHeader_sort'),
			'###BLOB_SORT_VAL###' => $this->getFieldContent('sort'),
			'###BLOB_CRDATE_LBL###' => $this->pi_getLL('listFieldHeader_date_create'),
			'###BLOB_CRDATE_VAL###' => date( $dateWrap, $this->getFieldContent('crdate')),
			'###BLOB_TSTAMP_LBL###' => $this->pi_getLL('listFieldHeader_date_lastchange'),
			'###BLOB_TSTAMP_VAL###' => date( $dateWrap, $this->getFieldContent('tstamp')),
			'###BLOB_FILENAME_LBL###' => $this->pi_getLL('listFieldHeader_blob_name'),
			'###BLOB_FILENAME_VAL###' => $this->getFieldContent('blob_name'),
			'###BLOB_FILESIZE_LBL###' => $this->pi_getLL('listFieldHeader_blob_size'),										
			'###BLOB_FILESIZE_VAL###' => $this->getFieldContent('blob_size'),
			'###BLOB_FILETYPE_LBL###' => $this->pi_getLL('listFieldHeader_blob_type'),										
			'###BLOB_FILETYPE_VAL###' => $this->getFieldContent('blob_type'),
			'###BLOB_FILEDATA_VAL###' => $this->getLink('download', $this->pi_getLL('singleButton_download') ),
			'###BLOB_SINGLE_RTN-URL###' => $this->pi_list_linkSingle( null,0,true,array(),true,$this->conf['topView.']['showSingle.']['returnPid'] ),
			'###BLOB_SINGLE_RTN-LBL###' => $this->pi_getLL('singleButton_close', 'Close')
		);
		
		switch( $mode ) {
			case 'single':
				$arrMarker['###BLOB_DATA_EXISTS_SWITCH_START###'] = $this->blobExists( $this->piVars['showUid'] ) ? '' : ' <!-- ';
				$arrMarker['###BLOB_DATA_EXISTS_SWITCH_END###'] = $this->blobExists( $this->piVars['showUid'] ) ? '' : ' --> ';
			break;
			case 'top':
				$toCut = 150;
				if ( strlen($arrMarker['###BLOB_DESCRIPTION_VAL###']) > $toCut ) {
					$add = "...";
				} else {
					$add = null;
				}
				$arrMarker['###BLOB_DESCRIPTION_VAL###'] = strip_tags( substr( $arrMarker['###BLOB_DESCRIPTION_VAL###'], 0, -(strlen( $arrMarker['###BLOB_DESCRIPTION_VAL###'] ) - $toCut ) ) ) . $add;
				
			break;
		}
		return $arrMarker;
	}
	
	
	/**
	 * @name			blobExists
	 * Checks wether a binary object exists- or not
	 * 
	 * @access (should be) private
	 * @param			int			uid
	 * @return 			bool
	 */
	function blobExists( $item ) {
		$rslt = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'blob_data, blob_name',
			'tx_drblob_content',
			'uid=' . $item . ' AND blob_data != \'\'',
			'',
			'',
			''
		);
		if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rslt ) == 1 ) {
			return true;
		} else {
			return false;
		}		
	}
	
	
	/**
	 * @name getLayouts
	 * Returns alternating layouts
	 * 
	 * @author	Rupert Germann <rupi@gmx.li>
     * @package TYPO3
     * @subpackage tt_news
	 * @param	string		html code of the template subpart
	 * @param	integer		number of alternatingLayouts
	 * @param	string		name of the content-markers in this template-subpart
	 * @return	array		html code for alternating content markers
	 */
	function getLayouts($templateCode, $alternatingLayouts, $marker ) {
		$out = array();
		for($a = 0; $a < $alternatingLayouts; $a++) {
			$m = '###'.$marker.($a?'_'.$a:'').'###';
			if (strstr($templateCode, $m)) {
				$out[] = $GLOBALS['TSFE']->cObj->getSubpart($templateCode, $m);
			} else {
				break;
			}
		}
		return $out;
	}
};



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/pi1/class.tx_drblob_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/pi1/class.tx_drblob_pi1.php']);
}
?>