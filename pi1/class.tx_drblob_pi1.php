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
 * @version 	2.2.1
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
	 * @var		Array	$sys_language_array
	 * This array caches the system languages
	 */
	/*private*/		var $sys_language_array = array();
	
	
	/**
	 * @var		tslib_content
	 * Local cObject for tx_drblob_content-records
	 */
	/*private*/		var $local_cObj;


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
		
		switch( $cmd = $this->getNamedConfigParameter( 'code' ) ) {
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
		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj');
		
		#############################################################################################
		### Language-related configurations                                                       ###
		#############################################################################################
			//Get site-language
		$this->sys_language_uid = $GLOBALS['TSFE']->sys_language_content;
		
		#$langRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
		#	'*',
		#	'sys_language',
		#	'1=1' . $this->cObj->enableFields( 'sys_language' )
		#);
		#$this->sys_language_array = array();
		#while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $langRes ) ) {
		#	$this->sys_language_array[$row['uid']] = $row;
		#}
		
		
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
	 * This method returns a configuration parameter by the priority 
	 * 	1. Value from Flexform
	 * 	2. Value from TypoScript
	 * 	3. Default value
	 *
	 * @param Array $ffData <code>array( 'sheet' => 'SHEETNAME', 'field' => 'FIELDNAME' )</code>
	 * @param String $tsData Value from TypoScript
	 * @param Mixed $defaultValue
	 * @return String Configuration Parameter
	 * @access private
	 */
	private function getConfigParameter( $ffData, $tsData, $defaultValue ) {
		if( is_array( $ffData ) ) {
			$ffField = $ffData['field'];
			$ffSheet = $ffData['sheet'];
		}
		$ffValue = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], $ffField, $ffSheet );
		if( !empty( $ffValue ) ) {
			return $ffValue;
		}
		if( !empty( $tsData ) ) {
			return $tsData;
		}
		return $defaultValue;
	}
	
	
	
	/**
	 * Returns a named configuration parameter
	 * 
	 * @param String Name of the configuration parameter
	 * @param String Default value to return
	 * @param String Mode to use
	 * @return Configuration Parameter
	 * @access private
	 */
	private function getNamedConfigParameter( $paramName, $default = null, $mode=null ) {
		$value = null;
		
		switch( $paramName ) {
			case 'templateFile': 	return $this->getTemplateFile(); break;
			case 'singlePID': 		return $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlSinglePID' ), $this->conf['singlePID'], $GLOBALS['TSFE']->id ); break;
			
			case 'code':
				$value = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlWhatToDisplay' ), $this->conf['code'], 'list' );
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
	 * This method generates a list of records. The records are recived from the parent cObject.
	 * 
	 * @param	String	$listType	
	 * @return	String	Parsed List
	 * @access	protected
	 */
	protected function makeList( $listType ) {
			//Make sure that a correct list type is set
		$arrListType = array( 'list', 'top', 'search', 'personal' );
		if( !in_array( $listType, $arrListType ) ) {
			$listType = 'list';
		}
		$lConf = $this->conf[($listType=='search'?'list':$listType).'View.'];

		//Prepare Configuration for the list
		$lConf['limit'] = $this->getConfigParameter( array( 'field' => 'xmlLimitCount', 'sheet' => 'sSettings' ), $lConf['limit'], 25 );		
		$lConf['alternatingLayouts'] = $this->getConfigParameter( false, $lConf['alternatingLayouts'], 2 );
		$lConf['categoryMode'] = $this->getConfigParameter( array( 'field' => 'xmlCategoriesShowWhat', 'sheet' => 'sDataSource' ), $lConf['categoryMode'], 0 );
		$lConf['categorySelection'] = $this->getConfigParameter( array( 'field' => 'xmlCategories', 'sheet' => 'sDataSource' ), false, false );
		
		
			//Prepare local helper variables
		$tmp_arrListSettings = array();
		$tmp_vFolderMarker = '';
		$tmp_GlobalMarkerArray = $this->getGlobalMarkerArray( $listType );

			//Add Type-specific marker
		switch( $listType ) {
			case 'top':
				$tmp_arrListSettings['sqlWhereClause'] = 'AND is_vip=\'1\' ';
				$lConf['altSubpartMarker'] = $this->getConfigParameter( false, $lConf['altSubpartMarker'], 'TEMPLATE_TOP' );
				$lConf['listOrderBy'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderBy' ), $lConf['listOrderBy'], 'crdate' );
				$lConf['listOrderDir'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderDirection' ), $lConf['listOrderDir'], '1' );
			break;
			
			case 'personal':
				$lConf['altSubpartMarker'] = $this->getConfigParameter( false, $lConf['altSubpartMarker'], 'TEMPLATE_PERSONAL' );
				$lConf['listOrderBy'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderBy' ), $lConf['listOrderBy'], 'tstamp' );
				$lConf['listOrderDir'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderDirection' ), $lConf['listOrderDir'], '1' );
			break;
			
			case 'search':
			case 'list':
				$lConf['altSubpartMarker'] = $this->getConfigParameter( false, $lConf['altSubpartMarker'], 'TEMPLATE_LIST' );
				$lConf['listOrderBy'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderBy' ), $lConf['listOrderBy'], 'title' );
				$lConf['listOrderDir'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderDirection' ), $lConf['listOrderDir'], '0' );
				$lConf['vFolderTreeInitialState'] = $this->getConfigParameter( array( 'sheet' => 'sVFolderTree', 'field' => 'ff_vFolderTreeInitialState' ), $lConf['vFolderTreeInitialState'], 'none' );
				$lConf['vFolderTreeEnable'] = $this->getConfigParameter( array( 'sheet' => 'sVFolderTree', 'field' => 'xmlShowVFolderTree' ), $lConf['vFolderTreeEnable'], false );
				
				#############################################################################################
				### vFolder related stuff                                                                 ###
				#############################################################################################
				if( $listType=='list' && intval( $lConf['vFolderTreeEnable'] ) ) {
					
					$tmp_vFolderMarker = $this->vFolderTree();
					
					//Check whether the folder given via URL is allowed for this Plugin, or not.
					$this->piVars['pid'] = intval( $this->piVars['pid'] );
					$pidList = explode( ',', $this->pi_getPidList( $this->conf['pidList'], $this->conf['recursive'] ) );
					
					//If we found a valid request, prepare the DB-Query according to it.
					if(  !empty( $this->piVars['pid'] ) && t3lib_div::inArray( $pidList, $this->piVars['pid'] ) ) {
						$tmp_arrListSettings['sqlWhereClause'] = 'AND pid=' . $this->piVars['pid'];
					} else {
						if( $lConf['vFolderTreeInitialState'] == 'none' ) {
							$tmp_arrListSettings['sqlWhereClause'] = 'AND 1=0';
						} elseif( $lConf['vFolderTreeInitialState'] == 'all' ) {
							$tmp_arrListSettings['sqlWhereClause'] = 'AND pid IN ( ' . implode( ',', $pidList ) . ' )';
						} elseif( $lConf['vFolderTreeInitialState'] == 'first' ) {
							$tmp_arrListSettings['sqlWhereClause'] = 'AND pid=' . $pidList[0] . '';
						}
					}
				}
				
			break;
		}
		
		
		#############################################################################################
		### Template-Related stuff                                                                ###
		#############################################################################################
		$tmpl = array();
		$tmpl['total'] = $this->cObj->fileResource( $this->getTemplateFile() );
		$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], '###'.$lConf['altSubpartMarker'].'###' );
		$tmpl['item'] = $this->getLayouts( $tmpl['total'], $lConf['alternatingLayouts'], 'BLOBITEM' );
		
		$markerArray = array();
		$subpartArray = array();
		$wrappedSubpartArray = array();
		
		
		#############################################################################################
		### Preparing Database Queries                                                            ###
		#############################################################################################
		if ( empty( $this->piVars['sort'] ) ) {
			$this->piVars['sort'] = $lConf['listOrderBy'].':'.$lConf['listOrderDir']; 
		}
		list( $this->internal['orderBy'], $this->internal['descFlag'] ) = explode( ':',$this->piVars['sort'] );
		$this->internal['orderByList'] = 'sorting,title,crdate,tstamp,cruser_id,blob_size,uid,download_count,blob_type';		
		$this->internal['results_at_a_time'] = $lConf['limit'];
		$this->pi_listFields = 'uid,pid,title,description,crdate,tstamp,sys_language_uid,cruser_id,blob_name,blob_size,blob_type,download_count,t3ver_label,blob_checksum';
		$tmp_arrListSettings['sqlWhereClause'] .= ' AND ( ' . $this->dbVars['table_content'] . '.sys_language_uid = 0 OR ' . $this->dbVars['table_content'] . '.sys_language_uid = (-1) )';

			//Show only [...] Categories (and/or)
		if( $lConf['categoryMode'] ) {
			$arrMM = array(
				'table' => $this->dbVars['table_categories'],
				'mmtable' => $this->dbVars['table_categories_mm']
			);

				//OR-Link
			if( $lConf['categoryMode'] == 1 ) {
				$arrMM['catUidList'] = $lConf['categorySelection'];

				//AND-Link
			} else if( $lConf['categoryMode'] == 2 ) {
				$arrMM['catUidList'] = $lConf['categorySelection'] . ' ) AND `tx_drblob_category_mm`.`uid_foreign` IN ( ';
				$tmpList = explode( ',', $lConf['categorySelection'] );
				for($i=0;$i<sizeof( $tmpList ); $i++ ) {
					$arrMM['catUidList'] .= $tmpList[$i] . 
						( ( sizeof( $tmpList )-$i > 1 ) ?  ' ) AND `tx_drblob_category_mm`.`uid_local` IN ( SELECT `tx_drblob_category_mm`.`uid_local` FROM ' . $arrMM['mmtable'] . ' WHERE `tx_drblob_category_mm`.`uid_foreign` IN ( ' : '' );
				}
				$arrMM['catUidList'] .= str_repeat( ' )', sizeof( $tmpList )-1 );
			}
		} else {
			$arrMM = null;
		}
		
		
		$rsltNumRows = $this->pi_exec_query( $this->dbVars['table_content'], true, $tmp_arrListSettings['sqlWhereClause'], $arrMM );
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
			#$queryParts = $this->pi_list_query( $this->dbVars['table_content'], 0, $tmp_arrListSettings['sqlWhereClause'], $arrMM, false, false, false, true );
				#Could place a hook here... write me if you'd need one
			#$rslt = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray( $queryParts );
			$rslt = $this->pi_exec_query( $this->dbVars['table_content'], 0, $tmp_arrListSettings['sqlWhereClause'], $arrMM );
			
			$count = 0;
			$arrItems = array();
			while( $this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) ) {
				if( $this->sys_language_uid != 0 ) {
					$this->internal['currentRow'] = $GLOBALS['TSFE']->sys_page->getRecordOverlay( $this->dbVars['table_content'], $this->internal['currentRow'], $this->sys_language_uid, '' );
						//If no translation exists...
					#if( !array_key_exists( '_LOCALIZED_UID', $this->internal['currentRow'] ) ) {
					#		//Check for records where langugage is set to '[ALL]'
					#	if( !$this->internal['currentRow']['sys_language_uid'] == '-1' ) {
					#		continue;
					#	}
					#}
				}
				
					//Prepare the marker array for the current record
				$rowMarkerArray = array();
				$rowMarkerArray = array_merge( 
					$this->getContentMarkerArray( $lConf ),
					$tmp_GlobalMarkerArray
				);

					//populate detail- and downloadlinks with contents
				$lConf['moreLink_stdWrap.']['typolink.']['parameter'] = $this->getNamedConfigParameter( 'singlePID' );
				$lConf['moreLink_stdWrap.']['typolink.']['additionalParams'] = $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',array( $this->prefixId => array( 'showUid' => $this->internal['currentRow']['uid'] ) ),'',1).$this->pi_moreParams;
				$LINK_ITEM = explode('|', $this->local_cObj->stdWrap( '|', $lConf['moreLink_stdWrap.']) );
				
				$lConf['downloadLink_stdWrap.']['typolink.']['useCacheHash'] = 0;
				$lConf['downloadLink_stdWrap.']['typolink.']['no_cache'] = 1;
				$lConf['downloadLink_stdWrap.']['typolink.']['parameter'] = $GLOBALS['TSFE']->id;
				$lConf['downloadLink_stdWrap.']['typolink.']['additionalParams'] = $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',array( $this->prefixId => array( 'downloadUid' => $this->internal['currentRow']['uid'] ) ),'',1).$this->pi_moreParams;
				$LINK_FILE = explode('|', $this->local_cObj->stdWrap( '|', $lConf['downloadLink_stdWrap.'] ) );

					//hide the download-link if no file is attached
				$blobUID = ( array_key_exists( '_LOCALIZED_UID', $this->internal['currentRow'] ) ? $this->internal['currentRow']['_LOCALIZED_UID'] : $this->internal['currentRow']['uid'] );
				if ( !$this->blobExists( $blobUID ) ) {
					$LINK_FILE = array( 0 => '' , 1=> '' );
					$rowMarkerArray['###BLOB_DOWNLOAD###'] = '';
					$rowMarkerArray['###LANG_DOWNLOAD###'] = '';
				} 
				
					//parse the current record
				$arrItems[] = $this->cObj->substituteMarkerArrayCached( 
					$tmpl['item'][$count%count( $tmpl['item'] )],
					$rowMarkerArray,
					array(),
					array(
						'###BLOB_LINK_ITEM###' => $LINK_ITEM,
						'###BLOB_LINK_FILE###' => $LINK_FILE
					)
				);

				$count++;
			}//End of while( $this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) )

			$markerArray = array_merge(
				$this->getGlobalMarkerArray( $listType ),
				$this->getSortMarkerArray()
			);
			$subpartArray['###CONTENT###'] = implode( '', $arrItems );
			
		} else {
				//no items were found --> use the according template marker
			$tmpl['total'] = $this->cObj->fileResource( $this->getNamedConfigParameter( 'templateFile' ) );
			$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], '###' . $lConf['altSubpartMarker'] . '_NOITEMS###' );
		}

			//parse the list.
			//Hide the 'no-records-found'-message if the searchfunction is enabled and no searchword is entered.
		if( !( $listType == 'search' && empty( $this->piVars['sword'] ) ) ) {
		
			$markerArray['###BLOB_VFOLDERTREE###'] = $tmp_vFolderMarker;
			
			$rtnVal = $this->cObj->substituteMarkerArrayCached(
				$tmpl['total'],
				array_merge(
					$tmp_GlobalMarkerArray,
					$markerArray
				),
				$subpartArray,
				$wrappedSubpartArray
			);
			
		} else {
			$rtnVal = null;
		}
		
		

		#############################################################################################
		### Append searchbox or Add2Favorites-Button                                              ###
		#############################################################################################
		if( $listType == 'search' ) {
			$rtnVal = $this->pi_list_searchBox() . $rtnVal;
		}
		
		if( $listType == 'list' && $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlAdd2Fav' ), $lConf['showAdd2Fav'] , false ) ) {
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

			#############################################################################################
			### Perform subscription / revocation of a subscription                                   ###
			#############################################################################################
			if ( $this->piVars['dr_blob']['action'] == 'add' ) {
				
				//Delete all items first...
				$rsltDelete = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
					'tx_drblob_personal',
					'uid_feusers = \'' . $GLOBALS['TSFE']->fe_user->user['uid'] . '\' AND ' .
						'uid_pages IN ( ' . $GLOBALS['TYPO3_DB']->cleanIntList( $this->piVars['dr_blob']['items'] ) . ' ) '
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
						'uid_pages IN ( ' . $GLOBALS['TYPO3_DB']->cleanIntList( $this->piVars['dr_blob']['items'] ) . ' ) '
				);
			}
			unset( $this->piVars['dr_blob'] );


			#############################################################################################
			### Render subscription form                                                              ###
			#############################################################################################
				//Subscribe all folders, or just the selected (Feature #5692)
			$mode = $this->getConfigParameter( array( 'field' => 'ff_vFolderTree_FolderSubscriptionMode', 'sheet' => 'sVFolderTree' ), $this->conf['listView.']['vFolderTree_FolderSubscriptionMode'], 'selected' );
			if( $mode != 'selected' || empty( $this->piVars['pid'] ) ) {
				$pidList = $this->pi_getPidList( $this->conf['pidList'], $this->conf['recursive'] );
			} else {
				$pidList = intval( $this->piVars['pid'] );
			}

			$markerArray = array(
				'###FORM_METHOD###' => 'post',
				'###FORM_TARGET###' => $this->pi_linkTP_keepPIvars_url( array( 'sort' => false ), false, false ),
				'###ACTION###' => '',
				'###ITEMS###' => $pidList,
				'###LANG_PERSADD###' => $this->pi_getLL( 'personal_button_add' ),
				'###LANG_PERSREMOVE###' => $this->pi_getLL( 'personal_button_remove' )
			);
			
				//Determines whether to add- or to remove entries
			$rsltStatus = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'count(*)',
				'tx_drblob_personal',
				'uid_feusers = \'' . $GLOBALS['TSFE']->fe_user->user['uid'] . '\' AND ' .
					'uid_pages IN ( ' . $pidList . ' ) '
			);
			$rowStatus = $GLOBALS['TYPO3_DB']->sql_fetch_row( $rsltStatus );
			if ( $rowStatus[0] != ( substr_count( $pidList, ',' ) + 1 ) ) {
				$markerArray['###ACTION###'] = 'add';
				$tmplSubpart = 'ADD';
			} else {
				$markerArray['###ACTION###'] = 'remove';
				$tmplSubpart = 'REMOVE';
			}
						
			return $this->cObj->substituteMarkerArrayCached( 
				$this->cObj->getSubpart( 
					$this->cObj->fileResource( 
						$this->getTemplateFile() 
					), 
					'###TEMPLATE_PERSONAL_' . $tmplSubpart . '_FOLDER###' 
				), 
				$markerArray
			);
			
		} else {
			return '';
		}
	}
	

	/**
	 * Generate the view for a single item
	 * 
	 * @return 	String $content
	 * @access 	private
	 */
	protected function vSingle() {

		#############################################################################################
		### Generate Back PID                                                                     ###
		#############################################################################################
		/* Return PID prio:
			1. URL-Param "backpid"
			2. Flexform-Value
			3. TS-Value
			4. $GLOBALS['TSFE']->id
		 */
		$tmp_returnPID = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlReturnPID' ), $this->conf['backPID'], $GLOBALS['TSFE']->id );
		if( intval( $this->piVars['backPID'] ) ) {
			$tmp_returnPID = intval( $this->piVars['backPID'] );
		}
			

		#############################################################################################
		### Template-Related stuff                                                                ###
		#############################################################################################
			$tmplSubpart = $this->conf['singleView.']['altSubpartMarker'] ? $this->conf['singleView.']['altSubpartMarker'] : 'TEMPLATE_SINGLE';
			$tmpl = $this->cObj->fileResource( $this->getNamedConfigParameter( 'templateFile' ) );
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
			
				//store the description in a register
			$this->local_cObj->LOAD_REGISTER(
				array( 'blobDescription' => strip_tags( $this->internal['currentRow']['description'] ) ), 
				''
			);
			
				//Generate Downloadlink
			$btnDownload = null;
			$blobUID =  ( $this->internal['currentRow']['_LOCALIZED_UID'] ? $this->internal['currentRow']['_LOCALIZED_UID'] : $this->internal['currentRow']['uid'] );
			if ( $this->blobExists( $blobUID ) ) {
				$this->conf['singleView.']['downloadLink_stdWrap.']['typolink.']['useCacheHash'] = 0;
				$this->conf['singleView.']['downloadLink_stdWrap.']['typolink.']['no_cache'] = 1;
				$this->conf['singleView.']['downloadLink_stdWrap.']['typolink.']['parameter'] = $this->getFieldContent( 'downloadPID' );
				$this->conf['singleView.']['downloadLink_stdWrap.']['typolink.']['additionalParams'] = $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',array( $this->prefixId => array( 'downloadUid' => $this->internal['currentRow']['uid'] ) ),'',1).$this->pi_moreParams;
				$LINK_FILE = explode('|', $this->local_cObj->stdWrap( '|', $this->conf['singleView.']['downloadLink_stdWrap.']) );
				$btnDownload = $this->pi_getLL('single_button_download');
			} else {
				$LINK_FILE = array( 0, 1 );
			}
			
				//Generate Backlink
			$this->conf['singleView.']['backLink_stdWrap.']['typolink.']['parameter'] = $tmp_returnPID;
			$LINK_BACK = explode('|', $this->local_cObj->stdWrap( '|', $this->conf['singleView.']['backLink_stdWrap.'] ) );

			return $this->cObj->substituteMarkerArrayCached( 
				$tmpl, 
				array_merge( 
					$this->getContentMarkerArray( $this->conf['singleView.'] ),
					$this->getGlobalMarkerArray( 'single' ),

						//Decaprecated stuff...
					array( 
						'###BLOB_SINGLE_RTN-URL###' => $this->pi_getPageLink( $returnPID ), 
						'###BLOB_DOWNLOAD_LINK###' => $LINK_FILE[0] . $btnDownload . $LINK_FILE[1],
						'###BLOB_DATA_EXISTS_SWITCH_START###' => ( $this->blobExists( $blobUID ) ? '' : ' <!-- ' ),
						'###BLOB_DATA_EXISTS_SWITCH_END###' => ( $this->blobExists( $blobUID ) ? '' : ' --!> ' )
					)
				),
				array(),
				array(
					'###BLOB_LINK_FILE###' => $LINK_FILE,
					'###BLOB_LINK_BACK###' => $LINK_BACK
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
	 * Overwrittes the pi-base-method <i>pi_list_searchBox</i> to add a search box that uses the template functionality
	 * 
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
				'###SWORDS###' => htmlspecialchars( $this->piVars['sword'] )
			) 
		);
		return $content;
	}


	/**
	 * This Method is used to load a record from the database and enable downloading it.
	 * Therefore it sends an HTTP-Header with blob_type as contentType
	 * 
	 * @see		RfC 2045, RfC 2046, RfC 2077 for Content Disposition
	 * @access 	protected
	 * 
	 * @internal IE6 SSL Bug: http://support.microsoft.com/default.aspx?scid=kb;EN-US;q297822
	 */
	public function vDownload( $sendHeaders = true, $uid=0 ) {
		$rowUID = ( $uid ? $uid : intval( $this->piVars['downloadUid'] ) );
	
		$this->internal['currentTable'] = $this->dbVars['table_content'];
		$this->internal['currentRow'] = $this->pi_getRecord( $this->dbVars['table_content'], $rowUID );
		
		if ( $this->sys_language_uid ) {
			$this->internal['currentRow'] = $GLOBALS['TSFE']->sys_page->getRecordOverlay( $this->dbVars['table_content'], $this->internal['currentRow'], $this->sys_language_uid );
			$lRowUID = $this->internal['currentRow']['_LOCALIZED_UID'];
		} else {
			$lRowUID = $this->internal['currentRow']['uid']; 
		}

			//prepare the array of information being sent with the header
		$blob = array(
			'blob_name' => urlencode( $this->getFieldContent( 'blob_name' ) ),
			'blob_size' => $this->getFieldContent( 'blob_size' ),
			'blob_data' => '',
			'blob_type' => $this->getFieldContent( 'blob_type' )
		);
		if( empty( $data['blob_type'] ) ) {
			$data['blob_type'] = 'text/plain';
		}
		
			//PostProcess the Filename for download
		if ( $this->conf['downloadFilenameUserFunc'] ) {
			$this->conf['downloadFilenameUserFunc.']['parentObj'] = &$this;
			$blob['blob_name'] = $GLOBALS['TSFE']->cObj->callUserFunction( 
				$this->conf['downloadFilenameUserFunc'], 
				$this->conf['downloadFilenameUserFunc.'], 
				$blob['blob_name']
			);
		}

			//Load Data
		if( $this->getFieldContent( 'type' ) == 1 ) {
			$blob['blob_data'] = $this->getFieldContent( 'blob_data' );
		} else {
			$file = tx_drblob_div::getStorageFolder() . $this->getFieldContent( 'blob_data' );
			$fp = fopen( $file, 'r' );
				$blob['blob_data'] = fread( $fp, filesize ( $file ) );
			fclose( $fp );
		}
		$blob['blob_data'] = stripslashes( $blob['blob_data'] );

				// Adds a hook for pre-processing the file to download
		if (is_array( $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_blob']['preProcessDownloadHook'] ) ) {
			foreach( $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_blob']['preProcessDownloadHook'] as $_classRef ) {
				$_procObj = & t3lib_div::getUserObj( $_classRef );
				$blob = $_procObj->downloadPreProcessor( $blob );
			}
		}
		
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
			if( ( $client['BROWSER'] == 'msie' ) && ( $client['VERSION'] == '6' || $client['VERSION'] == '7'  || $client['VERSION'] == '8' ) ) {
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
	 * Checks whether a binary object exists- or not
	 * 
	 * @param	Int		Uid of the record to check	
	 * @return 	Bool	Returns whether an file exists- or not
	 * @access	private
	 */
	private function blobExists( $item ) {
		
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

	
	/*
	 * 
	 * Content-Generating Methods
	 * 
	 */

	
	/**
	 * vFolderTree
	 *
	 * @return String	Rendered vFolder Tree
	 * @access protected
	 */
	/*protected*/ function vFolderTree() {

	if( !$this->getConfigParameter( array( 'sheet' => 'sVFolderTree', 'field' => 'xmlShowVFolderTree' ), $lConf['vFolderTreeEnable'], false ) ) {
			return null;
		} else {
			$treeClass = t3lib_div::makeInstance( 'tx_drblob_pi1_vFolderTree' );
			$treeClass->init( $this->conf, $this->cObj, $this->piVars );
			
			$treeClass->title = 'dr_blob vFolder Tree';
			$treeClass->expandAll = 1;
			$treeClass->expandFirst = 1;
			
			$treeContent = null;
			$treeContent .= $treeClass->getBrowsableTree();
			
			return $this->local_cObj->stdWrap( $treeContent, $this->conf['listView.']['vFolderTree_stdWrap.'] );
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
		$piVars = array();
		$piVars['sort'] = $fieldName . ':' . ( $this->internal['descFlag'] ? 0 : 1 );	
		if( $this->piVars['pid'] ) { $piVars['pid'] = intval( $this->piVars['pid'] ); }
		if( $this->piVars['sword'] ) { $piVars['sword'] = htmlspecialchars( $this->piVars['sword'] ); }
	
		return $this->pi_linkTP_keepPIvars( 
			$this->pi_getLL( 'list_field_'.$fieldName ),
			$piVars,
			0,
			$GLOBALS['TSFE']->id
		);
	}
	

	/**
	 * getCategories
	 * Returns an array with record categories
	 * 
	 * @param	Int		UID of the record
	 * @return	Array	Array with categories
	 * @access	private
	 */
	/*private*/function getCategories( $item ) {
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
	 * Returns the fileextension of the given Filename.
	 * 
	 * @param 	String 	$filename
	 * @return 	String 	Extension
	 * @access 	protected
	 */
	/*protected*/function getFileExtension( $fileName ) {
		if ( !empty( $fileName ) ) {
			$tmp = t3lib_div::split_fileref( $fileName );
			return $tmp['realFileext'];
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
	 * This method returns an array of parsed content markers; 
	 * The Field-Values are preprocessed with their TypoScript configurations
	 * 
	 * @param  TypoScript for this mode
	 * @return Array	Array containing the parsed marker
	 * @access protected 
	 */
	protected function getContentMarkerArray( $lConf ) {
		
		$downloadCount = sprintf( $this->pi_getLL( 'list_field_downloadcount_wrap' ), $this->getFieldContent('download_count') );
		$lConf['email_stdWrap.']['typolink.']['parameter'] = $this->getFieldContent('author_email');
		
		$tmp['cat'] = $this->getCategories( $this->getFieldContent('uid') );
		if( !empty( $tmp['cat'] ) ) {
			$tmp['lstCat'] = implode( ( $lConf['categoryDivider'] ? $lConf['categoryDivider'] : ',' ), $tmp['cat'] );
			$arrMarker['###BLOB_CATEGORIES###'] = $this->local_cObj->stdWrap( $tmp['lstCat'], $lConf['category_stdWrap.'] );
		}
			
		$row = $this->internal['currentRow'];
		$row['blob_filext'] = $this->getFileExtension( $row['blob_name'] );

		$this->local_cObj->start($row, 'tx_drblob_content');
		
		$arrContentMarker = array();	
		$arrContentMarker['###BLOB_UID###']				= $row['uid'];
		$arrContentMarker['###BLOB_TITLE###'] 			= $this->local_cObj->stdWrap( $row['title'], $lConf['title_stdWrap.'] );
		$arrContentMarker['###BLOB_DESCRIPTION###'] 	= $this->local_cObj->stdWrap( $this->pi_RTEcssText( $row['description'] ), $lConf['description_stdWrap.'] );
		$arrContentMarker['###BLOB_AUTHOR###'] 			= $this->local_cObj->stdWrap( $this->getFieldContent('author'), $lConf['author_stdWrap.'] );
		$arrContentMarker['###BLOB_AUTHOR_EMAIL###'] 	= $this->local_cObj->stdWrap( $tmpVars['email'], $lConf['email_stdWrap.'] );
		$arrContentMarker['###BLOB_CRDATE###'] 			= $this->local_cObj->stdWrap( $row['crdate'], $lConf['date_stdWrap.'] );
		$arrContentMarker['###BLOB_LASTCHANGE###'] 		= $this->local_cObj->stdWrap( $row['tstamp'], $lConf['date_stdWrap.'] );
		$arrContentMarker['###BLOB_VERSION###'] 		= $this->local_cObj->stdWrap( $row['t3ver_label'], $lConf['version_stdWrap.'] );
		##$arrContentMarker['###BLOB_LANGUAGE###'] 		= $this->local_cObj->stdWrap( $row['sys_language_uid'], $lConf['language_stdWrap.'] );
		$arrContentMarker['###BLOB_AGE###'] 			= $this->local_cObj->stdWrap( $row['crdate'], $lConf['age_stdWrap.'] );
		$arrContentMarker['###BLOB_DOWNLOADCOUNT###'] 	= $this->local_cObj->stdWrap( $downloadCount, $lConf['downloadcount_stdWrap.'] );
		$arrContentMarker['###BLOB_CHECKSUM###'] 		= $this->local_cObj->stdWrap( $row['blob_checksum'], $lConf['filechecksum_stdWrap.'] );
		$arrContentMarker['###BLOB_FILENAME###'] 		= $this->local_cObj->stdWrap( $row['blob_name'], $lConf['filename_stdWrap.'] );
		$arrContentMarker['###BLOB_FILESIZE###'] 		= $this->local_cObj->stdWrap( $row['blob_size'], $lConf['filesize_stdWrap.'] );
		$arrContentMarker['###BLOB_FILETYPE###'] 		= $this->local_cObj->stdWrap( $row['blob_type'], $lConf['filetype_stdWrap.'] );
		$arrContentMarker['###BLOB_FILEICON###'] 		= $this->local_cObj->stdWrap( $row['blob_filext'], $lConf['fileicon_stdWrap.'] );
		$arrContentMarker['###BLOB_CATEGORIES###'] 		= $this->local_cObj->stdWrap( $tmp['lstCat'], $lConf['category_stdWrap.'] );			

			// Adds a hook for processing additional content markers
		if (is_array( $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_blob']['additionalContentMarkerHook'] ) ) {
			foreach( $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_blob']['additionalContentMarkerHook'] as $_classRef ) {
				$_procObj = & t3lib_div::getUserObj( $_classRef );
				$arrContentMarker = $_procObj->additionalContentMarkerProcessor( $arrContentMarker, $lConf, $this );
			}
		}
		
		return $arrContentMarker;
	}
	
	
	/**
	 * This method returns an array of global markers such as the language markers
	 *
	 * @param String $mode to be used
	 * @return Array
	 * @access private
	 */
	private function getGlobalMarkerArray( $mode ) {
		$mode = ($mode!='search' ? $mode : 'list');
		
		$arrLanguageMarker = array();
		$arrLanguageMarker['###LANG_UID###'] 			= $this->pi_getLL( 'field_uid' );
		$arrLanguageMarker['###LANG_TITLE###'] 			= $this->pi_getLL( 'list_field_title' );
		$arrLanguageMarker['###LANG_DESCRIPTION###'] 	= $this->pi_getLL( 'list_field_description' );
		$arrLanguageMarker['###LANG_FILENAME###'] 		= $this->pi_getLL( 'list_field_blob_name' );
		$arrLanguageMarker['###LANG_FILESIZE###'] 		= $this->pi_getLL( 'list_field_blob_size' );
		$arrLanguageMarker['###LANG_FILETYPE###'] 		= $this->pi_getLL( 'list_field_blob_type' );
		$arrLanguageMarker['###LANG_CHECKSUM###'] 		= $this->pi_getLL( 'list_field_blob_checksum' );
		$arrLanguageMarker['###LANG_CRDATE###'] 		= $this->pi_getLL( 'list_field_crdate' );
		$arrLanguageMarker['###LANG_CATEGORIES###'] 	= $this->pi_getLL( 'list_field_categories' );
		$arrLanguageMarker['###LANG_LASTCHANGE###'] 	= $this->pi_getLL( 'list_field_tstamp' );
		$arrLanguageMarker['###LANG_VERSION###'] 		= $this->pi_getLL( 'list_field_version' );
		$arrLanguageMarker['###LANG_AGE###'] 			= $this->pi_getLL( 'list_field_age' );
		$arrLanguageMarker['###LANG_AUTHOR###'] 		= $this->pi_getLL( 'list_field_author' );
		$arrLanguageMarker['###LANG_AUTHOR_EMAIL###'] 	= $this->pi_getLL( 'list_field_author_mail' );
		$arrLanguageMarker['###LANG_VFOLDERTREE###'] 	= $this->pi_getLL( 'list_vfoldertree' );
		$arrLanguageMarker['###LANG_NOITEMS###'] 		= $this->pi_getLL( 'noRecordsFound' );
		$arrLanguageMarker['###LANG_DOWNLOADCOUNT###'] 	= $this->pi_getLL( 'list_field_downloadcount' );
		$arrLanguageMarker['###LANG_MORE###'] 			= $this->pi_getLL( $mode .'_button_show' );
		$arrLanguageMarker['###LANG_DOWNLOAD###'] 		= $this->pi_getLL( $mode.'_button_download' );
		$arrLanguageMarker['###LANG_BACK###'] 			= $this->pi_getLL( 'single_button_back' );

				// Adds a hook for processing additional content markers
		if (is_array( $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_blob']['additionalGlobalMarkerHook'] ) ) {
			foreach( $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_blob']['additionalGlobalMarkerHook'] as $_classRef ) {
				$_procObj = & t3lib_div::getUserObj( $_classRef );
				$arrContentMarker = $_procObj->additionalGlobalMarkerProcessor( $arrLanguageMarker, $mode, $this );
			}
		}
		
			//Deprecated from version 2.2.0
		$arrLanguageMarker['###BLOB_MORE###'] = $this->pi_getLL( $mode .'_button_show' );
		$arrLanguageMarker['###BLOB_DOWNLOAD###'] = $this->pi_getLL( $mode.'_button_download' );
		
		return $arrLanguageMarker;
	}
	
	
	/**
	 * This method returns the an array of parsed sortlink-marker
	 *
	 * @return Array
	 * @access private
	 */
	private function getSortMarkerArray() {
		$arrSortMarker = array();
		$arrSortMarker['###BLOB_SORTLINK_UID###'] = $this->getFieldHeader_sortLink('uid');
		$arrSortMarker['###BLOB_SORTLINK_TITLE###'] = $this->getFieldHeader_sortLink('title');
		$arrSortMarker['###BLOB_SORTLINK_CRDATE###'] = $this->getFieldHeader_sortLink('crdate');
		$arrSortMarker['###BLOB_SORTLINK_TSTAMP###'] = $this->getFieldHeader_sortLink('tstamp');
		$arrSortMarker['###BLOB_SORTLINK_LASTCHANGE###'] = $this->getFieldHeader_sortLink('tstamp');
		$arrSortMarker['###BLOB_SORTLINK_FILESIZE###'] = $this->getFieldHeader_sortLink('blob_size');
		$arrSortMarker['###BLOB_SORTLINK_DOWNLOADCOUNT###'] = $this->getFieldHeader_sortLink('downloadcount');
		$arrSortMarker['###BLOB_SORTLINK_FILETYPE###'] = $this->getFieldHeader_sortLink('blob_type');
		$arrSortMarker['###BLOB_SORTLINK_AUTHOR###'] = $this->getFieldHeader_sortLink('cruser_id');

			// Adds a hook for processing additional content markers
		if (is_array( $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_blob']['additionalSortMarkerHook'] ) ) {
			foreach( $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_blob']['additionalSortMarkerHook'] as $_classRef ) {
				$_procObj = & t3lib_div::getUserObj( $_classRef );
				$arrContentMarker = $_procObj->additionalSortMarkerProcessor( $arrSortMarker, $mode, $this );
			}
		}

		return $arrSortMarker;
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
