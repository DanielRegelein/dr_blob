<?php
class Tx_DrBlob_Domain_Model_Category extends Tx_Extbase_DomainObject_AbstractEntity {
	
	/**
	 * @var string
	 */
	protected $title = null;
	
	/**
	 * Returns this category's title
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
}
?>