CREATE TABLE IF NOT EXISTS ProfileConfigData (
  idProfileConfigData int(10) unsigned NOT NULL AUTO_INCREMENT,
  idProfile int(6) unsigned NOT NULL,
  `name` varchar(32) CHARACTER SET ascii NOT NULL,
  `value` text CHARACTER SET ascii NOT NULL,
  added datetime NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY (idProfileConfigData),
  KEY idProfile (idProfile),
  KEY added (added),
  KEY updated (updated),
  KEY tagname (`name`)
) ENGINE=InnoDB ;