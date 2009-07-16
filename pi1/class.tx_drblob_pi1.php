<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Daniel Regelein (Daniel.Regelein@diehl-informatik.de)
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

require_once( PATH_tslib . 'class.tslib_pibase.php' );
/**
 * @name tx_drblob_pi1
 * Plugin 'Binary Object List' for the 'dr_blob' extension.
 *
 * @extends tslib_pibase
 * @author	Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @category Frontend Plugins
 * @copyright Copyright &copy; 2005,Daniel Regelein
 * @filesource pi1/class.tx_drblob_pi1.php
 * @version 0.9.9
 */
class tx_drblob_pi1 extends tslib_pibase {
	/*protected*/var $prefixId = 'tx_drblob_pi1';
	/*protected*/var $scriptRelPath = 'pi1/class.tx_drblob_pi1.php';
	/*protected*/var $extKey = 'dr_blob';
	/*
	function __construct() {
	}
	function __destruct() {
	}
	*/
	
	/*public*/function main( $content, $conf ) {
		$this->pi_initPIflexForm();
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->conf = $conf;

		if ( $this->piVars['downloadUid'] ) {
			$this->pi_wrapInBaseClass( $this->vDownload() );
		}
		switch(  $this->getCmd() ) {
			case 'single':
				return $this->pi_wrapInBaseClass( $this->vSingle() );
			//break;

			
			case 'top':
				if ( strstr( $this->cObj->currentRecord, 'tt_content' ) ) {
					$this->conf['pidList'] = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlPages', 'sDataSource' );
					$this->conf['recursive'] = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlRecursive', 'sDataSource' );
				}
				return $this->pi_wrapInBaseClass( $this->vTop( $content ) );
			//break;
			
			case 'list': 
			default:
				if ( strstr( $this->cObj->currentRecord, 'tt_content' ) ) {
					
					$this->conf['pidList'] = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlPages', 'sDataSource' );
					$this->conf['recursive'] = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlRecursive', 'sDataSource' );
				}
				return $this->pi_wrapInBaseClass( $this->vList( $content ) );
			//break;
		}
	}


	/**
	 * @name vList
	 * Generates a list-View of Recordsets contained on selected Pages / Sysfolders
	 * 
	 * @param 	String $content 
	 * @return 	String $content
	 * @access 	private
	 */
	/*private*/function vList( $content ) {
		//Extract the FlexForm Values
		$ffQrySortBy = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlListOrderBy', 'sSettings' );
		$ffQrySortDirection = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlListOrderDirection', 'sSettings' );
		$ffQryLimit = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlLimitCount', 'sSettings' );
		$ffSinglePID = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlSinglePID', 'sSettings' );
		$ffTemplate = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlTemplate', 'sSettings' );
		
		$singlePID = ( $ffSinglePID ? $ffSinglePID : $GLOBALS['TSFE']->id );
		$tmplFile = ( $ffTemplate ? ( 'uploads/tx_drblob/' . $ffTemplate ) : 'typo3conf/ext/dr_blob/res/dr_blob.tmpl' );
		$this->conf['listView.']['alternatingLayouts'] = intval( $this->conf['listView.']['alternatingLayouts'] ) > 0 ? intval( $this->conf['listView.']['alternatingLayouts'] ) : 2;
		
		
		//Prepare Searchfunctions		
		if ( empty( $this->piVars['sort'] ) ) {
			//default: title ASC
			$ffQrySortDirection = $ffQrySortDirection ? $ffQrySortDirection : '0';
			$this->piVars['sort'] = $ffQrySortBy ? $ffQrySortBy.':'.$ffQrySortDirection : 'title'.':'.$ffQrySortDirection; 
		}
		list($this->internal['orderBy'], $this->internal['descFlag']) = explode(':',$this->piVars['sort']);
		$this->internal['orderByList'] = 'title,crdate,tstamp,cruser_id';


		//Number of results to show in a listing
		$this->internal['results_at_a_time'] = t3lib_div::intInRange( $ffQryLimit, 1, 1000, 50 );
			
			
		//Get number of records
		$rsltNumRows = $this->pi_exec_query( 'tx_drblob_content', 1 );
		list( $this->internal['res_count'] ) = $GLOBALS['TYPO3_DB']->sql_fetch_row( $rsltNumRows );
		
		
		//Exec Query- and Template
		$rslt = $this->pi_exec_query( 'tx_drblob_content', 0 );
		if ( $rslt ) {
			$tmpl['total'] = $this->cObj->fileResource( $tmplFile );			

			if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rslt ) > 0 ) {
				$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], '###TEMPLATE_LIST###' );
				$tmpl['item'] = $this->getLayouts( $tmpl['total'], $this->conf['listView.']['alternatingLayouts'], 'BLOBITEM' );
				
				$arrItems = array();
				$count = 0;
				while( $this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) )	{
					$arrItems[] = $this->cObj->substituteMarkerArrayCached( 
						$tmpl['item'][$count%count( $tmpl['item'] )],
						array_merge( 
							$this->getGlobalMarkerArray( 'list' ),
							array(
								'###BLOB_MORE_LINK###' => $this->pi_list_linkSingle ( 
									$this->conf['listView.']['showButtonValue'] ? $this->conf['listView.']['showButtonValue'] : $this->pi_getLL('list.button.show'), 
									$this->internal['currentRow']['uid'], 
									true, 
									array(), 
									false, 
									$singlePID 
								),								
								'###BLOB_DOWNLOAD_LINK###' => $this->pi_linkTP( 
									$this->conf['listView.']['downloadButtonValue'] ? $this->conf['listView.']['downloadButtonValue'] : $this->pi_getLL('list.button.download'), 
									array( 
										$this->prefixId => array( 
											'downloadUid' => $this->internal['currentRow']['uid'] 
										) 
									), 
									false, 
									$GLOBALS['TSFE']->id 
								)
							)
						)
					);
					$count++;
				}//End of while( $this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) )
				$markerArray = array(  
					'###BLOB_SORTLINK_TITLE###'  => $this->getFieldHeader_sortLink('title'),
					'###BLOB_SORTLINK_CRDATE###' => $this->getFieldHeader_sortLink('crdate'),
					'###BLOB_SORTLINK_TSTAMP###' => $this->getFieldHeader_sortLink('tstamp'),
					'###BLOB_SORTLINK_AUTHOR###' => $this->getFieldHeader_sortLink('cruser_id'),
				);
				return $this->cObj->substituteMarkerArrayCached( 
					$tmpl['total'],
					$markerArray,
					array(
						'###CONTENT###'=> implode( '', $arrItems ),
					)
				);
			} else {//End of if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rslt ) > 0 )

				$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], '###TEMPLATE_LIST_NOITEMS###' );
				return $this->cObj->substituteMarkerArrayCached( $tmpl['total'] );
	
			}//End of the Else-Part of if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rslt ) > 0 )
		}//End of if ( $rslt )
	}


	/**
	 * @name vTop
	 * Generates a list of Recordsets on selected Pages / Sysfolders where is_vip = 1
	 * 
	 * @param 	String $content 
	 * @return 	String $content
	 * @access 	private
	 */
	/*private*/function vTop ( $content ) {
		$ffSinglePID = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlSinglePID', 'sSettings' );
		$ffTemplate = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlTemplate', 'sSettings' );
		$ffQryLimit = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlLimitCount', 'sSettings' );
		
		$singlePID = ( $ffSinglePID ? $ffSinglePID : $GLOBALS['TSFE']->id );
		$tmplFile = ( $ffTemplate ? ( 'uploads/tx_drblob/' . $ffTemplate ) : 'typo3conf/ext/dr_blob/res/dr_blob.tmpl' );		

		$this->internal['results_at_a_time'] = t3lib_div::intInRange( $ffQryLimit, 1, 100, 5 );
		$this->pi_listFields = 'uid, title, description, crdate';

		$rslt = $this->pi_exec_query( 'tx_drblob_content', 0, 'AND is_vip=\'1\'', '', '', 'crdate DESC, title ASC', '' );

		$rtnValue = null;
		$tmpl['total'] = $this->cObj->fileResource( $tmplFile );		
		if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rslt ) > 0 ) {
			$this->conf['topView.']['alternatingLayouts'] = intval( $this->conf['topView.']['alternatingLayouts'] ) > 0 ? intval( $this->conf['topView.']['alternatingLayouts'] ) : 2;
			
			$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], '###TEMPLATE_TOP###' );
			$tmpl['item'] = $this->getLayouts( $tmpl['total'], $this->conf['topView.']['alternatingLayouts'], 'BLOBITEM' );
			$arrItems = array();
			$count = 0;
			while( $this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) )	{
				$arrItems[] = $this->cObj->substituteMarkerArrayCached( 
					$tmpl['item'][$count%count( $tmpl['item'] )],
					array_merge( 
						$this->getGlobalMarkerArray( 'top' ),
						array(
							'###BLOB_TITLE_LINK###' => $this->pi_list_linkSingle( $this->getFieldContent('title'), $this->internal['currentRow']['uid'], true, array(), false, $singlePID ),
							'###BLOB_MORE_LINK###' => $this->pi_list_linkSingle( $this->pi_getLL('top.button.show'), $this->internal['currentRow']['uid'], true, array(), false, $singlePID ),
							'###BLOB_DOWNLOAD_LINK###' => $this->pi_linkTP( $this->pi_getLL('top.button.download'), array( $this->prefixId => array( 'downloadUid' => $this->internal['currentRow']['uid'] ) ), false, $GLOBALS['TSFE']->id )
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
	 * @name vSingle
	 * Generate the view for a single item
	 * 
	 * @param 	String $content
	 * @return 	String $content
	 * @access 	private
	 */
	/*private*/function vSingle() {
		
		if ( !empty( $this->piVars['showUid'] ) ) {
		
			$ffReturnPID = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlReturnPID', 'sSettings' );
			$ffTemplate = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlTemplate', 'sSettings' );
			
			$returnPID = ( $ffReturnPID ? $ffReturnPID : $GLOBALS['TSFE']->id );
			$tmplFile = ( $ffTemplate ? ( 'uploads/tx_drblob/' . $ffTemplate ) : 'typo3conf/ext/dr_blob/res/dr_blob.tmpl' );
			
			$this->internal['currentTable'] = 'tx_drblob_content';
			$this->internal['currentRow'] = $this->pi_getRecord( 'tx_drblob_content', $this->piVars['showUid'] );
	
			$tmpl = $this->cObj->fileResource( $tmplFile );
			$tmpl = $this->cObj->getSubpart( $tmpl, '###TEMPLATE_SINGLE###' );
			
			return $this->cObj->substituteMarkerArrayCached( 
				$tmpl, 
				array_merge( 
					$this->getGlobalMarkerArray( 'single' ),
					array( 
						'###BLOB_SINGLE_RTN-URL###' => $this->pi_getPageLink( $returnPID ), 
						'###BLOB_DOWNLOAD_LINK###' => $this->pi_linkTP( $this->pi_getLL('single.button.download'), array( $this->prefixId => array( 'downloadUid' => $this->internal['currentRow']['uid'] ) ), false, $GLOBALS['TSFE']->id )
					)
				) 
			);
			
		} else {
			return $this->pi_getLL( 'single.noItemFound' );
		}
	}
	

	/**
	 * @name download
	 * Sends an HTTP-Header with blob_type as contentType as Attachment
	 * 
	 * @see	RfC 2045, RfC 2046, RfC 2077 for Content Disposition
	 * @access private
	 */
	/*private*/function vDownload() {
		$this->internal['currentTable'] = 'tx_drblob_content';
		$this->internal['currentRow'] = $this->pi_getRecord( 'tx_drblob_content', $this->piVars['downloadUid'] );
	    
	    $contentType = $this->getFieldContent( 'blob_type' );
	    if ( empty( $contentType ) ) {
	    	$contentType = 'text/plain';
	    }
	    
	    Header( 'Content-type: ' . $contentType );
	    Header( 'Content-disposition: attachment; filename='.$this->getFieldContent( 'blob_name' ) );
	    Header( 'Pragma: no-cache' );
	    Header( 'Expires: 0' );
		echo stripslashes( $this->getFieldContent( 'blob_data' ) );

		//Avoid Typo from displaying the page
		exit();
	}


	/**
	 * @name getCmd
	 * Returns the command that contains what to display.
	 * 
	 * @return 	String Command what to do 
	 * @access 	private
	 */
	/*private*/function getCmd() {
		
		$ffW2D = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlWhatToDisplay', 'sSettings' );
		if ( $this->piVars['showUid'] ) {
			return 'single';
		}
		return ( $ffW2D ? $ffW2D : 'list' );
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
	/*private*/function getLayouts($templateCode, $alternatingLayouts, $marker ) {
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
	
	
	/**
	 * @name	blobExists
	 * Checks wether a binary object exists- or not
	 * 
	 * @access (should be) private
	 * @param	int		uid
	 * @return 	bool
	 */
	/*private*/function blobExists( $item ) {
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
	 * @name getMarkerArray
	 * 
	 */
	function getGlobalMarkerArray( $mode ) {
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
			'###BLOB_TITLE###' => $this->getFieldContent('title'),
			'###BLOB_DESCRIPTION###' => $this->getFieldContent('description'),
			'###BLOB_AUTHOR###' => $this->getFieldContent('author'),
			'###BLOB_AUTHOR_EMAIL###' => $this->getFieldContent('author_email'),
			'###BLOB_CRDATE###' => date( $dateWrap, $this->getFieldContent('crdate')),
			'###BLOB_LASTCHANGE###' => date( $dateWrap, $this->getFieldContent('tstamp')),
			'###BLOB_FILENAME###' => $this->getFieldContent('blob_name'),
			'###BLOB_FILESIZE###' => $this->getFieldContent('blob_size'),								
			'###BLOB_FILETYPE###' => $this->getFieldContent('blob_type'),
		);
		
		switch( $mode ) {
			case 'single':
				$arrMarker['###BLOB_DATA_EXISTS_SWITCH_START###'] = $this->blobExists( $this->piVars['showUid'] ) ? '' : ' <!-- ';
				$arrMarker['###BLOB_DATA_EXISTS_SWITCH_END###'] = $this->blobExists( $this->piVars['showUid'] ) ? '' : ' --> ';
			break;
			case 'top':
				$toCut = intval( $this->conf['topView.']['lengthOfDescription'] ) > 0 ? intval( $this->conf['topView.']['lengthOfDescription'] ) : 150;
				$arrMarker['###BLOB_DESCRIPTION###'] = strip_tags( $arrMarker['###BLOB_DESCRIPTION###'] );
				if ( strlen( $arrMarker['###BLOB_DESCRIPTION###'] ) > $toCut ) {
					$add = '...';
				} else {
					$add = null;
				}
				$arrMarker['###BLOB_DESCRIPTION###'] = substr( $arrMarker['###BLOB_DESCRIPTION###'], 0, -(strlen( $arrMarker['###BLOB_DESCRIPTION###'] ) - $toCut ) ) . $add;
				
			break;
		}
		return $arrMarker;
	}
	

	/**
	 * @name getFieldContent
	 * displays a field's content identified by $fN
	 * 
	 * @access private
	 * @param String $fn FieldName of the field to query
	 * @return String
	 */
	/*private*/function getFieldContent( $fN ) {
		switch ( $fN ) {
			case 'author': return $this->getAuthor( $this->internal['currentRow']['cruser_id'] ); break;
			case 'author_email': return $this->getAuthor( $this->internal['currentRow']['cruser_id'], 'email' ); break;
		}
		
		return $this->internal['currentRow'][$fN];
	}

	
	/**
	 * @name	getAuthor
	 * Queries the be_users table for the Author of an record
	 * 
	 * @param	Integer	$item	Uid of the author
	 * @return	String	Authorname
	 */
	/*private*/function getAuthor( $item, $what='author' ) {
		$rslt = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'realname, email',
			'be_users',
			'uid=' . $item . '',
			'',
			'',
			''
		);
		if ( $rslt ) {
			if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rslt ) == 1 ) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt );
				
				switch($what){
					case 'email': return $row['email']; break;
					case 'author':
					default: return $row['realname']; break;
				}
			}
		}
	}


	/**
	 * @name getFieldHeader_sortLink
	 * Displays a field's title in LocalLang wrapped in a sortlink
	 * 
	 * @access 	private
	 * @param 	String Field
	 * @return 	String Field with sortLink
	 */
	/*private*/function getFieldHeader_sortLink( $fieldName ) {
		return $this->pi_linkTP_keepPIvars( 
			$this->pi_getLL( 'list.field.'.$fieldName ),
			array('sort' => $fieldName . ':' . ($this->internal['descFlag'] ? 0 : 1 ) ),
			0,
			$GLOBALS['TSFE']->id
		);
	}
};


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/pi1/class.tx_drblob_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/pi1/class.tx_drblob_pi1.php']);
}
?>