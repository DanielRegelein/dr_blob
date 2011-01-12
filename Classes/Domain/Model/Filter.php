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
 * Domain object "Filter"
 *
 * @author Daniel Regelein <daniel.regelein@diehl-informatik.de>
 * @package TYPO3
 * @subpackage dr_blob
 */
class Tx_DrBlob_Domain_Model_Filter extends Tx_Extbase_DomainObject_AbstractValueObject {
	
	protected $pointer = 0;
	protected $limit = 999;
	protected $orderByField = 'title';
	protected $orderByDirection = Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING;
	protected $categorySelection = array();
	protected $categoryCombinationMode = 0;
	const allowedOrderByFields = 'sorting,title,crdate,tstamp,blob_size,uid,download_count,blob_type,author,author_email,t3ver_label';
	
	/**
	 * Returns the limit of records a list consists of
	 * @return integer
	 * @see Tx_DrBlob_Domain_Model_Filter::setLimit
	 */
	public function getLimit() {
		return $this->limit; 
	}

	/**
	 * Defines the amount of records a list consists of
	 * @param integer $limit
	 * @see Tx_DrBlob_Domain_Model_Filter::getLimit
	 */
	public function setLimit( $limit ) {
		if( intval( $limit ) ) {
			$this->limit = $limit;
		} else {
			$this->limit = 999;
		}
	}
	
	/**
	 * Returns the pointer position
	 * @return integer
	 */
	public function getPointer() {
		return $this->pointer;
	}
	
	/**
	 * Enter description here...
	 * 
	 * @param integer $pointer
	 */
	public function setPointer( $pointer ) {
		if( intval( $pointer ) ) {
			$this->pointer = $pointer;
		} else {
			$this->pointer = 0;
		}
	}
	
	public function getCategoryCombinationMode() {
		return $this->categoryCombinationMode;
	}
	
	public function getCategorySelection() {
		if( $this->getCategoryCombinationMode() !== 0 ) {
			return $this->categorySelection;
		} {
			return false;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param array $categoryList
	 * @param integer $combinationMode
	 */
	public function setCategorySelection( $categoryList = array(), $combinationMode = 0 ) {
		if( ( intval( $combinationMode ) !== 1 ) || ( sizeof( $categoryList ) === 0 ) ) {
			$this->categorySelection = null;
			$this->categoryCombinationMode = 0; 
		} else {
			$this->categoryCombinationMode = 1;
			$this->categorySelection = $categoryList;
		}
	}
	
	/**
	 * Returns the array to be passed to the order statement
	 *
	 * @return array
	 */
	public function getOrderBy() {
		return array( $this->orderByField => $this->orderByDirection );
	}
	
	/**
	 * Define what- and in which direction to sort the list after 
	 *
	 * @param string $field
	 * @param string $direction
	 */
	public function setOrderBy( $field, $direction ) {
		if( !in_array( $field, explode( ',', self::allowedOrderByFields ) ) ) {
			$field = 'title';
		}
		if( $direction != Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING && $direction != Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING ) {
			$direction = Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING;
		}

		$this->orderByField = $field;
		$this->orderByDirection = $direction;
	}
}
?>