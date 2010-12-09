<?php

error_reporting( 1 );
set_time_limit( 0 );
include_once( 'config.php' );
define( 'DEBUG', 'file' );

class DB {
    private $link;
	
	function __construct() {
	
	    if( !$this->link = mysql_connect(DB_HOST, DB_USER, DB_PASS) ) {
		    if( DEBUG == 'show' ) echo "Could not connect: " . mysql_error();
			if( DEBUG == 'file' ) {
			    $fp = fopen( 'sql_log.log', 'a' );
				fwrite( $fp, "[".date( 'Y-m-d H:i:s' )."] Could not connect: " . mysql_error()."\n" );
				fclose( $fp );
			}
			return 0; 
		}
		
		if( !mysql_select_db( DB_BASE, $this->link) ) {
		    if( DEBUG == 'show' ) echo "Cant use db: " . mysql_error();
			if( DEBUG == 'file' ) {
			    $fp = fopen( 'sql_log.log', 'a' );
				fwrite( $fp, "[".date( 'Y-m-d H:i:s' )."] Cant use db: " . mysql_error()."\n" );
				fclose( $fp );
			}
			return 0;
		}
	}
	
	function query( $query, $type = MYSQL_ASSOC ) {
	    $result = mysql_query($query);
		
		if( $result ) {
			if( !mysql_num_rows($result) ) return 1;
			else {
			    $i = 0;
				while( $row = mysql_fetch_array($result, $type) ) {
					foreach( $row as $key=>$var ) {
					    //echo "$key => $var<br>";
						$returnRow[$i][$key]=$var;
					}
					$i++;
				}
				return $returnRow;
			}
		}
		else {
		    if( DEBUG == 'show' ) echo "Cant make query: " . mysql_error();
			if( DEBUG == 'file' ) {
			    $fp = fopen( 'sql_log.log', 'a' );
				fwrite( $fp, "[".date( 'Y-m-d H:i:s' )."] Cant make query: " . mysql_error()."\n" );
				fclose( $fp );
			}
			return 0;
		}
	}
	
	function secSQL( $var ) {
	    return mysql_real_escape_string( $var, $this->link );
	}
	
	function __destruct() {
	    mysql_close( $this->link );
	}
}

?>