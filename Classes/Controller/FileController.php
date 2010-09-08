<?php
class Tx_DrBlob_Controller_FileController extends Tx_Extbase_MVC_Controller_ActionController {
	
	/**
	 * @var Tx_DrBlob_Domain_Repository_FileRepository
	 */
	protected $fileRepository = null;
	
	public function initializeAction() {
		$this->fileRepository = t3lib_div::makeInstance( 'Tx_DrBlob_Domain_Repository_FileRepository' );
	}
	
	public function indexAction() {
		$this->view->assign( 'files', $this->fileRepository->findAll() );
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
		
		$this->response->setHeader( 'Content-Type', 'text/plain', true );
		$this->response->setHeader( 'Content-Length', strlen( $file->getTitle() ), true );
		$this->response->setHeader( 'Content-Transfer-Encoding', 'binary', true );
		$this->response->setHeader( 'Content-Disposition', 'attachment; filename=test.txt', true );
		
		$this->response->setContent( $file->getTitle() );
		
		$this->response->send();

		exit;
	}
	
}
?>