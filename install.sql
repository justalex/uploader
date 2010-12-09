CREATE TABLE  `onliner`.`users` (
`id` INT NOT NULL AUTO_INCREMENT ,
`mail` VARCHAR( 255 ) NOT NULL ,
`pass` VARCHAR( 64 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM ;

CREATE TABLE  `onliner`.`files` (
`id` INT NOT NULL AUTO_INCREMENT ,
`filename` VARCHAR( 255 ) NOT NULL ,
`localname` VARCHAR( 255 ) NOT NULL ,
`comment` INT( 11 ) NOT NULL ,
`userid` INT( 255 ) NOT NULL ,
`date` DATETIME NOT NULL ,
`ip` VARCHAR( 64 ) NOT NULL ,
`ua` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM ;