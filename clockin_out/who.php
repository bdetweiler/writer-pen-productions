<?php
// Start the session
session_start();
@include('../conn.inc.php');

// If no session is present, redirect the user
if(!isset($_SESSION['isroot']))
{ // # 2

     // Redirect them back to the login page.
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
     exit();
} // Close # 2

$order_by = $_GET['order_by'];
?>

<table border="0">
     <tr>
	     <td align="center">
			<a href=    
			"http://<?php echo $_SERVER[HTTP_HOST];?>/who.php?order_by=username">
	          <b>Username</b>
			</a>
		</td>
		<td align="center">
			<a href=    
			"http://<?php echo
			$_SERVER[HTTP_HOST];?>/who.php?order_by=clockintime">
  			<b>Clock In Time</b>
			</a>
		</td>
		<td align="center">
			<a href=    
			"http://<?php echo
			$_SERVER[HTTP_HOST];?>/who.php?order_by=doubletime">
			<b>Double Time</b>
			</a>
		</td>
	</tr>
	<?php 
	
          $query = "SELECT uname, clockin_time, doubletime
		          FROM work_hours
		          WHERE clocked_in = 1";
		
		if($order_by == "" || $order_by == "username")
		{
			$query .= " ORDER BY uname";
		}
		else if($order_by == "clockintime")
		{
			$query .= " ORDER BY clockin_time";
		}
		else if($order_by == "doubletime")
		{
		    	$query .= " ORDER BY doubletime";
		}

          $query_result = @mysql_query($query);
		
		$i = 0;
	     while($row = mysql_fetch_array($query_result))
		{
		     echo '<tr>';
			echo '<td align="left">';
			echo $row[0];
			echo '</td>';
			echo '<td align="center">';
			echo $row[1];
			echo '</td>';
			echo '<td align="center">';
			if($row[2] == 1)
			{ 
			     echo 'YES';
			}
			else
			{
			     echo 'NO';
			}
			echo '</td>';
			echo '</tr>';
			$i++;
		}
	          echo '<tr>';
		     echo '<td>';
		     echo '<b>' . $i . ' people are currently clocked in.</b><br>';
			echo '</td>';
			echo '</tr>';

	?>
</table>
</html>
