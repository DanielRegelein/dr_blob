<?php
class Tx_DrBlob_Domain_Repository_FileRepository extends Tx_Extbase_Persistence_Repository {
	
	public $qryParams = array(
		'orderBy' => 'title',
		'orderDir' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING
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
			->execute();
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
			->execute();
	}
	
	public function findSubscribedRecords() {
		die( 'not yet implemented' );
	}
	
	
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
	
	
	private function validateOrdering() {
		$orderArr = array();
		
		if( in_array( $this->qryParams['orderBy'], explode( ',', 'sorting,title,crdate,tstamp,blob_size,uid,download_count,blob_type,author,author_email' ) ) ) {
			$orderArr[$this->qryParams['orderBy']] = Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING;
		} else {
			$orderArr['title'] = Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING;
		}
		return $orderArr;
	}
}
?>