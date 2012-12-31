<?php
/*
*        Script Title: Clockin/Out v 1.0
*        Page Title: report.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*        This page was a last minute request. Basically, it takes a two-week
*        total of work hours for each employee, and prints them line by line.
*        Each employee gets his/her own line. At the bottom a grand total is
*        printed. This page is designed to be printer frindly.
*
*        The page takes two arguements: "new" and "old". If "new", it will
*        display the report for the most recently completed pay period. If
*        "old" it will display the pay period before last.
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
*        Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
*        02111-1307, USA.
*
*        My contact information is above.
*/


//session_start(); // Start the session
@include('../conn.inc.php');



/***************************************************************************
 *                                 FUNCTIONS                               *
 ***************************************************************************/

// input:  3 integers
// output: none
// return: a string
// notes:  This function takes the hours minutes and seconds, and turns them
//         into 100 minute/hour cumulative time.
function hhmmss($hour, $min, $sec) //////////////////////////////////////////
{ // # 1
echo 'Going through the hhmmss fx<br>';
echo $hour . '*' . $min . '*' . $sec . '<br>';
     // This will normalize the HH:MM:SS
        if($hour == 0 && $min == 0 && $sec == 0)
        {
            // I just realized that if you return 0, it will probably end the
            // program. So, then I will have to return something else. I guess.
            // Unless I'm retarted.
             return '::';
        }
     $min = floor(($min + ($sec / 60)));
        $sec = ($sec % 60);
        $hour = floor(($hour + ($min / 60)));
        $min = ($min % 60);

        // This converts 60 minute time to 100 minute time.
        // It is static cast as an int to truncate decimals.
        $min = (int) (($min / 60) * 100);
        $hms = $hour . ":" . $min;
        if($hms == '0:0')
        {
             return '::';
        }
        else
     {
                return $hms;
        }
} // Close # 1

/***********************************************************************
 *                        SECURITY                                     *
 ***********************************************************************/
// If no session is present, redirect the user
if ($_SESSION['isroot'] != 1)
{ // # 2
     // Redirect them back to the login page.
     //header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
     //exit();
} // Close # 2

// Grab the id value that was passed in the URL
$id = $_GET['id'];

echo 'id = ' . $_GET['id'] . '<br>';


/***********************************************************************
 *                         GET NEW PAY CYCLE DATES                     *
 ***********************************************************************/

// If new is specified, grab last pay cycle.
if ($id == "new")
{ // # 3
echo '<b>Getting dates for new <br>';
     // Select the latest pay period
        $query = "SELECT cycle_date
                  FROM pay_cycle
                  WHERE cycle_date <= NOW()
                  ORDER BY cycle_date DESC
                        LIMIT 1";
     $query_result = mysql_query($query);
     $pay_cycle_top = mysql_fetch_array($query_result);

        // Select the pay period before this one.
     $query = "SELECT cycle_date
               FROM pay_cycle
                        WHERE
                        cycle_date < '$pay_cycle_top[0]'
                        ORDER BY cycle_date DESC
                        LIMIT 1";
     $query_result = mysql_query($query);
     $pay_cycle_bottom = mysql_fetch_array($query_result);

} // Close # 3


/***********************************************************************
 *                        GET OLD PAY CYCLE DATES                      *
 ***********************************************************************/
if ($id == "old")
{
echo 'Getting the dates for OLD<br>';
     // Get most recent pay cycle start date to use as a marker
     $query = "SELECT cycle_date
                         FROM pay_cycle
                     WHERE cycle_date <= NOW()
                     ORDER BY cycle_date DESC
                     LIMIT 1";
        $query_result = mysql_query($query);
        $pay_cycle_mark = mysql_fetch_array($query_result);

        // Get pay cycle start date before that to be the top
        $query = "SELECT cycle_date
                         FROM pay_cycle
                        WHERE cycle_date < '$pay_cycle_mark[0]'
                        ORDER BY cycle_date DESC
                        LIMIT 1";
        $query_result = mysql_query($query);
        $pay_cycle_top = mysql_fetch_array($query_result);

        // Get pay cycle start date before THAT to be the bottom
        $query = "SELECT cycle_date
                            FROM pay_cycle
                        WHERE cycle_date < '$pay_cycle_top[0]'
                        ORDER BY cycle_date DESC
                        LIMIT 1";
        $query_result = mysql_query($query);
        $pay_cycle_bottom = mysql_fetch_array($query_result);

        // So now we have the top and bottom of the previous pay cycle.
}



/***********************************************************************
 *                 INNITIALIZE VARIABLES                               *
 ***********************************************************************/

// Set the table header
$table_header_report = "TRUE";

// Innitialize variables
$reg_hour_total     = null;
$reg_minute_total   = null;
$reg_second_total   = null;
$dt_hour_total      = null;
$dt_minute_total    = null;
$dt_second_total    = null;
$vac_hour_total     = null;
$vac_minute_total   = null;
$vac_second_total   = null;
$sick_hour_total    = null;
$sick_minute_total  = null;
$sick_second_total  = null;
$fun_hour_total     = null;
$fun_minute_total   = null;
$fun_second_total   = null;

// Over Time Vars
$over_time_hour     = null;
$over_time_minute   = null;
$over_time_second   = null;

// Totals
$total_hour_total   = null;
$total_minute_total = null;
$total_second_total = null;

// Number of people omitted
$omitted = 0;

echo 'Done innitializing.<br>';

/***********************************************************************
 *                 BEGINNING OF TABLE                                  *
 ***********************************************************************/

echo '<big><strong>Work Hour Summary for the Pay Period of ' .
         '<font color="#ef0000">' . $pay_cycle_top[0] . '</font> -<font
         color="#ef0000"> ' . $pay_cycle_bottom[0] . '</strong></big><br />';

?>
<!-- Link to the Cascading Style Sheet for use with table color and such -->
<link href="templates/style.css" type="text/css" rel="stylesheet">

<table cellSpacing="0" borderColorDark="c0c0c0" cellPadding="0"
 width="100%" borderColorLight="#ffffff" border="1">
     <tbody>
          <tr>
               <td>
                    <table id="hours" cellSpacing="0" cellPadding="0"
                                  width="100%" border="0" name="hours">
                                     <tbody>
<?php

/***********************************************************************
 *                       DATE AND USERNAME QUERIES                     *
 ***********************************************************************/

// Select the Middle of the pay cycle for Over Time.
// This will devide the pay cycle into two seperate weeks.
$query = "SELECT DATE_ADD('$pay_cycle_bottom[0]',
          INTERVAL 7 DAY)";
$query_result = mysql_query($query);
$pay_cycle_middle = mysql_fetch_array($query_result);

echo 'Grab mid week:<br>';
echo $pay_cycle_middle[0] . '<br>';

echo 'Get usernames...<br>';
// Get usernames in alphabetical order into an array.
$user = "SELECT uname
         FROM user_pass WHERE 1
            ORDER BY uname";
$user_query = mysql_query($user);

echo $user . '<br>';
echo 'Got usernames, now cycle through them<br><br>';

@include('templates/tableheader.inc');
echo 'Innitialize variables now...<br>';


/***********************************************************************
 *                       LOOP THROUGH USERS                            *
 ***********************************************************************/
while($u = mysql_fetch_array($user_query))
{ // # 4
     $query = "SELECT clockin_time, clockout_time,
                  reg_hour, reg_minute, reg_second,
                  dt_hour, dt_minute, dt_second,
                  vac_hour, vac_minute, vac_second
                     sick_hour, sick_minute, sick_second,
                     fun_hour, fun_minute, fun_second,
                     total_hour, total_minute, total_second
                     FROM work_hours
                        WHERE uname = \"$u[0]\"
                        AND clockin_time <= '$pay_cycle_top[0]'
                        AND clockout_time >= '$pay_cycle_bottom[0]'";
echo $query . '<br>';
echo $u[0] . ' just before cycling<br>';

     $query_result = mysql_query($query);

/***********************************************************************
 *                      LOOP THROUGH USER'S HOURS                             *
 ***********************************************************************/

        // Cycle through the current user's work hours
        while($hours = mysql_fetch_assoc($query_result))
        { // # 4.1

echo 'Now cycle through individual users hours. Add totals up<br>';

             // Add up hours into their respective totals.
             // Will have to compute Over Time
             $reg_hour_total    += $hours['reg_hour'];
             $reg_minute_total  += $hours['reg_minute'];
             $reg_second_total  += $hours['reg_second'];
             $dt_hour_total     += $hours['dt_hour'];
             $dt_minute_total   += $hours['dt_minute'];
             $dt_second_total   += $hours['dt_second'];
             $vac_hour_total    += $hours['vac_hour'];
             $vac_minute_total  += $hours['vac_minute'];
             $vac_second_total  += $hours['vac_second'];
             $sick_hour_total   += $hours['sick_hour'];
             $sick_minute_total += $hours['sick_minute'];
             $sick_second_total += $hours['sick_second'];
             $fun_hour_total    += $hours['fun_hour'];
             $fun_minute_total  += $hours['fun_minute'];
             $fun_second_total  += $hours['fun_second'];

                // TOTALS
             $total_hour_total   += $reg_hour_total;
          $total_hour_total   += $dt_hour_total;
                $total_hour_total   += $vac_hour_total;
                $total_hour_total   += $sick_hour_total;
                $total_hour_total   += $fun_hour_total;
                $total_minute_total += $reg_minute_total;
                $total_minute_total += $dt_minute_total;
                $total_minute_total += $vac_minute_total;
                $total_minute_total += $sick_minute_total;
                $total_minute_total += $fun_minute_total;
                $total_second_total += $reg_second_total;
                $total_second_total += $dt_second_total;
                $total_second_total += $vac_second_total;
                $total_second_total += $sick_second_total;
                $total_second_total += $fun_second_total;
     } // Close # 4.1
echo $total_hour_total . ":" . $total_minute_total . ":"
                    . $total_second_total . '<Br>';

/***********************************************************************
 *                     GRAB FIRST WEEK'S TOTALS                             *
 ***********************************************************************/
                if($hours['clockin_time'] <= $pay_cycle_middle[0])
                { // # 4.1.1
echo 'Get the first weeks totals so we can check if this is over 40 hours.';
                        $over_time_hour   += $total_hour_total;
                     $over_time_minute += $total_minute_total;
                        $over_time_second += $total_second_total;

                        // Now we have the total for the first week. We can
                        // check if this is over 40 hours, and we can also
                        // subtract this from the TOTAL total and get the
                        // to this. Badda-bing. Total Over Time.

          } // Close # 4.1.1


/***********************************************************************
 *                    IF EVERYTHING IS NULL (NO HOURS)                 *
 ***********************************************************************/

             // If all null, then increment $omitted and print the number of
          // omitted at the end. Else, print the current record to the table.
          // Question: Will this have trouble if it is NULL or 0?
          if(!$total_hour_total  && !$total_minute_total &&
                !$total_second_total)
             { // # 4.2
                     $omitted++;
echo 'Omitted: ' . $omitted . '<br>';
             } // Close # 4.2

/***********************************************************************
 *                   ELSE IF THERE IS DATA IN THE WORK HOURS           *
 ***********************************************************************/
                    else
             { // # 4.3
echo 'Not omitted: <Br>';
                     // Do the main routines to print the report
/*****************************************************************************
*    In this section, we break up the total time into two weeks to check if  *
*    either of the two weeks is over 40 hours. If they are, there's          *
*    overtime.                                                                                                          *
*    The first week is checked for overtime (anything over 40 hours), and    *
*    then we subtract the first week's hours from the total to get the       *
*    second week's hours. Then we subtract 40 from that to see if there's    *
*    any OT in the second week. Then we add the two overtimes together and   *
*    have a total happy overtime. Badda-boom. There it is.                                  *
*****************************************************************************/
echo 'Breaking up total time into two weeks.<Br>';
                        // May not need this.
                     $over_time_hour2   = $over_time_hour;
                            $over_time_minute2 = $over_time_minute;
                     $over_time_second2 = $over_time_second;

                  // Normalize the Over Time for Checks
echo 'Normalizing the overtime for checks.<Br>';
                     list($normal_ot_hour, $normal_ot_minute, $normal_ot_second) =
                                    explode(':', hhmmss($over_time_hour, $over_time_minute,
                                                                    $over_time_second));

/***********************************************************************
 *                    IF THERE IS OVERTIME FOR THE FIRST WEEK          *
 ***********************************************************************/
                        // If the Normalized Overtime hour is greater than 40:
                        if($normal_ot_hour >= 40)
                        { // # 4.3.1
                             // Anything over 40 hours per week is Over Time.
                                $over_time_hour -= 40;
                                // There's our first OT number
                        } // Close # 4.3.1

                        // Normalize totals to do math on

echo 'Normalizing the totals for checks.<br>';
                  list($normal_total_hour, $normal_total_minute,
                                $normal_total_second) = explode(':', hhmmss($total_hour_total,
                                                                                              $total_minute_total,
                                                                                              $total_second_total));
                     // Subtract the OTs to get the second week's hours
                     $normal_total_hour -= $normal_ot_hour;
                     $normal_total_minute -= $normal_ot_minute;
                     $normal_total_second -= $normal_ot_second;

/***********************************************************************
 *                  IF THERE IS OVERTIME FOR THE SECOND WEEK           *
 ***********************************************************************/
                     // Any overtime in the second week?
                     if($normal_total_hour >= 40)
                     { // # 4.3.2
echo 'Any OT for the Second week? YES. How much? <br>';
                          // If so, find out how much

                             $normal_total_hour -= 40;

                             // And finally, here's the OT totals.
                             $over_time_hour += $normal_total_hour;
                             $over_time_minute += $normal_total_minute;
                             $over_time_second += $normal_total_second;

                     } // Close # 4.3.2
echo 'After OT checks at the end of the else. <br>';
/***********************************************************************
 *                  END OF ELSE                                        *
 ***********************************************************************/
             }    // Close # 4.3

echo 'At the end of the while<bR>';

echo 'End of Employee Loop. <br>';

/***********************************************************************
 *                  END OF USERS WHILE                                 *
 ***********************************************************************/
} // Close # 4
// @@marker
echo 'test';

?>



