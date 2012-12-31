<?php
/*
*        Script Title: Clockin\Out v. 1.0
*        Page Title: log_time_off.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*	    This page allows employees to request time off. The concept is 
*	    simple (seemingly): Employee enters a date in proper date format
*	    (YYYY-MM-DD). Employee selects a reason from the radio button list:
*	    Vacation, Holiday, Sick, or Funeral. Employee selects full day or
*	    half day (4 hours or 8 hours). Employee hits submit.
*
*	    Script checks validity of the date they entered using RegEx. If 
*	    they screwed up, an error is printed and nothing is done. If they
*	    weren't too dumb, their request for time off is inserted into the 
*	    work_hours database as time served. It will not, IIRC, show up in  
*	    their work hours statement until after that day has passed. Rock? Rock.
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

session_start(); // Start the session
@include('../conn.inc.php');

// If no session is present, redirect the user
if (!(isset($_SESSION['first_name'])))
{ // # 1

     // Redirect them back to the login page.
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
     exit();
} // Close # 1

// Set the page title and include the HTML header.
$page_title = "Aurora Cooperative - Log Time Off";
include ('templates/header.inc');

// input:  a string
// output: none
// return: a string
// notes: This function normalizes the input in the Username and Password fields.
function escape_data($data)
{ // # 1.1

     if (ini_get('magic_quotes_gpc'))
     { // # 1.1.1
          $data = stripslashes($data);
     } // Close # 1.1.1
     return trim(mysql_real_escape_string($data));
} // Close # 1.1

$u = $_SESSION['username'];
$under_construction = TRUE;

// If they pressed the SUBMIT button:
if (isset($_POST['submit']))
{
     // Innitialize variables (self explanatory)
     $start_date    = $_POST['start_date'];
	$end_date	     = $_POST['end_date'];
     $message       = NULL;
     $fn            = $_SESSION['first_name'];
     $ln            = $_SESSION['last_name'];
     $title         = $_SESSION['title'];

     // Normalize the data:
     $d = escape_data($start_date);
     $e = escape_data($end_date);

     // Check if the Comments field is empty.
     if(empty($d))
     {

          $message .= "<p>Enter a date.</p>";
          $d  = NULL;
     }
	
     // If it's not empty:
     else
     {

          // If the length of the data is less than 10:
          if (strlen($d) < 10)
          {
               $message .= "<p>Please enter a date in the format of
			    <i>YYYY-MM-DD</i> (If the month or day is a single digit,
							   pad the number with a zero.) Example:
			    2004-07-31</p>";
               $d = NULL;
          }

          // If we get here, so far so good.
          else
          {

		     // If they typed it in the correct syntax:
               if (!ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $d, $regs))
               {

                    $message .= "Invalid Start Date format. Please use <i>YYYY-MM-DD</i> format";
                    $d = NULL;
               }
		     
			
			/* I commented this out per request. It was meant to keep
			 * lusers from entering a vacation time on a day when they
			 * actually clocked in, but I guess it really doesn't make that
			 * much sense. They could always take a day off in the future
			 * and clock in on that day. fsck it. 	
			else if(strtotime($d) <= strtotime(' now '))
			{
			 	$message .= "You entered a day in the past. You must ask
				             your supervisor to do this for you.";
				$d = NULL;
			}				
			*/
              
		    
			if(!empty($e))
			{
                    if (!ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $e, $regs))
				{
					$message .= "Invalid End Date format. Please use
					    <i>YYYY-MM-DD</i> format";
					$e = NULL;
					$d = NULL;
				}
				else if(strtotime($e) < strtotime($d))
				{
				     $message .= "Your End Date must be later than your Start Date.";
					$e = NULL;
					$d = NULL;
				}

			}
			else
			{
			     $e = NULL;

			}


          }

     }
    
     // If everything entered was valid:	
     if ($d)
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
 * When this is done, it just adds the hours. The only security check is that  *
 * it can not be in the past. But if they ask for a day off in the future,     *
 * then they clock in on that day, they will just have an overlap in time.     *
 * This could be problematic. The admins will have to keep an eye on this.     * 
 *******************************************************************************/

          switch ($_POST['hours'])
          {
               // Set the type of hours 
		     case '4':
                    
			     $d_start    = $d . ' 00:00:00';
		          
				// If the End Date is set, assign it:
				if(isset($_POST['end_date']))
				{
				     $d_end = $_POST['end_date'] . ' 04:00:00';
				}
				else
				{
				     $d_end = $d . ' 04:00:00'; 
				}
				$hour_04_08 = '04';
               break;
               case '8':
                    $d_start    = $d . ' 00:00:00';

				// If the End Date is set, assign it:
				if(isset($_POST['end_date']))
				{
				     $d_end = $_POST['end_date'] . ' 08:00:00';
				}
				else
				{
				     $d_end = $d . ' 08:00:00';
				}		
                    $d_end      = $d . ' 08:00:00';
				$hour_04_08 = '08';
               break;
               
			default:
                    $message = 'How did you get here?';
               break;
          }
		
		// If NO END date was set ($e == NULL):		    	 
		if(!$e)
		{
		     // Just do a single date
               $query = "INSERT INTO work_hours
                         (uname, first_name, last_name, title,
	   		          clockin_time, clockout_time, $type_hour,
                         $type_minute, $type_second)
                         VALUES ('$u', '$fn', '$ln', '$title', '$d_start',
				    		   '$d_end', '$hour_04_08', 00, 00)";
               
			$query_result = @mysql_query($query) or die('There was an error in
										          the query.');
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

			// This will get the number of days so we know how many times
			// to cycle
			$days = ($diff / 3600) / 24;
		
			// This will set the start date.	
			$date_increment_start[0] = $d_start; 
		     $date_increment_end[0]   = $start_yyyy . '-' . $start_mm . '-'
			    . $start_dd . ' ' . $hour_04_08 . ':00:00';
			    
			for($i = 0; $i <= $days; ++$i)
			{
                    // This will enter the dates requested day by day... by
				// day by day by day. By day. 
			     $query = "INSERT INTO work_hours
                              (uname, first_name, last_name, title,
	   		               clockin_time, clockout_time, $type_hour,
                              $type_minute, $type_second)
                              VALUES ('$u', '$fn', '$ln', '$title',
							   '$date_increment_start[0]',
				    		        '$date_increment_end[0]', '$hour_04_08', 00, 00)";
                   $query_result = @mysql_query($query) 
			                     or die('There was an error in the query.'); 

		 				
			     // This has to increment days, but also has to account for
				// months and years. What's a good way to do this?
			     // Why, with MySQL's DATE_ADD() of course! It will take
				// months, and years, and leap years into account for us!
				// Woohoo! Less work for the lazy programmer!
				// Get the begin day increment
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
				    of ' . $hour_type . ' time for ' . $d_start . ' to ' .
				    $d_end . ' has been recorded successfully.<br>'; 
			}
				   
		}
     }
}
if (isset($message))
{ // # 2
     // They are seeing this message because they have messed up somewhere along the line.
     echo '<font color="#ef0000">', $message, '</font>';
} // Close # 2

if($under_construction)
{
     echo '<br><font color="#ef0000"><big><strong>UNDER
	    CONSTRUCTION</big></strong></font><br>';
}
?>

<!-- Navigation Tabs -->
<table width="100%" border="0">
     <tr>
          <td align="right">
               <?php
                    $access_level = 'user';
                    $page = 'logtimeoff';
                    @include ('templates/tabs.inc');
               ?>
          </td>
     </tr>
</table>


<form action="<?php echo 'http://' . $_SERVER[HTTP_HOST] . '/log_time_off.php'; ?>" method="post">

<!-- The nice border around our login data starts here -->
<fieldset><legend><b>Log Time Off</b></legend>
<table width="100%">
     <tr>
          <td>
               <p><b>Start Date</b></p>
               <input type="text" name="start_date" maxlength="10">
               <br /><i>YYYY-MM-DD</i><br />
              
		     <p><b>End Date</b></p>
			<input type="text" name="end_date"	maxlength="10">
			<br /><i>YYYY-MM-DD</i><br /><br />

               <table>
                    <tr>
                         <td>
                              <fieldset>
			                    <legend>
			                         <b>Reason</b>
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
               4 Hours<input type="radio" name="hours" value="4" checked="checked">
               &nbsp; &nbsp;
               8 Hours<input type="radio" name="hours" value="8">
               </p>
               <input type="submit" name="submit" value="Submit">
 
         	</table>
     </fieldset>
</form>

<!-- Include the footer -->
<?php @include "templates/footer.inc" ?>
