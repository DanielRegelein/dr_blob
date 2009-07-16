<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2007 Daniel Regelein (Daniel.Regelein@diehl-informatik.de)
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
 * @name		tx_drblob_pi1
 * Frontend plugin "File List", Ext.Key "dr_blob"
 * This class is executed by the Typo3 Frontend to generate File Lists 
 * for secure downloads. 
 *
 * @extends 	tslib_pibase
 * @author		Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @category 	Frontend Plugins
 * @copyright 	Copyright &copy; 2005-past Daniel Regelein
 * @package 	dr_blob
 * @filesource 	pi1/class.tx_drblob_pi1.php
 * @version 	1.5.1
 */
class tx_drblob_pi1 extends tslib_pibase {

	/**
	 * @var		String	$prefixId
	 * @var		String 	$scriptRelPath
	 * @var		String  $extKey
	 * 
	 * Variables used by the piBase
	 * @access 	protected
	 */
	/*protected*/	var $prefixId = 'tx_drblob_pi1';
	/*protected*/	var $scriptRelPath = 'pi1/class.tx_drblob_pi1.php';
	/*protected*/	var $extKey = 'dr_blob';
	
	
	/**
	 * @var		Array	$dbVars
	 * @access	private
	 */
	/*protected*/	var $dbVars = array( 'table' => 'tx_drblob_content' );
	
	/**
	 * @var 	Array $searchFields
	 * Sets the fields that are used by the inbuild search function.
	 * 
	 * @access 	private
	 */
	/*private*/		var $searchFields = array( 'title', 'description', 'blob_name' );
	
	
	/**
	 * @var		Int	$sys_language_uid
	 * This is the variable to determine the current page language
	 */
	/*private*/		var $sys_language_uid;


	/**
	 * main
	 * This is the Interface method called automaticly when this plugin instance is loaded.
	 * 
	 * @param	String	$content
	 * @param	Array	TypoScript recived from the Ext. Manager
	 * @return	String	Rendered Content
	 * @access	public
	 */
	/*public*/function main( $content, $conf ) {
		$this->init( $conf );
		
		if ( $this->piVars['downloadUid'] ) {
			$this->vDownload();
		}
		
		switch( $cmd = $this->getCmd() ) {
			case 'single':
				return $this->pi_wrapInBaseClass( $this->vSingle() );
			//break;

			case 'personal_list':
				if ( strstr( $this->cObj->currentRecord, 'tt_content' ) ) {
					if ( $GLOBALS['TSFE']->loginUser ) {
						
						$rsltPIDList = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'uid_pages',
							'tx_drblob_personal',
							'uid_feusers=\'' . $GLOBALS['TSFE']->fe_user->user['uid'] . '\''
						);
						$this->conf['pidList'] = null;
						while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rsltPIDList ) ) {
							$this->conf['pidList'] .= $this->conf['pidList'] ? ',' : '';
							$this->conf['pidList'] .= $row['uid_pages'];
						}
						$this->conf['recursive'] = 0;

						return $this->pi_wrapInBaseClass( $this->makeList( 'personal_list' ) );
					}
				} else {
					return '';
				}
			break;

			case 'list':
			default:
				if ( strstr( $this->cObj->currentRecord, 'tt_content' ) ) {
					
					$this->conf['pidList'] = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlPages', 'sDataSource' );
					$this->conf['recursive'] = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlRecursive', 'sDataSource' );
				}
				return $this->pi_wrapInBaseClass( $this->makeList( $cmd ) );
		}
	}
	
	
	/**
	 * init
	 * Method to initialize some Member variables- or Methods
	 * 
	 * @param	Array	$conf	Array containg the TS
	 * @access	protected
	 */
	/*protected*/function init( $conf ) {
		
		$this->conf = $conf;
		$this->pi_initPIflexForm();
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

			//Get site-language
		$this->sys_language_uid = $GLOBALS['TSFE']->sys_language_content;
	}
	
	
	/**
	 * makeList
	 * Method to generate a list of records. The records are recived from the parent cObject.
	 * 
	 * @param	String	$listType	
	 * @return	String	Parsed List
	 * @access	protected
	 */
	/*protected*/function makeList( $listType ) {
			//Make sure that a correct list type is set
		$arrListType = array( 'list', 'top', 'search', 'personal_list' );
		if( !in_array( $listType, $arrListType ) ) {
			$listType = 'list';
		}
		
			//Extract FlexForm Configuration Values
		$ffSinglePID = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlSinglePID', 'sSettings' );
		$ffLimit = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlLimitCount', 'sSettings' );

			//Array Containing the Settings for the list, depending on the selected list type
		$arrListSettings = array();

		$arrListSettings['tmplFile'] = $this->getTemplateFile();
		$arrListSettings['singlePid'] = ( $ffSinglePID ? $ffSinglePID : $GLOBALS['TSFE']->id );
		$arrListSettings['btnWrapShow'] = $this->pi_getLL( $listType . '_button_show' );
		$arrListSettings['btnWrapDwnld'] = $this->pi_getLL( $listType . '_button_download' );
		
		switch( $listType ) {
			case 'top':
				$arrListSettings['tsObj'] = 'topView.';
				$arrListSettings['tmplSubpart'] = '###TEMPLATE_TOP###';
				$arrListSettings['tmplSubpart_noItem'] = '###TEMPLATE_TOP_NOITEMS###';
				$arrListSettings['recLimit'] = ( $ffLimit ? $ffLimit : 5 );
				$arrListSettings['sqlWhereClause'] = 'AND is_vip=\'1\' AND ' . $this->dbVars['table'] . '.sys_language_uid = ' . $this->sys_language_uid;

				$this->internal['orderBy'] = 'crdate';
				$this->internal['descFlag'] = '1';
			break;
			
			case 'personal_list':
				$arrListSettings['tsObj'] = 'personalView.';
				$arrListSettings['recLimit'] = ( $ffLimit ? $ffLimit : 5 );
				$arrListSettings['sqlWhereClause'] ='AND ' . $this->dbVars['table'] . '.sys_language_uid = ' . $this->sys_language_uid;
				$arrListSettings['tmplSubpart'] = '###TEMPLATE_PERSONAL###';
				$arrListSettings['tmplSubpart_noItem'] = '###TEMPLATE_PERSONAL_NOITEMS###';

				$this->internal['orderBy'] = 'tstamp';
				$this->internal['descFlag'] = '1';
			break;
			
			case 'search':
				if ( $this->piVars['sword'] ) {
					$arrListSettings['sqlWhereClause'] = ' AND ( ';
					foreach( $this->searchFields as $key=>$value ) {
						if ( $key != 0 ) {
							$arrListSettings['sqlWhereClause'] .= ' OR'; 
						}
						$arrListSettings['sqlWhereClause'] .= ' ' . $value . ' LIKE \'%' . htmlspecialchars( $this->piVars['sword'] ) . '%\'';
					}
					$arrListSettings['sqlWhereClause'] .= ' ) ';
				} else {
					$arrListSettings['sqlWhereClause'] = 'AND 0';
				}
				//no 'break' --> Fall-Through to list-mode

			case 'list':
			
				$arrListSettings['tsObj'] = 'listView.';
				$arrListSettings['recLimit'] = ( $ffLimit ? $ffLimit : 25 );
				$arrListSettings['tmplSubpart'] = '###TEMPLATE_LIST###';
				$arrListSettings['tmplSubpart_noItem'] = '###TEMPLATE_LIST_NOITEMS###';
				
				/**
				 * ---------------------------------------------------------------------------------------------------------
				 * @deprecated
				 */
				$arrListSettings['btnWrapShow'] = $this->conf['listView.']['showButtonValue'] ? $this->conf['listView.']['showButtonValue'] : $arrListSettings['btnWrapShow'];
				$arrListSettings['btnWrapDwnld'] = $this->conf['listView.']['downloadButtonValue'] ? $this->conf['listView.']['downloadButtonValue'] : $arrListSettings['btnWrapDwnld'];
				/**
				 * @deprecated
				 * ---------------------------------------------------------------------------------------------------------
				 */
				
				$ffQrySortBy = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlListOrderBy', 'sSettings' );
				$ffQrySortDirection = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlListOrderDirection', 'sSettings' );

					//Prepare Searchfunctions
				if ( empty( $this->piVars['sort'] ) ) {
					//default: title ASC
					$ffQrySortDirection = $ffQrySortDirection ? $ffQrySortDirection : '0';
					$this->piVars['sort'] = $ffQrySortBy ? $ffQrySortBy.':'.$ffQrySortDirection : 'title'.':'.$ffQrySortDirection; 
				}
				list( $this->internal['orderBy'], $this->internal['descFlag'] ) = explode( ':',$this->piVars['sort'] );

					//List-Header, used later
				$listHeaderMarkerArray = array(
					'###BLOB_SORTLINK_TITLE###'  => $this->getFieldHeader_sortLink('title'),
					'###BLOB_SORTLINK_CRDATE###' => $this->getFieldHeader_sortLink('crdate'),
					'###BLOB_SORTLINK_TSTAMP###' => $this->getFieldHeader_sortLink('tstamp'),
					'###BLOB_SORTLINK_LASTCHANGE###' => $this->getFieldHeader_sortLink('tstamp'),
					'###BLOB_SORTLINK_AUTHOR###' => $this->getFieldHeader_sortLink('cruser_id'),
				);
			break;
		}
		
		$arrListSettings['altLayouts'] = intval( $this->conf[$arrListSettings['tsObj']]['alternatingLayouts'] ) > 0 ? intval( $this->conf[$arrListSettings['tsObj']]['alternatingLayouts'] ) : 2;
			
			//Prepare DB Queries
		$this->internal['results_at_a_time'] = t3lib_div::intInRange( $arrListSettings['recLimit'], 1, 1000, 20 );
		$this->internal['orderByList'] = 'sorting,title,crdate,tstamp,cruser_id';
		$this->pi_listFields = 'uid,pid,title,description,crdate,tstamp,sys_language_uid,cruser_id,blob_name,blob_size,blob_type,download_count';
		$arrListSettings['sqlWhereClauseLocal'] = ' AND ( ' . $this->dbVars['table'] . '.sys_language_uid = 0 OR ' . $this->dbVars['table'] . '.sys_language_uid = (-1) )';

			//Load the Template
		$tmpl = array();
		$tmpl['total'] = $this->cObj->fileResource( $arrListSettings['tmplFile'] );
		$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], $arrListSettings['tmplSubpart'] );
		$tmpl['item'] = $this->getLayouts( $tmpl['total'], $arrListSettings['altLayouts'], 'BLOBITEM' );

			//DB Queries 
		$rsltNumRows = $this->pi_exec_query( $this->dbVars['table'], 1, $arrListSettings['sqlWhereClause'] . $arrListSettings['sqlWhereClauseLocal'] );
		list( $this->internal['res_count'] ) = $GLOBALS['TYPO3_DB']->sql_fetch_row( $rsltNumRows );
		if( $this->internal['res_count'] > 0 ) {

				//Building the List... (quering for all def. Records)
			$rslt = $this->pi_exec_query( $this->dbVars['table'], 0, $arrListSettings['sqlWhereClause'] . $arrListSettings['sqlWhereClauseLocal'] );
			
			$count = 0;
			$arrItems = array();
			
			while( $this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) ) {
				if( $this->sys_language_uid != 0 ) {
					$this->internal['currentRow'] = $GLOBALS['TSFE']->sys_page->getRecordOverlay( $this->dbVars['table'], $this->internal['currentRow'], $this->sys_language_uid, '' );
						//If no translation exists...
					if( array_key_exists( '_LOCALIZED_UID', $this->internal['currentRow'] ) ) {
					} else {
							//Check for records where langugage is set to '[ALL]'
						if( !$this->internal['currentRow']['sys_language_uid'] == '-1' ) {
							continue;
						}
					}
				}

					//generate listtype-specific marker
				$LINK_ITEM = explode('|', $this->pi_list_linkSingle( '|', $this->internal['currentRow']['uid'], true, array(),	false,	$arrListSettings['singlePid'] ) );
				$LINK_FILE = explode('|', $this->pi_linkTP( '|', array( $this->prefixId => array( 'downloadUid' => $this->internal['currentRow']['uid'] ) ), false, $GLOBALS['TSFE']->id ) );
				
				$specMarker = array();
				
				/**
				 * ---------------------------------------------------------------------------------------------------------
				 * @deprecated
				 */
				$specMarker['###BLOB_TITLE_LINK###'] = $LINK_ITEM[0] . $this->getFieldContent('title') . $LINK_ITEM[1];
				$specMarker['###BLOB_MORE_LINK###'] = $LINK_ITEM[0] . $arrListSettings['btnWrapShow'] . $LINK_ITEM[1];
				$specMarker['###BLOB_DOWNLOAD_LINK###'] = null;
				if ( $this->blobExists( $this->internal['currentRow']['uid'] ) ) {
					$specMarker['###BLOB_DOWNLOAD_LINK###'] = $LINK_FILE[0] . $arrListSettings['btnWrapDwnld'] . $LINK_FILE[1];
				}
				/**
				 * @deprecated
				 * ---------------------------------------------------------------------------------------------------------
				 */
				$blobUID =  $this->internal['currentRow']['_LOCALIZED_UID'] ? $this->internal['currentRow']['_LOCALIZED_UID'] : $this->internal['currentRow']['uid'];
				if ( !$this->blobExists( $blobUID ) ) {
					$LINK_FILE = array( 0 => '' , 1=> '' );
					$specMarker['###BLOB_DOWNLOAD###'] = '';
				} 
				
				$arrItems[] = $this->cObj->substituteMarkerArrayCached( 
					$tmpl['item'][$count%count( $tmpl['item'] )],
					array_merge( 
						$this->getGlobalMarkerArray( $listType ),
						$specMarker
					),
					array(),
					array(
						'###BLOB_LINK_ITEM###' => $LINK_ITEM,
						'###BLOB_LINK_FILE###' => $LINK_FILE
					)
				);

				$count++;
			}//End of while( $this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) )

			$wrappedSubpartArray = array();
			$subpartArray = array(
				'###CONTENT###' => implode( '', $arrItems )
			);
			$rtnVal = $this->cObj->substituteMarkerArrayCached( 
				$tmpl['total'],
				$listHeaderMarkerArray,
				$subpartArray,
				$wrappedSubpartArray
			);
		} else {
			$tmpl['total'] = $this->cObj->fileResource( $arrListSettings['tmplFile'] );
			$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], $arrListSettings['tmplSubpart_noItem'] );
			$rtnVal = $this->cObj->substituteMarkerArrayCached( $tmpl['total'] );

				//Hide the 'no-records-found'-message if the searchfunction is enabled and no searchword is entered.
			if( $listType == 'search' && empty( $this->piVars['sword'] ) ) {
				unset( $rtnVal );
			}
		}

			//Append searchbox or 'add2fav'-button
		if( $listType == 'search' ) {
			$rtnVal = $this->pi_list_searchBox() . $rtnVal;
		}
		if( $listType == 'list' ) {
			if ( $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlAdd2Fav', 'sSettings' ) ) {
				$rtnVal .= $this->vPersonal_Config();
			}		
		}
		return $rtnVal;
	}
	
	
	/**
	 * vPersonal_Config
	 * Method to display the button to add or remove folders to/from the current user's favorites.
	 * 
	 * @access 	private
	 * @return	Parsed String containing the button to add a folder 
	 */
	/*private*/function vPersonal_Config() {
		if ( $GLOBALS['TSFE']->loginUser ) {
			
			$GLOBALS['TSFE']->no_cache = true;
			
			if ( $this->piVars['dr_blob']['action'] == 'add' ) {
				
				//Delete all items first...
				$rsltDelete = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
					'tx_drblob_personal',
					'uid_feusers = \'' . $GLOBALS['TSFE']->fe_user->user['uid'] . '\' AND ' .
						'uid_pages IN ( ' . $this->piVars['dr_blob']['items'] . ' ) '
				);
				
				//Prepare Insert Values
				$arrItems = explode( ',', $this->piVars['dr_blob']['items'] );
				for( $i=0; $i < count($arrItems); $i++ ) {
					$rsltInsert= $GLOBALS['TYPO3_DB']->exec_INSERTquery(
						'tx_drblob_personal',
						array(
							'uid_feusers' => $GLOBALS['TSFE']->fe_user->user['uid'],
							'uid_pages' => $arrItems[$i]
						)
					);			
				}
			} else if ( $this->piVars['dr_blob']['action'] == 'remove' ) {
				
				//Delete all items first...
				$rsltDelete = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
					'tx_drblob_personal',
					'uid_feusers = \'' . $GLOBALS['TSFE']->fe_user->user['uid'] . '\' AND ' .
						'uid_pages IN ( ' . $this->piVars['dr_blob']['items'] . ' ) '
				);
			}
			unset( $this->piVars['dr_blob'] );

			$pidList = $this->pi_getPidList( $this->conf['pidList'], $this->conf['recursive'] );
			$rslt = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'count(*)',
				'tx_drblob_personal',
				'uid_feusers = \'' . $GLOBALS['TSFE']->fe_user->user['uid'] . '\' AND ' .
					'uid_pages IN ( ' . $pidList . ' ) '
			);
			$arr = $GLOBALS['TYPO3_DB']->sql_fetch_row( $rslt );
			
			$arrValues = array(
				'###FORM_METHOD###' => 'post',
				'###FORM_TARGET###' => $this->cObj->getTypoLink_URL( $GLOBALS['TSFE']->id, array( 'no_cache' => 1 ) ),
				'###ACTION###' => '',
				'###ITEMS###' => $pidList
			);
			
			$tmplFile = $this->getTemplateFile();
			$tmplContent = $this->cObj->fileResource( $tmplFile );
			if ( $arr[0] != ( substr_count( $pidList, ',' ) + 1 ) ) {
				$arrValues['###ACTION###'] = 'add';
				$tmplSubpart = '###TEMPLATE_PERSONAL_ADD_FOLDER###';
			} else {
				$arrValues['###ACTION###'] = 'remove';
				$tmplSubpart = '###TEMPLATE_PERSONAL_REMOVE_FOLDER###';
			}
			
			return $this->cObj->substituteMarkerArrayCached( 
				$this->cObj->getSubpart( $tmplContent, $tmplSubpart ), 
				$arrValues
			);
			
		} else {
			return '';
		}
	}
	

	/**
	 * vSingle
	 * Generate the view for a single item
	 * 
	 * @return 	String $content
	 * @access 	private
	 */
	/*private*/function vSingle() {
			$ffReturnPID = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlReturnPID', 'sSettings' );
			
			$returnPID = ( $ffReturnPID ? $ffReturnPID : $GLOBALS['TSFE']->id );
			$tmplFile = $this->getTemplateFile();
			$tmpl = $this->cObj->fileResource( $tmplFile );
			$tmpl = $this->cObj->getSubpart( $tmpl, '###TEMPLATE_SINGLE###' );

		
		if ( !empty( $this->piVars['showUid'] ) ) {
     		$this->pi_listFields = 'uid,pid,title,description,crdate,tstamp,sys_language_uid,cruser_id,blob_name,blob_size,blob_type,download_count';
			$this->internal['currentTable'] = $this->dbVars['table'];
			$this->internal['currentRow'] = $this->pi_getRecord( $this->dbVars['table'], intval( $this->piVars['showUid'] ) );
			
				//Fetch the translated version if exists
			if ( $this->sys_language_uid > 0 ) {
				$this->internal['currentRow'] = $GLOBALS['TSFE']->sys_page->getRecordOverlay( $this->dbVars['table'], $this->internal['currentRow'], $this->sys_language_uid );
			}
				//download-link
			
			$btnDownload = null;
			$blobUID =  ($this->internal['currentRow']['_LOCALIZED_UID'] ? $this->internal['currentRow']['_LOCALIZED_UID'] : $this->internal['currentRow']['uid']);
			if ( $this->blobExists( $blobUID ) ) {
				$LINK_FILE = explode('|', $this->pi_linkTP( '|', array( $this->prefixId => array( 'downloadUid' => $this->internal['currentRow']['uid'] ) ), false, $GLOBALS['TSFE']->id ) );
				$btnDownload = $this->pi_getLL('single_button_download');
			} else {
				$LINK_FILE = array( 0, 1 );
			}
			
			return $this->cObj->substituteMarkerArrayCached( 
				$tmpl, 
				array_merge( 
					$this->getGlobalMarkerArray( 'single' ),
					array( 
						'###BLOB_SINGLE_RTN-URL###' => $this->pi_getPageLink( $returnPID ), 
						'###BLOB_DOWNLOAD_LINK###' => $LINK_FILE[0] . $btnDownload . $LINK_FILE[1],
						'###BLOB_DATA_EXISTS_SWITCH_START###' => ( $this->blobExists( $blobUID ) ? '' : ' <!-- ' ),
						'###BLOB_DATA_EXISTS_SWITCH_END###' => ( $this->blobExists( $blobUID ) ? '' : ' --!> ' )
						
					)
				),
				array(),
				array(
					'###BLOB_LINK_FILE###' => $LINK_FILE
				)
			);
			
		} else {
			return $this->pi_getLL( 'single_noItemFound' );
		}
	}
	

	/**
	 * Returns the row $uid from $table
	 * This method overwrittes the original one, because the orgiginal one queries for '*'.
	 * In case of the (possibly) big blob-field this isn't very performant
	 *
	 * @param	string		The table name
	 * @param	integer		The uid of the record from the table
	 * @param	boolean		If $checkPage is set, it's required that the page on which the record resides is accessible
	 * @return	array		If record is found, an array. Otherwise false.
	 */
	function pi_getRecord($table,$uid,$checkPage=0)	{
		global $TCA;
		$uid = intval($uid);
		if (is_array($TCA[$table]))	{
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($this->pi_listFields, $table, 'uid='.intval($uid).$GLOBALS['TSFE']->sys_page->enableFields($table));
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$GLOBALS['TSFE']->sys_page->versionOL($table,$row);
				$GLOBALS['TYPO3_DB']->sql_free_result($res);

				if (is_array($row))	{
					if ($checkPage)	{
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'uid='.intval($row['pid']).$GLOBALS['TSFE']->sys_page->enableFields('pages'));
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($res))	{
							return $row;
						} else {
							return 0;
						}
					} else {
						return $row;
					}
				}
			}
		}
	}
	
	
	/**
	 * pi_list_searchBox
	 * Overwrittes the pi-base-method <i>pi_list_searchBox</i> to add a search box that uses the template functionality

	 * @see		parent::pi_list_searchBox
	 * @return 	String Parsed search box
	 * @access 	private
	 */
	/*private*/function pi_list_searchBox() {

		$tmplFile = $this->getTemplateFile();
		$tmpl = $this->cObj->fileResource( $tmplFile );
		$tmpl = $this->cObj->getSubpart( $tmpl, '###TEMPLATE_SEARCH###' );

		$content = $this->cObj->substituteMarkerArrayCached( 
			$tmpl, 
			array(
				'###FORM_URL###' => htmlspecialchars( t3lib_div::getIndpEnv( 'REQUEST_URI' ) ),
				'###SEARCH_BUTTON###' => $this->pi_getLL( 'search_button_search' ),
				'###SWORDS###' => $this->piVars['sword']
			) 
		);
		return $content;
	}


	/**
	 * download
	 * This Method is used to load a record from the database and enable downloading it.
	 * Therefore it sends an HTTP-Header with blob_type as contentType
	 * 
	 * @see		RfC 2045, RfC 2046, RfC 2077 for Content Disposition
	 * @access 	protected
	 * 
	 * @internal IE6 SSL Bug: http://support.microsoft.com/default.aspx?scid=kb;EN-US;q297822
	 */
	/*protected*/function vDownload() {

		$this->internal['currentTable'] = $this->dbVars['table'];
		$this->internal['currentRow'] = $this->pi_getRecord( $this->dbVars['table'], $this->piVars['downloadUid'] );
		
		if ( $this->sys_language_uid ) {
			$this->internal['currentRow'] = $GLOBALS['TSFE']->sys_page->getRecordOverlay( $this->dbVars['table'], $this->internal['currentRow'], $this->sys_language_uid );
			$lRowUID = $this->internal['currentRow']['_LOCALIZED_UID'];
		} else {
			$lRowUID = $this->internal['currentRow']['uid']; 
		}

			//increment the download_count field
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			$this->dbVars['table'],
			'uid= \'' . $lRowUID . '\'',
			array(
				'download_count' => ( $this->internal['currentRow']['download_count'] + 1 )
			)
		);

		$contentType = $this->getFieldContent( 'blob_type' );
		if ( empty( $contentType ) ) {
			$contentType = 'text/plain';
		}

		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', ( time()-3600 ) . ' GMT' ), true );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT', true );
		header( 'Cache-Control: post-check=0, pre-check=0', true );
		header( 'Content-Type: ' . $contentType, true );
		header( 'Content-Length: ' . $this->getFieldContent( 'blob_size' ) );
		header( 'Content-Transfer-Encoding: binary', true );
		header( 'Content-Disposition: attachment; filename='.$this->getFieldContent( 'blob_name' ) );
		
			//This is the workaround of the IE6-SSL Bug.
			//Thanks to Christoph Lorenz for that :-)
		$client = t3lib_div::clientInfo();
		if( ( $client['BROWSER'] == 'msie' ) && ( $client['VERSION'] == '6' ) ) {
			header( 'Pragma: anytextexeptno-cache', true );
		} else {
			header( 'Pragma: no-cache', true );
		}

		echo stripslashes( $this->getFieldContent( 'blob_data' ) );

		//Avoid Typo from displaying the page
		exit();
	}


	/**
	 * getCmd
	 * Returns the command that contains the mode to call
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
	 * getLayouts
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
		for( $a = 0; $a < $alternatingLayouts; $a++ ) {
			$m = '###'.$marker.( $a ? '_' . $a : '' ) . '###';
			if ( strstr( $templateCode, $m ) ) {
				$out[] = $GLOBALS['TSFE']->cObj->getSubpart( $templateCode, $m );
			} else {
				break;
			}
		}
		return $out;
	}
	
	
	/**
	 * blobExists
	 * Checks whether a binary object exists- or not
	 * 
	 * @param	Int		Uid of the record to check	
	 * @return 	Bool	Returns whether an file exists- or not
	 * @access	private
	 */
	/*private*/function blobExists( $item ) {
		
		$rslt = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*)',
			$this->dbVars['table'],
			'uid=' . $item . ' AND blob_data != \'\''
		);
		list( $recCnt ) = $GLOBALS['TYPO3_DB']->sql_fetch_row( $rslt );
		if ( $recCnt == 1 ) {
			return true;
		} else {
			return false;
		}		
	}
	

	/**
	 * getMarkerArray
	 * Method to get an Array containing the template marker used in the plugin depending on the mode
	 * 
	 * @param	String	Mode
	 * 					 - single
	 * 					 - top
	 * 					 - list
	 * 					 - personal
	 * @return Array	Array containing the parsed marker
	 * @access	protected 
	 */
	/*protected*/function getGlobalMarkerArray( $mode ) {

		$dateWrap = is_array( $this->conf[$mode.'View.']['date_stdWrap.'] ) ? $this->conf[$mode.'View.']['date_stdWrap.'] : array( 'date' => $this->pi_getLL( $mode.'_dateFormat' ) ) ;

		$arrMarker = array(
			'###BLOB_TITLE###' => $this->getFieldContent('title'),
			'###BLOB_DESCRIPTION###' => $this->pi_RTEcssText( $this->getFieldContent('description') ),
			'###BLOB_AUTHOR###' => $this->getFieldContent('author'),
			'###BLOB_AUTHOR_EMAIL###' => $this->getFieldContent('author_email'),
			'###BLOB_CRDATE###' => $this->cObj->stdWrap( $this->getFieldContent( 'crdate' ), $dateWrap ),
			'###BLOB_LASTCHANGE###' => $this->cObj->stdWrap( $this->getFieldContent( 'tstamp' ), $dateWrap ),
			'###BLOB_DOWNLOADCOUNT###' => $this->getFieldContent('download_count'),
			'###BLOB_FILENAME###' => $this->getFieldContent('blob_name'),
			'###BLOB_FILESIZE###' => t3lib_div::formatSize( $this->getFieldContent('blob_size'), (' B| KB| MB| GB' ) ),
			'###BLOB_FILETYPE###' => $this->getFieldContent('blob_type'),
			'###BLOB_FILEICON###' => $this->getFileIcon( $this->getFieldContent('blob_name') ),
			'###BLOB_MORE###' => $this->pi_getLL( $mode.'_button_show' ),
			'###BLOB_DOWNLOAD###' => $this->pi_getLL( $mode.'_button_download' )
		);
		
		switch( $mode ) {
			case 'single':
				unset( $arrMarker['###BLOB_MORE###'] );
			break;
			
			case 'personal':
			case 'top':
				$toCut = intval( $this->conf[$mode.'View.']['lengthOfDescription'] ) > 0 ? intval( $this->conf[$mode.'View.']['lengthOfDescription'] ) : 150;
				$arrMarker['###BLOB_DESCRIPTION###'] = strip_tags( $arrMarker['###BLOB_DESCRIPTION###'] );
				if ( strlen( $arrMarker['###BLOB_DESCRIPTION###'] ) > $toCut ) {
					$arrMarker['###BLOB_DESCRIPTION###'] = substr( $arrMarker['###BLOB_DESCRIPTION###'], 0, -(strlen( $arrMarker['###BLOB_DESCRIPTION###'] ) - $toCut ) ) . '...';
				}
			break;
		}
		return $arrMarker;
	}
	

	/**
	 * getFieldContent
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
	 * getFileIcon
	 * Returns the fileicon for the given Filename.
	 * 
	 * @param 	String 	$filename
	 * @return 	String 	<img>-tag
	 * @access 	protected
	 */
	/*protected*/function getFileIcon( $fileName ) {

		$icon = array(
			'path' => ( !empty( $this->conf['fileExtIconFolder'] ) ? $this->conf['fileExtIconFolder'] : 'typo3/gfx/fileicons/' ),
			'height' => ( !empty( $this->conf['fileExtIconHeight'] ) ? $this->conf['fileExtIconHeight'] : 16 ),
			'width' => ( !empty( $this->conf['fileExtIconWidth'] ) ? $this->conf['fileExtIconWidth'] : 18 ),
			'file' => ''
		);
		$icon['file'] = @is_file( $icon['path'] . $tmp['realFileext'].'.gif' ) ? $icon['path'] . $tmp['realFileext'].'.gif' : $icon['path'] . 'default.gif';
		
		if ( !empty( $fileName ) ) {
			$tmp = t3lib_div::split_fileref( $fileName );
			return '<img src="'.$icon['file'].'" border="0" alt="'.$tmp['realFileext'].'" height="'.$icon['height'].'px" width="'.$icon['width'].'px" />';
		} else {
			return '';
		}
	}


	/**
	 * getAuthor
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
	 * getFieldHeader_sortLink
	 * Displays a field's title in LocalLang wrapped in a sortlink
	 * 
	 * @access 	private
	 * @param 	String Field
	 * @return 	String Field with sortLink
	 */
	/*private*/function getFieldHeader_sortLink( $fieldName ) {
		return $this->pi_linkTP_keepPIvars( 
			$this->pi_getLL( 'list_field_'.$fieldName ),
			array( 'sort' => $fieldName . ':' . ( $this->internal['descFlag'] ? 0 : 1 ) ),
			0,
			$GLOBALS['TSFE']->id
		);
	}


	/**
	 * getTemplateFile
	 * Function to get templatefile by checking against a internal priority
	 * Priority (high to low):
	 * 		(1)	File set in the plugin configuration (Flexform)
	 * 		(2)	Templatefile set in the TS Setup
	 * 		(3)	default Template (typo3conf/ext/dr_blob/res/dr_blob.tmpl)
	 * 
	 * @access private
	 * @param
	 * @return	String	Template File
	 */
	/*private*/function getTemplateFile() {
		$tmplFile = array();

		//FF Tmpl File
		$tmplFile[0] = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlTemplate', 'sSettings' );
		$tmplFile[0] = ( !empty( $tmplFile[0] ) ) ? ( 'uploads/tx_drblob/' . $tmplFile[0] ) : null;
		
		//TS Tmpl File
		$tmplFile[1] = $this->conf['templateFile'];
		
		//Standard Template
		$tmplFile[2] = t3lib_extMgm::extPath('dr_blob').'res/dr_blob.tmpl';

		for ( $i=0; $i < sizeof( $tmplFile ); $i++ ) {
			if ( !empty( $tmplFile[$i] ) ) {
				if ( file_exists( $tmplFile[$i] ) && is_readable( $tmplFile[$i] ) ) {
					return $tmplFile[$i];
				}
			}
		}
		return '';
	}
};


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/pi1/class.tx_drblob_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/pi1/class.tx_drblob_pi1.php']);
}
?>