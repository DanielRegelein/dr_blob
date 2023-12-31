<?php
/*                                                                        *
 * This script belongs to the TYPO3 extension "dr_blob".                  *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Controller for the File records
 *
 * @author Daniel Regelein <daniel.regelein@diehl-informatik.de>
 * @package TYPO3
 * @subpackage dr_blob
 */
class Tx_DrBlob_Controller_FileController extends Tx_Extbase_MVC_Controller_ActionController {
	
	/**
	 * @var Tx_DrBlob_Domain_Repository_FileRepository
	 */
	protected $fileRepository = null;
	
	/**
	 * Lenght of the filecontent to stream into the output buffer
	 */
	const BUFFER_LEN = 8192;
	
	/**
	 * Initialize Action, automaticlly called by the dispatcher
	 */
	public function initializeAction() {
		$this->fileRepository = t3lib_div::makeInstance( 'Tx_DrBlob_Domain_Repository_FileRepository' );
	}
	
	/**
	 * List-View Mode
	 * 
	 * @param string $sort Field to use for sorting (will be validated in the repository)
	 * @param int $pointer Pointer for the Page browser
	 */
	public function indexAction( $sort = null, $pointer = 0 ) {
		if( $this->settings['code'] == 'recordinsert' ) {
			$cObjArr = $this->configurationManager->getContentObject()->data;
			$this->forward( 'details', null, null, array( 'file' => $cObjArr['uid'] ) );
		}
		
		$filter = t3lib_div::makeInstance( 'Tx_DrBlob_Domain_Model_Filter' );

		$filter->setLimit( $this->settings['limit'] );
		$filter->setPointer( $pointer );
		$filter->setOrderBy( ( $sort ? $sort : $this->settings['orderBy'] ), $this->settings['orderDir'] );
		
		if( !empty( $this->settings['categorySelection'] ) ) {
			$filter->setCategorySelection( 
				Tx_Extbase_Utility_Arrays::integerExplode( 
					',', 
					$this->settings['categorySelection']
				), 
				$this->settings['categoryMode'] 
			);
		}
		
		if( !empty( $this->settings['templateFile'] ) ) {
			$this->view->setTemplatePathAndFilename( $this->settings['templateFile'] );
		}
		
		switch( $this->settings['code'] ) {
			case 'top': 
				$filelist = $this->fileRepository->findVipRecords( $filter );
				$numRows = $this->fileRepository->countVipRecords( $filter );
				$template = 'Top';
			break;
			case 'personal':
				if ( $GLOBALS['TSFE']->loginUser ) {
					$filelist = $this->fileRepository->findSubscribedRecords( $filter );
					$numRows = $this->fileRepository->countSubscribedRecords( $filter );
					$template = 'Personal';
				} else {
					return '';
				}
			break;
			case 'list':
			default:
				$this->request->setArgument( 'isFolderSubscribed', $this->isFolderSubscribed() );
				$filelist = $this->fileRepository->findAllByFilter( $filter );
				$numRows = $this->fileRepository->countAllByFilter( $filter );
				$template = 'Index';
			break;
		}
		
		$this->view->assign( 'files', $filelist );
		$this->view->assign( 'files_count', $numRows );

		return $this->view->render( $template );
	}

	/**
	 * Method to show the details of a given file
	 *
	 * @param Tx_DrBlob_Domain_Model_FileInterface $file
	 */
	public function detailsAction( Tx_DrBlob_Domain_Model_FileInterface $file ) {
		if( $this->settings['code'] != 'recordinsert' ) {
				//substitute Pagetitle [should be part of the view, ot of the controller...]
			if( (bool)$this->settings['substitutePagetitle'] == true ) {
				$GLOBALS['TSFE']->page['title'] = $file->getTitle();	
			}
	
				//substitute Indextitle
			if( (bool)$this->settings['substituteIndextitle'] == true ) {
				$GLOBALS['TSFE']->indexedDocTitle = $file->getTitle();
			}
		}
		
		$this->view->assign( 'file', $file );
	}
	
	/**
	 * Enter description here...
	 */
	public function manageSubscriptionAction() {
		if ( $GLOBALS['TSFE']->loginUser ) {
	
			$storagePids = $this->getStoragePidForThisPlugin();
			
				//the return value has to be "cached" because the first step is 
				//to delete the exisiting subscription for this folder
			$isAlreadySubscribed = $this->isFolderSubscribed();
	
				//Delete all items first...
			$rsltDelete = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
				'tx_drblob_personal',
				'uid_feusers = \'' . $GLOBALS['TSFE']->fe_user->user['uid'] . '\' AND ' .
					'uid_pages IN ( ' . $GLOBALS['TYPO3_DB']->cleanIntList( $storagePids ) . ' ) '
			);
		
				//folder has not been subscribed till now, so we can asume 
				//a "subscribe" button has been clicked.
				//otherwise it would have been an "unsubscribe"-button
			if( !$isAlreadySubscribed ) {
				$arrItems = Tx_Extbase_Utility_Arrays::trimExplode( ',', $storagePids );
				for( $i=0; $i < count($arrItems); $i++ ) {
					$rsltInsert= $GLOBALS['TYPO3_DB']->exec_INSERTquery(
						'tx_drblob_personal',
						array(
							'uid_feusers' => $GLOBALS['TSFE']->fe_user->user['uid'],
							'uid_pages' => $arrItems[$i]
						)
					);
				}
			}
		}
		$this->forward( 'index' );
	}
	
	public function isFolderSubscribed() {
		return false;
	}
	
	/**
	 * The method is called when dr_blob sees an incoming download request.
	 * Therefore it uses manipulates the http-header sent.
	 * The exact type- and amount of header sent out depends on the record type
	 * This method is also used to increment the download counter
	 *
	 * @param Tx_DrBlob_Domain_Model_FileInterface $file
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
	 * 
	 * @internal IE6 SSL Bug: http://support.microsoft.com/default.aspx?scid=kb;EN-US;q297822
	 * 
	 * @see		RfC 2045, RfC 2046, RfC 2077 for Content Disposition
	 */
	public function downloadAction( Tx_DrBlob_Domain_Model_FileInterface $file ) {
		$this->response->setStatus( 100 );
		
		if( !$file->hasWorkload() ) { $this->response->setStatus( 400 ); }
		if( $insufficent_rights = false ) { $this->response->setStatus( 401 ); }
		if( $file_exists = false ) { $this->response->setStatus( 404 ); }
		if( $deleted_in_T3 = false ) { $this->response->setStatus( 410 ); }
		if( !$this->validateResponseMimeType( $file->getFileMimeType() ) ) { $this->response->setStatus( 415 ); }
		
		if( $this->response->getStatus() == '100 Continue' ) {

			$this->response->setStatus( 200 );
			
				//Post-Load the record workload
			$record = $this->fileRepository->getFileWorkload( $file->getUid() );
			
			switch( $file->getRecordType() ) {
				case Tx_DrBlob_Domain_Model_FileInterface::RECORD_TYPE_FILESYSTEM_SECURE: 
					$fileReference = Tx_DrBlob_Div::getStorageFolder() . $record['blob_data'];
					
						//asume the file to be quoted --> no streaming possible
					if( empty( $record['blob_checksum'] ) || $record['blob_checksum'] != Tx_DrBlob_Div::calculateFileChecksum( $fileReference ) ) {
						$fp = fopen( $fileReference, 'r' );
							$record['blob_data'] = fread( $fp, filesize ( $fileReference ) );
						fclose( $fp );
						$record['blob_data'] = stripslashes( $record['blob_data'] );
						$record['is_quoted'] = true;
					} else {
						$record['is_quoted'] = false;
						$record['blob_data'] = $fileReference;
					}
				break;
				
				case Tx_DrBlob_Domain_Model_FileInterface::RECORD_TYPE_DATABASE: 
					$record['blob_data'] = stripslashes( $record['blob_data'] );
					$record['is_quoted'] = true;
				break;
			}
			
				//Increment the download counter
			$file->incrementDownloadCounter();
			$this->fileRepository->update( $file );

				//Perform download action
			if( $file->getRecordType() == Tx_DrBlob_Domain_Model_FileInterface::RECORD_TYPE_FILESYSTEM_UNSECURE ) {
				$this->redirectToURI(  t3lib_div::locationHeaderUrl( Tx_DrBlob_Div::getUploadFolder() . 'storage/' . $record['blob_data'] ) );
			} else {
			
					//content related header
				$this->response->setHeader( 'Content-Type', $file->getFileMimeType(), true );
				$this->response->setHeader( 'Content-Length', $file->getFileSize(), true );
				$this->response->setHeader( 'Content-Transfer-Encoding', 'binary', true );
				$this->response->setHeader( 'Content-Disposition', ( (bool)$this->settings['tryToOpenFileInline'] ? 'attachment' : 'inline' ) . '; filename=' . $file->getFileName(), true );
				
					//caching related header
				$this->response->setHeader( 'Expires', gmdate( 'D, d M Y H:i:s', ( time()-3600 ) . ' GMT' ), true );
				$this->response->setHeader( 'Last-Modified', gmdate( 'D, d M Y H:i:s', ( time()-3600 ) . ' GMT' ), true );
				$this->response->setHeader( 'Cache-Control', 'post-check=0, pre-check=0', true );
				$this->response->setHeader( 'Pragma', 'no-cache', true );
				
				$client = t3lib_div::clientInfo();
				if( ( $client['BROWSER'] == 'msie' ) && ( $client['VERSION'] == '6' || $client['VERSION'] == '7'  || $client['VERSION'] == '8' ) ) {
					$this->response->setHeader( 'Pragma', 'anytextexeptno-cache', true );					
				}

					//Send out the headers
				$this->response->send();
				
					//if we run into a type=3-record, a redirect-header is already sent out, 
					//so the following lines won't be processed
					//if not, the file is either send to the client in one piece, or streamed in 
					//several pieces. The method used depends on whether the file is quoted- or not.
					//Quoted files cannot be streamed.
				if( $record['is_quoted'] ) {
					echo $record['blob_data'];
				} else {
					if( file_exists( $record['blob_data'] ) ) {
						$fp = fopen( $record['blob_data'], 'r' );
						while ( !feof( $fp ) ) {
							echo fread( $fp, self::BUFFER_LEN );
						}
						fclose( $fp );
					}
				}

					//Call the persistence manager to store the updated download counter
					//It won't be called by the Dispatcher because the render process is 
					//killed at the end of this method
				$persitMgr = Tx_Extbase_Dispatcher::getPersistenceManager();
				$persitMgr->persistAll();
				
					//prevend TYPO3 from rendering the page
				exit;
			}
		}
	}
	
	/**
	 * This method validates the response mime type of the file
	 * against to request mime type the client sent to the server
	 *
	 * @param string $mimeType The response mime type 
	 * @return bool whether the response mime type matches
	 */	
	private function validateResponseMimeType( $mimeType ) {
		$tmpRespMimeType = explode( '/', $mimeType );
		if( sizeof( $tmpRespMimeType ) == 2 ) {
			
				//Fill in the response content type to check
			$respMimeTypes = array(
				$tmpRespMimeType[0] . '/' . $tmpRespMimeType[1],
				$tmpRespMimeType[0] . '/*',
				'*/*'
			);
			
				//Fill in the request content type
			$tmpReqMimeType = explode( ',', $_SERVER['HTTP_ACCEPT'] );
			$reqMimeTypes = array();
			for( $i=0; $i < sizeof( $tmpReqMimeType ); $i++ ) {
				$reqMimeTypes[] = strrpos( $tmpReqMimeType[$i], ';' ) ? substr( $tmpReqMimeType[$i], 0, strrpos( $tmpReqMimeType[$i], ';' ) ) : $tmpReqMimeType[$i];
			}
			
				//validate the response against the request content type
			for( $i=0; $i < sizeof( $respMimeTypes ); $i++ ) {
				if( in_array( $respMimeTypes[$i], $reqMimeTypes ) ) {
					return true; 
				}
			}
			fclose( $fp );
		}
		return false;
	}
	
	
	private function getStoragePidForThisPlugin() {
		$frameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		return $frameworkConfiguration['persistence']['storagePid'];
	}
}
?>