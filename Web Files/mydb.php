<?php

 // this will avoid mysql_connect() deprecation error.
 error_reporting( ~E_DEPRECATED & ~E_NOTICE );
 // but I strongly suggest you to use PDO or MySQLi.
 
 define('DBHOST', 'localhost');
 define('DBUSER', 'root');
 define('DBPASS', '');
 define('DBNAME', 'evoting');
 
 $serverConnection = mysql_connect(DBHOST,DBUSER,DBPASS);
 $dbConnection = mysql_select_db(DBNAME);
 
 if ( !$serverConnection ) {
  die("Connection failed : " . mysql_error());
 }
 
 if ( !$dbConnection ) {
  die("Database Connection failed : " . mysql_error());
 }
 
 ?>