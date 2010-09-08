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
require_once( t3lib_extMgm::extPath( 'dr_blob' ) . '/Classes/Div.php' );


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
 * @package 	TYPO3
 * @subpackage  dr_blob
 * @filesource 	EXT:dr_blob/Classes/Pi1.php
 * @version 	2.4.0
 */
class Tx_DrBlob_Pi1 extends tslib_pibase {

	/**
	 * @var		String	$prefixId
	 * @var		String 	$scriptRelPath
	 * @var		String  $extKey
	 * 
	 * Variables used by the piBase
	 * @access 	protected
	 */
	/*protected*/	var $prefixId = 'tx_drblob_pi1';
	/*protected*/	var $scriptRelPath = 'Resources/Private/Language/locallang.xml';
	/*protected*/	var $extKey = 'dr_blob';
	
	
	/**
	 * @var		Array	$dbVars
	 * @access	private
	 * @deprecated
	 */
	private $dbVars = array( 'table_categories' => 'tx_drblob_category', 'table_categories_mm' => 'tx_drblob_category_mm', 'table_personal' );
	
	/**
	 * @var 	Array $searchFields
	 * Sets the fields that are used by the inbuild search function.
	 * 
	 * @access 	private
	 */
	protected $searchFields = array( 'title', 'description', 'blob_name', 't3ver_label', 'author', 'author_email' );
	
	
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
	private $local_cObj;
	
	
	/**
	 * @var 	Array
	 * This array contains the configuration parameters defined in the Flexform
	 */
	private $config = array();
	
	

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
		switch( $cmd = $this->config['code'] ) {
			case 'dummy':
				return '';
			break;
			
			case 'single':
				if ( strstr( $this->cObj->currentRecord, Tx_DrBlob_Div::CONTENT_TABLE ) ) {
					$this->piVars['showUid'] = $this->cObj->data['uid'];
					$this->config['isRecordInsert'] = true;
				}
				$content = $this->singleView();
			break;
			
			case 'personal':
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

						$content = $this->makeList( 'personal' );
					}
				} else {
					$content = '<!-- not logged in -->';
				}
			break;

			case 'list':
			default:
				if ( strstr( $this->cObj->currentRecord, 'tt_content' ) ) {
					$ffPidList = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlPages', 'sDataSource' );
					$ffRecursive = $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlRecursive', 'sDataSource' );
					$this->conf['pidList'] = ( $ffPidList ? $ffPidList : $this->conf['pidList'] ); 
					$this->conf['recursive'] = ( $ffRecursive ? $ffRecursive : $this->conf['recursive'] );
				} else if ( strstr( $this->cObj->currentRecord, Tx_DrBlob_Div::CONTENT_TABLE ) ) {
					
				}
				$content = $this->makeList( $cmd );
		}
		
		return $this->cObj->stdWrap( $content, $this->conf['stdWrap.'] );
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
		$this->local_cObj = t3lib_div::makeInstance( 'tslib_cObj' );
		
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
		### Extract Flexform Variables                                                            ###
		#############################################################################################

		$tmpFFvars = array(
			'pidList' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlPages', 'sDataSource' ),
			'recursive' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlRecursive', 'sDataSource' ),
			'categoryMode' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlCategoriesShowWhat', 'sDataSource' ),
			'categorySelection' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlCategories', 'sDataSource' ),
			'code' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlWhatToDisplay', 'sSettings' ),
			'templateFile' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlTemplate', 'sSettings' ),
			'altSubpartMarker' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'ff_altSubpartMarker', 'sSettings' ),
			'singlePID' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlSinglePID', 'sSettings' ),
			'backPID' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlReturnPID', 'sSettings' ),
			'listOrderBy' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlListOrderBy', 'sSettings' ),
			'listOrderDir' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlListOrderDirection', 'sSettings' ),
			'limit' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlLimitCount', 'sSettings' ),
			'usePageBrowser' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'ff_usePageBrowser', 'sSettings' ),
			'showAdd2Fav' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlAdd2Fav', 'sSettings' ),
			'vFolderTreeEnable' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'xmlShowVFolderTree', 'sVFolderTree' ),
			'vFolderTreeInitialState' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'ff_vFolderTreeInitialState', 'sVFolderTree' ),
			'vFolderTree_FolderSubscriptionMode' => $this->pi_getFFvalue( $this->cObj->data['pi_flexform'], 'ff_vFolderTree_FolderSubscriptionMode', 'sVFolderTree' ),
		);
		
		$this->config['isRecordInsert'] = false;
		
		
		$this->config['code'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlWhatToDisplay' ), $this->conf['code'], 'list' );
		if( $this->config['code'] == 'personal_list' ) {
			$this->config['code'] = 'personal';
		}
		if ( $this->piVars['showUid'] && $this->config['code'] != 'dummy' ) {
			$this->config['code'] = 'single';
		}
		
		$this->config['templateFile'] 		= Tx_DrBlob_Div::getPrioParam( ( $tmpFFvars['templateFile'] ? Tx_DrBlob_Div::getUploadFolder() . $tmpFFvars['templateFile'] : null ), $this->conf['templateFile'], 'EXT:dr_blob/Resources/Private/ClassicTemplates/dr_blob_v2.tmpl' );
		$this->config['altSubpartMarker'] 	= Tx_DrBlob_Div::getPrioParam( $tmpFFvars['altSubpartMarker'], $this->conf[$this->config['code'].'View.']['altSubpartMarker'], false );
		$this->config['usePageBrowser'] 	= Tx_DrBlob_Div::getPrioParam( $tmpFFvars['usePageBrowser'], $this->conf[$this->config['code'].'View.']['usePageBrowser'], false );
		$this->config['vFolderTreeEnable'] 	= Tx_DrBlob_Div::getPrioParam( $tmpFFvars['vFolderTreeEnable'], $this->conf['listView.']['vFolderTreeEnable'], false );
		$this->config['backPID'] 			= Tx_DrBlob_Div::getPrioParam( $tmpFFvars['backPID'], null, null );
		$this->config['singlePID'] 			= Tx_DrBlob_Div::getPrioParam( $tmpFFvars['singlePID'], $this->conf['singlePID'], $GLOBALS['TSFE']->id );		
		$this->config['downloadPID']		= intval( $this->conf['downloadPID'] ) ? $this->conf['downloadPID'] : $GLOBALS['TSFE']->id;

		$this->config['showAdd2Fav'] = $tmpFFvars['showAdd2Fav'];
		if( empty( $this->config['showAdd2Fav'] ) || $this->config['showAdd2Fav'] == 'ts' ) { 
			$this->config['showAdd2Fav'] = ( $this->conf['listView.']['showAdd2Fav'] == '1' ? true : false ); 
		} else {
			$this->config['showAdd2Fav'] = ( $tmpFFvars['showAdd2Fav'] == '1' ? true : false );
		}
		if( $this->config['code'] != 'list' ) {
			$this->config['showAdd2Fav'] = false;
		}
		$this->config['renderAsXML'] = ( ( empty( $this->conf['renderAsXML'] ) || ( $this->conf['renderAsXML'] != '1' ) ) ? false : true );
		

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
		if( !empty( $ffValue ) && ( $ffValue != 'ts' ) ) {
			return $ffValue;
		}
		if( !empty( $tsData ) ) {
			return $tsData;
		}
		return $defaultValue;
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
				$lConf['altSubpartMarker'] = ( $this->config['altSubpartMarker'] ? $this->config['altSubpartMarker'] : 'TEMPLATE_TOP');
				$lConf['listOrderBy'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderBy' ), $lConf['listOrderBy'], 'crdate' );
				$lConf['listOrderDir'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderDirection' ), $lConf['listOrderDir'], '1' );
			break;
			
			case 'personal':
				$lConf['altSubpartMarker'] = ( $this->config['altSubpartMarker'] ? $this->config['altSubpartMarker'] : 'TEMPLATE_PERSONAL');
				$lConf['listOrderBy'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderBy' ), $lConf['listOrderBy'], 'tstamp' );
				$lConf['listOrderDir'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderDirection' ), $lConf['listOrderDir'], '1' );
			break;
			
			case 'search':
			case 'list':
				$lConf['altSubpartMarker'] = ( $this->config['altSubpartMarker'] ? $this->config['altSubpartMarker'] : 'TEMPLATE_LIST');
				$lConf['listOrderBy'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderBy' ), $lConf['listOrderBy'], 'title' );
				$lConf['listOrderDir'] = $this->getConfigParameter( array( 'sheet' => 'sSettings', 'field' => 'xmlListOrderDirection' ), $lConf['listOrderDir'], '0' );
				$lConf['vFolderTreeInitialState'] = $this->getConfigParameter( array( 'sheet' => 'sVFolderTree', 'field' => 'ff_vFolderTreeInitialState' ), $lConf['vFolderTreeInitialState'], 'none' );
				
				#############################################################################################
				### vFolder related stuff                                                                 ###
				#############################################################################################
				if( $listType=='list' && $this->config['vFolderTreeEnable'] ) {
					
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
		$tmpl['total'] = $this->cObj->fileResource( $this->config['templateFile'] );
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
		$this->internal['orderByList'] = 'sorting,title,crdate,tstamp,blob_size,uid,download_count,blob_type,author,author_email';		
		$this->internal['results_at_a_time'] = $lConf['limit'];
		$this->pi_listFields = 'uid,pid,title,description,images,crdate,tstamp,sys_language_uid,author,author_email,blob_name,blob_size,blob_type,download_count,t3ver_label,blob_checksum';
		$tmp_arrListSettings['sqlWhereClause'] .= ' AND ( ' . Tx_DrBlob_Div::CONTENT_TABLE . '.sys_language_uid = 0 OR ' . Tx_DrBlob_Div::CONTENT_TABLE . '.sys_language_uid = (-1) )';

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
		
		
		$rsltNumRows = $this->pi_exec_query( Tx_DrBlob_Div::CONTENT_TABLE, true, $tmp_arrListSettings['sqlWhereClause'], $arrMM, 'uid' );
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
			#$queryParts = $this->pi_list_query( Tx_DrBlob_Div::CONTENT_TABLE, 0, $tmp_arrListSettings['sqlWhereClause'], $arrMM, false, false, false, true );
				#Could place a hook here... write me if you'd need one
			#$rslt = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray( $queryParts );
			$rslt = $this->pi_exec_query( Tx_DrBlob_Div::CONTENT_TABLE, 0, $tmp_arrListSettings['sqlWhereClause'], $arrMM, 'uid' );
			
			$count = 0;
			$arrItems = array();
			while( $this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) ) {
				if( $this->sys_language_uid != 0 ) {
					$this->internal['currentRow'] = $GLOBALS['TSFE']->sys_page->getRecordOverlay( Tx_DrBlob_Div::CONTENT_TABLE, $this->internal['currentRow'], $this->sys_language_uid, '' );
				}
				
					//Prepare the marker array for the current record
				$rowMarkerArray = array();
				$rowMarkerArray = array_merge( 
					$this->getContentMarkerArray( $lConf ),
					$tmp_GlobalMarkerArray
				);

					//populate detail- and downloadlinks with contents
				$lConf['moreLink_stdWrap.']['typolink.']['parameter'] = $this->config['singlePID'];
				$lConf['moreLink_stdWrap.']['typolink.']['additionalParams'] = 
					$this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl(
						'',
						array( 
							$this->prefixId => array( 
								'showUid' => $this->internal['currentRow']['uid'],
								'backPid' => ( $this->config['backPID'] ? $this->config['backPID'] : '' ) 
							) 
						),
						'',
						1
					).$this->pi_moreParams;
				$LINK_ITEM = explode( '|', $this->local_cObj->stdWrap( '|', $lConf['moreLink_stdWrap.'] ) );
				$rowMarkerArray['###BLOB_URL_ITEM###'] = $this->local_cObj->lastTypoLinkUrl;
				
					//hide the download-link if no file is attached
				$blobUID = ( array_key_exists( '_LOCALIZED_UID', $this->internal['currentRow'] ) ? $this->internal['currentRow']['_LOCALIZED_UID'] : $this->internal['currentRow']['uid'] );
				$LINK_FILE = $this->generateDownloadLink( $blobUID, $lConf['downloadLink_stdWrap.'] );
				$rowMarkerArray['###BLOB_URL_FILE###'] = $this->local_cObj->lastTypoLinkUrl;
				
					//If the list if rendered as XML (f.e. for RSS feeds) add additional marker
				if( $this->config['renderAsXML'] ) {
					$rowMarkerArray['###BLOB_URL_ITEM###'] = '###SITE_LINK###'.htmlspecialchars( $rowMarkerArray['###BLOB_URL_ITEM###'] );
					$rowMarkerArray['###BLOB_URL_FILE###'] = '###SITE_LINK###'.htmlspecialchars( $rowMarkerArray['###BLOB_URL_FILE###'] );
				}

					//unset the file marker if no file is attached
				if ( !$this->blobExists( $blobUID ) ) {
					$rowMarkerArray['###LANG_DOWNLOAD###'] = '';
					$rowMarkerArray['###BLOB_DOWNLOAD###'] = '';
					$rowMarkerArray['###BLOB_URL_FILE###'] = '';
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
				$this->getGlobalMarkerArray( $listType, false ),
				$this->getSortMarkerArray()
			);
			$markerArray['###BLOB_PAGEBROWSER###'] = '';
			if( $this->config['usePageBrowser'] ) {
				$markerArray['###BLOB_PAGEBROWSER###'] = $this->pi_list_browseresults();
			}
			$subpartArray['###CONTENT###'] = implode( '', $arrItems );
			
		} else {
				//no items were found --> use the according template marker
			$tmpl['total'] = $this->cObj->fileResource( $this->config['templateFile'] );
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
						$this->config['templateFile'] 
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
	protected function singleView() {

		#############################################################################################
		### Generate Back PID                                                                     ###
		#############################################################################################
		/* Return PID prio:
			1. URL-Param "backpid"
			2. Flexform-Value
			3. TS-Value
			4. $GLOBALS['TSFE']->id
		 */
		if( !$this->config['isRecordInsert'] ) {
			if( intval( $this->piVars['backPid'] ) ) {
				$tmp_returnPID = intval( $this->piVars['backPid'] );
			} else {
				$tmp_returnPID = $this->config['backPID'] ? $this->config['backPID'] : ( $this->conf['backPID'] ? $this->conf['backPID'] : $GLOBALS['TSFE']->id );
			}
		}
			

		#############################################################################################
		### Template-Related stuff                                                                ###
		#############################################################################################
			$tmplSubpart = $this->config['altSubpartMarker'] ? $this->config['altSubpartMarker'] : 'TEMPLATE_SINGLE';
			$tmpl['total'] = $this->cObj->fileResource( $this->config['templateFile'] );
			$tmpl['total'] = $this->cObj->getSubpart( $tmpl['total'], '###'.$tmplSubpart.'###' );
			$tmpl['fileexists'] = $this->cObj->getSubpart( $tmpl['total'], '###'.$tmplSubpart.'###' );

		
		if ( !empty( $this->piVars['showUid'] ) ) {
     		$this->pi_listFields = 'uid,pid,title,description,images,crdate,tstamp,sys_language_uid,author,author_email,blob_name,blob_size,blob_type,download_count,t3ver_label,blob_checksum';
			$this->internal['currentTable'] = Tx_DrBlob_Div::CONTENT_TABLE;
			$this->internal['currentRow'] = $this->pi_getRecord( Tx_DrBlob_Div::CONTENT_TABLE, intval( $this->piVars['showUid'] ) );
			
			
				//Fetch the translated version if exists
			if ( $this->sys_language_uid != 0 ) {
				$this->internal['currentRow'] = $GLOBALS['TSFE']->sys_page->getRecordOverlay( Tx_DrBlob_Div::CONTENT_TABLE, $this->internal['currentRow'], $this->sys_language_uid );
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
				array( 
					'blobDescription' => strip_tags( $this->internal['currentRow']['description'] ), 
					'blobAuthor' => $this->internal['currentRow']['author'],
					'blobAuthorEmail' => $this->internal['currentRow']['author_email']
				),
				''
			);
			
				//Generate Downloadlink
			$blobUID =  ( $this->internal['currentRow']['_LOCALIZED_UID'] ? $this->internal['currentRow']['_LOCALIZED_UID'] : $this->internal['currentRow']['uid'] );
			$LINK_FILE = $this->generateDownloadLink( $blobUID, $this->conf['singleView.']['downloadLink_stdWrap.'] );
			
				//Generate Backlink
			$this->conf['singleView.']['backLink_stdWrap.']['typolink.']['parameter'] = $tmp_returnPID;
			$LINK_BACK = explode('|', $this->local_cObj->stdWrap( '|', $this->conf['singleView.']['backLink_stdWrap.'] ) );

			
			return $this->cObj->substituteMarkerArrayCached( 
				$tmpl['total'], 
				array_merge( 
					$this->getContentMarkerArray( $this->conf['singleView.'] ),
					$this->getGlobalMarkerArray( 'single', $this->blobExists( $blobUID ) ),
						//Decaprecated stuff...
					array( 
						'###BLOB_SINGLE_RTN-URL###' => $this->pi_getPageLink( $returnPID ), 
						'###BLOB_DOWNLOAD_LINK###' => $LINK_FILE[0] . $this->pi_getLL('single_button_download') . $LINK_FILE[1],
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
	function pi_getRecord($table,$uid,$checkPage=0,$listFields=null) {
		if( $table == Tx_DrBlob_Div::CONTENT_TABLE ) {
			global $TCA;
			if( empty( $listFields ) ) {
				$listFields = $this->pi_listFields;
			}
			$uid = intval( $uid );
			if ( is_array( $TCA[$table] ) ) {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $listFields, $table, 'uid='.intval($uid).$GLOBALS['TSFE']->sys_page->enableFields($table));
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $res );
				$GLOBALS['TYPO3_DB']->sql_free_result( $res );
				if ( $row ) {
					$GLOBALS['TSFE']->sys_page->versionOL($table,$row);
					if (is_array($row))	{
						if ($checkPage)	{
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery( 'uid', 'pages', 'uid='.intval($row['pid']).$GLOBALS['TSFE']->sys_page->enableFields('pages'));
							$numRows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
							$GLOBALS['TYPO3_DB']->sql_free_result($res);
							if ($numRows>0)	{
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
		} else {
			return parent::pi_getRecord( $table, $uid, $checkPage );
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

		$tmplFile = $this->config['templateFile'];
		$tmpl = $this->cObj->fileResource( $tmplFile );
		$tmpl = $this->cObj->getSubpart( $tmpl, '###TEMPLATE_SEARCH###' );

		$content = $this->cObj->substituteMarkerArrayCached( 
			$tmpl, 
			array(
				'###FORM_URL###' => htmlspecialchars( t3lib_div::getIndpEnv( 'REQUEST_URI' ) ),
				'###SEARCH_BUTTON###' => $this->pi_getLL( 'search_button_search' ),
				'###LANG_SEARCH###' => $this->pi_getLL( 'search_button_search' ),
				'###SWORDS###' => htmlspecialchars( $this->piVars['sword'] )
			) 
		);
		return $content;
	}


	/**
	 * The method is called when dr_blob sees an incoming download request.
	 * Therefore it uses manipulates the http-header sent.
	 * The exact type- and amount of header sent out depends on the record type
	 * This method is also used to increment the download counter
	 * 
	 * @internal
	 * 		type=1		The file is decoded and this method controls the download procedure. Therefore the file's content
	 * 					is written to the PHP output buffer in one big piece
	 * 		type=2		The file is either decodes and handled like a type=1-record (which was the old behaviour), or
	 * 					it is sliced into pieces and streamed to the client 
	 * 		type=3		The file is downloaded from a unsecure directory underneath the TYPO3 document root directory
	 * 					Unlike for the other types this download is handled by the webserver, not inside this method.
	 * @internal API spots:	
	 * 		The hook preProcessDownloadHook
	 * 		The TS API call downloadFilenameUserFunc
	 * 
	 * @internal IE6 SSL Bug: http://support.microsoft.com/default.aspx?scid=kb;EN-US;q297822
	 *  
	 * @see		RfC 2045, RfC 2046, RfC 2077 for Content Disposition
	 * @access 	protected
	 */
	public function vDownload( $sendHeaders = true, $uid=0 ) {

		$rowUID = ( $uid ? $uid : intval( $this->piVars['downloadUid'] ) );

		if( empty( $rowUID ) ) {
			$GLOBALS['TSFE']->pageNotFoundAndExit( 'The requested file does not exist!' );
		}
		
		$this->internal['currentTable'] = Tx_DrBlob_Div::CONTENT_TABLE;
		$this->internal['currentRow'] = $this->pi_getRecord( Tx_DrBlob_Div::CONTENT_TABLE, $rowUID );
		
			//Check whether the file exists (deleted != 1)
		if( !empty( $this->internal['currentRow'] ) ) {
			
			if ( $this->sys_language_uid ) {
				$this->internal['currentRow'] = $GLOBALS['TSFE']->sys_page->getRecordOverlay( Tx_DrBlob_Div::CONTENT_TABLE, $this->internal['currentRow'], $this->sys_language_uid );
				$lRowUID = $this->internal['currentRow']['_LOCALIZED_UID'];
			} else {
				$lRowUID = $this->internal['currentRow']['uid']; 
			}
			
			
				//prepare the array of information being sent with the header
			$blob = array(
				'blob_name' => urlencode( $this->getFieldContent( 'blob_name' ) ),
				'blob_size' => $this->getFieldContent( 'blob_size' ),
				'blob_checksum' => $this->getFieldContent( 'blob_checksum' ),
				'blob_data' => '',
				'blob_type' => $this->getFieldContent( 'blob_type' ),
				'type' => $this->getFieldContent( 'type' ),
				'is_quoted' => false
			);
			if( empty( $data['blob_type'] ) ) {
				$data['blob_type'] = 'text/plain';
			}
	
			
				//Update counter only if we're having a download (and not a indexed_search-request)
			if( $sendHeaders == true ) {
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					Tx_DrBlob_Div::CONTENT_TABLE,
					'uid= \'' . $lRowUID . '\'',
					array(
						'download_count' => ( $this->getFieldContent( 'download_count' ) + 1 )
					)
				);
			}		
			
				//Preprocess the content depending on the type of the record
			switch( $this->getFieldContent( 'type' ) ) {
				case '3': 
					$blob['blob_data'] = Tx_DrBlob_Div::getUploadFolder() . 'storage/' . $this->getFieldContent( 'blob_data' );
					$blob['is_quoted'] = false;
				break;
				
				case '2': 
					$file = Tx_DrBlob_Div::getStorageFolder() . $this->getFieldContent( 'blob_data' );
					
						//asume the file to be quoted --> no streaming possible
					if( $blob['blob_checksum'] != Tx_DrBlob_Div::calculateFileChecksum( $file ) ) {
						$fp = fopen( $file, 'r' );
							$blob['blob_data'] = fread( $fp, filesize ( $file ) );
						fclose( $fp );
						$blob['blob_data'] = stripslashes( $blob['blob_data'] );
						$blob['is_quoted'] = true;
					} else {
						$blob['is_quoted'] = false;
						$blob['blob_data'] = $file;
					}
				break;
				
				case '1': 
					$blob['blob_data'] = $this->getFieldContent( 'blob_data' );
					$blob['blob_data'] = stripslashes( $blob['blob_data'] );
					$blob['is_quoted'] = true;
				break;
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
			
	
				// Adds a hook for pre-processing the file to download
			if (is_array( $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_blob']['preProcessDownloadHook'] ) ) {
				foreach( $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_blob']['preProcessDownloadHook'] as $_classRef ) {
					$_procObj = & t3lib_div::getUserObj( $_classRef );
					$blob = $_procObj->downloadPreProcessor( $blob );
				}
			}
	
			
				//send a redirect header if a type=3-record is found. Then the webserver will manage the download
				//The user will be redirected to the requested file.
				//Otherwise the content streamed to the browser
			$headerList = array();
			if( $this->getFieldContent( 'type' ) == 3 ) {
				$headerList['Location'] = t3lib_div::locationHeaderUrl( $blob['blob_data'] );
			} else {
				$contentDisposition = 'attachment';
				if( !empty( $this->conf['tryToOpenFileInline'] ) && (bool)$this->conf['tryToOpenFileInline'] == true ) {
					$contentDisposition = 'inline';
				}
				
					//caching related header
				$headerList['Expires'] = gmdate( 'D, d M Y H:i:s', ( time()-3600 ) . ' GMT' );
				$headerList['Last-Modified'] = gmdate( 'D, d M Y H:i:s', ( time()-3600 ) . ' GMT' );
				$headerList['Cache-Control'] = 'post-check=0, pre-check=0';
				$headerList['Pragma'] = 'no-cache';
				
					//content related header
				$headerList['Content-Type'] = $blob['blob_type'];
				$headerList['Content-Length'] = $blob['blob_size'];
				$headerList['Content-Transfer-Encoding'] = 'binary';
				$headerList['Content-Disposition'] = $contentDisposition.'; filename='. $blob['blob_name'];
				
				if( false ) {
					$headerList['Etag'] = $blob['blob_checksum'];
					$headerList['Last-Modified'] = gmdate( 'D, d M Y H:i:s', ( $this->getFieldContent( 'tstamp' ) ) . ' GMT' );
					$headerList['Cache-Control'] = 'public';
					$headerList['Pragma'] = 'public';
					$headerList['Expires'] = '0';
					
						//downloads continueable
					$headerList['Accept-Ranges'] = 'bytes';
				}
				
				$client = t3lib_div::clientInfo();
				if( ( $client['BROWSER'] == 'msie' ) && ( $client['VERSION'] == '6' || $client['VERSION'] == '7'  || $client['VERSION'] == '8' ) ) {
					$headerList['Pragma'] = 'anytextexeptno-cache';
				}
			}
			
			
				//we got a download request --> send out headers, stream file and kill the process afterwards
			if( $sendHeaders == true ) {
				
				foreach( $headerList as $key=>$value ) {
					header( $key . ': ' . $value, true );
				}
				
					//if we run into a type=3-record, a redirect-header is already sent out, 
					//so the following lines won't be processed
					//if not, the file is either send to the client in one piece, or streamed in 
					//several pieces. The method used depends on whether the file is quoted- or not.
					//Quoted files cannot be streamed.
				if( $blob['is_quoted'] ) {
					echo $blob['blob_data'];
				} else {
					if( file_exists( $blob['blob_data'] ) ) {
						$fp = fopen( $blob['blob_data'], 'r' );
						while ( !feof( $fp ) ) {
							echo fread( $fp, 1024*8 );
						}
						fclose( $fp );
					}
				}
				
					//Kill the process --> avoid TYPO3 from rendering the page
				exit();
	
			} else {
					//Return Filecontent to the indexer
				return $blob;
			}
		
		} else {
			//404
			$GLOBALS['TSFE']->pageNotFoundAndExit( 'The requested file does not exist!' );
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
		intval( $item );
		if( $item ) {
			$rslt = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'COUNT(*)',
				Tx_DrBlob_Div::CONTENT_TABLE,
				'uid=' . $item . ' AND blob_data != \'\''
			);
			list( $recCnt ) = $GLOBALS['TYPO3_DB']->sql_fetch_row( $rslt );
			
			if ( $recCnt == 1 ) {
				return true;
			}
		}
		return false;
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
	protected function vFolderTree() {
		require_once( t3lib_extMgm::extPath( 'dr_blob' ) . '/Classes/FolderTree.php' );
	
		if( !$this->config['vFolderTreeEnable'] ) {
			return null;
		} else {
			$treeClass = t3lib_div::makeInstance( 'tx_DrBlob_FolderTree' );
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
	 * @access	protected
	 */
	protected function getCategories( $item ) {
		$rslt = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			$this->dbVars['table_categories'].'.title',
			Tx_DrBlob_Div::CONTENT_TABLE,
			$this->dbVars['table_categories_mm'],
			$this->dbVars['table_categories'],
			' AND ' . Tx_DrBlob_Div::CONTENT_TABLE . '.uid=' . $item .
			$this->cObj->enableFields( $this->dbVars['table_categories'] )
		);
		$arrCat = array();
		while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rslt ) ) {
			$arrCat[] = $row['title'];
		}
		return $arrCat;
	}

	
	/**
	 * This method generates a (download-)link to a file
	 * @param Integer $item	Record to generate the link for
	 * @param Array $linkStdWrap The stdWrap-Array for the download link
	 * @return Array
	 * @access private
	 */
	private function generateDownloadLink( $item, $linkStdWrap ) {
		if ( $this->blobExists( $item ) ) {
			$linkStdWrap['typolink.']['useCacheHash'] = 0;
			$linkStdWrap['typolink.']['no_cache'] = 1;
			$linkStdWrap['typolink.']['parameter'] = $this->config['downloadPID'];
			$linkStdWrap['typolink.']['additionalParams'] = $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',array( $this->prefixId => array( 'downloadUid' => $item ) ),'',1).$this->pi_moreParams;
			$LINK_FILE = explode('|', $this->local_cObj->stdWrap( '|', $linkStdWrap ) );
		} else {
			$LINK_FILE = array( 0 => '', 1 => '' );
		}
		return $LINK_FILE;
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
		$temp = preg_replace( '/\#\#\#TIMES\#\#\#/', '%s', $this->pi_getLL( 'list_field_downloadcount_wrap' ), 1 );
		$downloadCount = sprintf( $temp, $this->getFieldContent('download_count') );
		
		$tmp['cat'] = $this->getCategories( $this->getFieldContent('uid') );
		if( !empty( $tmp['cat'] ) ) {
			$tmp['lstCat'] = implode( ( $lConf['categoryDivider'] ? $lConf['categoryDivider'] : ',' ), $tmp['cat'] );
			$arrMarker['###BLOB_CATEGORIES###'] = $this->local_cObj->stdWrap( $tmp['lstCat'], $lConf['category_stdWrap.'] );
		}
			
		$row = $this->internal['currentRow'];
		$row['blob_filext'] = Tx_DrBlob_Div::getFileExtension( $row['blob_name'] );

		$this->local_cObj->start( $row, Tx_DrBlob_Div::CONTENT_TABLE );
		
		$arrContentMarker = array();	
		$arrContentMarker['###BLOB_UID###']				= $row['uid'];
		$arrContentMarker['###BLOB_TITLE###'] 			= $this->local_cObj->stdWrap( $row['title'], $lConf['title_stdWrap.'] );
		$arrContentMarker['###BLOB_DESCRIPTION###'] 	= $this->local_cObj->stdWrap( $this->pi_RTEcssText( $row['description'] ), $lConf['description_stdWrap.'] );
		$arrContentMarker['###BLOB_AUTHOR###'] 			= $this->local_cObj->stdWrap( $row['author'], $lConf['author_stdWrap.'] );
		$arrContentMarker['###BLOB_AUTHOR_EMAIL###'] 	= $this->local_cObj->stdWrap( $row['author_email'], $lConf['email_stdWrap.'] );
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
		$arrContentMarker['###BLOB_IMAGES###'] 			= $this->local_cObj->stdWrap( $row['images'], $lConf['images_stdWrap.'] );
		$arrContentMarker['###BLOB_ISFILEATTACHED###']	= $this->local_cObj->stdWrap( (int)$this->blobExists( $row['uid'] ), $lConf['isFileAttached_stdWrap.'] );
		#die( '###BLOB_PREVIEW### autogeneration mit einem IMAGE-Object, target=blobfile' );
		
		
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
	 * @param Bool Is true is a file is attached
	 * @return Array
	 * @access protected
	 */
	protected function getGlobalMarkerArray( $mode, $fileExists=true ) {
		$mode = ( $mode!='search' ? $mode : 'list' );
		
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
		$arrLanguageMarker['###LANG_IMAGES###'] 		= $this->pi_getLL( 'list_field_images' );
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
		$arrLanguageMarker['###LANG_TOPHEADER###'] 		= $this->pi_getLL( 'top_header' );
		$arrLanguageMarker['###LANG_LISTHEADER###'] 	= $this->pi_getLL( 'list_header' );
		$arrLanguageMarker['###LANG_PERSONALHEADER###'] = $this->pi_getLL( 'personal_header' );
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

		if( !$fileExists ) {
			$arrLanguageMarker['###BLOB_DOWNLOAD###'] = '';
			$arrLanguageMarker['###LANG_DOWNLOAD###'] = '';
		}
		
		return $arrLanguageMarker;
	}
	
	
	/**
	 * This method returns the an array of parsed sortlink-marker
	 *
	 * @return Array
	 * @access protected
	 */
	protected function getSortMarkerArray() {
		$arrSortMarker = array();
		$arrSortMarker['###BLOB_SORTLINK_UID###'] = $this->getFieldHeader_sortLink('uid');
		$arrSortMarker['###BLOB_SORTLINK_TITLE###'] = $this->getFieldHeader_sortLink('title');
		$arrSortMarker['###BLOB_SORTLINK_CRDATE###'] = $this->getFieldHeader_sortLink('crdate');
		$arrSortMarker['###BLOB_SORTLINK_TSTAMP###'] = $this->getFieldHeader_sortLink('tstamp');
		$arrSortMarker['###BLOB_SORTLINK_LASTCHANGE###'] = $this->getFieldHeader_sortLink('tstamp');
		$arrSortMarker['###BLOB_SORTLINK_FILESIZE###'] = $this->getFieldHeader_sortLink('blob_size');
		$arrSortMarker['###BLOB_SORTLINK_DOWNLOADCOUNT###'] = $this->getFieldHeader_sortLink('downloadcount');
		$arrSortMarker['###BLOB_SORTLINK_FILETYPE###'] = $this->getFieldHeader_sortLink('blob_type');
		$arrSortMarker['###BLOB_SORTLINK_AUTHOR###'] = $this->getFieldHeader_sortLink('author');
		$arrSortMarker['###BLOB_SORTLINK_AUTHOR_EMAIL###'] = $this->getFieldHeader_sortLink('author_email');

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
	 * @access	private
	 */
	private function getLayouts($templateCode, $alternatingLayouts, $marker ) {
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
	
};


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Classes/Pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Classes/Pi1.php']);
}
?>