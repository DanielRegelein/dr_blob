<?php
interface Tx_DrBlob_Domain_Model_FileInterface {
	
	const RECORD_TYPE_DATABASE = 1;
	const RECORD_TYPE_FILESYSTEM_SECURE = 2;
	const RECORD_TYPE_FILESYSTEM_UNSECURE = 3;
	const RECORD_TYPE_DAM = 4;
	
	public function getTitle();
	public function getDescription();
	public function getFileName();
	public function getFileSize();
	public function getFileMimeType();
	public function getFileExtension();
	public function getFileChecksum();
	public function getCreateDate();
	public function getChangeDate();
}
?>