CREATE TABLE IF NOT EXISTS Amis (
  idAmi int(10) unsigned NOT NULL AUTO_INCREMENT,
  regionName varchar(32) NOT NULL,
  amiName varchar(32) NOT NULL,
  added datetime NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY (idAmi),
  UNIQUE KEY regionName (regionName),
  KEY added (added),
  KEY updated (updated)
) ENGINE=InnoDB;
