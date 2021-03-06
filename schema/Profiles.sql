CREATE TABLE IF NOT EXISTS Profiles (
  idProfile int(6) unsigned NOT NULL AUTO_INCREMENT,
  idCustomer int(10) unsigned NOT NULL,
  domainName varchar(255) CHARACTER SET ascii DEFAULT NULL,
  sslRequired char(1) CHARACTER SET ascii DEFAULT 'N',
  publicIp varchar(255) CHARACTER SET ascii DEFAULT NULL,
  instanceType varchar(32) CHARACTER SET ascii NOT NULL,
  regionName varchar(32) CHARACTER SET ascii DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'pending',
  metaStatus text CHARACTER SET ascii NOT NULL,
  added datetime NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY (idProfile),
  KEY idCustomer (idCustomer),
  KEY added (added),
  KEY updated (updated),
  KEY regionName (regionName),
  KEY domainName (domainName),
  KEY ipAddress (publicIp),
  KEY requestedInstanceType (instanceType),
  KEY sslRequired (sslRequired),
  KEY `status` (`status`)
) ENGINE=InnoDB;
