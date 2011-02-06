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
	
	/**
	 * Returns all objects of this repository
	 *
	 * @param Tx_DrBlob_Domain_Model_Filter $filter 
	 * @return array An array of objects, empty if no objects found
	 * @api
	 */
	public function findAllByFilter( Tx_DrBlob_Domain_Model_Filter $filter ) {
		$query = $this->createQuery();
		
		if( $filter->getCategoryCombinationMode() !== 0 ) {
			$query->matching( $this->buildCategorySelectionSubQuery( $filter->getCategorySelection(), $filter->getCategoryCombinationMode() ) );
		}
		
		return $query
			->setOrderings( $filter->getOrderBy() )
			->setLimit( $filter->getLimit() )
			->setOffset( $filter->getPointer() * $filter->getLimit() )
			->execute();
	}

	/**
	 * Counts all objects of this repository
	 *
	 * @param Tx_DrBlob_Domain_Model_Filter $filter
	 * @return integer The number of records
	 * @api
	 */
	public function countAllByFilter( Tx_DrBlob_Domain_Model_Filter $filter ) {
		$query = $this->createQuery();
		if( $filter->getCategoryCombinationMode() !== 0 ) {
			$query->matching( $this->buildCategorySelectionSubQuery( $filter->getCategorySelection(), $filter->getCategoryCombinationMode() ) );
		}
		return $query->execute()->count();
	}
	
	/**
	 * Returns vip records
	 *
	 * @param Tx_DrBlob_Domain_Model_Filter $filter
	 * @return array
	 */
	public function findVipRecords( Tx_DrBlob_Domain_Model_Filter $filter ) {
		$query = $this->createQuery();
		
		$constraints = array();
		$constraints[] = $query->equals( 'is_vip', 1 );
		
		if( $filter->getCategoryCombinationMode() !== 0 ) {
			$constraints[] =  $this->buildCategorySelectionSubQuery( $filter->getCategorySelection(), $filter->getCategoryCombinationMode() );
		}
		
		return $query
			->matching( $query->logicalAnd( $constraints ) )
			->setOrderings( $filter->getOrderBy() )
			->setLimit( $filter->getLimit() )
			->setOffset( $filter->getPointer() )
		 	->execute();
	}
	
	/**
	 * Counts all vip records
	 *
	 * @param Tx_DrBlob_Domain_Model_Filter $filter
	 * @return integer The number of records
	 */
	public function countVipRecords( Tx_DrBlob_Domain_Model_Filter $filter ) {
		$query = $this->createQuery();
		
		$constraints = array();
		$constraints[] = $query->equals( 'is_vip', 1 );
		
		if( $filter->getCategoryCombinationMode() !== 0 ) {
			$constraints[] =  $this->buildCategorySelectionSubQuery( $filter->getCategorySelection(), $filter->getCategoryCombinationMode() );
		}
		
		return $query
			->matching( $query->logicalAnd( $constraints ) )
			->execute()
		 	->count();
	}
	
	/**
	 * Returns a list of subscribe records
	 *
	 * @param Tx_DrBlob_Domain_Model_Filter $filter
	 * @return array
	 * @throws Tx_DrBlob_Exception_NotLoggedIn if no user is logged in
	 */
	public function findSubscribedRecords( Tx_DrBlob_Domain_Model_Filter $filter ) {
		if ( $GLOBALS['TSFE']->loginUser ) {
			
			$pidList = array();
			$rsltPIDList = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid_pages',
				'tx_drblob_personal',
				'uid_feusers=\'' . $GLOBALS['TSFE']->fe_user->user['uid'] . '\''
			);
			while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rsltPIDList ) ) {
				$pidList[] = $row['uid_pages'];
			}
			
			$querySettings = t3lib_div::makeInstance( 'Tx_Extbase_Persistence_Typo3QuerySettings' );
			$querySettings->setStoragePageIds( $pidList );
			$this->setDefaultQuerySettings( $querySettings );
			
			return $this->findAllByFilter( $filter );
		} else {
			throw new Tx_DrBlob_Exception_NotLoggedIn( 'No user logged in, so this query is invalid', 1296768814 );
		}
	}
	
	/**
	 * Returns the number of subscribe records
	 *
	 * @param Tx_DrBlob_Domain_Model_Filter $filter
	 * @return integer The number of records
	 */
	public function countSubscribedRecords( Tx_DrBlob_Domain_Model_Filter $filter ) {
		if ( $GLOBALS['TSFE']->loginUser ) {
			
			$pidList = array();
			$rsltPIDList = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid_pages',
				'tx_drblob_personal',
				'uid_feusers=\'' . $GLOBALS['TSFE']->fe_user->user['uid'] . '\''
			);
			while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rsltPIDList ) ) {
				$pidList[] = $row['uid_pages'];
			}
			
			$querySettings = t3lib_div::makeInstance( 'Tx_Extbase_Persistence_Typo3QuerySettings' );
			$querySettings->setStoragePageIds( $pidList );
			$this->setDefaultQuerySettings( $querySettings );
			
			return $this->CountAllByFilter( $filter );
		} else {
			throw new Tx_DrBlob_Exception_NotLoggedIn( 'No user logged in, so this query is invalid', 1296768815 );
		}
	}

	/**
	 * This method is called by the download method.
	 * It is used to return the record's workload
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
	 * This method generates the subquery to build the category selection.
	 * @return Tx_Extbase_Persistence_QOM_OrInterface or null
	 */
	protected function buildCategorySelectionSubQuery( $selectedCategories, $combinationMode ) {
		if( $combinationMode === 0 || sizeof( $selectedCategories ) === 0 ) {
			return null;
		} else {
			
			if( $combinationMode === 1 ) {
				$query = $this->createQuery();
				$constraints = array();
				foreach( $selectedCategories as $cat ) {
					$constraints[] = $query->equals( 'category.uid', $cat );
				}
				return $query->logicalOr( $constraints );
			}
		}
		
		return null;
	}
}
?>