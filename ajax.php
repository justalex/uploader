<?php

//sleep(5); echo 'done1';

if( $_POST['host'] && $_POST['user'] && $_POST['base'] && $_POST['show'] ) {
    $host = $_POST['host'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $base = $_POST['base'];
    $show = $_POST['show'];
    
    if( !$link = mysql_connect( $host, $user, $pass ) ) {
        die('Could not connect to host');
    }
    if( !mysql_select_db( $base, $link ) ) {
        die('Can\'t select base');
    }
    
    $sql1 = "CREATE TABLE  IF NOT EXISTS `$base`.`users` (
`id` INT NOT NULL AUTO_INCREMENT ,
`mail` VARCHAR( 255 ) NOT NULL ,
`pass` VARCHAR( 64 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM ;";

    $sql2 = "CREATE TABLE  IF NOT EXISTS `$base`.`files` (
`id` INT NOT NULL AUTO_INCREMENT ,
`filename` VARCHAR( 255 ) NOT NULL ,
`localname` VARCHAR( 255 ) NOT NULL ,
`comment` INT( 11 ) NOT NULL ,
`userid` INT( 255 ) NOT NULL ,
`date` DATETIME NOT NULL ,
`ip` VARCHAR( 64 ) NOT NULL ,
`ua` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM ;";

    $sql3 = "CREATE TABLE  IF NOT EXISTS `$base`.`comments` (
`id` INT NOT NULL AUTO_INCREMENT ,
`softid` INT NOT NULL ,
`date` DATETIME NOT NULL ,
`userid` INT NOT NULL ,
`message` TEXT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM ;";

    if( !mysql_unbuffered_query($sql1) ) {
        die('Can\'t create table [1]');
    }
    if( !mysql_unbuffered_query($sql2) ) {
        die('Can\'t create table [2]');
    }
    if( !mysql_unbuffered_query($sql3) ) {
        die('Can\'t create table [3]');
    }
    
    if( !$fp = fopen( 'config.php','w' ) ) {
        die('Can\'t create config file');
    }
    
    $config = "<?php

define( 'DB_HOST', '$host' );
define( 'DB_USER', '$user' );
define( 'DB_PASS', '$pass' );
define( 'DB_BASE', '$base' );

define( MAX_SHOW, $show );

?>";
    fwrite( $fp, $config );
    fclose( $fp );
    
    echo 'done';
    
}

?>