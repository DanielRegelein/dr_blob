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

require_once( PATH_tslib . 'class.tslib_pibase.php' );
require_once( t3lib_extMgm::extPath( 'dr_blob' ) . '/pi1/class.tx_drblob_pi1_vFolderTree.php' );


/**
 * @name		tx_drblob_pi1
 * Frontend plugin "File List", Ext.Key "dr_blob"
 * This class is executed by the Typo3 Frontend to generate File Lists 
 * for secure downloads. 
 *
 * @extends 	tslib_pibase
 * @author		Daniel Regelein <Daniel.Regelein@diehl-informatik.de>
 * @category 	Frontend Plugins
 * @copyright 	Copyright &copy; 2005-present Daniel Regelein
 * @package 	dr_blob
 * @filesource 	pi1/class.tx_drblob_pi1.php
 * @version 	2.0.0
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
	/*protected*/	var $dbVars = array( 'table_content' => 'tx_drblob_content', 'table_categories' => 'tx_drblob_category', 'table_categories_mm' => 'tx_drblob_category_mm', 'table_personal' );
	
	/**
	 * @var 	Array $searchFields
	 * Sets the fields that are used by the inbuild search function.
	 * 
	 * @access 	private
	 */
	/*private*/		var $searchFields = array( 'title', 'description', 'blob_name', 't3ver_label' );
	
	
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
		
		switch( $cmd = $this->getConfParam( 'code' ) ) {
			case 'dummy':
				return '';
			break;
			
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

						return $this->pi_wrapInBaseClass( $this->makeList( 'personal' ) );
					}
				} else {
					return '';
				}
			break;

			case 'list':
			default:
				if ( strstr( $this->cObj->currentRecord, 'tt_content' ) ) {
					$ffPidList = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlPages', 'sDataSource' );
					$ffRecursive = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlRecursive', 'sDataSource' );
					$this->conf['pidList'] = ( $ffPidList ? $ffPidList : $this->conf['pidList'] ); 
					$this->conf['recursive'] = ( $ffRecursive ? $ffRecursive : $this->conf['recursive'] );
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
		
		
		#############################################################################################
		### Configure Search function                                                             ###
		#############################################################################################
		$sFldLst = null;
		if( empty( $conf['searchFieldList'] ) ) {
			$conf['searchFieldList'] = implode( ',', $this->searchFields );
		}
		foreach( explode( ',', $conf['searchFieldList'] ) as $field ) {
			if( in_array( $field, $this->searchFields ) ) {
				$sFldLst .= $field . ',';
			}
		}
		$this->internal['searchFieldList'] = rtrim( $sFldLst, ',');
	}
	

	/**
	 * getCmd
	 * Returns the command that contains the mode to call
	 * 
	 * @return 	String Command what to do
	 * @access 	private
	 */
	/*private*/function getCmd() {
		

	}
	
	
	/**
	 * Just a test for now
	 */
	/*private*/ function getConfParam( $paramName, $default = null, $mode=null ) {
		$value = null;
		
		switch( $paramName ) {
			case 'templateFile': $value = $this->getTemplateFile(); break;
			
			case 'singlePID':
				$tmpFFval = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlSinglePID', 'sSettings' );
				$tmpTSval = $this->conf['singlePID'];
				$value = $tmpFFval ? $tmpFFval : ( $tmpTSval ? $tmpTSval : $GLOBALS['TSFE']->id );
			break;
			
			case 'showAdd2Fav': 
				$tmpFFval = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlAdd2Fav', 'sSettings' );
				$tmpTSval = $this->conf['listView.']['showAdd2Fav'];
				$value = (bool)$tmpFFval ? (bool)$tmpFFval : ( (bool)$tmpTSval ? (bool)$tmpTSval : false );
			break;
			
			case 'code':
				$tmpFFval = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlWhatToDisplay', 'sSettings' );
				$tmpTSval = ( !empty( $this->conf['code'] ) ) ? strtolower( $this->conf['code'] ) : 'list';
				$value = ( $tmpFFval ? $tmpFFval : $tmpTSval );
				if ( $this->piVars['showUid'] && $mode != 'dummy' ) {
					$value = 'single';
				}
			break;
		}
		
		unset( $tmpFFval );
		unset( $tmpTSval );
		return $value;
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
		$arrListType = array( 'list', 'top', 'search', 'personal' );
		if( !in_array( $listType, $arrListType ) ) {
			$listType = 'list';
		}


			//Extract FlexForm Configuration Values
		$ffCategoriesShowWhat = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlCategoriesShowWhat', 'sDataSource' );
		$ffCategories = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlCategories', 'sDataSource' );
		$ffLimit = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlLimitCount', 'sSettings' );
		
		
			//Array Containing the Settings for the list, depending on the selected list type
		$arrListSettings = array();
		$arrListSettings['tmplFile'] = $this->getTemplateFile();
		$arrListSettings['btnWrapShow'] = $this->pi_getLL( $listType . '_button_show' );
		$arrListSettings['btnWrapDwnld'] = $this->pi_getLL( $listType . '_button_download' );
		$arrListSettings['recLimit'] = ( $ffLimit ? $ffLimit : ( $this->conf[$listType.'View.']['limit'] ? $this->conf[$listType.'View.']['limit'] : 5 ) );
		
		$listHeaderMarkerArray = array();
		$vFolderMarker = '';

			//Add Type-specific marker
		switch( $listType ) {
			case 'top':
				$arrListSettings['tsObj'] = 'topView.';
				$arrListSettings['tmplSubpart'] = 'TEMPLATE_TOP';
				$arrListSettings['sqlWhereClause'] = 'AND is_vip=\'1\' ';

				$this->internal['orderBy'] = 'crdate';
				$this->internal['descFlag'] = '1';
			break;
			
			case 'personal':
				$arrListSettings['tsObj'] = 'personalView.';
				$arrListSettings['tmplSubpart'] = 'TEMPLATE_PERSONAL';
				$this->internal['orderBy'] = 'tstamp';
				$this->internal['descFlag'] = '1';
			break;
			
			case 'search':
			case 'list':
			
				$arrListSettings['tsObj'] = 'listView.';
				$arrListSettings['tmplSubpart'] = 'TEMPLATE_LIST';
				

				#############################################################################################
				### Sorting records ( default: title ASC )                                                ###
				#############################################################################################
				if ( empty( $this->piVars['sort'] ) ) {
					//Extract FF Sorting vars
					$ffQrySortBy = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlListOrderBy', 'sSettings' );
					$ffQrySortDirection = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlListOrderDirection', 'sSettings' );
					
					$sortBy = $ffQrySortBy ? $ffQrySortBy : ( $this->conf[$arrListSettings['tsObj']]['listOrderBy'] ? $this->conf[$arrListSettings['tsObj']]['listOrderBy'] : 'title' );
					$sortDir = $ffQrySortDirection ? $ffQrySortDirection : ( $this->conf[$arrListSettings['tsObj']]['listOrderDir'] ? $this->conf[$arrListSettings['tsObj']]['listOrderDir'] : '0' );

					$this->piVars['sort'] = $sortBy.':'.$sortDir; 
				}
				list( $this->internal['orderBy'], $this->internal['descFlag'] ) = explode( ':',$this->piVars['sort'] );


				
				#############################################################################################
				### List-Header, used later                                                               ###
				#############################################################################################
				$listHeaderMarkerArray = array(
					'###BLOB_SORTLINK_UID###'  => $this->getFieldHeader_sortLink('uid'),
					'###BLOB_SORTLINK_TITLE###'  => $this->getFieldHeader_sortLink('title'),
					'###BLOB_SORTLINK_CRDATE###' => $this->getFieldHeader_sortLink('crdate'),
					'###BLOB_SORTLINK_TSTAMP###' => $this->getFieldHeader_sortLink('tstamp'),
					'###BLOB_SORTLINK_LASTCHANGE###' => $this->getFieldHeader_sortLink('tstamp'),
					'###BLOB_SORTLINK_FILESIZE###' => $this->getFieldHeader_sortLink('blob_size'),
					'###BLOB_SORTLINK_DOWNLOADCOUNT###'  => $this->getFieldHeader_sortLink('downloadcount'),
					'###BLOB_SORTLINK_FILETYPE###'  => $this->getFieldHeader_sortLink('blob_type'),
					'###BLOB_SORTLINK_AUTHOR###' => $this->getFieldHeader_sortLink('cruser_id'),
				);
				
				

				#############################################################################################
				### vFolder related stuff                                                                 ###
				#############################################################################################
				if( $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlShowVFolderTree', 'sVFolderTree' ) ) {
					
					$vFolderMarker = $this->vFolderTree();
					
					//Check whether the folder given via URL is allowed for this Plugin, or not.
					$this->piVars['pid'] = intval( $this->piVars['pid'] );
					$pidList = explode( ',', $this->pi_getPidList( $this->conf['pidList'], $this->conf['recursive'] ) );
					
					//If we found a valid request, prepare the DB-Query according to it.
					if( t3lib_div::inArray( $pidList, $this->piVars['pid'] ) && !empty( $this->piVars['pid'] ) ) {
						$arrListSettings['sqlWhereClause'] = 'AND pid=' . $this->piVars['pid'];
					} else {
						$arrListSettings['sqlWhereClause'] = 'AND  pid=' . $pidList[0];
					}
				}
				
			break;
		}
		

		
		#############################################################################################
		### Template-Related stuff                                                                ###
		#############################################################################################
		$arrListSettings['altLayouts'] = intval( $this->conf[$arrListSettings['tsObj']]['alternatingLayouts'] ) > 0 ? intval( $this->conf[$arrListSettings['tsObj']]['alternatingLayouts'] ) : 2;
		$arrListSettings['tmplSubpart'] = $this->conf[$arrListSettings['tsObj']]['altSubpartMarker'] ? $this->conf[$arrListSettings['tsObj']]['altSubpartMarker'] : $arrListSettings['tmplSubpart'];

		$tmpl = array();
		$tmpl['total'] = $this->cObj->fileResource( $arrListSettings['tmplFile'] );
		$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], '###'.$arrListSettings['tmplSubpart'].'###' );
		$tmpl['item'] = $this->getLayouts( $tmpl['total'], $arrListSettings['altLayouts'], 'BLOBITEM' );
		
		
		
		#############################################################################################
		### Preparing Database Queries                                                            ###
		#############################################################################################
		$this->internal['results_at_a_time'] = t3lib_div::intInRange( $arrListSettings['recLimit'], 1, 1000, 20 );
		$this->internal['orderByList'] = 'sorting,title,crdate,tstamp,cruser_id,blob_size,uid,download_count,blob_type';
		$this->pi_listFields = 'uid,pid,title,description,crdate,tstamp,sys_language_uid,cruser_id,blob_name,blob_size,blob_type,download_count,t3ver_label,blob_checksum';
		$arrListSettings['sqlWhereClauseLocal'] = ' AND ( ' . $this->dbVars['table_content'] . '.sys_language_uid = 0 OR ' . $this->dbVars['table_content'] . '.sys_language_uid = (-1) )';

			//Show only [...] Categories
		if( $ffCategoriesShowWhat ) {
			$arrMM = array(
				'table' => $this->dbVars['table_categories'],
				'mmtable' => $this->dbVars['table_categories_mm'],
				'catUidList' => $ffCategories
			);
		} else {
			$arrMM = null;
		}
		
		$rsltNumRows = $this->pi_exec_query( $this->dbVars['table_content'], 1, $arrListSettings['sqlWhereClause'] . $arrListSettings['sqlWhereClauseLocal'], $arrMM, 'title' );		
		list( $this->internal['res_count'] ) = $GLOBALS['TYPO3_DB']->sql_fetch_row( $rsltNumRows );
		
			//Don't show a list if TS emptySearchAtStart is not 0
		if( ( $listType == 'search' && empty( $this->piVars['sword'] ) ) && ( $this->conf['emptySearchAtStart'] != '0' ) ) {
			$this->internal['res_count'] = 0;
		}

		#############################################################################################
		### Perform database queries                                                              ###
		#############################################################################################
		if( $this->internal['res_count'] > 0 ) {
			
				//Building the List... (quering for all def. Records)
			$rslt = $this->pi_exec_query( $this->dbVars['table_content'], 0, $arrListSettings['sqlWhereClause'] . $arrListSettings['sqlWhereClauseLocal'], $arrMM, 'title' );
			
			$count = 0;
			$arrItems = array();
			while( $this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) ) {
				if( $this->sys_language_uid != 0 ) {
					$this->internal['currentRow'] = $GLOBALS['TSFE']->sys_page->getRecordOverlay( $this->dbVars['table_content'], $this->internal['currentRow'], $this->sys_language_uid, '' );
						//If no translation exists...
					if( !array_key_exists( '_LOCALIZED_UID', $this->internal['currentRow'] ) ) {
							//Check for records where langugage is set to '[ALL]'
						if( !$this->internal['currentRow']['sys_language_uid'] == '-1' ) {
							continue;
						}
					}
				}

					//generate listtype-specific marker
				$this->conf[$arrListSettings['tsObj']]['moreLink_stdWrap.']['typolink.']['useCacheHash'] = 1;
				$this->conf[$arrListSettings['tsObj']]['moreLink_stdWrap.']['typolink.']['no_cache'] = 0;
				$this->conf[$arrListSettings['tsObj']]['moreLink_stdWrap.']['typolink.']['parameter'] = $this->getConfParam( 'singlePID' );
				$this->conf[$arrListSettings['tsObj']]['moreLink_stdWrap.']['typolink.']['additionalParams'] = $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',array( $this->prefixId => array( 'showUid' => $this->internal['currentRow']['uid'] ) ),'',1).$this->pi_moreParams;
				$LINK_ITEM = explode('|', $this->cObj->stdWrap( '|', $this->conf[$arrListSettings['tsObj']]['moreLink_stdWrap.']) );
				
				$this->conf[$arrListSettings['tsObj']]['downloadLink_stdWrap.']['typolink.']['useCacheHash'] = 0;
				$this->conf[$arrListSettings['tsObj']]['downloadLink_stdWrap.']['typolink.']['no_cache'] = 1;
				$this->conf[$arrListSettings['tsObj']]['downloadLink_stdWrap.']['typolink.']['parameter'] = $GLOBALS['TSFE']->id;
				$this->conf[$arrListSettings['tsObj']]['downloadLink_stdWrap.']['typolink.']['additionalParams'] = $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',array( $this->prefixId => array( 'downloadUid' => $this->internal['currentRow']['uid'] ) ),'',1).$this->pi_moreParams;
				$LINK_FILE = explode('|', $this->cObj->stdWrap( '|', $this->conf[$arrListSettings['tsObj']]['downloadLink_stdWrap.']) );

				$specMarker = array();
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
			
			$listHeaderMarkerArray['###BLOB_VFOLDERTREE###'] = $vFolderMarker;
			$rtnVal = $this->cObj->substituteMarkerArrayCached( 
				$tmpl['total'],
				array_merge(
					$listHeaderMarkerArray,
					$this->getGlobalMarkerArray( $listType, 'lang' )
				),
				$subpartArray,
				$wrappedSubpartArray
			);
		} else {
			$tmpl['total'] = $this->cObj->fileResource( $arrListSettings['tmplFile'] );
			$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], '###' . $arrListSettings['tmplSubpart'] . '_NOITEMS###' );

			$markerArray = $this->getGlobalMarkerArray( $listType, 'lang' );
			$markerArray['###BLOB_VFOLDERTREE###'] = $vFolderMarker;
			$rtnVal = $this->cObj->substituteMarkerArrayCached( $tmpl['total'], $markerArray );

				//Hide the 'no-records-found'-message if the searchfunction is enabled and no searchword is entered.
			if( $listType == 'search' && empty( $this->piVars['sword'] ) ) {
				unset( $rtnVal );
			}
		}


		#############################################################################################
		### Append searchbox or Add2Favorites-Button                                              ###
		#############################################################################################
		if( $listType == 'search' ) {
			$rtnVal = $this->pi_list_searchBox() . $rtnVal;
		}
		if( $listType == 'list' && $this->getConfParam( 'showAdd2Fav' ) ) {
				$rtnVal .= $this->vPersonal_Config();
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
				'###ITEMS###' => $pidList,
				'###LANG_PERSADD###' => $this->pi_getLL( 'personal_button_add' ),
				'###LANG_PERSREMOVE###' => $this->pi_getLL( 'personal_button_remove' )
			);
			
			$tmplContent = $this->cObj->fileResource( $this->getTemplateFile() );
			if ( $arr[0] != ( substr_count( $pidList, ',' ) + 1 ) ) {
				$arrValues['###ACTION###'] = 'add';
				$tmplSubpart = 'ADD';
			} else {
				$arrValues['###ACTION###'] = 'remove';
				$tmplSubpart = 'REMOVE';
			}
			
			return $this->cObj->substituteMarkerArrayCached( 
				$this->cObj->getSubpart( $tmplContent, '###TEMPLATE_PERSONAL_' . $tmplSubpart . '_FOLDER###' ), 
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

		#############################################################################################
		### Generate Back PID                                                                     ###
		#############################################################################################
			/* Return PID prio:
				1. URL-Param "backpid"
				2. Flexform-Value
				3. TS-Value
				4. $GLOBALS['TSFE']->id
			 */
			$tmp_ffReturnPID = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlReturnPID', 'sSettings' );
			$tmp_tsReturnPID = $this->conf['backPID'];
			$tmp_urlReturnPID = intval( $this->piVars['backPID'] );
			
			if( true ) 							{ $returnPID = $GLOBALS['TSFE']->id; }
			if( !empty( $tmp_tsReturnPID ) ) 	{ $returnPID = $tmp_tsReturnPID; } 
			if( !empty( $tmp_ffReturnPID ) )	{ $returnPID = $tmp_ffReturnPID; }
			if( !empty( $tmp_urlReturnPID ) )	{ $returnPID = $tmp_urlReturnPID; }
			

		#############################################################################################
		### Template-Related stuff                                                                ###
		#############################################################################################
			$tmplFile = $this->getTemplateFile();
			$tmplSubpart = $this->conf['singleView.']['altSubpartMarker'] ? $this->conf['singleView.']['altSubpartMarker'] : 'TEMPLATE_SINGLE';
			$tmpl = $this->cObj->fileResource( $tmplFile );
			$tmpl = $this->cObj->getSubpart( $tmpl, '###'.$tmplSubpart.'###' );

		
		if ( !empty( $this->piVars['showUid'] ) ) {
     		$this->pi_listFields = 'uid,pid,title,description,crdate,tstamp,sys_language_uid,cruser_id,blob_name,blob_size,blob_type,download_count,t3ver_label,blob_checksum';
			$this->internal['currentTable'] = $this->dbVars['table_content'];
			$this->internal['currentRow'] = $this->pi_getRecord( $this->dbVars['table_content'], intval( $this->piVars['showUid'] ) );
			
				//Fetch the translated version if exists
			if ( $this->sys_language_uid != 0 ) {
				$this->internal['currentRow'] = $GLOBALS['TSFE']->sys_page->getRecordOverlay( $this->dbVars['table_content'], $this->internal['currentRow'], $this->sys_language_uid );
			}
			
				//substitute Pagetitle
			if( (bool)$this->conf['singleView.']['substitutePagetitle'] == true ) {
				$GLOBALS['TSFE']->page['title'] = $this->internal['currentRow']['title'];	
			}
					
				//substitute Indextitle
			if( (bool)$this->conf['singleView.']['substituteIndextitle'] == true ) {
				$GLOBALS['TSFE']->indexedDocTitle = $this->internal['currentRow']['title'];
			}
			
				//download-link
			$btnDownload = null;
			$blobUID =  ( $this->internal['currentRow']['_LOCALIZED_UID'] ? $this->internal['currentRow']['_LOCALIZED_UID'] : $this->internal['currentRow']['uid'] );
			if ( $this->blobExists( $blobUID ) ) {
				$this->conf['singleView.']['downloadLink_stdWrap.']['typolink.']['useCacheHash'] = 0;
				$this->conf['singleView.']['downloadLink_stdWrap.']['typolink.']['no_cache'] = 1;
				$this->conf['singleView.']['downloadLink_stdWrap.']['typolink.']['parameter'] = $this->getFieldContent( 'downloadPID' );
				$this->conf['singleView.']['downloadLink_stdWrap.']['typolink.']['additionalParams'] = $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',array( $this->prefixId => array( 'downloadUid' => $this->internal['currentRow']['uid'] ) ),'',1).$this->pi_moreParams;
				$LINK_FILE = explode('|', $this->cObj->stdWrap( '|', $this->conf['singleView.']['downloadLink_stdWrap.']) );
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
	function pi_getRecord($table,$uid,$checkPage=0,$listFields=null)	{
		global $TCA;
		if( empty( $listFields ) ) {
			$listFields = $this->pi_listFields;
		}
		$uid = intval($uid);
		if (is_array($TCA[$table]))	{
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($listFields, $table, 'uid='.intval($uid).$GLOBALS['TSFE']->sys_page->enableFields($table));
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
	/*protected*/function vDownload( $sendHeaders = true, $uid=0 ) {
		$rowUID = ( $uid ? $uid : $this->piVars['downloadUid'] );
	
		$this->internal['currentTable'] = $this->dbVars['table_content'];
		$this->internal['currentRow'] = $this->pi_getRecord( $this->dbVars['table_content'], $rowUID );
		
		if ( $this->sys_language_uid ) {
			$this->internal['currentRow'] = $GLOBALS['TSFE']->sys_page->getRecordOverlay( $this->dbVars['table_content'], $this->internal['currentRow'], $this->sys_language_uid );
			$lRowUID = $this->internal['currentRow']['_LOCALIZED_UID'];
		} else {
			$lRowUID = $this->internal['currentRow']['uid']; 
		}


		$blob = array(
			'blob_name' => urlencode( $this->getFieldContent( 'blob_name' ) ),
			'blob_size' => $this->getFieldContent( 'blob_size' ),
			'blob_data' => '',
			'blob_type' => $this->getFieldContent( 'blob_type' )
		);
		if( empty( $data['blob_type'] ) ) {
			$data['blob_type'] = 'text/plain';
		}
		
			//Load Data
		if( $this->getFieldContent( 'type' ) == 1 ) {
			$blob['blob_data'] = $this->getFieldContent( 'blob_data' );
		} else {
			$extConf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dr_blob'] );
			$file = t3lib_div::dirname( $extConf['fileStorageFolder'] ) . '/' . $this->getFieldContent( 'blob_data' );
			$fp = fopen( $file, 'r' );
				$blob['blob_data'] = fread( $fp, filesize ( $file ) );
			fclose( $fp );
		}
		$blob['blob_data'] = stripslashes( $blob['blob_data'] );
		
		
		if( $sendHeaders == true ) {

				//increment the download_count field
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				$this->dbVars['table_content'],
				'uid= \'' . $lRowUID . '\'',
				array(
					'download_count' => ( $this->internal['currentRow']['download_count'] + 1 )
				)
			);

			if( empty( $this->conf['tryToOpenFileInline'] ) || (bool)$this->conf['tryToOpenFileInline'] == false ) {
				$contentDisposition = 'attachment';
			} else {
				$contentDisposition = 'inline';
			}
	
				//Send out header
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', ( time()-3600 ) . ' GMT' ), true );
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT', true );
			header( 'Cache-Control: post-check=0, pre-check=0', true );
			header( 'Content-Type: ' . $blob['blob_type'], true );
			header( 'Content-Length: ' . $blob['blob_size'] );
			header( 'Content-Transfer-Encoding: binary', true );
			header( 'Content-Disposition: '.$contentDisposition.'; filename='. $blob['blob_name'] );
			
				//This is the workaround of the IE-X-SSL Bug.
				//Thanks to Christoph Lorenz for that :-)
			$client = t3lib_div::clientInfo();
			if( ( $client['BROWSER'] == 'msie' ) && ( $client['VERSION'] == '6' || $client['VERSION'] == '7' ) ) {
				header( 'Pragma: anytextexeptno-cache', true );
			} else {
				header( 'Pragma: no-cache', true );
			}
			
			echo $blob['blob_data'];
	
				//Avoid Typo from displaying the page
			exit();
			
		} else {
				//Return Filecontent
			return $blob;
		}
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
			$this->dbVars['table_content'],
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
	 * vFolderTree
	 *
	 * @return String	Rendered vFolder Tree
	 * @access protected
	 */
	/*protected*/ function vFolderTree() {
		if( !$this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlShowVFolderTree', 'sVFolderTree' ) ) {
			return null;
		} else {
			$treeClass = t3lib_div::makeInstance( 'tx_drblob_pi1_vFolderTree' );
			$treeClass->init( $this->conf, $this->cObj, $this->piVars );
			
			$treeClass->title = 'dr_blob vFolder Tree';
			$treeClass->expandAll = 1;
			$treeClass->expandFirst = 1;
			
			$treeContent = null;
			$treeContent .= $treeClass->getBrowsableTree();
			
			return $this->cObj->stdWrap( $treeContent, $this->conf['listView.']['vFolderTree_stdWrap.'] );
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
	 * @param	String	What to return. May be
	 * 					 - content
	 * 					 - lang
	 * 					 - both
	 * @return Array	Array containing the parsed marker
	 * @access	protected 
	 */
	/*protected*/function getGlobalMarkerArray( $mode, $what='both' ) {
		$mode = ($mode!='search' ? $mode : 'list');
		if( $what != 'lang' ) {
			
			//Preparing stdWraps...
			if( !is_array( $this->conf[$mode.'View.']['date_stdWrap.']  ) ) {
				$this->conf[$mode.'View.']['date_stdWrap.']['date'] = $this->pi_getLL( $mode.'_dateFormat' );
			}
			$downloadCount = sprintf( $this->pi_getLL( 'list_field_downloadcount_wrap' ), $this->getFieldContent('download_count') );
			$this->conf[$mode.'View.']['email_stdWrap.']['typolink.']['parameter'] = $this->getFieldContent('author_email');
			
			$arrMarker = array(
				'###BLOB_UID###' => $this->getFieldContent('uid'),
				'###BLOB_TITLE###' => $this->cObj->stdWrap( $this->getFieldContent('title'), $this->conf[$mode.'View.']['title_stdWrap.'] ),
				'###BLOB_DESCRIPTION###' => $this->cObj->stdWrap( $this->pi_RTEcssText( $this->getFieldContent('description') ), $this->conf[$mode.'View.']['description_stdWrap.'] ),
				'###BLOB_AUTHOR###' => $this->cObj->stdWrap( $this->getFieldContent('author'), $this->conf[$mode.'View.']['author_stdWrap.'] ),
				'###BLOB_AUTHOR_EMAIL###' => $this->cObj->stdWrap( $tmpVars['email'], $this->conf[$mode.'View.']['email_stdWrap.'] ),
				'###BLOB_CRDATE###' => $this->cObj->stdWrap( $this->getFieldContent( 'crdate' ), $this->conf[$mode.'View.']['date_stdWrap.'] ),
				'###BLOB_LASTCHANGE###' => $this->cObj->stdWrap( $this->getFieldContent( 'tstamp' ), $this->conf[$mode.'View.']['date_stdWrap.'] ),
				'###BLOB_VERSION###' => $this->cObj->stdWrap( $this->getFieldContent( 't3ver_label' ), $this->conf[$mode.'View.']['version_stdWrap.'] ),
				'###BLOB_AGE###' => $this->cObj->stdWrap( $this->getFieldContent( 'crdate' ), $this->conf[$mode.'View.']['age_stdWrap.'] ),
				'###BLOB_DOWNLOADCOUNT###' => $this->cObj->stdWrap( $downloadCount, $this->conf[$mode.'View.']['downloadcount_stdWrap.'] ),
				'###BLOB_CHECKSUM###' => $this->cObj->stdWrap( $this->getFieldContent('blob_checksum'), $this->conf[$mode.'View.']['filechecksum_stdWrap.'] ),
				'###BLOB_FILENAME###' => $this->cObj->stdWrap( $this->getFieldContent('blob_name'), $this->conf[$mode.'View.']['filename_stdWrap.'] ),
				'###BLOB_FILESIZE###' => $this->cObj->stdWrap( $this->getFieldContent('blob_size'), $this->conf[$mode.'View.']['filesize_stdWrap.'] ),
				'###BLOB_FILETYPE###' => $this->cObj->stdWrap( $this->getFieldContent('blob_type'), $this->conf[$mode.'View.']['filetype_stdWrap.'] ),
				'###BLOB_FILEICON###' => $this->getFileIcon( $this->getFieldContent( 'blob_name' ) ),
				'###BLOB_CATEGORIES###' => '',
			);
			
			$tmp['cat'] = $this->getCategories( $this->getFieldContent('uid') );
			if( !empty( $tmp['cat'] ) ) {
				$tmp['lstCat'] = implode( ( $this->conf[$mode.'View.']['categoryDivider'] ? $this->conf[$mode.'View.']['categoryDivider'] : ',' ), $tmp['cat'] );
				$arrMarker['###BLOB_CATEGORIES###'] = $this->cObj->stdWrap( $tmp['lstCat'], $this->conf[$mode.'View.']['category_stdWrap.'] );
			}
			
				//Special Content Marker for the different modes
			switch( $mode ) {
				case 'single':
					unset( $arrMarker['###BLOB_MORE###'] );
				break;
			}
		}

		if( $what != 'content' ) {
			$arrLangMarker = array(
				'###LANG_UID###' => $this->pi_getLL( 'field_uid' ),
				'###LANG_TITLE###' => $this->pi_getLL( 'list_field_title' ),
				'###LANG_DESCRIPTION###' => $this->pi_getLL( 'list_field_description' ),
				'###LANG_FILENAME###' => $this->pi_getLL( 'list_field_blob_name' ),
				'###LANG_FILESIZE###' => $this->pi_getLL( 'list_field_blob_size' ),
				'###LANG_FILETYPE###' => $this->pi_getLL( 'list_field_blob_type' ),
				'###LANG_CHECKSUM###' => $this->pi_getLL( 'list_field_blob_checksum' ),
				'###LANG_CRDATE###' => $this->pi_getLL( 'list_field_crdate' ),
				'###LANG_CATEGORIES###' => $this->pi_getLL( 'list_field_categories' ),
				'###LANG_LASTCHANGE###' => $this->pi_getLL( 'list_field_tstamp' ),
				'###LANG_VERSION###' => $this->pi_getLL( 'list_field_version' ),
				'###LANG_AGE###' => $this->pi_getLL( 'list_field_age' ),
				'###LANG_NOITEMS###' => $this->pi_getLL( 'noRecordsFound' ),
				'###LANG_DOWNLOADCOUNT###' => $this->pi_getLL( 'list_field_downloadcount' ),
				'###BLOB_MORE###' => $this->pi_getLL( $mode .'_button_show' ),
				'###BLOB_DOWNLOAD###' => $this->pi_getLL( $mode.'_button_download' ),
				'###LANG_AUTHOR###' => $this->pi_getLL(  'list_field_author' ),
				'###LANG_AUTHOR_EMAIL###' => $this->pi_getLL( 'list_field_author_mail' ),
				'###LANG_VFOLDERTREE###' => $this->pi_getLL( 'list_vfoldertree' ),
			);
			
				//Special Langugage Marker for the different modes
			switch( $mode ) {
				case 'single':
					$arrLangMarker['###LANG_BACK###'] = $this->pi_getLL( 'single_button_back' );
				break;
			}
		}
		switch( $what ) {
			case 'lang': return $arrLangMarker; break;
			case 'content': return $arrMarker; break;
			default: return array_merge( $arrMarker, $arrLangMarker );
		}
		
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
			case 'downloadPID': return intval( $this->conf['downloadPID'] ) ? intval( $this->conf['downloadPID'] ) : $GLOBALS['TSFE']->id; break;
		}
		return $this->internal['currentRow'][$fN];
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

	
	/*
	 * 
	 * Content-Generating Methods
	 * 
	 */


	/**
	 * getCategories
	 * Returns an array with record categories
	 * 
	 * @param	Int		UID of the record
	 * @return	Array	Array with categories
	 * @access	private
	 */
	/*private*/function getCategories( $item ) {
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
		$rslt = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			$this->dbVars['table_categories'].'.title',
			$this->dbVars['table_content'],
			$this->dbVars['table_categories_mm'],
			$this->dbVars['table_categories'],
			' AND ' . $this->dbVars['table_content'] . '.uid=' . $item .
			$this->cObj->enableFields( $this->dbVars['table_categories'] )
		);
		$arrCat = array();
		while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) ) {
			$arrCat[] = $row['title'];
		}
		return $arrCat;
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
		if ( !empty( $fileName ) ) {
			$tmp = t3lib_div::split_fileref( $fileName );
			$tmp['icoFolderPath'] = ( !empty( $this->conf['fileExtIconFolder'] ) ? $this->conf['fileExtIconFolder'] : 'typo3/sysext/cms/tslib/media/fileicons/' );

			$tsConf = array(
				'file' => ( @is_file( $tmp['icoFolderPath'] . $tmp['realFileext'].'.gif' ) ? $tmp['icoFolderPath'] . $tmp['realFileext'].'.gif' : $tmp['icoFolderPath'] . 'default.gif'),
				'border' => '0',
				'altText' => $tmp['realFileext']
			);
			return $this->cObj->cObjGetSingle( 'IMAGE', $tsConf );
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
	
	
	/*
	 * 
	 * Template-Related Methods
	 * 
	 */
	

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
		$tmplFile[2] = t3lib_extMgm::siteRelPath('dr_blob').'res/dr_blob.tmpl';
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