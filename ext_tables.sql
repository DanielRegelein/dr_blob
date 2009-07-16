# TYPO3 Extension Manager dump 1.1
#
# Host: 190.9.130.150    Database: typo3intranet
#--------------------------------------------------------


#
# Table structure for table "tx_drblob_content"
#
CREATE TABLE tx_drblob_content (
  uid int(11) DEFAULT '0' NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  fe_group int(11) unsigned DEFAULT '0' NOT NULL,
  title varchar(255) DEFAULT '' NOT NULL,
  description text NOT NULL,
  sort int(11) DEFAULT '0' NOT NULL,
  is_vip enum('0','1') DEFAULT '0' NOT NULL,
  blob_name varchar(255) DEFAULT '' NOT NULL,
  blob_size varchar(255) DEFAULT '' NOT NULL,
  blob_type varchar(255) DEFAULT '' NOT NULL,
  blob_data longblob NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);