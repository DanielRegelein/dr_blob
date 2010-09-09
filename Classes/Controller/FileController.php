<?php
class Tx_DrBlob_Controller_FileController extends Tx_Extbase_MVC_Controller_ActionController {
	
	/**
	 * @var Tx_DrBlob_Domain_Repository_FileRepository
	 */
	protected $fileRepository = null;
	
	public function initializeAction() {
		$this->fileRepository = t3lib_div::makeInstance( 'Tx_DrBlob_Domain_Repository_FileRepository' );
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $sort Field to use for sorting
	 */
	public function indexAction( $sort = null) {
		$this->fileRepository->qryParams['orderBy'] = $sort;
		
		switch( $this->setings['code'] ) {
			case 'top':  		$filelist = $this->fileRepository->findVipRecords(); 		break;
			case 'personal':  	$filelist = $this->fileRepository->findSubscribedRecords(); break;
			case 'list':
			default: 			$filelist = $this->fileRepository->findAll(); 				break;
		}
		
		$this->view->assign( 'files', $filelist );
	}

	/**
	 * Method to show the details of a given file
	 *
	 * @param Tx_DrBlob_Domain_Model_File $file
	 */
	public function detailsAction( Tx_DrBlob_Domain_Model_File $file ) {
		$this->view->assign( 'file', $file );
	}
	
	/**
	 * Method to download the given file
	 * Downloading should be done by manipulating the response content - and content type
	 *
	 * @param Tx_DrBlob_Domain_Model_File $file
	 */
	public function downloadAction( Tx_DrBlob_Domain_Model_File $file ) {
		$this->response->setStatus( 100 );
		
		if( $file == false ) { $this->response->setStatus( 400 ); }
		if( $insufficent_rights = false ) { $this->response->setStatus( 401 ); }
		if( $file_exists = false ) { $this->response->setStatus( 404 ); }
		if( $deleted_in_T3 = false ) { $this->response->setStatus( 410 ); }
		if( $unsupportedMediaType = false ) { $this->response->setStatus( 415 ); }
		
		if( $this->response->getStatus() == '100 Continue' ) {
			$this->response->setStatus( 200 );
			
				//content related header
			$this->response->setHeader( 'Content-Type', $file->getFileMimeType(), true );
			$this->response->setHeader( 'Content-Length', $file->getFileSize(), true );
			$this->response->setHeader( 'Content-Transfer-Encoding', 'binary', true );
			$this->response->setHeader( 'Content-Disposition', 'attachment; filename=' . $file->getFileName(), true );
				
				//caching related header
			$this->response->setHeader( 'Expires', gmdate( 'D, d M Y H:i:s', ( time()-3600 ) . ' GMT' ) );
			$this->response->setHeader( 'Last-Modified', gmdate( 'D, d M Y H:i:s', ( time()-3600 ) . ' GMT' ) );
			$this->response->setHeader( 'Cache-Control', 'post-check=0, pre-check=0' );
			$this->response->setHeader( 'Pragma', 'no-cache' );
			
			#$this->response->setContent( $file->getTitle() );
		}

		#$this->response->send();
	
		exit;
	}
	
}
?>