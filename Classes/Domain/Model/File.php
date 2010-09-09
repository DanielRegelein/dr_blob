<?php
class Tx_DrBlob_Domain_Model_File extends Tx_Extbase_DomainObject_AbstractEntity {
	
	/**
	 * @var string
	 */
	protected $title = null;
	
	/**
	 * @var string
	 */
	protected $version = null;
	
	/**
	 * @var string
	 */
	protected $description = null;
	
	/**
	 * @var string
	 */
	protected $images = null;
	
	/**
	 * @var string
	 */
	protected $author = null;
	
	/**
	 * @var string
	 */
	protected $authorEmail = null;
	
	/**
	 * @var array<Tx_DrBlob_Domain_Model_Category>
	 */
	protected $category = null;
	
	/**
	 * @var int
	 */
	protected $downloadCount = 0;
	
	/**
	 * @var string
	 */
	protected $blobName = null;
	
	/**
	 * @var int
	 */
	protected $blobSize = 0;
	
	/**
	 * @var string
	 */
	protected $blobSype = null;
	
	/**
	 * @var string
	 */
	protected $blobChecksum = null;
	
	/**
	 * @return bool
	 */	
	public function hasWorkload() {
		return false;
	}
	
	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}
	
	/**
	 * @return string
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * @return string
	 */
	public function getAuthorEmail() {
		return $this->authorEmail;
	}
	
	/**
	 * @return int
	 */
	public function getDownloadCount() {
		return $this->downloadCount;
	}
	
	/**
	 * @return string
	 */
	public function getFileName() {
		return $this->blobName;
	}
	
	/**
	 * @return string
	 */
	public function getFileSize() {
		return $this->blobSize;
	}
	
	/**
	 * @return string
	 */
	public function getFileChecksum() {
		return $this->blobChecksum;
	}
	
	/**
	 * @return string
	 */
	public function getFileMimeType() {
		return $this->blobType;
	}
	
	/**
	 * @return string
	 */
	public function getFileExtension() {
		if( $this->getFileName() ) {
			return Tx_DrBlob_Div::getFileExtension( $this->getFileName() );
		} else {
			return null;
		}
	}
}
?>