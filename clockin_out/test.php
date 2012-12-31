#!./php -q 
<?php # MySQL connect script
/*
define ('DB_USER', 'root');
define ('DB_PASSWORD', 'k00ab1d');
define ('DB_HOST', 'localhost');
define ('DB_NAME', 'coop');

$connect = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)
           or die('Could not connect to the database: ' . mysql_error());
		 $dbc = @mysql_select_db(DB_NAME) or
		        die('Could not select the database: ' . mysql_error());
*/
			   ?>

<?php
/*
//include('../conn.inc.php');
$person = "SELECT uname, first_name, last_name
           FROM user_pass
           WHERE 1
           ORDER BY uname";
$person_query = mysql_query($person);
$person_result = mysql_fetch_array($person_query);
echo $person_result[0] . '\n';
echo 'test';
*/

//scandir() with regexp matching on file name and sorting options based on stat().

function myscandir($dir, $exp, $how='name', $desc=0)
{
   $r = array();
   $dh = @opendir($dir);
   if ($dh) {
       while (($fname = readdir($dh)) !== false) {
           if (preg_match($exp, $fname)) {
               $stat = stat("$dir/$fname");
               $r[$fname] = ($how == 'name')? $fname: $stat[$how];
           }
       }
       closedir($dh);
       if ($desc) {
           arsort($r);
       }
       else {
           asort($r);
       }
   }
   return(array_keys($r));
}

$r = myscandir('./Report/', '[0]', 'ctime', 1);
echo $r[0];
?>
