<?php
/*
*        Script Title: Clockin/Out v 1.0
*        Page Title: admin_page.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*			Code organized = true;
*
*        This is the main work hours page for the Administrator/Supervisors. Here
*        they can view employees' work hours, audit employees' accounts, enter commissions,
*        add/remove users (root only), change their own passwords, and log out.
*
*        This page primarily uses code from loggedin.php, the user login page.
*        Unfortunately, because of all the queries and figuring, it can be fairly slow.
*        Efficiency was not too high on my priority list. Usability and Security
*        were my top two, followed by just getting it done. Efficiency has fallen
*        short. Perhaps a retooling of some of the routines could speed up the process.
*
*        Looking back, a lot of stuff could have been put into functions and includes
*        to make for more concise and proficient code. I will consider this after Beta
*        testing.
*
*        Copyright (C) 2004  Brian Detweiler
*
*        This program is free software; you can redistribute it and/or
*        modify it under the terms of the GNU General Public License
*        as published by the Free Software Foundation; either version 2
*        of the License, or (at your option) any later version.
*
*        This program is distributed in the hope that it will be useful,
*        but WITHOUT ANY WARRANTY; without even the implied warranty of
*        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*        GNU General Public License for more details.
*
*        You should have received a copy of the GNU General Public License
*        along with this program; if not, write to the Free Software
*        Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*
*        My contact information is above.
*/

session_start();
@include('../conn.inc.php');

// input:  two date-time strings
// output: none
// return: a time string
// notes: This function takes two date-time strings and takes the difference
//        between the two. It returns the difference in time format. If the
//        time is 1 day or more, it is added up in hours.
function DATEDIFF($date1, $date2) 
{
	// Explode the number we just sent in here
	list($hours_date1, $minutes_date1, $seconds_date1) = explode(':',$date1);
	list($hours_date2, $minutes_date2, $seconds_date2) = explode(':',$date2);

	// Minutes are currently in 100 min/hour format.

	// Now, add those minutes to the converted hours (which are now in minute
	// format) and we get a total time in minutes.
	$date1   = ($hours_date1 * 100) + $minutes_date1;
	$date2   = ($hours_date2 * 100) + $minutes_date2;
	$diff    = abs($date1 - $date2);
	$hours   = floor($diff / 100);

	// This gives us the remainder (minutes)
	$minutes = $diff % 100;
	$time = $hours . ':' . $minutes;

	return $time;
}

function convert_to_100($min)
{
	// Convert 60 minute hours to 100 minute hours
	// This is specific to the CoOp conversion chart
	// If it's a multiple of 3, there's no decimal; it stays the same.
	if(($min % 3) == 0)
	{
		// It is static cast as an int to truncate decimals.
		$min = (int) (($min / 60) * 100);
	}

	// If you subtract one or two, and it is a multiple of 6, you round up.
	else if(((abs($min - 1) % 6) == 0) || ((abs($min - 2) % 6) == 0))
	{
		$min = (int) ceil(($min / 60) * 100);
	}

	// Otherwise (which seems to be sufficient), round down.
	else
	{
		$min = (int) floor(($min / 60) * 100);
	}

	if($min == 0)
	{
		$min = 0;
	}
	if(strlen($min) < 2)
	{
		// Pad the minutes with a zero (so instead of 1, you get 01)
		$min = '0' . $min;
	}

	return $min;
}

// I don't think I ever use this. Save it for later, or delete it.
// Convert a 100 minute time back to 60 minute time.
function convert_to_60($min)
{
	// Convert 100 minute hours to 60 minute hours
	$minute_test = (($min / 100) * 60);
	if(($minute_test % 3) == 0)
	{
		// It is static cast as an int to truncate decimals.
		$min = (int) (($min / 100) * 60);
	}

	// I found that if you subtract one or two from the number and it is a
	// multiple of 6, then you have to floor it (round down).
	else if(((abs($minute_test - 1) % 6) == 0) || ((abs($minute_test - 2) % 6) == 0))
	{
		$min = (int) floor(($min / 100) * 60);
	}

	// The final posibility is to have to round up.
	else
	{
		$min = (int) ceil(($min / 100) * 60);
	}

	if(strlen($min) < 2)
	{
		$min = '0' . $min;
	}
	return $min;
}

// input:  3 integers
// output: none
// return: a string
// notes:  This function takes the standard (60 min/hour) hours minutes and
//         seconds, and turns them into 100 minute/hour cumulative time.
function hhmmss($hour, $min, $sec = 0) 
{
	// Need to truncate seconds. Otherwise, you times will be off by a few minutes 
	$sec = 0;

	// This will normalize the HH:MM:SS
	// Floor truncates any trailing decimals.
	$min = floor(($min + ($sec / 60)));
	$sec = ($sec % 60);
	$hour = floor(($hour + ($min / 60)));
	$min = ($min % 60);

	// This converts 60 minute time to 100 minute time.
	// It is static cast as an int to truncate decimals.

	$hms = $hour . ":" . $min;
	return $hms;
}


// I don't think I ever use this function either. Save it for later or scrap
// it.
// input:  2 integers
// output: none
// return: a string
// notes:  This function takes the total hours and minutes, and turns them
//         into 100 minute/hour cumulative time. OBSOLETE?
function tot_hhmmss($tot_hour, $tot_min) 
{
	// This will normalize the grand total
	// Use this after you have hhmmss()'d the hours, and added them to a
	// total. Then send the total in here. It will just normalize it to be
	// printed.

	// How many hours are in the acrued minutes
	$tot_hour = floor(($tot_hour + ($tot_min / 100)));

	// How many of those acrued minutes were minutes?
	$tot_min = ($tot_min % 100);

	// Adds a leading zero, so :9 becomes :09. Looks more proper.
	if(strlen($tot_min) < 2)
	{
		$tot_min = '0' . $tot_min;
	}

	// If the hour is null, turn it into a zero for printing purposes.
	if(!$tot_hour)
	{
		$tot_hour = '0';
	}
     
	$tot_hms = $tot_hour . ":" . $tot_min;
	return $tot_hms;
}

/*
// input:  3 integers
// output: none
// return: a string
// notes:  This function takes the hours minutes and seconds, and turns them
//         into 100 minute/hour cumulative time.
function hhmmss($hour, $min, $sec)
{ 
	// This will normalize the HH:MM:SS
	$min = floor(($min + ($sec / 60)));
	$sec = ($sec % 60);
	$hour = floor(($hour + ($min / 60)));
	$min = ($min % 60);

	// This converts 60 minute time to 100 minute time.
	// It is static cast as an int to truncate decimals.
	$min = (int) (($min / 60) * 100);

	$hms = $hour . ":" . $min;
	return $hms;
} 
*/

################################################################################
#                   END FUNCTIONS / BEGIN PAGE                                 #
################################################################################

if (!(isset($_SESSION['isroot'])))
{ 
	header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
	exit();
} 

if ($_SESSION['expired_pass'] == 1)
{ 
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/changeadminpasswd.php");
     exit();
} 

$page_title = "Aurora Cooperative - Administrator";
@include ('templates/header.inc');

$fn = $_SESSION['first_name'];

?>

<!-- Link to the Cascading Style Sheet for use with table color and such -->
<link href="templates/style.css" type="text/css" rel="stylesheet">

<!--------------------------------------------------------------------------->
<!--                    JavaScript functions here                          -->
<!--------------------------------------------------------------------------->

<!-- For NOTES and COMMISSION use this java script to pop up a new window. -->
<script language="javascript">
     function pop_window(url)
     {
          var popit = window.open(url,'console','menubar,toolbar,location,directories,status,scrollbars, resizable,width=640,height=480');
     }
</script>

<div align="center">
<br />

<?php
if($_SESSION['isroot'] == 1)
{
?>
<b>REPORTS</b>
<table border="1">
	<tr>
		<td>
			<b>
				NEW
			</b>
		</td>
		<td>
			<b>
				OLD
			</b>
		</td>
	</tr>
	<tr>
		<td>
			<a href="javascript:pop_window('http://<?php echo
				$_SERVER[HTTP_HOST]; ?>/report.php')">Create</a>
		</td>
		<td>
			<a href="javascript:pop_window('http://<?php echo
				$_SERVER[HTTP_HOST]; ?>/report.php?id=old')">Create</a>
		</td>
	</tr>
	<tr>
		<td>
			<a href="http://<?php echo $_SERVER[HTTP_HOST]; ?>/Report/new_report.csv">
              Download*</a>
		</td>
		<td>
			<a href="http://<?php echo $_SERVER[HTTP_HOST]; ?>/Report/old_report.csv">
              Download*</a>
		</td>
	</tr>
	<tr>
		<td>
		 	&nbsp;
		</td>
		<td>
			<a href="http://<?php echo $_SERVER[HTTP_HOST]; ?>/archive.php">Archive</a>
		</td>
	</tr>
</table>
<font size="2" color="ef0000">To download, right click and "Save As"</font>
<br />
<br />
<?php
} // End if
?>

<!----------------------------------------------------------------------------->
<!--                           Drop Down Menu                                -->
<!----------------------------------------------------------------------------->

<form action=<?php echo '"http://' . $_SERVER[HTTP_HOST] . '/admin_page.php"'; ?> method="post">
<table border="0" cellpadding="5">
	<tr>
		<td>
			<?php
				$employees = "SELECT uname
								  FROM user_pass
								  WHERE 1
								  ORDER BY uname";
				$employees_results = @mysql_query($employees) or die(mysql_error());
			?>
			<select name="employee">
				<option value="">
					Display Employee:
				</option>
				<?php
					while($rows = mysql_fetch_array($employees_results))
					{ 
						echo "<option value=\"$rows[0]\">$rows[0]</option>\n";
					} 
				?>
			</select>
		</td>
		<td>
			<input type="submit" name="whois" value="Who Is This Person?">
		</td>
	</tr>
	<tr>
		<td vAlign="top" align="left">
			<select name="display_by">
				<option value="">Display Work Hours By:</option>
				<option value="this_period">This Pay Period</option>
				<option value="last_period">Last Pay Period</option>
				<option value="this_month">This Month</option>
				<option value="30">Last 30 Days</option>
				<option value="60">Last 60 Days</option>
				<option value="90">Last 90 Days</option>
				<option value="180">Last 180 Days
				<option value="year">Year To Date</option>
				<option value="prev_year">Previous Year</option>
			</select>
		</td>
		<td vAlign="top" align="left">
			<input type="submit" name="submit" value="Go" />
		</td>
	</tr>
</table>

<br />

</div>

<!----------------------------------------------------------------------------->
<!--                         End of Drop Down Menu                           -->
<!----------------------------------------------------------------------------->


<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td vAlign="bottom" align="left">
			<big>
			<strong>
				<?php

					if (isset($_POST['submit']))
					{ 
						if($_POST['employee'] == '')
						{
							$u = $_SESSION['uname'];
						}
						else
						{
							$u = $_POST['employee'];
							$_SESSION['uname'] = $u;
						}
					}
					if(isset($_SESSION['uname']))
					{ 
						echo 'Statement of Work Hours for <font color="#ef0000">'
								. $_SESSION['uname'] . '</font>';
						echo ", " . date("F j") . ',&nbsp;' .  date("Y");
					} 
				?>
			</strong>
			</big>
		</td>
	</tr>
	<tr>
		<td vAlign="bottom" align="right">
			<?php
				$access_level = 'admin';
				$page = "admin";
				@include ('templates/tabs.inc');
			?>
		</td>
	</tr>
</table>
<table cellSpacing="0" borderColorDark="c0c0c0" cellPadding="0" width="100%"
       borderColorLight="ffffff" border="1">
	<tbody>
		<tr>
			<td>
				<table id="hours" cellSpacing="0" cellPadding="0" width="100%" border="0"
					name="hours">
					<tbody>
						<?php

################################################################################
#                 Who Is This Person?														 #
################################################################################

							if(isset($_POST['whois']))
							{
								$_SESSION['uname'] = $_POST['employee'];
								$u = $_POST['employee'];

								$query = "SELECT first_name, last_name
											 FROM user_pass
											 WHERE uname = '$u'";
								$query_result = mysql_query($query);
								$query_result_array = mysql_fetch_array($query_result);
								$name = $query_result_array[0] . ' ' . $query_result_array[1];
								echo '<body onLoad="javascript:alert(\'' . $name . '\')">';
							}

################################################################################
#                                  Drop Down Menu                              #
################################################################################

							@include ('templates/tableheader.inc');
							$hours = "SELECT entry_id, clockin_time, first_name, last_name,
										 clockout_time, reg_hour, reg_minute, reg_second,
										 dt_hour, dt_minute, dt_second,
										 vac_hour, vac_minute, vac_second,
										 hol_hour, hol_minute, hol_second,
										 sick_hour, sick_minute, sick_minute,
										 fun_hour, fun_minute, fun_second,
										 total_hour, total_minute, total_second,
										 flag, notes
										 FROM work_hours ";

							if (isset($_POST['submit']))
							{ 

/*******************************************************************************
* NOTE: This feature utilizes hardcoded dates in the database based on the     *
* bi-weekly pay cycle. If any of the bi-weekly dates have changed, the dates   *
* in the database will need to change. This can be done by hardcoding a valid  *
* date (pick a paycycle start date in the past - HINT - Paycycles start at     *
* 12:01 AM Monday) into the pay_cycle table, and then running                  *
* create_pay_cycle.php. It should last til about 2030. 								 *
*******************************************************************************/

								// If they select a name once, they can just select
								// the "Display Work Hours By" field instead of 
								// selecting both. 
								if($_POST['employee'] == '')
								{
									$u = $_SESSION['uname'];
								}
								else
								{
									$u = $_POST['employee'];
									$_SESSION['uname'] = $u;
								}
								// This grabs the current pay cycle date from the pay_cycle table.
								$this_cycle = "SELECT cycle_date
													FROM pay_cycle
													WHERE cycle_date <= NOW()
													ORDER BY entry_id DESC
													LIMIT 0, 1";
								$this_cycle_query = @mysql_query($this_cycle) or die(error());
								$this_cycle_query_result = mysql_fetch_array($this_cycle_query);

								// This grabs the last pay cycle from the pay_cycle table.
								$last_cycle = "SELECT cycle_date
													FROM pay_cycle
													WHERE cycle_date <= NOW()
													ORDER BY entry_id DESC
													LIMIT 1, 1";
								$last_cycle_query = @mysql_query($last_cycle) or die(error());
								$last_cycle_query_result = mysql_fetch_array($last_cycle_query);
//echo 'last = ' . $last_cycle_query_result[0] . '<br>this = ' . $this_cycle_query_result[0] . '<br>';

								// Determine display method the user has selected.
								switch($_POST['display_by'])
								{ 
									case 'this_period':
										$period = 'this pay period.';
										$hours .= "WHERE uname = '$u'
													  AND clockin_time >= '$this_cycle_query_result[0]'
													  AND clockin_time <= NOW()
													  AND clocked_in = 0
													  ORDER BY clockin_time DESC";
										break;
									case 'last_period':
										$period = 'last pay period.';
										$hours .= "WHERE uname = '$u'
													  AND clockin_time >= '$last_cycle_query_result[0]'
													  AND clockin_time <= '$this_cycle_query_result[0]'
													  AND clocked_in = 0
													  ORDER BY clockin_time DESC";
										break;
									case 'this_month':
										$period = 'this month.';
										$month  = date('m');
										$year   = date('Y');
										$hours .= "WHERE uname = '$u'
													  AND clockin_time >= '" . $year . "-" . $month . "-00 00:00:00'
													  AND clockin_time <= NOW()
													  AND clocked_in = 0
													  ORDER BY clockin_time DESC";
										break;
									case '30':
										$period = 'the last 30 days.';
										$date_add = "SELECT
														 DATE_SUB(NOW(), INTERVAL 30 DAY)";
										$date_add_query = @mysql_query($date_add) or die(mysql_error());
										$date_add_query_result = mysql_fetch_array($date_add_query);
	
										$hours .= "WHERE uname = '$u'
													  AND clocked_in = 0
													  AND clockin_time >= '$date_add_query_result[0]'
													  AND clockin_time <= NOW()
													  ORDER BY clockin_time DESC";
										break;
									case '60':
										$period = 'the last 60 days.';
										$date_add = "SELECT
														 DATE_SUB(NOW(), INTERVAL 60 DAY)";
										$date_add_query = @mysql_query($date_add) or die(mysql_error());
										$date_add_query_result = mysql_fetch_array($date_add_query);
	
										$hours .= "WHERE uname = '$u'
													  AND clocked_in = 0
													  AND clockin_time >= '$date_add_query_result[0]'
												 	  AND clockin_time <= NOW()
													  ORDER BY clockin_time DESC";
										break;
									case '90':
										$period = 'the last 90 days.';
										$date_add = "SELECT
														 DATE_SUB(NOW(), INTERVAL 90 DAY)";
										$date_add_query = @mysql_query($date_add) or die(mysql_error());
										$date_add_query_result = mysql_fetch_array($date_add_query);
	
										$hours .= "WHERE uname = '$u'
													  AND clocked_in = 0
													  AND clockin_time >= '$date_add_query_result[0]'
													  AND clockin_time <= NOW()
													  ORDER BY clockin_time DESC";
										break;
									case '180':
										$period = 'the last 180 days.';
										$date_add = "SELECT
														 DATE_SUB(NOW(), INTERVAL 180 DAY)";
										$date_add_query = @mysql_query($date_add) or die(mysql_error());
										$date_add_query_result = mysql_fetch_array($date_add_query);
	
										$hours .= "WHERE uname = '$u'
													  AND clocked_in = 0
													  AND clockin_time >= '$date_add_query_result[0]'
													  AND clockin_time <= NOW()
													  ORDER BY clockin_time DESC";
										break;
									case 'year':
										$period = 'this year to date.';
										$date_add = "SELECT
														 DATE_SUB(NOW(), INTERVAL 365 DAY)";
										$date_add_query = @mysql_query($date_add) or die(mysql_error());
										$date_add_query_result = mysql_fetch_array($date_add_query);
	
										$hours .= "WHERE uname = '$u'
													  AND clocked_in = 0
													  AND clockin_time >= '$date_add_query_result[0]'
													  AND clockin_time <= NOW()
													  ORDER BY clockin_time DESC";
										break;
									case 'prev_year':
										$period = 'last year.';
										$date_add = "SELECT
														 DATE_SUB(NOW(), INTERVAL 730 DAY)";
										$date_add_query = @mysql_query($date_add) or die(mysql_error());
										$date_add_query_result = mysql_fetch_array($date_add_query);
	
										$date_sub = "SELECT
														 DATE_SUB(NOW(), INTERVAL 365 DAY)";
										$date_sub_query = @mysql_query($date_add) or die(mysql_error());
										$date_sub_query_result = mysql_fetch_array($date_add_query);
	
										$hours .= "WHERE uname = '$u'
													  AND clocked_in = 0
													  AND clockin_time >= '$date_add_query_result[0]'
													  AND clockin_time <= '$date_sub_query_result[0]'
													  ORDER BY clockin_time DESC";
										break;
									default:
										// Default is LAST PERIOD
										$period = 'last pay period';
										$hours .= "WHERE uname = '$u'
													  AND clockin_time >= '$last_cycle_query_result[0]'
													  AND clockin_time <= '$this_cycle_query_result[0]'
													  AND clocked_in = 0
													  ORDER BY clockin_time DESC";
										break;
								}
							}	
							else // If(!isset($_POST['submit'])) 
							{
								// Enters here when page loads without pressing the 
								// "Go" button.
								$u = $_SESSION['uname'];
								$period = 'last pay period.';
								$hours .= "WHERE uname = '$u'
											  AND clockin_time >= '$last_cycle_query_result[0]'
											  AND clockin_time <= '$this_cycle_query_result[0]'
											  AND clocked_in = 0
											  ORDER BY clockin_time DESC";
	
################################################################################
# 	               PURGE DATA OLDER THAN 2 YEARS ON EACH LOGIN                   #
################################################################################

								$two_years = "SELECT
												  DATE_SUB(NOW(), INTERVAL 2 YEAR)";
								$two_years_query = @mysql_query($two_years) or die(mysql_error());
								$two_years_query_result = mysql_fetch_array($two_years_query);

								$purge_data = "DELETE FROM work_hours
													WHERE clockin_time <=
													'$two_years_query_result[0]'";
								@mysql_query($purge_data) or die(mysql_query());
							} 
//echo $hours;
//exit;

							$hours_results = @mysql_query($hours) or die(mysql_error());

################################################################################
#                                  Table Start                                 #
################################################################################
						// This displays at the top of the table
						if(isset($_SESSION['uname']))
						{
							echo '<b>Displaying ' . $period . '</b>';	
						}
						// $i keeps track of the row colors.
						$i = 0;
						// $k is for a "do once" routine
						$k = 1;

/***************** OVERTIME STUFF HERE ****************************************/

						$tot_ot_hour   = 0;
						$tot_ot_minute = 0;
						$tot_ot_second = 0;
						$ot_hour       = 0;
						$ot_minute     = 0;
						$ot_second     = 0;

						$total_hour    = 0;
						$total_minute  = 0;
						$total_second  = 0;

################################################################################
#                        Begin Itterations of Body/Rows                        #
################################################################################

						while ($row = mysql_fetch_assoc($hours_results))
						{ 

							// Convert the row's minutes to 100 minute time to start with
							$row['reg_minute']  = convert_to_100($row['reg_minute']);
							$row['dt_minute']   = convert_to_100($row['dt_minute']);
							$row['vac_minute']  = convert_to_100($row['vac_minute']);
							$row['sick_minute'] = convert_to_100($row['sick_minute']);
							$row['hol_minute']  = convert_to_100($row['hol_minute']);
							$row['fun_minute']  = convert_to_100($row['fun_minute']);
							
							if($k) // Execute ONCE.
							{
/*
 * Note: This needs to change. The Over Time cycle needs to be synchronized with the
 * pay cycles. So overtime starts on Mondays at 00:01 am
 */
								// Get the current row's day of the week
								$day_of_week = date('w', strtotime($row['clockin_time']));

								// Get the current row's date/time stamp
								$date_of_row = $row['clockin_time'];
//echo '<font color=#EF0000 size=5>' . $day_of_week . '= day of week <br>' . $date_of_row . ' = date of row <br>';

								$date_of_row = substr_replace($date_of_row, '00:00:01', 11);
	
								/*
								 * Because Monday is the start of the overtime cycle,
								 * the pattern is odd. It goes like this:
								 * __DAY_OF_WEEK______SUBTRACT_THIS_NUMBER
								 * 		1						0
								 *			2						1
								 *			3						2
								 *			4						3
								 *			5						4
								 *			6						5	
								 *			0						6
								 *
								 * So the following if clause must be used to get last Monday:
								 */
								if($day_of_week == 0)
								{
									$query = "SELECT DATE_SUB('$date_of_row', INTERVAL 6 DAY)";
								}
								else
								{
									$day_of_week -= 1;
									$query = "SELECT DATE_SUB('$date_of_row', INTERVAL $day_of_week DAY)";
								}

								$query_result = @mysql_query($query)
													 or die (mysql_error());

								// $last_sunday[0] is last Sunday from first element in the array.
								$last_sunday = mysql_fetch_array($query_result);
//echo $last_sunday[0] . ' = last sunday<br>';
								// This will give us next Sunday by adding 7 days to $last_sunday[0]
								$day_of_week = "SELECT DATE_ADD('$last_sunday[0]', INTERVAL 7 DAY)";
								$query = @mysql_query($day_of_week)
											or die(mysql_error());

								// This is next sunday from the first element in the array.
								$next_sunday = mysql_fetch_array($query);
//echo $next_sunday[0] . ' = next sunday<br>';
								$k = NULL;
							}

							// If the entry was flagged
							if($row['flag'])
							{ 
								$errors = '<img src=\'images/redflag.gif\'>';
							}
							else
							{
								// If there is no error, set $error as a space. This is
								// because older Netscape browsers will not shade the cell if
								// there is nothing in it. 
								$errors = '&nbsp;';
							}

/********************************************************************************
 * Note: Vaction, Holiday, Sick, and Funeral hours do not count towards a       *
 * "Total Hours Worked", or towards Over Time. They are simply their own hours. *
 ********************************************************************************/
							list($regular_hour, $regular_min, $regular_sec) =
							explode(':',tot_hhmmss($row['reg_hour'],
														  $row['reg_minute'],
														  $row['reg_second']));
							list($doubletime_hour, $doubletime_min, $doubletime_sec) =
							explode(':',tot_hhmmss($row['dt_hour'],
														  $row['dt_minute'],
														  $row['dt_second']));

/******************************************************************************
 * NOTE: Upon reflecting on this, I just remembered that I switched paradigms *
 * somewhere along the line. I was going to have multiple entries per row.    *
 * But now I only let people have one type of hour on one row. No doubletime  *
 * on the same row  as a regular time. The admins can, however, do this       *
 * manually (right now) if they are so inclined. I would recommend against it *
 * and I will probably change this later. But just incase, I will leave the   *
 * following intact.                                                          *
 ******************************************************************************/
###############################################################################
#                              ROW TOTALS                                     #
###############################################################################

                    // Add up total hours for each __ROW__. Not a cumulative total.
                    // For cumulative total, see $tot_total_x variables at bottom.

                    // Add the row's reg_hour/minute/second to the row's
                    // dt_hour/minute/second to get a total. This is the
                    // equivalent of adding a number to zero and setting a
                    // variable to equal that.
                    $total_hour   = $regular_hour + $doubletime_hour;
                    $total_minute = $regular_min  + $doubletime_min;
                    $total_second = $regular_sec  + $doubletime_sec;

                    // If there is something in the NOTES column, print the image
                    $notes = NULL;
                    if(!(empty($row['notes'])))
                    {
                         $notes = '<img border=0 src=\'images/notes.png\'>';
                    }
                    // We will not asign a space to $notes. If it is empty, we
                    // will just print a space later.
################################################################################
#                              OVER TIME                                       #
################################################################################

                    // THIS WHOLE SECION IS FOR ADDING UP OVERTIME

                    // Innitialize our OT variables to NULL.
                    $print_ot_hour   = NULL;
                    $print_ot_minute = NULL;
                    $print_ot_second = NULL;

//echo $next_sunday[0] . ' >= ' . $row['clockin_time'] . ' >= ' . $last_sunday[0] . '<hr><br>';
                    // If the day in the itteration is between Sundays it gets added up:
                    if($row['clockin_time'] >= $last_sunday[0]
                    && $row['clockin_time'] <= $next_sunday[0])
                    {
//echo 'if is true = ' . $next_sunday[0] . ' >= ' . $row['clockin_time'] . ' >= ' . $last_sunday[0] . '<br>';
                         // The first one is guaranteed to go in here. If it's the
                         // first one, we've still got the two Sundays we grabbed
                         // from it earlier. After that, it's up to the conditional.

                         // So here, we're getting a total of hours worked to be later
                         // compared to 40, to see if it is less or greater than.
                         // If it is greater than, everything over 40 is OT.
                         $ot_hour   += $row['reg_hour']   + $row['dt_hour'];
                         $ot_minute += $row['reg_minute'] + $row['dt_minute'];
                         $ot_second += $row['reg_second'] + $row['dt_second'];
                    }
                    else
                    {
//echo 'else, ' . $next_sunday[0] . ' <= ' . $row['clockin_time'] . ' <= ' . $last_sunday[0] . '<br>  New Week<br>';                    

                         // If it doesn't fit in our old week, set the print
                         // variables, reset the ot variables,
                         // find the new week, and add up the new row to the ot totals.

                         // Since it is outside of the last week, we need to test our
                         // accumulated week's totals to see if they are over 40 hrs.

                         // Create a Spacer Row
                         echo '<tr CLASS=WEEK>';

                         echo '<td align=left><b>New Week</b></td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '</tr>';

                         // Hand off the variables to be tested and printed
                         $print_ot_hour   = $ot_hour;
                         $print_ot_minute = $ot_minute;
                         $print_ot_second = $ot_second;

                         // Ok, $ot_hour/minute/second is clear now, and can be
                         // reinitialized.
                         // Null the old OT variables
                         $ot_hour      = 0;
                         $ot_minute    = 0;
                         $ot_second    = 0;

                         // Not sure if this is used.
                         $last         = $last_sunday[0];
                         $next         = $next_sunday[0];

                         // Go get the current row's boundries.
                         $day_of_week  = date('w', strtotime($row['clockin_time'])) - 1;
                         $date_of_row  = $row['clockin_time'];
                         $date_of_row = substr_replace($date_of_row, '00:00:01', 11);
                         $query        = "SELECT DATE_SUB('$date_of_row', INTERVAL $day_of_week DAY)";
                         $query_result = @mysql_query($query)
                                         or die (mysql_error());

                         // $last_sunday[0] is last Sunday from first element in the array.
                         $last_sunday  = mysql_fetch_array($query_result);

                         // This will give us next Sunday
                         $day_of_week  = "SELECT DATE_ADD('$last_sunday[0]', INTERVAL 7 DAY)";
                         $query        = @mysql_query($day_of_week)
                                         or die(mysql_error());

                         // This is next sunday from the first element in the array.
                         $next_sunday = mysql_fetch_array($query);


                         $ot_hour   += $row['reg_hour']   + $row['dt_hour'];
                         $ot_minute += $row['reg_minute'] + $row['dt_minute'];
                         $ot_second += $row['reg_second'] + $row['dt_second'];

                    }

                    // Send $ot_hour/minute up to the function to be normalized
                    // This will only be something other than zero at the begging
                    // of every new week. This will also take care of reinitializing
                    // the $h, so it doesn't go into the next IF every itteration.
                    list($h, $m) = explode(':', tot_hhmmss($print_ot_hour,
                                                           $print_ot_minute));
                    // Now check for overtime. This can only happen at the beginning
                    // of every new week.
                    if ($h >= 40)
                    {

                         // Subtract 40 hours from the week's total,
                         // and voila - overtime.
                         $print_ot_hour   = $h - 40;

                                        // $m is already normalized from the above hhmmss()
                                        $print_ot_minute = $m;

                         // Overtime gets added to it's own grand total
                         $tot_ot_hour    += $print_ot_hour;
                         $tot_ot_minute  += $print_ot_minute;

                         // REG total = 40, or DT = 40 (capped)
                         // Then later on down, add it to the
                         // TOTAL REG total and print it at the end.
                    }
                    else
                    {
                         // If nothing over 40, then null the print_ot vars, so
                         // nothing gets printed later.
                         $print_ot_hour   = NULL;
                         $print_ot_minute = NULL;
                         $print_ot_second = NULL;
                    }


################################################################################
#                             STYLIZE ROWS                                     #
################################################################################

                    // Even rows will be grey, while odd rows white.
                    $row_color = ($i % 2);

                    // Choose row color from the style.css page.
                    // ODD is grey (#c0c0c0) while EVEN is white (#000000).
                    if ($row_color)
                    { // # 13.2
                         echo '<TR color = \'#000000\' class=ODD vAlign=top>';
                    } // Close # 13.2
                    else
                    { // # 13.3
                         echo '<TR color = \'#000000\' class=EVEN vAlign=top>';
                    } // Close # 13.3

                    // List the data across the table
################################################################################
#                                 Print Table Body                             #
################################################################################
                    // This will occur if there is any Over Time.
                    if(isset($print_ot_hour))
                    {
                         echo '<tr class=HEADER>';
                         echo '<td align=left><b>Over&nbsp;Time&nbsp;-&nbsp;Week&nbsp;Of:</b></td>';
                         echo '<td align=left>', date("Y-m-d", strtotime($last)),
                               "&nbsp;-&nbsp;", date("Y-m-d", strtotime($next)), '</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>'
                              . $print_ot_hour, ':', $print_ot_minute .
                              '</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '</tr>';

                         // Increment $i to change the row color. This needs to
                         // change in order to stay consistant with the other
                         // rows.
                         ++$i;

                         // Even rows will be grey, while odd rows white.
                         $row_color = ($i % 2);

                         // Choose row color. ODD is grey (#c0c0c0).
                         if ($row_color)
                         { // # 13.2
                              echo '<TR color=\'#000000\' class=ODD vAlign=top>';
                         } // Close # 13.2
                         else
                         { // # 13.3
                              echo '<TR color = \'#000000\' class=EVEN vAlign=top>';
                         } // Close # 13.3

                    } // End of IF OVERTIME

################################################################################
#                    PRINT HOURS FOR EACH ROW                                  #
################################################################################

                    // CLOCKIN TIME
                    echo '<td align=left>'
                         . $row['clockin_time'] .
                         '</td>';
                    // CLOCKOUT TIME
                    echo '<td align=left>'
                         . $row['clockout_time'] .
                         '</td>';
                    // REGULAR
                    echo '<td align=left>'
                         . tot_hhmmss($row['reg_hour'], $row['reg_minute']) .
                         '</td>';
                    // DOUBLE TIME
                    echo '<td align=left>'
                         . tot_hhmmss($row['dt_hour'], $row['dt_minute']) .
                         '</td>';
                    echo '<td align=left>&nbsp;</td>';
                    // VACATION
                    echo '<td align=left>'
                         . tot_hhmmss($row['vac_hour'], $row['vac_minute']) .
                         '</td>';
                    // SICK
                    // echo '<td align=left>'
                    echo '<td align=left>'
                         . tot_hhmmss($row['sick_hour'], $row['sick_minute']) .
                         '</td>';
                    // HOLIDAY
                    echo '<td align=left>'
                         . tot_hhmmss($row['hol_hour'], $row['hol_minute']) .
                         '</td>';
                    // FUNERAL
                    echo '<td align=left>'
                         . tot_hhmmss($row['fun_hour'], $row['fun_minute']) .
                         '</td>';

                    // Total is already computed. Don't need to send it into
                    // hhmmss(). Just echo it.
                    echo '<td align=left>' . $total_hour, ':';

                    // This will pad the minutes field in the totals. The minutes
                    // are automatically padded in convert_to_100, but it gets
                    // lost when we add reg and dt hours together to get the totals.
                    if($total_minute < 10)
                    {
                         $total_minute = '0' . $total_minute;
                    }
                    echo $total_minute;
                         '</td>';
                    echo '<td align=center>'
                         . $errors .
                         '</td>';
                    if($notes != null)
                    {
                         echo '<td align=center>
                              <a href="javascript:pop_window(\'http://'
                              . $_SERVER[HTTP_HOST] .
                              '/note.php?entry_id='
                              . $row['entry_id']
                              . '\')">'
                              . $notes
                              . '</a></td>';
                         echo '</tr>';
                    }
                    else
                    {
                         echo '<td align=center>
                               &nbsp;
                               </td>
                               </tr>';
                    }
################################################################################
#                       ADD UP TOTALS                                          #
################################################################################
                    // Here, all the hours are added to their respective totals,
                    // and to the GRAND TOTAL.
                    // Hours need to be converted before they are added.
                    // Otherwise, things just don't quite add up correctly.



                    list($vac_hour_convert, $vac_minute_convert,
                         $vac_second_convert) = explode(':',hhmmss($row['vac_hour'],
                                                                    $row['vac_minute'],
                                                                    $row['vac_second']));
                    $tot_vac_hour    += $vac_hour_convert;
                    $tot_vac_minute  += $vac_minute_convert;
                    $tot_vac_second  += $vac_second_convert;

                    list($sick_hour_convert, $sick_minute_convert,
                         $sick_second_convert) = explode (':',hhmmss($row['sick_hour'],
                                                                     $row['sick_minute'],
                                                                     $row['sick_second']));

                    $tot_sick_hour   += $sick_hour_convert;
                    $tot_sick_minute += $sick_minute_convert;
                    $tot_sick_second += $sick_second_convert;

                    list($hol_hour_convert, $hol_minute_convert,
                         $hol_second_convert) = explode (':',hhmmss($row['hol_hour'],
                                                                    $row['hol_minute'],
                                                                    $row['hol_second']));

                    $tot_hol_hour     += $hol_hour_convert;
                    $tot_hol_minute   += $hol_minute_convert;
                    $tot_hol_second   += $hol_second_convert;

                    list($fun_hour_convert, $fun_minute_convert,
                         $fun_second_convert) = explode (':',hhmmss($row['fun_hour'],
                                                                    $row['fun_minute'],
                                                                    $row['fun_second']));

                    $tot_fun_hour     += $fun_hour_convert;
                    $tot_fun_minutel  += $fun_hour_convert;
                    $tot_fun_second   += $fun_hour_convert;

                    // Here's the GRAND TOTAL
                    // IF print_ot_hour is set, then obviously there is overtime
                    // for the week.
################################################################################
#                         ADD UP GRAND TOTAL                                   #
################################################################################

                    $tot_total_hour   += $total_hour;
                    $tot_total_minute += $total_minute;
                    $tot_total_second += $total_second;

                                // Reset to NULL to avoid falsly repeating the flag
                    $errors = NULL;

                    // Increment $i to change the color of the next table
                    ++$i;
               } // Close # 8 (Main While)

// I think I have to do this to wrap up the last element, since it won't go through the loop again.
################################################################################
#                    DO ONE MORE OVERTIME CHECK BEFORE ENDING THE PAGE         #
################################################################################
                    $print_ot_hour   = $ot_hour;
                    $print_ot_minute = $ot_minute;
                    $print_ot_second = $ot_second;

                    list($h, $m) = explode(':', tot_hhmmss($print_ot_hour, $print_ot_minute));


                    // It's the end of the week. Check for overtime.
                    if ($h >= 40)
                    {
                         // Subtract 40 hours, and voila - overtime.
                         $print_ot_hour   = $h - 40;
                         $print_ot_minute = $m;
                         $print_ot_second = $s;
                         $tot_ot_hour    += $print_ot_hour;
                         $tot_ot_minute  += $print_ot_minute;
                         $tot_ot_second  += $print_ot_second;
                    }
                    else
                    {
                         $print_ot_hour   = NULL;
                         $print_ot_minute = NULL;
                         $print_ot_second = NULL;
                    }
                    if (isset($print_ot_hour))
                    {
                                        // print_ot_hour PART II
                         echo '<tr class=HEADER>';

                         echo '<td align=left><b>Over&nbsp;Time&nbsp;-&nbsp;Week&nbsp;Of:</b></td>';
                         echo '<td align=left>', date("Y-m-d", strtotime($last_sunday[0])),
                               "&nbsp;-&nbsp;", date("Y-m-d", strtotime($next_sunday[0])), '</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>'
                              . $print_ot_hour, ':', $print_ot_minute .
                              '</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '<td align=left>&nbsp;</td>';
                         echo '</tr>';

                    }
               mysql_free_result($hours_results);
               mysql_free_result($hours_array);
               ?>
               </tr>
               <tr class="EVEN">

               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
               <td align="left">&nbsp;</td>
               </tr>


<!------------------------------------------------------------------------------
|                           Bottom of Table - Totals                           |
------------------------------------------------------------------------------->

               <tr class="HEADER">
                    <td align="left"><b>TOTALS</b></td>
                    <td align="left">&nbsp;</td>
                         <?php
/***************** THIS BELOW IS EXPIEREMENTAL ********************************/

                         // This will remove the "DESC" specification at the end of
                         // the $hours mysql query, and it will order it in ASC,
                         // as is default. Then we can do some real math!
                         $hours = rtrim($hours, ' DESC');
                         $hours_result = @mysql_query($hours) or die(mysql_error());
                         $k = 1;
                         $ot_hour = 0;
                         $ot_minute = 0;
                         $ot_second = 0;
                         $do_once = 1;

                    while($row = @mysql_fetch_assoc($hours_result))
                    {
$row['reg_minute']  = convert_to_100($row['reg_minute']);
$row['dt_minute']   = convert_to_100($row['dt_minute']);
$row['vac_minute']  = convert_to_100($row['vac_minute']);
$row['sick_minute'] = convert_to_100($row['sick_minute']);
$row['hol_minute']  = convert_to_100($row['hol_minute']);
$row['fun_minute']  = convert_to_100($row['fun_minute']);
                         if($k) // Execute ONCE.
                         {
                              // Get the current row's day of the week
                              $day_of_week = date('w', strtotime($row['clockin_time']));

                              // Get the current row's date/time stamp
                              $date_of_row = $row['clockin_time'];
$date_of_row = substr_replace($date_of_row, '00:00:01', 11);
//echo 'Date of row = ' . $date_of_row . '<br>';
                              // Decrease the current row's day of the week by the $day_of_week
                              // number, there by getting us last Sunday.

								if($day_of_week == 0)
								{
									$query = "SELECT DATE_SUB('$date_of_row', INTERVAL 6 DAY)";
								}
								else
								{
									$day_of_week -= 1;
									$query = "SELECT DATE_SUB('$date_of_row', INTERVAL $day_of_week DAY)";
								}

						//				$query = "SELECT DATE_SUB('$date_of_row', INTERVAL $day_of_week DAY)";
  
  										$query_result = @mysql_query($query)
                                              or die (mysql_error());

                              // $last_sunday[0] is last Sunday from first element in the array.
                              $last_sunday = mysql_fetch_array($query_result);
//echo 'Last monday = ' . $last_sunday[0] . '<br>';
                              // This will give us next Sunday by adding 7 days to $last_sunday[0]
                              $day_of_week = "SELECT DATE_ADD('$last_sunday[0]', INTERVAL 7 DAY)";
                              $query = @mysql_query($day_of_week)
                                       or die(mysql_error());

                              // This is next sunday from the first element in the array.
                              $next_sunday = mysql_fetch_array($query);
//echo 'Next monday = ' . $next_sunday[0] . '<Br>';
                              // Ok, we're done with this conditional already. We just wanted to get
                              // the first and last sunday of the first row.
                              $k = NULL;
                         }

/*******************************************************************************
 * UPDATE: This cannot be done here. Reason: The query pulls from newest to    *
 * oldest. Therefore, if you worked one day regular hours and your total was   *
 * below 40, but then you worked the next day (same week) as DT and your total *
 * went above 40, the way it is now, it looks at it in reverse. So it sees the *
 * DT hour first, and then the REGs.                                           *
 *                                                                             *
 * SOLUTION: What I propose is, DT and REG hours will not be tallied in the    *
 * main while. They will get requerried again at the end and run in the        *
 * correct direction. Since VAC, HOL, SICK, and FUN hours do not count toward  *
 * OT, they can stay the same. The GRAND total is also fine. It is just for    *
 * REG and DT. Make Sense? Ok, here's the original plan that should still work *
 * but we'll do it at the end:                                                 *
 *                                                                             *
 * IDEA: While in the current week, add the current row to a total. Then check *
 * for overtime. If there is overtime, the current hour type gets capped, as   *
 * does every thing after it. So, when it gets to the number in question, the  *
 * one that tips it over, here's what we do:                                   *
 * 1.) Subtract 40 from the current overtime total. If it's 45 hours, you now  *
 *     have 5 hours. This is the $h variable.                                  *
 * 2.) Subtract that number from the current row's number. If the current row  *
 *     is 12 regular hours, it is now 7 regular hours. (Need to think about    *
 *     minutes here.                                                           *
 * 3.) Add the new row's total to the grand total for the column. (Add the 7   *
 *     regular hours to the row total.)                                        *
 * 4.) Cap any future hours and do not add them to the total. Only add them to *
 *     the Over Time total.                                                    *
 ******************************************************************************/
                    // If the day in the itteration is between Sundays it gets added up:
                    if($row['clockin_time'] >= $last_sunday[0]
                        && $row['clockin_time'] <= $next_sunday[0])
                    {

                         // The first one is guaranteed to go in here. If it's the
                         // first one, we've still got the two Sundays we grabbed
                         // from it earlier. After that, it's up to the conditional.

                         // So here, we're getting a total of hours worked to be later
                         // compared to 40, to see if it is less or greater than.
                         // If it is greater than, everything over 40 is OT.
                         $ot_hour   += $row['reg_hour']   + $row['dt_hour'];
                         $ot_minute += $row['reg_minute'] + $row['dt_minute'];
                         $ot_second += $row['reg_second'] + $row['dt_second'];
                    }
                    else
                    {

                         // If it doesn't fit in our old week, set the print
                         // variables, reset the ot variables,
                         // find the new week, and add up the new row to the ot totals.

                         // Since it is outside of the last week, we need to test our
                         // accumulated week's totals to see if they are over 40 hrs.

                         // Ok, $ot_hour/minute/second is clear now, and can be
                         // reinitialized.
                         // Null the old OT variables
                         $ot_hour     = 0;
                         $ot_minute   = 0;
                         $ot_second   = 0;
                         $stop_adding = NULL;
                         $do_once = 1;

                         // Not sure if this is used.
                         $last = $last_sunday[0];
                         $next = $next_sunday[0];

                         // Go get the current row's boundries.
                         $day_of_week = date('w', strtotime($row['clockin_time']));
                         $date_of_row = $row['clockin_time'];
$date_of_row = substr_replace($date_of_row, '00:00:01', 11);
								if($day_of_week == 0)
								{
									$query = "SELECT DATE_SUB('$date_of_row', INTERVAL 6 DAY)";
								}
								else
								{
									$day_of_week -= 1;
									$query = "SELECT DATE_SUB('$date_of_row', INTERVAL $day_of_week DAY)";
								}

//                         $query = "SELECT DATE_SUB('$date_of_row', INTERVAL $day_of_week DAY)";
                         $query_result = @mysql_query($query)
                                         or die (mysql_error());

                         // $last_sunday[0] is last Sunday from first element in the array.
                         $last_sunday = mysql_fetch_array($query_result);

                         // This will give us next Sunday
                         $day_of_week = "SELECT DATE_ADD('$last_sunday[0]', INTERVAL 7 DAY)";
                         $query = @mysql_query($day_of_week)
                                  or die(mysql_error());

                         // This is next sunday from the first element in the array.
                         $next_sunday = mysql_fetch_array($query);


                         $ot_hour   += $row['reg_hour']   + $row['dt_hour'];
                         $ot_minute += $row['reg_minute'] + $row['dt_minute'];
                         $ot_second += $row['reg_second'] + $row['dt_second'];
                   }
                    // IF in the week, add the shit
                    // Get week stuff, and all that junk.
                    list($h, $m, $s) = explode(':', tot_hhmmss($ot_hour, $ot_minute,
                                                                $ot_second));
                    // Now check for overtime. This can only happen at the beginning
                    // of every new week.
                    if($h >= 40 && $do_once == 1)
                    {
                         // Subtract 40 hours from the week's total,
                         // and voila - overtime.
                         $over_time_hour = $h - 40;
                         $over_time_min  = $m;
                         $over_time_sec  = $s;
                         $ot_hms = $over_time_hour . ':' . $over_time_min . ':' . $over_time_sec;
                         $row_reg_hms    = $row['reg_hour'] . ':' . $row['reg_minute'] . ':' .
                                           $row['reg_second'];

                         $row_dt_hms     = $row['dt_hour'] . ':' . $row['dt_minute'] . ':' .
                                           $row['dt_second'];

                                        // NEED A TIMEDIFF() FUNCTION HERE. HAS TO FIGURE IN MINUTES.
                         $row_reg_hour   = $row['reg_hour'];
                         $row_reg_minute = $row['reg_minute'];
                         $row_reg_second = $row['reg_second'];

                         // Here we check to see which one (Reg or DT) tipped it over.
                         // Since there can only be one entry per line, one is going to
                         // be all zeros. The other is the offender. Set that variable

                         if($row_reg_hour == 0 && $row_reg_minute == 0
                            && $row_reg_second == 0)
                         {
                              $cap_dt_diff  = DATEDIFF($ot_hms, $row_dt_hms);
                         }
                         else
                         {
                              $cap_reg_diff = DATEDIFF($ot_hms, $row_reg_hms);
                         }

                         // NOW WE HAVE THE DIFFERENCE. Step 2 complete.

                         // REG total = 40, or DT = 40 (capped)
                         // Then later on down, add it to the
                         // TOTAL REG total and print it at the end.
                    }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////// BELOW IS WHAT I WAS USING TO ADD UP THE TOTALS FOR REG AND DT ///////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // If the $cap_reg_diff is set, cap off the hours.
                    if(isset($cap_reg_diff))
                    {

                         list($reg_hour_convert, $reg_minute_convert, $reg_second_convert)
                              = explode(':',$cap_reg_diff);
                         list($reg_hour_convert, $reg_minute_convert, $reg_second_convert)
                              = explode(':',tot_hhmmss($reg_hour_convert, $reg_minute_convert));
                         $tot_reg_hour    += $reg_hour_convert;
                         $tot_reg_minute  += $reg_minute_convert;
                         $tot_reg_second  += $reg_second_convert;
                         $do_once          = NULL;
                         $cap_reg_diff     = NULL;
                         $stop_adding      = 1;

                    }

                    if(!isset($stop_adding))
                    {

                         list($reg_hour_convert, $reg_minute_convert,
                              $reg_second_convert) = explode (':',tot_hhmmss($row['reg_hour'],
                                                                         $row['reg_minute'],
                                                                         $row['reg_second']));

                         $tot_reg_hour    += $reg_hour_convert;
                         $tot_reg_minute  += $reg_minute_convert;
                         $tot_reg_second  += $reg_second_convert;
                    }


                    if(isset($cap_dt_diff))
                    {
                         list($dt_hour_convert, $dt_minute_convert, $dt_second_convert)
                             = explode(':',$cap_dt_diff);
                                        list($dt_hour_convert, $dt_minute_convert, $dt_second_convert)
                                            = explode(':',tot_hhmmss($dt_hour_convert, $dt_minute_convert));

                         $tot_dt_hour     += $dt_hour_convert;
                         $tot_dt_minute   += $dt_minute_convert;
                         $tot_dt_second   += $dt_minute_convert;


                         $do_once     = NULL;
                         $cap_dt_diff = NULL;
                         $stop_adding = 1;
                    }
                    if(!isset($stop_adding))
                    {
                         list($dt_hour_convert, $dt_minute_convert,
                              $dt_second_convert) = explode(':',tot_hhmmss($row['dt_hour'],
                                                                       $row['dt_minute'],
                                                                       $row['dt_second']));
                         $tot_dt_hour     += $dt_hour_convert;
                         $tot_dt_minute   += $dt_minute_convert;
                         $tot_dt_second   += $dt_minute_convert;

                    }

               } // End while

/************************* END EXPIEREMENT ************************************/

                                     // Normalize the totals first before
                                     // printing them.
                                     list($tot_reg_hour,   $tot_reg_minute)   =
                                          explode(':', tot_hhmmss($tot_reg_hour,   $tot_reg_minute));
                                     list($tot_dt_hour,    $tot_dt_minute)    =
                                          explode(':', tot_hhmmss($tot_dt_hour,    $tot_dt_minute));

                                     list($tot_ot_hour,    $tot_ot_minute)    =
                                          explode(':', tot_hhmmss($tot_ot_hour,    $tot_ot_minute));

                                     list($tot_vac_hour,   $tot_vac_minute)   =
                                          explode(':', tot_hhmmss($tot_vac_hour,   $tot_vac_minute));

                                     list($tot_sick_hour,  $tot_sick_minute)  =
                                          explode(':', tot_hhmmss($tot_sick_hour,  $tot_sick_minute));

                                     list($tot_hol_hour,   $tot_hol_minute)   =
                                          explode(':', tot_hhmmss($tot_hol_hour,   $tot_hol_minute));

                                     list($tot_fun_hour,   $tot_fun_minute)   =
                                          explode(':', tot_hhmmss($tot_fun_hour,   $tot_fun_hour));

                                     list($tot_total_hour, $tot_total_minute) =
                                          explode(':', tot_hhmmss($tot_total_hour, $tot_total_minute));

                                     // Print the totals with the minutes
                                     // converted to 100 min/hours.
                                     echo '<td align=left><b>' . $tot_reg_hour   . ':' . $tot_reg_minute
                                                                                 . '</b></td>';
                                     echo '<td align=left><b>' . $tot_dt_hour    . ':' . $tot_dt_minute
                                                                                 . '</b></td>';
                                     echo '<td align=left><b>' . $tot_ot_hour    . ':' . $tot_ot_minute
                                                                                 . '</b></td>';
                                     echo '<td align=left><b>' . $tot_vac_hour   . ':' . $tot_vac_minute
                                                                                 . '</b></td>';
                                     echo '<td align=left><b>' . $tot_sick_hour  . ':' . $tot_sick_minute
                                                                                 . '</b></td>';
                                     echo '<td align=left><b>' . $tot_hol_hour   . ':' . $tot_hol_minute
                                                                                 . '</b></td>';
                                     echo '<td align=left><b>' . $tot_fun_hour   . ':' . $tot_fun_minute
                                                                                 . '</b></td>';
                                     echo '<td align=left><b>' . $tot_total_hour . ':' . $tot_total_minute
                                                                                 .  '</b></td>';

                         ?>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
               </tr>

               <tr class="HEADER">
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
               </tr>
               <tr class="HEADER">
                    <?php
                         echo '<td align="left"><b>See COMMISSIONS:</b></td>';
                         echo '<td align=left>
                               <a href="javascript:pop_window(\'http://'
                               . $_SERVER[HTTP_HOST] .
                               '/commission.php\')">
                               <img width="10" hight="10" border="0" src="images/dollarsign.png">
                               </a>
                               </td>';
                    ?>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
               </tr>
                        <tr class="HEADER">
                                <td align="left">See
                                <a href="javascript:pop_window('http://<?php echo $_SERVER[HTTP_HOST]; ?>/who.php')">WHO</a> is currently clocked in.
                                </td>
                                <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                        </tr>
               </tbody>
               </table>
          </td>
     </tr>
</tbody>
</table>
<br />
<hr />
<!-- Include the footer. -->
<?php @include('templates/footer.inc'); ?>
