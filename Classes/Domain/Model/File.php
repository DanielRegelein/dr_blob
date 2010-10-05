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
 * Domain object "File"
 *
 * @author Daniel Regelein <daniel.regelein@diehl-informatik.de>
 * @package TYPO3
 * @subpackage dr_blob
 */
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
	 * Returns an array containing the path to the images attacted to this record (if any) 
	 * @return array<string>
	 */
	public function getImages() {
		$tmpImgArr = array();
		if( $this->images ) {
			$tmpImgArr = explode( ',', $this->images );
			
			for( $i = 0; $i < sizeof( $tmpImgArr ); $i++ ) {
				$tmpImgArr[$i] = 'uploads/pics/' . $tmpImgArr[$i]; 
			}
		}
		return $tmpImgArr;
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
	
	/**
	 * This method increments the download counter
	 * It is called when downloading a file
	 */
	public function incrementDownloadCounter() {
		$this->downloadCount++;
	}
}
?>