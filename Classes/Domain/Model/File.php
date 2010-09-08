<?php
class Tx_DrBlob_Domain_Model_File extends Tx_Extbase_DomainObject_AbstractEntity {
	
	/**
	 * @var string
	 */
	protected $title = null;
	
	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	
	
}
?>