<?php
/*
*        Script Title: Clockin/Out v 1.0
*        Page Title: admin_page.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
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

// Start the session
@include ('../conn.inc.php');
session_start();

// input:  two date-time strings
// output: none
// return: a time string
// notes: This function takes two date-time strings and takes the difference
//        between the two. It returns the difference in time format. If the
//        time is 1 day or more, it is added up in hours.
function DATEDIFF($date1, $date2) /////////////////////////////////
{
// THIS FUNCTION WORKS!!!!

     // Explode the number we just sent in here
     list($hours_date1, $minutes_date1, $seconds_date1) = explode(':',$date1);
     list($hours_date2, $minutes_date2, $seconds_date2) = explode(':',$date2);

        // Minutes are currently in 100 min/hour format.

     // Now, add those minutes to the converted hours (which are now in minute
     // format) and we get a total time in minutes.
     $date1   = ($hours_date1 * 100) + $minutes_date1;
     $date2   = ($hours_date2 * 100) + $minutes_date2;

     $diff    = abs($date1 - $date2);


     // Get Hours
     $hours   = floor($diff / 100);

     // This gives us the remainder (minutes)
     $minutes = $diff % 100;

     // Set it up all nice and pretty
     $time = $hours . ':' . $minutes;

     return $time;
}

function convert_to_100($min)
{
     // Convert 60 minute hours to 100 minute hours
     // The way the conversion chart does it is fucking weird. Therefore we have
     // to do this dumb shit.

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

     // Return the minutes
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
     // Return the newly converted minutes.
     return $min;

}

// input:  3 integers
// output: none
// return: a string
// notes:  This function takes the standard (60 min/hour) hours minutes and
//         seconds, and turns them into 100 minute/hour cumulative time.
function hhmmss($hour, $min, $sec = 0) //////////////////////////////////////////
{
     // Oops. Need to truncate seconds. Soz.
     // Otherwise, you'll get all these funky "off by a few minutes" errors.
     // We all know those are no fun. Ergo, seconds go into a black hole.
     // I just realized this, so that's why I didn't just leave it out.
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
function tot_hhmmss($tot_hour, $tot_min) //////////////////////////
{
     // This will normalize the grand total
     // Use this after you have hhmmss()'d the hours, and added them to a
     // total. Then send the total in here. It will just normalize it to be
     // printed.

     // How many hours are in the acrued minutes
     $tot_hour = floor(($tot_hour + ($tot_min / 100)));

     // How many of those acrued minutes were minutes?
     $tot_min = ($tot_min % 100);

     if(strlen($tot_min) < 2)
     {
          $tot_min = '0' . $tot_min;
     }
     $tot_hms = $tot_hour . ":" . $tot_min;

     return $tot_hms;
}
################################################################################
#                   END FUNCTIONS / BEGIN PAGE                                 #
################################################################################

//$id = $_GET['id'];
$id = 'NEW';

// If no session is present, or the user is not root, redirect the user
if ($_SESSION['isroot'] != 1)
{ // # 2

     // Redirect them back to the login page.
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
     //echo $_SESSION['isroot'] . " IS ROOT";

     exit();
} // Close # 2

// If their password is the same as their username, we need to forward
// them to the change password page. Keeping on top of security!
if ($_SESSION['expired_pass'] == 1)
{ // # 3

     // Redirect them to the change password page.
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/changeadminpasswd.php");
     exit();
} // Close # 3

// Set the page title and include the HTML header.
$page_title = "Aurora Cooperative - Administrator";
@include ('templates/header.inc');

// Set first name variable
//$fn = $_SESSION['first_name'];

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

<table cellSpacing="0" borderColorDark="c0c0c0" cellPadding="0" width="100%"
       borderColorLight="ffffff" border="1">
<tbody>
     <tr>
          <td>
               <table id="hours" cellSpacing="0" cellPadding="0" width="100%" border="0"
                         name="hours">
               <tbody>



               <?php
               @include('templates/reporttableheader.inc');

               $person = "SELECT uname, first_name, last_name
                          FROM user_pass
                          WHERE 1
                          ORDER BY uname";
               $person_query = mysql_query($person);

               $row_color = 0;
/*******************************************************************************
* NOTE: This feature utilizes hardcoded dates in the database based on the     *
* bi-weekly pay cycle. If any of the bi-weekly dates have changed, the dates   *
* in the database will need to change. This can be done by hardcoding a valid  *
* date (pick a paycycle start date in the past - HINT - Paycycles start at     *
* 12:01 AM Monday) into the pay_cycle table, and then running                  *
* create_pay_cycle.php. It is currently valid for 25 years.                    *
*******************************************************************************/
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
                         // This grabs the paycycle before last (used for "OLD")
                         $before_last_cycle = "SELECT cycle_date
                                               FROM pay_cycle
                                               WHERE cycle_date <= NOW()
                                               ORDER BY entry_id DESC
                                               LIMIT 2, 1";
                         $before_last_cycle_query = @mysql_query($before_last_cycle) or die(error());
                         $before_last_cycle_query_result = mysql_fetch_array($before_last_cycle_query);



               if($id != 'old')
               {
                    $header = 'Company-Wide Summary of Hours for Pay Period <b>' . $last_cycle_query_result[0]
                            . '</b> Through <b>' . $this_cycle_query_result[0] . '</b>';
                    echo $header;
                    $cycle = $last_cycle_query_result[0] . ' - ' . $this_cycle_query_result[0];
                    $filename = './Report/new_report.csv';
               }
               else
               {

                    $header = 'Company-Wide Summary of Hours for Pay Period <b>' . $before_last_cycle_query_result[0]
                            . '</b> Through <b>' . $last_cycle_query_result[0] . '</b>';
                    echo $header;
                    $cycle = $before_last_cycle_query_result[0] . ' - ' . $last_cycle_query_result[0];
                    $filename = './Report/old_report.csv';
               }

// This will stick the header in the CSV.

$cycle .= "\nLast Name,First Name,Regular Hours,Double Time Hours,Over Time Hours,"
       . "Vacation Time,Sick Time,Holiday Time,Funeral Time,Commissions,Total Hours Worked\n";
// Let's make sure the file exists and is writable first.
if (is_writable($filename))
{

   // In our example we're opening $filename in append mode.
   // The file pointer is at the bottom of the file hence
   // that's where $somecontent will go when we fwrite() it.
   if (!$handle = fopen($filename, 'w'))
   {
         //echo "Cannot open file ($filename)";
         exit;
   }

   // Write $somecontent to our opened file.
   if (fwrite($handle, $cycle) === FALSE)
   {
       // maybe stick this in a log or something
       //echo "Cannot write to file ($filename)";
       exit;
   }

   fclose($handle);

}
else
{
   // Maybe stick this in a log or something
  // echo "The file $filename is not writable";
  // of course if the original file isn't writeable, what makes
  // me think a log file will be? 
}
               // Innitialize the GRAND TOTALS (the company totals) to zero
               $GRAND_tot_reg_hour      = 0;
               $GRAND_tot_reg_minute    = 0;
               $GRAND_tot_dt_hour       = 0;
               $GRAND_tot_dt_minute     = 0;

               $GRAND_tot_ot_hour       = 0;
               $GRAND_tot_ot_minute     = 0;

               $GRAND_tot_vac_hour      = 0;
               $GRAND_tot_vac_minute    = 0;
               $GRAND_tot_hol_hour      = 0;
               $GRAND_tot_hol_minute    = 0;
               $GRAND_tot_sick_hour     = 0;
               $GRAND_tot_sick_minute   = 0;
               $GRAND_tot_fun_hour      = 0;
               $GRAND_tot_fun_minute    = 0;
               $GRAND_tot_commission    = 0;
               $GRAND_tot_total_hours   = 0;
               $GRAND_tot_total_minutes = 0;

               while($uname_row = mysql_fetch_assoc($person_query))
               { // Begin BIG While
                    $u = $uname_row['uname'];

                    // Innitialize the TOTALS to zero
                    $tot_reg_hour      = 0;
                    $tot_reg_minute    = 0;
                    $tot_dt_hour       = 0;
                    $tot_dt_minute     = 0;

                    $tot_ot_hour       = 0;
                    $tot_ot_minute     = 0;

                    $tot_vac_hour      = 0;
                    $tot_vac_minute    = 0;
                    $tot_hol_hour      = 0;
                    $tot_hol_minute    = 0;
                    $tot_sick_hour     = 0;
                    $tot_sick_minute   = 0;
                    $tot_fun_hour      = 0;
                    $tot_fun_minute    = 0;
                    $tot_commission    = 0;
                    $tot_total_hours   = 0;
                    $tot_total_minutes = 0;

                    $ot_hour           = 0;
                    $ot_minute         = 0;
                    $ot_second         = 0;

                    $k                 = 1;
                    $do_once           = 1;
                    $row               = null;

                    /******************************************
                    * DO A MySQL QUERY TO LOOK UP THE HOURS. *
                    * STORE THEM IN A ROW                    *
                    * RETURN THE ROW IN A WHILE STATEMENT    *
                    ******************************************/
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



                    if ($id != 'old')
                    { // # 6

                         $hours .= "WHERE uname = '$u'
                                    AND clockin_time >= '$last_cycle_query_result[0]'
                                    AND clockin_time <= '$this_cycle_query_result[0]'
                                    AND clocked_in = 0
                                    ORDER BY clockin_time DESC";

                    } // Close # 6
                    else
                    { // # 7

                         // Default is last pay period
                         $hours .= "WHERE uname = '$u'
                                    AND clockin_time >= '$before_last_cycle_query_result[0]'
                                    AND clockin_time <= '$last_cycle_query_result[0]'
                                    AND clocked_in = 0
                                    ORDER BY clockin_time DESC";
                    } // Close # 7

################################################################################
#                                  Table Start                                 #
################################################################################


/***************** THIS BELOW IS EXPIEREMENTAL ********************************/

                    // This will remove the "DESC" specification at the end of
                    // the $hours mysql query, and it will order it in ASC,
                    // as is default. Then we can do some real math!
                    $hours = rtrim($hours, ' DESC');
                    $hours_result = @mysql_query($hours) or die(mysql_error());

                    while($row = @mysql_fetch_assoc($hours_result))
                    {

                         // Convert the minutes immediately to 100s to keep math consistant
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
								if($day_of_week == 0)
								{
									$query = "SELECT DATE_SUB('$date_of_row', INTERVAL 6 DAY)";
								}
								else
								{
									$day_of_week -= 1;
									$query = "SELECT DATE_SUB('$date_of_row', INTERVAL $day_of_week DAY)";
								}

                               // Decrease the current row's day of the week by the $day_of_week
                               // number, there by getting us last Sunday.
//                               $query = "SELECT DATE_SUB('$date_of_row', INTERVAL $day_of_week DAY)";
                               $query_result = @mysql_query($query)
                                               or die (mysql_error());

                               // $last_sunday[0] is last Sunday from first element in the array.
                               $last_sunday = mysql_fetch_array($query_result);

                               // This will give us next Sunday by adding 7 days to $last_sunday[0]
                               $day_of_week = "SELECT DATE_ADD('$last_sunday[0]', INTERVAL 7 DAY)";
                               $query = @mysql_query($day_of_week)
                                        or die(mysql_error());

                               // This is next sunday from the first element in the array.
                               $next_sunday = mysql_fetch_array($query);

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
                              list($h, $m, $s) = explode(':', tot_hhmmss($ot_hour, $ot_minute,
                                                                   $ot_second));
                              if($h >= 40)
                              {


                                   // This is wrong. It's mostly just a place holder.
                                   $tot_ot_hour      += ($h - 40);
                                   $tot_ot_minute    += $m;

                              }
                              // ######### It DOES go in here. Bad Brian#####

                              // Since it is outside of the last week, we need to test our
                              // accumulated week's totals to see if they are over 40 hrs.


                              // New Week
                              // Null the old OT variables

                              $ot_hour     = 0;
                              $ot_minute   = 0;
                              $ot_second   = 0;
                              $stop_adding = NULL;
                              $do_once     = 1;

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

//                              $query = "SELECT DATE_SUB('$date_of_row', INTERVAL $day_of_week DAY)";
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
                        // of every new week (a.k.a. The end of the old week).
                        if($h >= 40 && $do_once == 1)
                        {
                              // Subtract 40 hours from the week's total,
                              // and voila - overtime.
                              $over_time_hour = $h - 40;

                              // This is wrong. It's mostly just a place holder.
                              $over_time_minute  = $m;
                              $over_time_second  = $s;

                              $ot_hms            = $over_time_hour . ':' . $over_time_minute . ':' . $over_time_second;
                              $row_reg_hms       = $row['reg_hour'] . ':' . $row['reg_minute'] . ':' .
                                                   $row['reg_second'];

                              $row_dt_hms        = $row['dt_hour'] . ':' . $row['dt_minute'] . ':' .
                                                   $row['dt_second'];

                              $row_reg_hour      = $row['reg_hour'];
                              $row_reg_minute    = $row['reg_minute'];
                              $row_reg_second    = $row['reg_second'];

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
                              list($reg_hour_convert, $reg_minute_convert,
                                   $reg_second_convert) = explode (':',tot_hhmmss($row['reg_hour'],
                                                                                  $row['reg_minute'],
                                                                                  $row['reg_second']));
                              $tot_reg_hour     += $reg_hour_convert;
                              $tot_reg_minute   += $reg_minute_convert;
                              $tot_reg_second   += $reg_minute_convert;

                              list($dt_hour_convert, $dt_minute_convert,
                                   $dt_second_convert) = explode(':',tot_hhmmss($row['dt_hour'],
                                                                                $row['dt_minute'],
                                                                                $row['dt_second']));
                              $tot_dt_hour     += $dt_hour_convert;
                              $tot_dt_minute   += $dt_minute_convert;
                              $tot_dt_second   += $dt_minute_convert;


                         }
                         // Add the rest of the hours
                         $tot_vac_hour    += $row['vac_hour'];
                         $tot_vac_minute  += $row['vac_minute'];
                         $tot_hol_hour    += $row['hol_hour'];
                         $tot_hol_minute  += $row['hol_minute'];
                         $tot_fun_hour    += $row['fun_hour'];
                         $tot_fun_minute  += $row['fun_minute'];
                         $tot_sick_hour   += $row['sick_hour'];
                         $tot_sick_minute += $row['sick_minute'];



                    } // End INNER While
// IF in the week, add the shit
// JUST ADDED THIS!!!!!!!!!!!!!!!!!!!!11
################################################################################
#                    DO ONE MORE OVERTIME CHECK BEFORE ENDING THE PAGE         #
################################################################################
                   // This happens on the second week.
                    list($h, $m, $s)
                         = explode(':', tot_hhmmss($ot_hour, $ot_minute, $ot_second));

                    // It's the end of the week. Check for overtime.
                    if ($h >= 40)
                    {

                         // Subtract 40 hours, and voila - overtime.
                         $tot_ot_hour    += ($ot_hour - 40);
                         $tot_ot_minute  += $ot_minute;
                         $tot_ot_second  += $ot_second;
                    }
                    $ot_hour   = 0;
                    $ot_minute = 0;
                    $ot_second = 0;

// DOWN TO HERE!!!!!!!!!!!!
                    $reg_hour_convert   = 0;
                    $reg_minute_convert = 0;
/************************* END EXPIEREMENT ************************************/

                    // Normalize the totals first before
                    // printing them.
                    $tot_total_hour   += $tot_reg_hour   + $tot_dt_hour   + $tot_ot_hour;
                    $tot_total_minute += $tot_reg_minute + $tot_dt_minute + $tot_ot_minute;
                    // Total REGULAR Hours
                    list($tot_reg_hour,   $tot_reg_minute)   =
                          explode(':', tot_hhmmss($tot_reg_hour,   $tot_reg_minute));

                    // Total DOUBLETIME Hours
                    list($tot_dt_hour,    $tot_dt_minute)    =
                          explode(':', tot_hhmmss($tot_dt_hour,    $tot_dt_minute));

                    // Total OVERTIME Hours
                    // NOTE: This isn't happening, because in the "Expieremental" section,
                    // overtime was not added. Need to put this function in.
                    list($tot_ot_hour,    $tot_ot_minute)    =
                          explode(':', tot_hhmmss($tot_ot_hour,    $tot_ot_minute));

                    // Total VACATION Hours
                    // NOTE: See above for why this isn't printing.
                    list($tot_vac_hour,   $tot_vac_minute)   =
                          explode(':', tot_hhmmss($tot_vac_hour,   $tot_vac_minute));

                    // Total SICK Hours
                    // NOTE: See above
                    list($tot_sick_hour,  $tot_sick_minute)  =
                          explode(':', tot_hhmmss($tot_sick_hour,  $tot_sick_minute));

                    // Total HOLIDAY Hours
                    // NOTE: See above
                    list($tot_hol_hour,   $tot_hol_minute)   =
                          explode(':', tot_hhmmss($tot_hol_hour,   $tot_hol_minute));

                    // Total FUNERAL Hours
                    // NOTE: See above
                    list($tot_fun_hour,   $tot_fun_minute)   =
                          explode(':', tot_hhmmss($tot_fun_hour,   $tot_fun_hour));

                    // Total TOTAL Hours
                    list($tot_total_hour, $tot_total_minute) =
                          explode(':', tot_hhmmss($tot_total_hour, $tot_total_minute));

################################################################################
#                                   Print the ROW TOTALS                       #
################################################################################

                    // If all the hours are zero
                    if(   $tot_total_hour == 0 && $tot_total_minute == 0
                       && $tot_reg_hour   == 0 && $tot_reg_minute   == 0
                       && $tot_dt_hour    == 0 && $tot_dt_minute    == 0
                       && $tot_vac_hour   == 0 && $tot_vac_minute   == 0
                       && $tot_hol_hour   == 0 && $tot_hol_minute   == 0
                       && $tot_sick_hour  == 0 && $tot_sick_minute  == 0
                       && $tot_fun_hour   == 0 && $tot_fun_minute   == 0)
                    { // Begin If
                         // Do nothing

                    } // End If
                    else
                    { // Begin Else
                         if(($row_color % 2) == 0)
                         {
                              echo '<tr class="ODD">';
                              $row_color++;
                         }
                         else
                         {
                              echo '<tr class="EVEN">';
                              $row_color++;
                         }

                         // Gotta print commissions. Gather them from the db first.
                         $commish = "SELECT commish_dollar, commish_cent
                                     FROM commission
                                     WHERE uname = '$u'
                                     AND pay_period >= '$last_cycle_query_result[0]'
                                     AND pay_period <= '$this_cycle_query_result[0]'";
                         $commish_query = @mysql_query($commish)
                                          or die(mysql_error());
                         $commish_result = @mysql_fetch_array($commish_query);

                         // Now set them
                         if($commish_result[0] == '')
                         {
                              $commish_result[0] = '0';
                         }
                         if($commish_result[1] == '')
                         {
                              $commish_result[1] = '00';
                         }
                         if(strlen($commish_result[1]) < 2)
                         {
                              $commish_result[1] = '0' . $commish_result[1];
                         }
                         $tot_commish = '$' . $commish_result[0] . '.' . $commish_result[1];

                         echo '<td align="left">' . $uname_row['last_name'] . '</td>';
                         echo '<td align="left">' . $uname_row['first_name'] . '</td>';
                         echo '<td align="left">' . $tot_reg_hour   . ':' . $tot_reg_minute . '</td>';
                         echo '<td align="left">' . $tot_dt_hour    . ':' . $tot_dt_minute . '</td>';
                         echo '<td align="left">' . $tot_ot_hour    . ':' . $tot_ot_minute . '</td>';
                         echo '<td align="left">' . $tot_vac_hour   . ':' . $tot_vac_minute . '</td>';
                         echo '<td align="left">' . $tot_sick_hour  . ':' . $tot_sick_minute . '</td>';
                         echo '<td align="left">' . $tot_hol_hour   . ':' . $tot_hol_minute . '</td>';
                         echo '<td align="left">' . $tot_fun_hour   . ':' . $tot_fun_minute . '</td>';
                         echo '<td align="left">' . $tot_commish    . '</b></td>';
                         echo '<td align="left">' . $tot_total_hour . ':' . $tot_total_minute . '</td>';

$content = $uname_row['last_name'] . ',' . $uname_row['first_name'] . ','
         . $tot_reg_hour   . '.'  . $tot_reg_minute   . ','
         . $tot_dt_hour    . '.'  . $tot_dt_minute    . ','
         . $tot_ot_hour    . '.'  . $tot_ot_minute    . ','
         . $tot_vac_hour   . '.'  . $tot_vac_minute   . ','
         . $tot_sick_hour  . '.'  . $tot_sick_minute  . ','
         . $tot_hol_hour   . '.'  . $tot_hol_minute   . ','
         . $tot_fun_hour   . '.'  . $tot_fun_minute   . ','
         . $tot_commish    . ','
         . $tot_total_hour . '.'  . $tot_total_minute . "\n";

// let's make sure the file exists and is writable first.
if (is_writable($filename)) {

   // in our example we're opening $filename in append mode.
   // the file pointer is at the bottom of the file hence
   // that's where $somecontent will go when we fwrite() it.
   if (!$handle = fopen($filename, 'a')) {
         //echo "cannot open file ($filename)";
         exit;
   }

   // write $somecontent to our opened file.
   if (fwrite($handle, $content) === false) {
      // echo "cannot write to file ($filename)";
       exit;
   }

   //echo "success, wrote ($content) to file ($filename)";

   fclose($handle);

} else {
   //echo "the file $filename is not writable";
}

                         // Innitialize the GRAND TOTALS (the company totals) to zero
                         $GRAND_tot_reg_hour       += $tot_reg_hour;
                         $GRAND_tot_reg_minute     += $tot_reg_minute;
                         $GRAND_tot_dt_hour        += $tot_dt_hour;
                         $GRAND_tot_dt_minute      += $tot_dt_minute;
                         $GRAND_tot_ot_hour        += $tot_ot_hour;
                         $GRAND_tot_ot_minute      += $tot_ot_minute;
                         $GRAND_tot_vac_hour       += $tot_vac_hour;
                         $GRAND_tot_vac_minute     += $tot_vac_minute;
                         $GRAND_tot_hol_hour       += $tot_hol_hour;
                         $GRAND_tot_hol_minute     += $tot_hol_minute;
                         $GRAND_tot_sick_hour      += $tot_sick_minute;
                         $GRAND_tot_sick_minute    += $tot_sick_minute;
                         $GRAND_tot_fun_hour       += $tot_fun_hour;
                         $GRAND_tot_fun_minute     += $tot_fun_minute;
                         // Total COMMISSION
                         $GRAND_tot_commish_dollar += $commish_result[0];
                         $GRAND_tot_commish_cent   += $commish_result[1];
                         // GRAND TOTAL TOTAL TOTAL TOTAL ad nauseum.
                         $GRAND_tot_total_hour    += $tot_total_hour;
                         $GRAND_tot_total_minute  += $tot_total_minute;

                         // Reinnitialize totals to null
                         $tot_reg_hour     = NULL;
                         $tot_reg_minute   = NULL;
                         $tot_dt_hour      = NULL;
                         $tot_dt_minute    = NULL;
                         $tot_ot_hour      = NULL;
                         $tot_ot_minute    = NULL;
                         $tot_vac_hour     = NULL;
                         $tot_vac_minute   = NULL;
                         $tot_sick_hour    = NULL;
                         $tot_sick_minute  = NULL;
                         $tot_hol_hour     = NULL;
                         $tot_hol_minute   = NULL;
                         $tot_fun_hour     = NULL;
                         $tot_fun_minute   = NULL;
                         $tot_commish      = NULL;
                         $tot_total_hour   = NULL;
                         $tot_total_minute = NULL;
                    } // End Else
                    $stop_adding = NULL;
                    $do_once     = 1;
               } // END OF THE BIG WHILE
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
               <tr class="HEADER">
                    <td align="left"><b>Company&nbsp;</b></td>
                    <td align="left"><b>Totals:</td>
                    <?php
################################################################################
#                       PRINT COMPANY GRAND TOTALS                             #
################################################################################
                         echo '<td align="left">' . tot_hhmmss($GRAND_tot_reg_hour,  $GRAND_tot_reg_minute) . '</td>';
                         echo '<td align="left">' . tot_hhmmss($GRAND_tot_dt_hour,   $GRAND_tot_dt_minute) . '</td>';
                         echo '<td align="left">' . tot_hhmmss($GRAND_tot_ot_hour,   $GRAND_tot_ot_minute) . '</td>';
                         echo '<td align="left">' . tot_hhmmss($GRAND_tot_vac_hour,  $GRAND_tot_vac_minute) . '</td>';
                         echo '<td align="left">' . tot_hhmmss($GRAND_tot_sick_hour, $GRAND_tot_sick_minute) . '</td>';
                         echo '<td align="left">' . tot_hhmmss($GRAND_tot_hol_hour,  $GRAND_tot_hol_minute) . '</td>';
                         echo '<td align="left">' . tot_hhmmss($GRAND_tot_fun_hour,  $GRAND_tot_fun_minute) . '</td>';
                         list($GRAND_tot_commish_dollar, $GRAND_tot_commish_cent) =
                              explode(':', tot_hhmmss($GRAND_tot_commish_dollar, $GRAND_tot_commish_cent));
                         if(strlen($GRAND_tot_commish_cent) < 2)
                         {
                              $GRAND_tot_commish_cent = '0' . $GRAND_tot_commish_cent;
                         }
                         echo '<td align="left">' . '$' . $GRAND_tot_commish_dollar
                                                  . '.' . $GRAND_tot_commish_cent . '</td>';
                         echo '<td align="left">' . tot_hhmmss($GRAND_tot_total_hour, $GRAND_tot_total_minute) . '</td>';
                         $GRAND_tot_reg  = explode(":",tot_hhmmss($GRAND_tot_reg_hour,  $GRAND_tot_reg_minute));
                         $GRAND_tot_dt   = explode(":",tot_hhmmss($GRAND_tot_dt_hour,  $GRAND_tot_dt_minute));
                         $GRAND_tot_ot   = explode(":",tot_hhmmss($GRAND_tot_ot_hour,  $GRAND_tot_ot_minute));
                         $GRAND_tot_vac  = explode(":",tot_hhmmss($GRAND_tot_vac_hour,  $GRAND_tot_vac_minute));
                         $GRAND_tot_sick = explode(":",tot_hhmmss($GRAND_tot_sick_hour,  $GRAND_tot_sick_minute));
                         $GRAND_tot_hol  = explode(":",tot_hhmmss($GRAND_tot_hol_hour,  $GRAND_tot_hol_minute));
                         $GRAND_tot_fun  = explode(":",tot_hhmmss($GRAND_tot_fun_hour,  $GRAND_tot_fun_minute));
                         $GRAND_tot_tot  = explode(":",tot_hhmmss($GRAND_tot_total_hour,  $GRAND_tot_total_minute));


$content = "COMPANY,TOTALS:,"
         . $GRAND_tot_reg[0]  . '.'  . $GRAND_tot_reg[1]   . ','
         . $GRAND_tot_dt[0]   . '.'  . $GRAND_tot_dt[1]    . ','
         . $GRAND_tot_ot[0]   . '.'  . $GRAND_tot_ot[1]    . ','
         . $GRAND_tot_vac[0]  . '.'  . $GRAND_tot_vac[1]   . ','
         . $GRAND_tot_sick[0] . '.'  . $GRAND_tot_sick[1]  . ','
         . $GRAND_tot_hol[0]  . '.'  . $GRAND_tot_hol[1]   . ','
         . $GRAND_tot_fun[0]  . '.'  . $GRAND_tot_fun[1]   . ','
         . '$' . $GRAND_tot_commish_dollar . '.' . $GRAND_tot_commish_cent . ','
         . $GRAND_tot_tot[0]  . '.'  . $GRAND_tot_tot[1]   . "\n";

// Let's make sure the file exists and is writable first.
if (is_writable($filename)) {

   // In our example we're opening $filename in append mode.
   // The file pointer is at the bottom of the file hence
   // that's where $somecontent will go when we fwrite() it.
   if (!$handle = fopen($filename, 'a')) {
       //  echo "Cannot open file ($filename)";
         exit;
   }

   // Write $somecontent to our opened file.
   if (fwrite($handle, $content) === FALSE) {
       //echo "Cannot write to file ($filename)";
       exit;
   }

   //echo "Success, wrote ($content) to file ($filename)";

   fclose($handle);

} else {
   //echo "The file $filename is not writable";

}


                    ?>

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

               </tr>
               </tbody>
               </table>
          </td>
     </tr>
</tbody>
</table>
<br />
<hr />
</body>
</html>

<?php @include('templates/footer.inc'); // Include the footer. ?>
