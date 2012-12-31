<?php
/*
*        Script Title: Clockin/Out v 1.0
*        Page Title: login.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*	    This is another big page. This page does a lot of checks and 
*	    writing. When the user clicks "Submit", their information is
*	    immediately checked and, if applicable, entered into the db. 
*
*	    The big checks here are for checking if the hours were over
*	    18, or if they were already clocked in our out. Variables
*	    are set and then passed to loggedin.php to tell it how to
*	    respond to the request. 
*
*	    This page, combined with loggedin.php, is a resource hog. 
*	    The ammount of checks it has to do makes the wait time
*	    longer than any of the other scripts, though it depends
*	    on how much info it has to retreive from the db. 
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


// Remember to do @mysql_query when all is working well

// Maximum amount of hours a user may remain clocked in. Default is 18.
define ('MAX_CLOCKIN_TIME', 18);



// If the Submit button has been pressed:
if (isset($_POST['submit']))
{ // # 1
     require_once('../conn.inc.php');
     session_start();


################################################################################
#                                  FUNCTIONS                                   #
################################################################################

function log_action($u, $action, $query)
{
/*
   // WILL LEAVE THIS FOR NOW. MAY BE NEEDED IN THE FUTURE

	if($u == 'glinnc' || $u == 'carlestromt' || $u == 'snobergerj' || $u == 'ericksond' || $u == 'detweilerb')
	{
		$content  = $u 	  . '     ';
		$content .= $action . '     ';
		$content .= $query  . "\n";

		$filename = './Report/login.log';
		if (is_writable($filename)) 
		{
  		 	// in our example we're opening $filename in append mode.
  		 	// the file pointer is at the bottom of the file hence
  		 	// that's where $somecontent will go when we fwrite() it.
  		 	if (!$handle = fopen($filename, 'a')) 
			{
  	      	//echo "cannot open file ($filename)";
  	      	exit;
  		 	}
  		 	// write $somecontent to our opened file.
  		 	if (fwrite($handle, $content) === false) 
			{
  		    	// echo "cannot write to file ($filename)";
  		     	exit;
  		 	}
			  	//echo "success, wrote ($content) to file ($filename)";
	  		fclose($handle);
		} 
		else 
		{
  		 	echo "the file $filename is not writable";
			exit;
		}
	}
*/
}


     // input:  two date-time strings
     // output: none
     // return: a time string
     // notes: This function takes two date-time strings and takes the difference
     //        between the two. It returns the difference in time format. If the
     //        time is 1 day or more, it is added up in hours.
     function DATEDIFF( $date1, $date2 ) /////////////////////////////////
     {
          $date1 = strtotime( "$date1" );
          $date2 = strtotime( "$date2" );
          $diff = abs($date1-$date2);
          $seconds = 0;
          $hours  = 0;
          $minutes = 0;

          if($diff % 86400 > 0)
          {
               $rest = ($diff % 86400);
               $days = ($diff - $rest) / 86400;
               if( $rest % 3600 > 0 )
               {
                    $rest1 = ($rest % 3600);
                    $hours = ($rest - $rest1) / 3600;

                    if( $rest1 % 60 > 0 )
                    {
                         $rest2 = ($rest1 % 60);
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
          $time = $hours . ':' . $minutes . ':' . $seconds;
          return $time;
     }
     // input:  a string
     // output: none
     // return: a string
     // notes: This function normalizes the input in the Username and Password fields.
     function escape_data($data) ////////////////////////////////////////////
     { // # 1.1

          // This is in conn.inc.php
          global $dbc;
          if (ini_get('magic_quotes_gpc'))
          { // # 1.1.1
               $data = stripslashes($data);
          } // Close # 1.1.1
          return trim(mysql_real_escape_string($data));
     } // Close # 1.1

     // input:  A string
     // output: none
     // return: an integer of value 1 or 0
     // notes: This function checks to see if they are already logged in with "Double Time".
     //        It does not matter if they check "Double Time" or not.
     function doubletime_check($u) //////////////////////////////////////////
     { // # 1.2
          $query1 = "SELECT clocked_in
                     FROM work_hours
                     WHERE uname = '$u'
                     AND clocked_in = 1
                     AND doubletime = 1";
          $query1_result = @mysql_query($query1)
                           or die (mysql_error());
          $query1_result_array = mysql_fetch_array($query1_result);
          return $query1_result_array[0]; // Returns 1 or 0 (double time or not)

     } // Close # 1.2

     // input:  an integer (1 for Double Time, 0 For Regular Time)
     // output: none
     // return: none
     // notes: This function clocks the user in either as Double Time or Regular Time
     function clock_in($u, $fn, $ln, $title, $dt) ////////////////////////////
     { // # 1.3
          $query2 = "INSERT INTO work_hours (uname, first_name, last_name, title,
                                            clockin_time, clocked_in, doubletime)
                     VALUES ('$u', '$fn', '$ln', '$title', NOW(), 1, '$dt')";
log_action($u, 'clock_in', $query2);
          // Remember to do @mysql_query when all is working well
          $query2_result = @mysql_query($query2) or die(mysql_error());
     } // Close # 1.3

     // input:  A string
     // output: none
     // return: an integer of value 1 or 0
     // notes: This function checks to see if they are clocked in already. 1 means
     //        they were clocked in, 0 means they were not clocked in.
     function clocked_in_check($u) ///////////////////////////////////////////
     { // # 1.5
          $query3 = "SELECT clocked_in
                     FROM work_hours
                     WHERE uname = '$u' 
				 AND clocked_in = 1";
          
		// Returns FALSE on failed execution, and a Resource id # on success.
          // Does not matter how many rows were returned.
          $query3_result = @mysql_query($query3) or die (mysql_error());
          
		// Analyzes the Resource ID # and turns it into either NULL or data.
          return mysql_num_rows($query3_result);

     } // Close # 1.5

     // input:  none
     // output: none
     // return: a two digit integer
     // notes: This function checks to see if they were clocked in overnight.
     function over18($u) ///////////////////////////////////////////////////
     { // # 1.6
          // Query to see if they've been clocked in past 18 hours.
          // This query will always find at least something, because
          // the user is already clocked in when the function is called.
          $query4 = "SELECT clockin_time
                     FROM work_hours
                     WHERE uname = '$u' 
				 AND clocked_in = 1";
//echo $query4 . '<br>';
          $query4_result = @mysql_query($query4)
                           or die(mysql_error());
          $query4_result_array = mysql_fetch_array($query4_result);

          $diff = DATEDIFF("now", "$query4_result_array[0]");
          global $inhour;
          global $inminute;
          global $insecond;
          // Break down the time into hours, minutes, and seconds to compare the hour only.
          list($inhour, $inminute, $insecond) = explode(':',$diff);
          return $inhour;
     } // Close # 1.6

     // input:  6 strings and one integer
     // output: none
     // return: none
     // notes:  Here's the big one: This function does all the main logging. It
     //         clocks the user out either in Double Time or Regular Hours,
     //         depending on what is passed to the function. Valid arguements are either:
     //         {dt_hour, dt_minute, dt_second} or {reg_hour, reg_minute, reg_second}
     //         This is the only place anything is entered into the database, except for
     //         than the clock_in() function.
     function clock_out($u, $hours = 'reg_hour', $minutes = 'reg_minute',
                         $seconds = 'reg_seconds', $flag = 0) //////////////
     { // # 1.7
	  		// For vacation time on Sundays
			$vacation_hours = 0;
          $query4 = "SELECT clockin_time
                     FROM work_hours
                     WHERE uname = '$u' 
				 			AND clocked_in = 1";
          $query4_result = @mysql_query($query4)
                           or die(mysql_error());
          $query4_result_array = mysql_fetch_array($query4_result);

          $diff = DATEDIFF("now", "$query4_result_array[0]");
          // Break down the time into hours, minutes, and seconds to compare the hour only.
          list($inhour, $inminute, $insecond) = explode(':',$diff);

			$day_of_week = date('w', strtotime($query4_result_array[0]));
			// If they clocked in on Sunday
			if($day_of_week == 0)
			{
					  // If they were clocked in Sunday for more than 1 hour but less
					  // than 5, give them 4 hours of vacation
					  if($inhour >= 1 && $inhour < 5)
				  		{
							$vacation_hours = 4;
					 	}
						// If it was more than 5 hours, give them 8 hours of vacation
						else if($inhour >= 5)
						{
						  	$vacation_hours = 8;
				 		}
			}			
          // Clock them out with D/T or Regular hours and set an error flag in this entry if needed.

          $query5 = "UPDATE work_hours
                     SET clockout_time = NOW(), 
								 $hours = $inhour, 
								 $minutes = $inminute,
                         $seconds = $insecond, 
								 clocked_in = 0, 
								 flag = $flag,
							    vac_hour = $vacation_hours
                     WHERE uname = '$u' 
				 AND clocked_in = 1";
log_action($u, 'clock_out', $query5);	
          $query5_result = @mysql_query($query5)
                           or die(mysql_error());
     } // Close # 1.7


################################################################################
#                              NORMALIZING ROUTINES                            #
################################################################################


     // Innitialize session variables
     $_SESSION['already_clocked_in'] = 0;
     $_SESSION['already_clocked_out'] = 0;
     $_SESSION['clocking_in'] = 0;
     $_SESSION['clocking_out'] = 0;
     $_SESSION['overnight'] = 0;
     $_SESSION['expired_pass'] = 0;
     $_SESSION['doubletime'] = 0;

	  // This is for Sunday vacation hours
	  $vacation_hours = 0;

     // Innitialize $message which is the error message string
     $message = NULL;

     // Make sure USERNAME is not null
     if (empty($_POST['username']))
     { // # 1.8
          $u = FALSE;
          $message .= '<p>You forgot to enter your username!</p>';
     } // Close # 1.8
     else
     { // # 1.9
          $u = escape_data($_POST['username']);
          global $u;
          $_SESSION['username'] = $u;
     } // Close # 1.9
     // Make sure PASSWORD is not null
     if (empty($_POST['password']))
     { // # 1.10
          $p = FALSE;
          $message .= '<p>You forgot to enter your password!<p>';
     } // Close # 1.10
     else
     { // # 1.11
          $p = escape_data($_POST['password']);
          $_SESSION['password'] = $p;
     } // Close # 1.11

     // If the USERNAME and PASSWORD are both not blank
     if ($u && $p)
     { // # 1.12

          // Retrieve USERNAME and PASSWORD from the database
          $query6 = "SELECT first_name, last_name, title
                     FROM user_pass
                     WHERE uname = '$u' 
				 AND pass = PASSWORD('$p')";
          $query6_result = @mysql_query($query6)
                           or die(mysql_error());
	// If we found a match:
          if (mysql_num_rows($query6_result) == 1)
          { // # 1.12.1
               // If the user's pass is the same as his username, they will
               // be required to change it when they log in.
               if ($u == $p)
               { // # 1.12.1.1
                    $_SESSION['expired_pass'] = 1;
               } // # 1.12.1.1

               // Assign session variables to later insert into the database
               $row1 = mysql_fetch_assoc($query6_result);
	       if (isset($row1['first_name']))
	       {
                    $_SESSION['first_name'] = $row1['first_name'];
 	       }
	       else
	       {
  	            $_SESSION['first_name'] = $u;
	       }	
               $_SESSION['last_name'] = $row1['last_name'];
               $_SESSION['title'] = $row1['title'];
               $_SESSION['username'] = $_POST['username'];
               $fn = $_SESSION['first_name'];
               $ln = $_SESSION['last_name'];
               $title = $_SESSION['title'];
               // Determine if user has checked the "Double Time" box
               if ($_POST['doubletime'])
               { // # 1.12.1.2
                    $_SESSION['doubletime'] = 1;
               } // Close # 1.12.1.2

               // Determine which button the user checked: Clock In, Clock Out, or Neither
               // If they chose something other than "Neither":
               if (!($_POST['clock'] == "Neither"))
               { // # 1.12.1.3

###############################################################################
#                              CLOCK IN                                       #
###############################################################################

                    // User wants to CLOCK IN. Sounds good.
                    if ($_POST['clock'] == "Clock In")
                    { // # 1.12.1.3.1
                         
			 // If they are already clocked in:
                         if (clocked_in_check($u) == 1)
                         { // # 1.12.1.3.1.1


                              // If $inhour (clockin hour) is greater than MAX_CLOCKIN_TIME (18):
                              if (over18($u) > MAX_CLOCKIN_TIME)
                              { // # 1.12.1.3.1.1.1

                                   // If they were clocked in with double_time:
                                   if (doubletime_check($u) == 1)
                                   {  # 1.12.1.3.1.1.1.1
                                 // They appear to have left themselves clocked in over night
                                        // We will flag this record, clock them out with the current time,
                                        // and change the clocked_in status to 0, thereby closing out this row.
                                        clock_out($u, 'dt_hour', 'dt_minute', 'dt_second', 1);

                                        // Now we will clock them in as they had intended
                                        clock_in($u, $fn, $ln, $title, $_SESSION['doubletime']);

                                        // Set this to 1 to let us know they have been clocked in
                                        $_SESSION['overnight'] = 1;
                                        $_SESSION['already_clocked_in'] = 1;
                                        $_SESSION['clocking_in'] = 1;

                                        // Now we forward them to the login page.
                                        header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                                        exit();
                                   } // Close # 1.12.1.3.1.1.1.1

                                   // Not clocking in with double time.
                                   else
                                   { // # 1.12.1.3.1.1.1.2

                                        // They appear to have left themselves clocked in over night
                                        // We will flag this record, clock them out with the current time,
                                        // and change the clocked_in status to 0, thereby closing out this row.
                                        clock_out($u, 'reg_hour', 'reg_minute', 'reg_second', 1);

                                        // Now we will clock them in as they had intended
                                        clock_in($u, $fn, $ln, $title, $_SESSION['doubletime']);

                                        // Set this to 1 to let us know they have been clocked in
                                        $_SESSION['overnight'] = 1;
                                        $_SESSION['already_clocked_in'] = 1;
                                        $_SESSION['clocking_in'] = 1;

                                        // Now we forward them to the login page.
                                        header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                                        exit();
                                   } // Close # 1.12.1.3.1.1.1.2

                              } // # Close 1.12.1.3.1.1.1
                              else
                              { // # 1.12.1.3.1.1.2
                                   // The user is already clocked in, but thankfully not more than 18 hours.
                                   // Log them in (without clocking in) and tell them.

                                   // This variable is set to one because they were already clocked in.
                                   // This way, we know to tell them about their error when they log in.
                                   $_SESSION['already_clocked_in'] = 1;
			       	   header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                                   exit();
                              } // Close # 1.12.1.3.1.1.2
                         } // # Close 1.12.1.3.1.1

                         else
                         { // # 1.12.1.3.1.2
                              // User is not clocked in and is trying to clock in.
                              // Good. Clock them in and start their day.
                              // Clock them in with double time or not.
                                   clock_in($u, $fn, $ln, $title, $_SESSION['doubletime']);
                                   $_SESSION['clocking_in'] = 1;
                                   header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                                   exit();
                         } // Close # 1.12.1.3.1.2
                    } // Close # 1.12.1.3.1

###############################################################################
#                              CLOCK OUT                                      #
###############################################################################

                    else
                    { // # 1.12.1.3.2

                         // The user has opted to clock out.
                         // Let's see if they're already clocked out.

                         if (!clocked_in_check($u))
                         { // # 1.12.1.3.2.1

                              // The user is already clocked out.
                              // Send them on their way without doing any db modification.
                              // This variable is set to one because they were already clocked out.
                              // This way, we know to tell them about their error when they log in.
                              $_SESSION['already_clocked_out'] = 1;
                              header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                              exit();
                         } // Close # 1.12.1.3.2.1
                         else
                         { // # 1.12.1.3.2.2

                              // So they want to clock out and they're actually clocked in, eh?
                              // But just how long have they been clocked in? Would you say,
                              // 1 million hours? How about 18 hours?
                              // Call the function and see if it's over 18 hours.

                              if(over18($u) > MAX_CLOCKIN_TIME)
                              { // # 1.12.1.3.2.2.1
						  
                                   // They appear to have left themselves clocked in over night.
                                   // Did they check double time when they logged in?
                                   if(doubletime_check($u))
                                   { // # 1.12.1.3.2.2.1.1

                                        // We will flag this record, clock them out with the current time,
                                        // and change the clocked_in status to 0, thereby closing out this row.
                                        clock_out($u, 'dt_hour', 'dt_minute', 'dt_second', 1, $vacation_hours);

                                        // This variable is to let the user know that they have been flagged
                                        $_SESSION['overnight'] = 1;

                                        // Set this to 1 to let us know they have been clocked out
                                        $_SESSION['clocking_out'] = 1;

                                        // Now we forward them to the login page.
                                        header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                                        exit();
                                   } // Close # 1.12.1.3.2.2.1.1
                                   else
                                   { // # 1.12.1.3.2.2.1.2

                                        // We will flag this record, clock them out with the current time,
                                        // and change the clockd_in status to 0, thereby closing out this row.
                                        clock_out($u, 'reg_hour', 'reg_minute', 'reg_second', 1);
                                        // This variable is to let the user know that they have been flagged
                                        $_SESSION['overnight'] = 1;

                                        // Set this to 1 to let us know they have been clocked out
                                        $_SESSION['clocking_out'] = 1;

                                        // Now we forward them to the login page.
                                        header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                                        exit();
                                   } // Close # 1.12.1.3.2.2.1.2
                              } // Close # 1.12.1.3.2.2.1
                              else
                              { // # 1.12.1.3.2.2.2

                                   // The user is clocked in, not past 18 hours, and wants to clock out.
                                   // Fine. Let's do that. The "if" that this "else" belongs to called
                                   // the function over18(), and therefore set the globals we need.

                                   if(doubletime_check($u))
                                   { // # 1.12.1.3.2.2.2.1
                                        // They originally elected double time.
                                        clock_out($u, 'dt_hour', 'dt_minute', 'dt_second', 0);
                                        // Set this to 1 to let us know they have been clocked out
                                        $_SESSION['clocking_out'] = 1;

                                        // Now we forward them to the login page.
                                        header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                                        exit();
                                   } // Close # 1.12.1.3.2.2.2.1
                                   else
                                   { // # 1.12.1.3.2.2.2.2
                                        // They did not elect double time.
                                        clock_out($u, 'reg_hour', 'reg_minute', 'reg_second', 0);
                                        // Set this to 1 to let us know they have been clocked out
                                        $_SESSION['clocking_out'] = 1;

                                        // Now we forward them to the login page.
                                        header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                                        exit();
                                   } // Close # 1.12.1.3.2.2.2.2
                              } // Close # 1.12.1.3.2.2.2
                         } // Close # 1.12.1.3.2.2
                    } // Close # 1.12.1.3.2
               } // Close # 1.12.1.3

###############################################################################
#                                 NEITHER                                     #
###############################################################################
               else
               { // # 1.12.1.4
                    // The user has chosen that they neither want to clock in or out
                    // and just want to proceed to their account. Not so fast, though.
                    // They still have to go through the checks.
			     
			     // This checks to see if they are currently clocked in.
                    $query7 = "SELECT clockin_time
                               FROM work_hours
                               WHERE uname = '$u' 
						 AND clocked_in = 1";
                    $query7_result = @mysql_query($query7)
                                     or die(mysql_error());
                    $row2 = mysql_num_rows($query7_result);
                    
				// If they are not currently clocked in, they don't need
				// to be checked. Send them on their way.
			     if (!$row2)
                    { // # 1.12.1.4.1
                         // If we got here, there were no entries in the db to
                         // check for this user. This most likely means it is
					// their first time clocking in. Forward them on.
                         header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                         exit();
                    } // Close # 1.12.1.4.1

				// If they got here, then they are currently clocked in.
				// See if it is over 18 hours.
                    else
                    { // # 1.12.1.4.2
                         
				     // If they ARE clocked in over 18 hours:
				     if (over18($u) > MAX_CLOCKIN_TIME)
                         { // # 1.12.1.4.2.1
// Check to see if they clocked in on a Sunday,
// and give them 8 hours of vacation time.
					     // They appear to have left themselves clocked in over night.
                              // They are trying to sneak by, eh? They have been clocked
                              // in past 18 hours, so they will be logged out before continuing.

					     // See if they are clocked in as Double Time:
						if (doubletime_check($u) == 1)
                              { // # 1.12.1.4.2.1.1
                              	$flag        = 1;     
							$hour_type   = 'dt_hour';
							$minute_type = 'dt_minute';
							$second_type = 'dt_second';	
                              } // Close # 1.12.1.4.2.1.1

                              // Else, they are just REG hours: 
						else
                              { // # 1.12.1.4.2.1.2
						     $flag        = 1;
							$hour_type   = 'reg_hour';
							$minute_type = 'reg_minute';
							$second_type = 'reg_second';
                              } // Close # 1.12.1.4.2.1.2
						
                              // Clock them out with either REG or DT hours:
						clock_out($u, $hour_type, $minute_type,
								$second_type, $flag);
						// This variable is set to let the user know that they have been flagged.
                              $_SESSION['overnight'] = 1;
                              
						// Now we forward them to the login page.
                              header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                              exit();
                         } // # Close 1.12.1.4.2.1

                         else
                         { // # 1.12.1.4.2.2
                              
					     // User has not been clocked in past 18 hours. Everything is gravy.
                              // Send 'em on.
                              header("Location: http://" . $_SERVER[HTTP_HOST] . "/loggedin.php");
                              exit();
                         } // Close # 1.12.1.4.2.2
                    } // Close # 1.12.1.4.2
               } // Close #1.12.1.4
          } // Close # 1.12.1
          else
          { // # 1.12.2

               // If the user name and passwords don't match, tell them.
               $message = '<p>The username and password entered do not
                           match those on file.</p>';
          } // Close # 1.12.2
          mysql_close(); // Close the MySQL connection after we're done.
     } // Close # 1.12
     else
     { // # 1.13

          // If they messed up along the way by not entering something,
          // store their mistakes in a variable and print it later.
          $message .= '<p>Please try again.</p>';
     } // Close # 1.13
} // Close # 1

// The title of the page will be Login, and sent into header.inc,
// which is declared next.

$page_title = 'Aurora Cooperative - Login';
@include ('templates/header.inc');

// Error messages printed here
if (isset($message))
{ // # 2
     // They are seeing this message because they have messed up somewhere along the line.
     echo '<font color="ef0000">', $message, '</font>';
} // Close # 2



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional/EN"
"http://www.w3.org/TR/2000/REC-xhtml-20000126/DTD.xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<!-- THIS IS THE FORM WHICH WILL BE DISPLAYED WHEN USER IS NOT LOGGED IN -->
<form action="<?php echo ("http://" . $_SERVER[HTTP_HOST] . "/login.php"); ?>" method="post">

<div align=right>Administrators Click <a href="http://<?php echo
$_SERVER[HTTP_HOST]; ?>/admin.php"><b>HERE</b></a>
<!-- The nice border around our login data starts here -->
<fieldset><legend><b>Sign In</b></legend>
<br />

<!-- Begin the table -->
<table width="100%">
<cellpadding="5">

<!-- Username field -->
<tr align="left">
     <td>
          <b>
          Username:&nbsp;
          <input type="text" name="username" maxlength="20">
          </b>
     </td>
</tr>

<td>
<!-- This is here for formating purposes -->
</td>

<!-- Here's the password recommendations -->
<th rowspan="3">
     <font color="#ef0000" size="2">
     <b>
     <i>
         Username and Password are Case Sensitive.
     <br />Passwords can be all letters, all numbers, special characters,
     <br />or a combination of any of these.
     <br />Username consists of your last name and first initial.
     <br />Passwords should be 6 to 15 characters long.
     </i>
     </b>
     </font>
</th>

<!-- Password field -->
<tr align="left">
     <td>
     <b>
     Password:&nbsp;&nbsp;
     <input type="password" name="password" maxlength="15">
     </b>
     </td>
</tr>

<!-- Radio buttons -->
<tr align="left">
     <td>
          <b>
          Clock In<input type="radio" name="clock" value="Clock In">
               &nbsp; &nbsp;
          Clock Out<input type="radio" name="clock" value="Clock Out">
               &nbsp; &nbsp;
          Neither<input type="radio" name="clock" value="Neither" checked="checked">
          </b>
     </td>
</tr>

<br />
<tr align="left">
     <td>
     <b>
     Double Time<input type="checkbox" name="doubletime" value="Doubletime">
     </b>
     </td>

</tr>

<!-- Submit button -->
<tr align="left">
     <td>
     <br />
     <input type="submit" name="submit" value="Submit">
     </td>
</tr>
</table>
<br />
<div align="right">
Having trouble? Click <a href="http://<?php echo
$_SERVER[HTTP_HOST]; ?>/help.html">HERE</a>
for Help.
</div>
</fieldset>

<!-- Include the footer -->
<?php @include "templates/footer.inc" ?>
</form>
