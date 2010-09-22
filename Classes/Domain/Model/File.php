<?php
class Tx_DrBlob_Domain_Model_File extends Tx_Extbase_DomainObject_AbstractEntity {
	
	/**
	 * @var string
	 */
	protected $title = null;
	
	/**
	 * @var int
	 */
	protected $createTS = 0;
	
	/**
	 * @var int
	 */
	protected $modifyTS = 0;
	
	/**
	 * @var string
	 */
	protected $version = null;
	
	/**
	 * @var array<Tx_DrBlob_Domain_Model_Category>
	 */
	protected $categories = array();
	
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
	 * @var int
	 */
	protected $type = 0;
	
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
	protected $blobType = null;
	
	/**
	 * @var string
	 */
	protected $blobChecksum = null;
	
	/**
	 * @return bool
	 */	
	public function hasWorkload() {
		return $this->getFileSize() ? true : false;
	}
	
	/**
	 * @return bool
	 */	
	public function getHasWorkload() {
		return $this->hasWorkload();
	}
	
	/**
	 * @return int
	 */
	public function getRecordType() {
		return $this->type;
	}
	
	/**
	 * @return DateTime
	 */
	public function getCreateDate() {
		return new DateTime( date( 'Y-m-d', $this->createTS ) );
	}
	
	/**
	 * @return DateTime
	 */
	public function getChangeDate() {
		return new DateTime( date( 'Y-m-d', $this->modifyTS ) );
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
	 * @TODO this method only returns the first image
	 */
	public function getFirstImage() {
		if( $this->images ) {
			return 'uploads/pics/' . ( is_array( $this->images ) ? $this->images[0] : $this->images );
		}
		return null;
	}
	
	/**
	 * Returns the Category-Objects assigned to this record
	 * @return array<Tx_DrBlob_Domain_Model_Category>
	 */
	public function getCategories() {
		return $this->categories;
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
	
	public function incrementDownloadCounter() {
		$this->downloadCount++;
	}
}
?>