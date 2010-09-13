<?php
class Tx_DrBlob_Domain_Model_Category extends Tx_Extbase_DomainObject_AbstractEntity {
	
	/**
	 * @var string
	 */
	protected $title = null;
	
	public function getTitle() {
		return $this->title;
	}
}
?>