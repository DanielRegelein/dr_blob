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
 * Domain Repository for the File records.
 * 
 * ---------------------------------------------------------------------------
 * If you've got any ideas on how to do better, please let me know. I'd love
 * to improve this code.
 * ---------------------------------------------------------------------------
 * 
 * @author Daniel Regelein <daniel.regelein@diehl-informatik.de>
 * @package TYPO3
 * @subpackage dr_blob
 */
class Tx_DrBlob_Domain_Repository_FileRepository extends Tx_Extbase_Persistence_Repository {
	
	public $qryParams = array(
		'orderBy' => 'title',
		'orderDir' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING,
		'limit' => 30,
		'pointer' => 0
	);
	
	/**
	 * Returns all objects of this repository
	 *
	 * @return array An array of objects, empty if no objects found
	 * @api
	 */
	public function findAll() {
		$query = $this->createQuery();
		return $query
			->setOrderings( $this->validateOrdering() )
			->setLimit( (integer)$this->qryParams['limit'] )
			->setOffset( (integer)$this->qryParams['pointer'] * (integer)$this->qryParams['limit'] )
			->execute();
	}

	/**
	 * Counts all objects of this repository
	 *
	 * @return integer The number of results
	 * @api
	 */
	public function countAll() {
		$query = $this->createQuery();
		return $query
			->count();
	}
	
	/**
	 * Returns vip records
	 *
	 * @return array
	 */
	public function findVipRecords() {
		$query = $this->createQuery();
		return $query
			->matching( $query->equals( 'is_vip', 1 ) )
			->setOrderings( $this->validateOrdering() )
			->setLimit( (integer)$this->qryParams['limit'] )
			->setOffset( (integer)$this->qryParams['pointer'] )
			->execute();
	}
	
	/**
	 * Counts all vip records
	 *
	 * @return array
	 */
	public function countVipRecords() {
		$query = $this->createQuery();
		return $query
			->matching( $query->equals( 'is_vip', 1 ) )
			->count();
	}
	
	public function findSubscribedRecords() {
		die( 'not yet implemented' );
	}

	/**
	 * This method is called by the download method.
	 * It is used to return the record workload
	 *
	 * @param integer $uid
	 * @return string the value of the blob_data-field
	 */	
	public function getFileWorkload( $uid ) {
		$query = $this->createQuery();
		
		$tmpReturnRawQueryResult = $query->getQuerySettings()->getReturnRawQueryResult();
		$query->getQuerySettings()->setReturnRawQueryResult( true );

		$data = $query
			->matching( $query->equals( 'uid', $uid ) )
			->execute();
		
		$query->getQuerySettings()->setReturnRawQueryResult( $tmpReturnRawQueryResult );
		
		return $data[0];
	}
	
	/**
	 * This method validates to ordering
	 *
	 * @return array
	 */
	private function validateOrdering() {
		$orderArr = array();
		
		if( in_array( $this->qryParams['orderBy'], explode( ',', 'sorting,title,crdate,tstamp,blob_size,uid,download_count,blob_type,author,author_email' ) ) ) {
			$orderArr[$this->qryParams['orderBy']] = ( 
				$this->qryParams['orderDir'] == Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING || 
				$this->qryParams['orderDir'] == Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING ) ? 
					$this->qryParams['orderDir'] : 
					Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING;
		} else {
			$orderArr['title'] = Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING;
		}
		
		return $orderArr;
	}
}
?>