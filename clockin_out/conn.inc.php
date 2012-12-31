<?php # MySQL connect script

define ('DB_USER', 'root');
define ('DB_PASSWORD', 'bdantps');
define ('DB_HOST', 'localhost');
define ('DB_NAME', 'coop');

$connect = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)
           or die('Could not connect to the database: ' . mysql_error());
$dbc = @mysql_select_db(DB_NAME) or
       die('Could not select the database: ' . mysql_error());
?>