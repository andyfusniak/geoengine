CREATE TABLE IF NOT EXISTS Customers (
  idCustomer int(10) unsigned NOT NULL AUTO_INCREMENT,
  username varchar(32) CHARACTER SET ascii NOT NULL,
  firstname varchar(255) NOT NULL,
  added datetime NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY (idCustomer),
  UNIQUE KEY username (username),
  KEY firstname (firstname),
  KEY updated (updated)
) ENGINE=InnoDB;
