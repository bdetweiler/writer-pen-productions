<?php
/*
*        Script Title: Clockin\Out v. 1.0
*        Page Title: audit2.php
*        Author:  Brian Detweiler
*        Contact: bdetweiler@gmail.com
*
*        This is the main auditing page. Here, the Administrator or a Supervisor
*        may make corrections to a user's hours. If a user clocked in, and forgot
*        to clock out and got an error flag because they were clocked in for like,
*        72 hours, the supervisor may go in here and correct that entry. Along those
*        lines, the supervisor may also delete an entry completely.
*
*        One more very important and powerful feature (that I added at the last
*        minute) gives the administrator/supervisor the ability to completely
*        add in entries manually. This is for cases such as a user forgetting to
*        login at all during the day. Or if a user didn't have access to a computer.
*        This should be used with care. There's not a lot of error checking here, so
*        the boss could theoretically have the user clock in at 6 pm today, and clock
*        clock out at 6 am today. "Hey, time travel rocks!" Yeah, but not in this case.
*        But thankfully, if an admin does fubar this, it can easily be deleted.
*
*        Users are selected to begin with from the pre-audit page (audit.php), but
*        they can be changed on this page. The page got a little bloated and ugly,
*        but it has all the necessary features. Learn to love it!
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

require_once('../conn.inc.php');
session_start();
include('templates/pagenames.inc');

if(!(isset($_SESSION['isroot'])))
{ 
	header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
	exit();
} 


$admin_username     = $_SESSION['admin_username'];
$under_construction = FALSE;
$message            = NULL;

#################################################################################
#										FUNCTIONS													  #
#################################################################################

// input:  a string
// output: none
// return: a string
// notes: This function normalizes the input in the Username and Password fields.
function escape_data($data)
{ 
	if(ini_get('magic_quotes_gpc'))
	{ 
		$data = stripslashes($data);
	}
	return trim(mysql_real_escape_string($data));
} 

// input:  two date-time strings
// output: none
// return: a time string
// notes: This function takes two date-time strings and takes the difference
//        between the two. It returns the difference in time format. If the
//        time is 1 day or more, it is added up in hours.
function DATEDIFF( $date1, $date2 )
{
	$date1   = strtotime( "$date1" );
	$date2   = strtotime( "$date2" );
	$diff    = abs($date1-$date2);
	$seconds = 0;
	$hours   = 0;
	$minutes = 0;

	if($diff % 86400 > 0)
	{
		$rest = ($diff % 86400);
		$days = ($diff - $rest) / 86400;
		if($rest % 3600 > 0)
		{
			$rest1 = ($rest % 3600);
			$hours = ($rest - $rest1) / 3600;
			if($rest1 % 60 > 0)
			{
				$rest2   = ($rest1 % 60);
				$minutes = ($rest1 - $rest2) / 60;
				$seconds = $rest2;
			}
			else
			{
				$minutes = $rest1 / 60;
			}
		}
		else
		{
			$hours = $rest / 3600;
		}
	}
	else
	{
		$days = $diff / 86400;
	}
	$hours = ($days * 24) + $hours;
	$time  = $hours . ':' . $minutes . ':' . $seconds;
	return $time;
}

################################################################################
#                              Switch Users                                    #
################################################################################

if(isset($_POST['switch']))
{
	if ($_POST['user'] == "")
	{ 
		$message.= '<p>Please select a user.</p>';
	} 
	else
	{ 
		$_SESSION['uname'] = $_POST['user'];
		$u 					 = $_POST['user'];
	} 
}

################################################################################
#                            Delete An Entry                                   #
################################################################################

if(isset($_POST['delete']))
{ 
	$u = $_POST['u'];
	$original_clockin_delete = $_POST['original_clockin_delete'];
	if($original_clockin_delete[0] == "")
	{ 
		$message .= '<p>Please select a Clock In Time.</p>
						 <p>If you have selected a time, make sure you did not
						 select the "<-Clock In Time-> <-Clock Out Time->"
						 desciption field';
	} 
	else
	{
		$clockin_time = $_POST['original_clockin_delete'];
		for($i = 0; $row = $original_clockin_delete[$i]; $i++)
		{
			$delete_entry = "DELETE FROM work_hours
								  WHERE uname = '$u'
								  AND clockin_time = '$row'";
			$delete_entry_query = mysql_query($delete_entry)
										 or die(mysql_error());
			if($delete_entry_query)
			{ 
				$message .= 'Deletion of entry ' . $row . ' successful.<br>';
			} 
			else
			{
				$message .= 'Deletion failed for ' . $row . '.<br>';
			}
		}
	}
} 

################################################################################
#                             Correct An Entry                                 #
################################################################################

if(isset($_POST['correct']))
{ 
	$u = $_POST['u'];
	$clockin_box_correct = trim($_POST['clockin_box_correct']);
	$original_clockin_correct = $_POST['original_clockin_correct'];
	if ($original_clockin_correct == "")
	{
		$message .= 'Please select a Clock In Time.';
		$clockin  = NULL;
	} 
	else
	{
		if ($clockin_box_correct == '0000-00-00 00:00:00' || $clockin_box_correct == '')
		{ 
			$clockin = $_POST['original_clockin_correct'];
		}
		else
		{
			if(!ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})+[[:space:]]+([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", 
				      $clockin_box_correct))
			{ 
				$message .= '<p><i>Check your format. Use this format:</i>&nbsp;0000-00-00 00:00:00</p>';
				$clockin = NULL;
			}
			else
			{
				$clockin = $clockin_box_correct;
			}
		} 
		$clockout_box_correct = trim($_POST['clockout_box_correct']);
		if($clockout_box_correct == '0000-00-00 00:00:00' || $clockout_box_correct == '')
		{
			$clockout_time = "SELECT clockout_time
									FROM work_hours
									WHERE uname = '$u'
									AND clockin_time = '$original_clockin_correct'";
			$clockout_time_query = mysql_query($clockout_time);
			$clockout_time_query_result = mysql_fetch_array($clockout_time_query);
			$clockout = $clockout_time_query_result[0];
		}
		else
		{
			if(!ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})+[[:space:]]+([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", 
						 $clockout_box_correct))
			{ 
				$message .= '<p><i>Check your format. Use this format:</i>&nbsp;0000-00-00 00:00:00</p>';
				$clockout = NULL;
			}
			else
			{
				$clockout = $clockout_box_correct;
			}
		}
		if($clockin && $clockout)
		{
			$reg_hour    = 0;
			$reg_minute  = 0;
			$reg_second  = 0;
			$dt_hour     = 0;
			$dt_minute   = 0;
			$dt_second   = 0;
			$vac_hour    = 0;
			$vac_minute  = 0;
			$vac_second  = 0;
			$hol_hour    = 0;
			$hol_minute  = 0;
			$hol_second  = 0;
			$sick_hour   = 0;
			$sick_minute = 0;
			$sick_second = 0;
			$fun_hour    = 0;
			$fun_minute  = 0;
			$fun_second  = 0;
			
			// Get the hours worked between the two times.
			$diff = DATEDIFF("$clockout", "$clockin");
			list($h, $m, $s) = explode(":", $diff);

			$day_of_week = date('w', strtotime($clockin));
			// If the clock in day is Sunday
			if($day_of_week == 0)
			{
					  // If they were clocked in Sunday for more than 1 hour but less
					  // than 5, give them 4 hours of vacation
					  if($h >= 1 && $h < 5)
				  		{
							$vac_hour = 4;
					 	}
						// If it was more than 5 hours, give them 8 hours of vacation
						else if($h >= 5)
						{
						  	$vac_hour = 8;
				 		}
			}		
			switch($_POST['hours_correct'])
			{ 
				/*
				 * See what kind of hours they are entering
				 * The other hours will be set to 0, thereby overwriting
				 * the old entry. This eliminates the problem of having a
				 * duplicate hour type per line.
				 */
				case 'Regular':
					$reg_hour     = $h;
					$reg_minute   = $m;
					$reg_second   = $s;
					break;
				case 'Doubletime':
					$dt_hour      = $h;
					$dt_minute    = $m;
					$dt_second    = $s;
					break;
				case 'Vacation':
					$vac_hour     = $h;
					$vac_minute   = $m;
					$vac_second   = $s;
					break;
				case 'Holiday':
					$hol_hour     = $h;
					$hol_minute   = $m;
					$hol_second   = $s;
					break;
				case 'Sick':
					$sick_hour    = $h;
					$sick_minute  = $m;
					$sick_second  = $s;
					break;
				case 'Funeral';
   				$fun_hour     = $h;
   				$fun_minute   = $m;
   				$fun_second   = $s;
					break;
				default:
					$reg_hour     = $h;
					$reg_minute   = $m;
					$reg_second   = $s;
				break;
			} 
			$correct = "UPDATE work_hours
							SET clockin_time = '$clockin',
							clockout_time    = '$clockout',
							reg_hour         = $reg_hour,
							reg_minute       = $reg_minute,
							reg_second       = $reg_second,
							dt_hour          = $dt_hour,
							dt_minute        = $dt_minute,
							dt_second        = $dt_second,
							vac_hour         = $vac_hour,
							vac_minute       = $vac_minute,
							vac_second       = $vac_second,
							hol_hour         = $hol_hour,
							hol_minute       = $hol_minute,
							hol_second       = $hol_second,
							sick_hour        = $sick_hour,
							sick_minute      = $sick_minute,
							sick_second      = $sick_second,
							fun_hour         = $fun_hour,
							fun_minute       = $fun_minute,
							fun_second       = $fun_second";

			if($_POST['error'] == "flag")
			{
				$correct .= ", flag = 0";
			}
			$correct .= " WHERE uname = '$u'
							 AND clockin_time = '$original_clockin_correct'";
			$correct_query = mysql_query($correct)
								  or die(mysql_error());
			if($correct_query)
			{ 
				$message .= '<p>Your entry has been updated.</p>';
			} 
			else
			{ 
				$message .= '<p>Your entry was unsuccessful.</p>';
			}
		}
	} 
} 

################################################################################
#                Add an Entry From Scratch - Use with Caution!                 #
################################################################################

if(isset($_POST['add']))
{ 
	$u = $_POST['u'];
	$clockin_box_add = $_POST['clockin_box_add'];
	$clockout_box_add = $_POST['clockout_box_add'];

	// If the Clock In field is empty
	if ($clockin_box_add == "" || $clockin_box_add == '0000-00-00 00:00:00'
		|| $clockout_box_add == "" || $clockout_box_add == '0000-00-00 00:00:00')
	{ 
		$message .= 'You must enter both a Clock In time and a Clock Out time.';
		$clockin = NULL;
	} 
	else
	{
		if(!ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})+[[:space:]]+([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", 
					$clockin_box_add))
		{ 
			$message .= '<p><i>Check your format. Use this format:</i>&nbsp;0000-00-00 00:00:00</p>';
			$clockin  = NULL;
		}
		else
		{ 
			$clockin = $clockin_box_add;
		} 
		if(!ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})+[[:space:]]+([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", 
			$clockout_box_add))
		{ 
			$message .= '<p><i>Check your format. Use this format:</i>&nbsp;0000-00-00 00:00:00</p>';
			$clockout = NULL;
		} 
		else
		{
			$clockout = $clockout_box_add;
		} 
		if($clockin && $clockout)
		{
			// Get the hours worked between the two times.
			$diff 			  = DATEDIFF("$clockout", "$clockin");
			list($h, $m, $s) = explode(":", $diff);
				// For Sunday vacation purposes
				$vac_hour 	  = 0;
				$day_of_week  = date('w', strtotime($clockin));

				// If clockin time is on a Sunday
				if($day_of_week == 0)
				{
					// If they were clocked in Sunday for more than 1 hour but less
					// than 5, give them 4 hours of vacation
					if($h >= 1 && $h < 5)
			  		{
						$vac_hour = 4;
				 	}
					// If it was more than 5 hours, give them 8 hours of vacation
					else if($h >= 5)
					{
					  	$vac_hour = 8;
			 		}
				}	
			switch ($_POST['hours_add'])
			{ 
				case 'Regular':
					$correct = "INSERT INTO work_hours
									(uname, clockin_time, clockout_time, reg_hour, reg_minute, reg_second, vac_hour)
									VALUES('$u', '$clockin', '$clockout', $h, $m, $s, $vac_hour)";
					break;
				case 'Doubletime':
					$correct = "INSERT INTO work_hours
									(uname, clockin_time, clockout_time, dt_hour, dt_minute, dt_second, vac_hour)
									VALUES('$u', '$clockin', '$clockout', $h, $m, $s, $vac_hour)";
					break;
				case 'Vacation':
					$correct = "INSERT INTO work_hours
									(uname, clockin_time, clockout_time, vac_hour, vac_minute, vac_second)
									VALUES('$u', '$clockin', '$clockout', $h, $m, $s)";
					break;
				case 'Holiday':
					$correct = "INSERT INTO work_hours
									(uname, clockin_time, clockout_time, hol_hour, hol_minute, hol_second)
									VALUES('$u', '$clockin', '$clockout', $h, $m, $s)";
					break;
				case 'Sick':
					$correct = "INSERT INTO work_hours
									(uname, clockin_time, clockout_time, sick_hour, sick_minute, sick_second)
									VALUES('$u', '$clockin', '$clockout', $h, $m, $s)";
					break;
				case 'Funeral';
					$correct = "INSERT INTO work_hours
									(uname, clockin_time, clockout_time, fun_hour, fun_minute, fun_second)
									VALUES('$u', '$clockin', '$clockout', $h, $m, $s)";
					break;
				default:
					$correct = "INSERT INTO work_hours
									(uname, clockin_time, clockout_time, reg_hour, reg_minute, reg_second, vac_hour)
									VALUES('$u', '$clockin', '$clockout', $h, $m, $s, $vac_hour)";
					break;
			}
			$correct_query = mysql_query($correct)
								  or die(mysql_error());

			if($correct_query)
			{ 
				$message .= '<p>Your entry has been updated.</p>';
			}
			else
			{ 
				$message .= '<p>Your entry was unsuccessful.</p>';
			} 
		} 
	}
}

################################################################################
#                           ENTER TIME OFF                                     #
################################################################################

if(isset($_POST['submit']))
{
	$start_date = $_POST['start_date'];
	$end_date   = $_POST['end_date'];
	$message    = NULL;
	$fn         = "";
	$ln         = "";
	$title      = "";
	$u = $_POST['u'];

	$d 			= escape_data($start_date);
	$e 			= escape_data($end_date);

	if(empty($d))
	{
		$message .= "<p>Enter a date.</p>";
		$d   		 = NULL;
	}
	else
	{
		if(strlen($d) < 10)
		{
			$message .= "<p>Please enter a date in the format of
							 <i>YYYY-MM-DD</i> (If the month or day is a single digit,
							 pad the number with a zero.) Example:
							 2004-07-31</p>";
			$d 		 = NULL;
		}
		else
		{
			if(!ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $d, $regs))
			{
				$message .= "Invalid Start Date format. Please use <i>YYYY-MM-DD</i> format";
				$d        = NULL;
			}
			if(!empty($e))
			{
				if(!ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $e, $regs))
				{
					$message .= "Invalid End Date format. Please use
									 <i>YYYY-MM-DD</i> format";
					$e        = NULL;
					$d 		 = NULL;
				}
				else if(strtotime($e) < strtotime($d))
				{
					$message .= "Your End Date must be later than your Start Date.";
					$e 		 = NULL;
					$d 		 = NULL;
				}
			}
			else
			{
				$e = NULL;
			}
		}
	}

	// If everything entered was valid:
	if($d)
	{
		// Set the hour type:
		switch ($_POST['reason'])
		{
			case 'vacation':
				$type_hour   = 'vac_hour';
				$type_minute = 'vac_minute';
				$type_second = 'vac_second';
				$hour_type   = 'Vacation';
				break;
			case 'holiday':
				$type_hour   = 'hol_hour';
				$type_minute = 'hol_minute';
				$type_second = 'hol_second';
				$hour_type   = 'Personal Holiday';
				break;
			case 'sick':
				$type_hour   = 'sick_hour';
				$type_minute = 'sick_minute';
				$type_second = 'sick_second';
				$hour_type   = 'Sick';
				break;
			case 'funeral':
				$type_hour   = 'fun_hour';
				$type_minute = 'fun_minute';
				$type_second = 'fun_second';
				$hour_type   = 'Funeral';
				break;
			default:
				$message .= 'You may only use the available radiobuttons.';
				break;
		}

/*******************************************************************************
 * If they ask for a day off in the future,     										 *
 * then they clock in on that day, they will just have an overlap in time.     *
 * This could be problematic. The admins will have to keep an eye on this.     *
 *******************************************************************************/

		switch ($_POST['hours'])
		{
			case '4':
				$d_start    = $d . ' 00:00:00';
				if(isset($_POST['end_date']))
				{
					$d_end   = $_POST['end_date'] . ' 04:00:00';
				}
				else
				{
					$d_end   = $d . ' 04:00:00';
				}
				$hour_04_08 = '04';
				break;
			case '8':
				$d_start    = $d . ' 00:00:00';
				if(isset($_POST['end_date']))
				{
					$d_end   = $_POST['end_date'] . ' 08:00:00';
				}
				else
				{
					$d_end   = $d . ' 08:00:00';
				}
				$d_end      = $d . ' 08:00:00';
				$hour_04_08 = '08';
				break;
			default:
				$message 	= 'How did you get here?';
				break;
		}

		// If NO END date was set ($e == NULL), Just do a single date:
		if(!$e)
		{
			$query = "INSERT INTO work_hours
						 (uname, first_name, last_name, title,
						 clockin_time, clockout_time, $type_hour,
						 $type_minute, $type_second)
						 VALUES ('$u', '$fn', '$ln', '$title', '$d_start',
									'$d_end', '$hour_04_08', 00, 00)";
			$query_result = @mysql_query($query) or die('There was an error in the query.');
			if($query_result)
			{
				$message .= 'Your selection for ' . $hour_04_08 . ' hours
								 of ' . $hour_type . ' time for ' . $d_start . ' has
								 been recorded successfully.<br>';
			}
		}
		// If there was an END date set:
		else
		{
			list($start_yyyy, $start_mm, $start_dd) = explode('-', $d);
			list($end_yyyy, $end_mm, $end_dd) = explode('-', $e);

			// This will return the difference between the times in seconds
			$diff = abs(strtotime($d) - strtotime($e));

			// This will get the number of days so we know how many times to cycle
			$days = ($diff / 3600) / 24;

			// This will set the start date.
			$date_increment_start[0] = $d_start;
			$date_increment_end[0]   = $start_yyyy . '-' . $start_mm . '-'
												. $start_dd . ' ' . $hour_04_08 . ':00:00';
			for($i = 0; $i <= $days; ++$i)
			{
				// This will enter the dates requested day by day
				// Save dates in an array to print later as a confirmation
				$array_of_dates[$i]   = substr_replace($date_increment_start[0], '', 11);
				$query 				    = "INSERT INTO work_hours
											  (uname, first_name, last_name, title,
											  clockin_time, clockout_time, $type_hour,
											  $type_minute, $type_second)
											  VALUES ('$u', '$fn', '$ln', '$title',
														 '$date_increment_start[0]',
														 '$date_increment_end[0]',
														 '$hour_04_08', 00, 00)";
				$query_result 		    = @mysql_query($query)
											   or die('There was an error in the query.');
			
				// MySQL's DATE_ADD will account for months, years, and leap years
				$date_increment       = "SELECT DATE_ADD('$date_increment_start[0]', INTERVAL 1 DAY)";
				$date_increment_get   = @mysql_query($date_increment)
											   or die('There was an error in the query.');
				$date_increment_start = @mysql_fetch_array($date_increment_get);
				$date_increment       = "SELECT DATE_ADD('$date_increment_end[0]', INTERVAL 1 DAY)";
				$date_increment_get   = @mysql_query($date_increment)
												or die('There was an error in the query.');
				$date_increment_end   = @mysql_fetch_array($date_increment_get);
			}
			if($query_result)
			{
				$message .= 'Your selection for ' . $hour_04_08 . ' hours
								 of ' . $hour_type . ' time for<br>';
				for($i = 0; $array_of_dates[$i] != null; ++$i)
				{
					$message .= $array_of_dates[$i] . '<br>';
				}
				$message .= ' has been recorded successfully.<br>';
			}
		}
	}
}

###############################################################################
#           DISPLAY            MAIN               PAGE                        #
###############################################################################

$page_title = "Administrator - Audit an Account";
include('templates/header.inc');
include('templates/pagenames.inc');

$u				= $_SESSION['uname'];

	if(isset($message))
	{ 
		echo '<font color = "#ef0000">', $message, '</font>';
	} 
	if($under_construction)
	{
		echo '<br><font color = "#ef0000"><big><strong>UNDER
				CONSTRUCTION</strong></big></font><br>';
	}
?>

<table width="100%" border="0">
	<tr align="right">
		<td>
			&nbsp;
		</td>
		<td align="right">
			<?php
				$page = 'audit';
				$access_level = 'admin';
				include ('templates/tabs.inc');
			?>
		</td>
	</tr>
</table>

<fieldset>
	<legend>
		<b>
			Audit an Account
		</b>
	</legend>
	<?php
########################################################################
#           Delete An Entry / Select a New User                        #
########################################################################
	?>
	<table width="100%" border="0" cellpadding="5">
		<tr>
			<td>
				<b>
					Delete an Entry:
				</b>
				<font color="#ef0000">
					<br />To delete multiple entries, hold down the ctrl key
					<br />while you click, or hold the shift key to select
					<br />multiple entries in a row.
          	</font>
			</td>
			<td>
				<b>
					Current User:
				</b>
				<big><strong>
	 				<?php 
						echo $u 
					?>
				</big></strong>
			</td>
		</tr>
		<tr>
			<td align="left">
				<form action="<?php echo 'http://' . $_SERVER[HTTP_HOST] . '/audit2.php'; ?>" method="post">
					
					<?php // Possible security concearn here: ?>
					<input type="hidden" name="u" value="<?php echo $u ?>">
					<select name="original_clockin_delete[]" multiple size=5>
					<option value="">
						<-Clock In Time->
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<-Clock Out Time->
					</option>
					<?php
						$limit 		 = "SELECT DATE_SUB(NOW(), INTERVAL 1 YEAR)";
						$limit_query = @mysql_query($limit)
											or die(mysql_error());
						$limit_query_result = mysql_fetch_array($limit_query);

						$get_clockin_time = "SELECT clockin_time,
													clockout_time, flag
													FROM work_hours
													WHERE uname = '$u'
													AND clockin_time >= '$limit_query_result[0]'
													AND clocked_in = 0
													ORDER BY clockin_time DESC";
						$get_clockin_time_query = @mysql_query($get_clockin_time)
														  or die(mysql_error());
						while($row = mysql_fetch_array($get_clockin_time_query))
						{ 
							// If there is a flag, make it more noticable
							if($row[2] == 1)
							{
								echo '<option value="' . $row[0] .
									  '">' . $row[0] . '&nbsp;&nbsp;&nbsp;&nbsp;' .
									  $row[1] . ' ***</option>';
							}
							else
							{
								echo '<option value="' . $row[0] . '">' .
										$row[0] . '&nbsp;&nbsp;&nbsp;&nbsp;' .
										$row[1] . '</option>';
							}
						}
					?>
				</select>
				<br />
				<br />
				<input type="submit" name="delete" value="Delete">
			</td>
			<?php
############################################################################
#                        Switch Users                                      #
############################################################################
			?>
			<td>
				<select name="user">
					<option value="">
						<-Select New User->
					</option>
					<?php
						$get_user = "SELECT uname
										 FROM user_pass
										 WHERE uname != '$admin_username'";
						$get_user_query = @mysql_query($get_user)
												or die(mysql_error());
						while($row = mysql_fetch_array($get_user_query))
						{
							echo '<option value="' . $row[0] . '">' . $row[0] . '</option>';
						} 
					?>
				</select>
               &nbsp;&nbsp;&nbsp;&nbsp;
               <input type="submit" name="switch" value="Switch Users">
          </td>
		</tr>
	</table>
	<?php
###########################################################################
#                    Correct An Entry												  #
###########################################################################
	?>
	<hr />
	<table width="100%" border="0">
		<tr>
			<td align="left">
				<b>
					Or:
				</b>
			</td>
			<td align="left">

			</td>
		</tr>
		<tr>
			<td>
				<b>
					Correct an Entry:
				</b>
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
		<tr>
			<td align="left">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<b>
					1.
				</b>
				&nbsp;Select the entry to fix:
			</td>
			<td>
				<form action="<?php echo 'http://' . $_SERVER[HTTP_HOST] . '/audit2.php'; ?>" method="post">
				<input type="hidden" name="u" value="<?php echo $u ?>">
				<select name="original_clockin_correct">
					<option value="">
						<-Clock In Time->
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<-Clock Out Time->
					</option>
					<?php
						$limit 		 		  = "SELECT DATE_SUB(NOW(), INTERVAL 1 YEAR)";
						$limit_query		  = @mysql_query($limit)
													 or die(mysql_error());
						$limit_query_result = mysql_fetch_array($limit_query);
						$get_clockin  		  = "SELECT clockin_time, clockout_time, flag
													  FROM work_hours
													  WHERE uname = '$u'
													  AND clockin_time >= '$limit_query_result[0]'
													  AND clocked_in = 0
													  ORDER BY clockin_time DESC";
						$get_clockin_query  = @mysql_query($get_clockin)
													 or die(mysql_error());
						while($row = mysql_fetch_array($get_clockin_query))
						{ 
							if($row[2] == 1)
							{
								echo '<option value="' . $row[0] . '">'
										. $row[0] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'  
										. $row[1] . ' ***</option>';
							}
							else
							{
								echo '<option value="' . $row[0] . '">'
								. $row[0] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' 
								. $row[1] .  '</option>';
							}
						} 
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				<b>
					2.
				</b>
				&nbsp;
				<font color="#ef0000">
					*
				</font>
				Enter a 
				<b>
					Clock In
				</b> 
				Date and Time, or leave blank to use existing Date and Time:
			</td>
			<td>
				<input type="text" name="clockin_box_correct" size="19" maxlength="19" value="0000-00-00 00:00:00">
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				<b>
					3.
				</b>
				&nbsp;
				<font color="#ef0000">
					*
				</font>
				Enter a 
				<b>
					Clock Out
				</b> 
				Date and Time, or leave blank to leave existing Date and Time unchanged:
			</td>
			<td>
				<input type="text" name="clockout_box_correct" size="19" maxlength="19" value="0000-00-00 00:00:00"></p>
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				<b>
					4.
				</b>
				&nbsp;
				Select type of hours:
			</td>
			<td align="left">
				<b>
					<table border="0">
						<tr>
							<td align="right">
								Regular
							</td>
							<td>
								<input type="radio" name="hours_correct" value="Regular" checked="checked">
							</td>
							<td align="right">
								Double Time
							</td>
							<td>
								<input type="radio" name="hours_correct" value="DoubleTime">
							</td>
						</tr>
						<tr>
							<td>
								<img src="<?php echo 'http://' . $_SERVER[HTTP_HOST] .
								'/images/redflag.gif'; ?>"></img>
									Remove Error Flag
							</td>
							<td>
								<input type="checkbox" name="error" value="flag">
							</td>
						</tr>
					</table>
				</b>
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" name="correct" value="Correct">
			</td>
			<td>
				<font color ="#ef0000" size="2"> 
					*Enter time in 24-Hour
					("Military") format.
					<br />
					Use this format:
					<br /> 
					YYYY-MM-DD HH:MM:SS
					<br /> 
					Example: 2004-04-09 13:30:00
				</font>
         </td>
		</tr>
	</table>
	<hr />
	<?php
#######################################################################################
#                              Add a New Entry                                        #
#######################################################################################
	?>
	<table width="100%" border="0">
		<tr>
			<td align="left">
				<b>
					Or:
				</b>
			</td>
			<td align="left">
			</td>
		</tr>
		<tr>
			<td>
				<b>
					Add an Entry From Scratch:
				</b>
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				<b>
					1.
				</b>
				&nbsp;
				<font color="#ef0000">
					*
				</font>
				Enter a 
				<b>
					Clock In
				</b> 
				Date and Time:
			</td>
			<td>
				<form action="<?php echo 'http://' . $_SERVER[HTTP_HOST] . '/audit2.php'; ?>" method="post">
					<input type="text" name="clockin_box_add" size="19" maxlength="19" value="0000-00-00 00:00:00">
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				<b>
					2.
				</b>
				&nbsp;
				<font color="#ef0000">
					*
				</font>
				Enter a 
				<b>
					Clock Out
				</b> 
				Date and Time, or leave blank to leave existing Date and Time unchanged:
			</td>
		<td>
			<input type="text" name="clockout_box_add" size="19" maxlength="19" value="0000-00-00 00:00:00"></p>
		</td>
	</tr>
	<tr>
		<td align="left">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
			<b>
				3.
			</b>
			&nbsp;
			Select type of hours:
		</td>
		<td align="right">
			<b>
				<table border="0">
					<tr>
						<td align="right">
							Regular
						</td>
						<td>
							<input type="radio" name="hours_add" value="Regular" checked="checked">
						</td>
						<td align="right">
							Double Time
						</td>
						<td>
							<input type="radio" name="hours_add" value="DoubleTime">
						</td>
					</tr>
				</table>
			</b>
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit" name="add" value="Add Entry">
		</td>
		<td>
			<font color ="#ef0000" size="2"> 
				*Enter time in 24-Hour
				("Military") format.<br />Use this format:<br /> YYYY-MM-DD HH:MM:SS
			<br />
		  	Example: 2004-04-09 13:30:00</font>
		</td>
	</tr>
</table>

<form action="<?php echo 'http://' . $_SERVER[HTTP_HOST] . '/audit2.php'; ?>" method="post">
	<hr />
	<legend>
		<b>
			Enter Time Off
		</b>
	</legend>
	<table width="100%">
		<tr>
			<td>
				<p>
					<b>
						Start Date
					</b>
				</p>
				<input type="text" name="start_date" maxlength="10">
				<br />
				<i>
					YYYY-MM-DD
				</i>
				<br />
				<p>
					<b>
						End Date
					</b>
				</p>
				<input type="text" name="end_date" maxlength="10">
				<br />
				<i>
					YYYY-MM-DD
				</i>
				<br />
				<br />
				<table>
					<tr>
						<td>
							<fieldset>
								<legend>
									<b>
										Reason
									</b>
								</legend>
								<table width="25" border="0">
									<tr>
										<td>
											Vacation
										</td>
										<td>
											<input type="radio" name="reason" value="vacation" checked="checked">
										</td>
									</tr>
									<tr>
										<td>
											Personal Holiday
										</td>
										<td>
											<input type="radio" name="reason" value="holiday">
										</td>
									</tr>
									<tr>
										<td>
											Sick
										</td>
										<td>
											<input type="radio" name="reason" value="sick">
										</td>
									</tr>
									<tr>
										<td>
											Funeral
										</td>
										<td>
											<input type="radio" name="reason" value="funeral">
										</td>
									</tr>
								</table>
							</fieldset>
						</td>
					</tr>
				</table>
				<p>
					4 Hours
					<input type="radio" name="hours" value="4" checked="checked">
					&nbsp; &nbsp;
					8 Hours<input type="radio" name="hours" value="8">
				</p>
				<input type="submit" name="submit" value="Submit">
			</td>
		</tr>
	</table>
</form>
</fieldset>
<?php
	include('templates/footer.inc');
?>
