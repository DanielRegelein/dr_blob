<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2001-2008 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Indexing class for TYPO3 frontend, some changes for dr_blob
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author  Daniel Regelein <daniel.regelein@diehl-informatik.de>
 * @package TYPO3
 * @subpackage tx_indexedsearch
 */
class ux_tx_indexedsearch_indexer extends tx_indexedsearch_indexer {

	/**
	 * Extract links (hrefs) from HTML content and if indexable media is found, it is indexed.
	 *
	 * @param	string		HTML content
	 * @return	void
	 */
	function extractLinks($content) {
		
			// Get links:
		$list = $this->extractHyperLinks($content);

		if ($this->indexerConfig['useCrawlerForExternalFiles'] && t3lib_extMgm::isLoaded('crawler'))	{
			$this->includeCrawlerClass();
			$crawler = t3lib_div::makeInstance('tx_crawler_lib');
		}

			// Traverse links:
		foreach($list as $linkInfo)	{
				// Decode entities:
			if ($linkInfo['localPath'])	{	// localPath means: This file is sent by a download script. While the indexed URL has to point to $linkInfo['href'], the absolute path to the file is specified here!
				$linkSource = t3lib_div::htmlspecialchars_decode($linkInfo['localPath']);
			} else {
				$linkSource = t3lib_div::htmlspecialchars_decode($linkInfo['href']);
			}

				// Parse URL:
			$qParts = parse_url($linkSource);
			// Check for jumpurl (TYPO3 specific thing...)
			if ($qParts['query'] && strstr($qParts['query'],'jumpurl='))	{
				parse_str($qParts['query'],$getP);
				$linkSource = $getP['jumpurl'];
				$qParts = parse_url($linkSource);	// parse again due to new linkSource!
			}
			
			//Search for dr_blob
			if ($qParts['query'] && (
				strstr($qParts['query'], rawurlencode( 'tx_drblob_pi1[downloadUid]' ) . '=' )  ||
				strstr($qParts['query'], 'tx_drblob_pi1[downloadUid]=' )
			) )	{
				parse_str( $qParts['query'], $tmp );
				$qParts['tx_drblob'] = $tmp['tx_drblob_pi1']['downloadUid'];
			}

			if ($qParts['scheme'])	{
				if ($this->indexerConfig['indexExternalURLs'])	{
						// Index external URL (http or otherwise)
					$this->indexExternalUrl($linkSource);
				}
			} elseif ($qParts['tx_drblob']) {
				
				//Index the blob-File
				$this->indexBlobFile( $qParts['path'] . '?' . $qParts['query'], $qParts['tx_drblob'] );
				
			} elseif (!$qParts['query']) {
				if (t3lib_div::isAllowedAbsPath($linkSource))	{
					$localFile = $linkSource;
				} else {
					$localFile = t3lib_div::getFileAbsFileName(PATH_site.$linkSource);
				}
				
				if ($localFile  && @is_file($localFile))	{
					

					// Index local file:
					if ($linkInfo['localPath'])	{
						$fI = pathinfo($linkSource);
						$ext = strtolower($fI['extension']);
						if (is_object($crawler))	{
							$params = array(
								'document' => $linkSource,
								'alturl' => $linkInfo['href'],
								'conf' => $this->conf
							);
							unset($params['conf']['content']);

							$crawler->addQueueEntry_callBack(0,$params,'EXT:indexed_search/class.crawler.php:&tx_indexedsearch_files',$this->conf['id']);
							$this->log_setTSlogMessage('media "'.$params['document'].'" added to "crawler" queue.',1);
						} else {
							$this->indexRegularDocument($linkInfo['href'], false, $linkSource, $ext);
						}
					} else {
						if (is_object($crawler))	{
							$params = array(
								'document' => $linkSource,
								'conf' => $this->conf
							);
							unset($params['conf']['content']);
							$crawler->addQueueEntry_callBack(0,$params,'EXT:indexed_search/class.crawler.php:&tx_indexedsearch_files',$this->conf['id']);
							$this->log_setTSlogMessage('media "'.$params['document'].'" added to "crawler" queue.',1);
						} else {
							$this->indexRegularDocument($linkSource);
						}
					}
				}
			}
		}
	}


	/******************************************
	 *
	 * Indexing; BLOB-File
	 *
	 ******************************************/
	
	
	/**
	 * Index BLOB-File content
	 *
	 * @param	string		URL of the Blob-File
	 * @param	int			UID of the Blob-File
	 * @return	void
	 * @see indexRegularDocument()
	 */
	function indexBlobFile( $blobURL, $blobUID ) {

		$cObj = t3lib_div::makeInstance( 'tx_drblob_pi1' );
		$dataArr = $cObj->vDownload( false, intval( $blobUID ) );
		$dataArr['blob_name'] = t3lib_div::split_fileref( $dataArr['blob_name'] );
		
		$createdTempFile = false;
		if( $dataArr['type'] == '3' ) {
			$tmpFile = $dataArr['blob_data'];
		} else {
			if ( $dataArr['type'] == '2' && $dateArr['is_quoted'] == false ) {
				//the file has to be stored in a temp-file to ensure a nice index title.
				//otherwise the index title would be something like abcedefgh.blob
				$tmpFile = $dataArr['blob_data'];
				$dataArr['blob_data'] = file_get_contents( $tmpFile );				
			}
			
				//Store file
			$tmpFile = PATH_site . 'typo3temp/' . $dataArr['blob_name']['file'];
			t3lib_div::writeFileToTypo3tempDir( $tmpFile, $dataArr['blob_data'] );	
			$createdTempFile = true;				
		}
		
			// Index that file:
		$this->indexRegularDocument( $blobURL, TRUE, $tmpFile, $dataArr['blob_name']['realFileext'] );		
		
		if( $createdTempFile ) {
			t3lib_div::unlink_tempfile( $tmpFile );
		}
		
	}
}


if ( defined( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Resources/Private/PHP/class.ux_tx_indexedsearch_indexer.php'] ) {
	include_once( $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dr_blob/Resources/Private/PHP/class.ux_tx_indexedsearch_indexer.php'] );
}
?>