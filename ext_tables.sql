# TYPO3 Extension Manager dump 1.1
#
# Host: localhost    Database: typo3
#--------------------------------------------------------


#
# Table structure for table "tx_drblob_content"
#
CREATE TABLE tx_drblob_content (
  uid int(11) NOT NULL auto_increment,
  pid int(11) default '0',
  tstamp int(11) unsigned default '0',
  crdate int(11) unsigned default '0',
  cruser_id int(11) unsigned default '0',
  deleted tinyint(4) unsigned default '0',
  hidden tinyint(4) unsigned default '0',
  starttime int(11) unsigned default '0',
  endtime int(11) unsigned default '0',
  fe_group int(11) unsigned default '0',
  sorting int(11) unsigned default '0',
  type tinyint(4) NOT NULL default '1',
  sys_language_uid int(11) default '0',
  l18n_parent int(11) default '0',
  l18n_diffsource mediumblob,
  t3ver_oid int(11) default '0',
  t3ver_id int(11) default '0',
  t3ver_wsid int(11) default '0',
  t3ver_label varchar(30) default '0',
  t3ver_state tinyint(4) default '0',
  t3ver_stage tinyint(4) default '0',
  t3ver_count int(11) default '0',
  t3ver_tstamp int(11) default '0',
  title varchar(255) default '',
  description text,
  category int(11) unsigned default '0',
  is_vip tinyint(4) unsigned default '0',
  download_count int(11) default '0',
  blob_name varchar(255) default '',
  blob_size int(11) default '0',
  blob_type varchar(255) default '',
  blob_checksum varchar(255) default '',
  blob_data longblob,
  PRIMARY KEY (uid),
  KEY title (title),
  KEY parent (pid),
  KEY t3ver_oid (t3ver_oid,t3ver_wsid)
);


#
# Table structure for table "tx_drblob_personal"
#
CREATE TABLE tx_drblob_personal (
  uid_feusers int(11) unsigned default '0',
  uid_pages int(11) unsigned default '0'
);


#
# Table structure for table "tx_drblob_category"
#
CREATE TABLE tx_drblob_category (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) default '0',
  tstamp int(11) unsigned default '0',
  crdate int(11) unsigned default '0',
  cruser_id int(11) unsigned default '0',
  deleted tinyint(1) default '0',
  hidden tinyint(1) default '0',
  title varchar(255) default '',
  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY title (title)
);


#
# Table structure for table "tx_drblob_category_mm"
#
CREATE TABLE tx_drblob_category_mm (
  uid_local int(11) default '0',
  uid_foreign int(11) default '0',
  tablenames varchar(30) default '',
  sorting int(11) default '0',
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);