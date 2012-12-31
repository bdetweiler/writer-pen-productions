<?php
/*
*        Script Title: Clockin\Out v. 1.0
*        Page Title: enter_commission.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*   	    This page allows the Administrator or Supervisor to enter commissions
*	    for employees. To enter a commission, the Admin/Supervisor selects
*	    an employee from the drop down list. Then they
*	    select a date for which to enter a commission.
*
*	    Then the Admin/Supervisor enters a desired commission ammount and 
*	    simply clicks "Submit" and the commission gets added. The user can
*	    then check their commissions by logging in, going to the bottom of
*	    their screen and clicking on the dollar sign. It will pop up a 
*	    Java Script window and they can select a date to view. 
*
*		It feeds off of the cycle_date table which is created by taking an
*		innitial pay date and recursively adding 14 days to it. 
*	    Though this is probably not the best way to implement it, it works,
*	    and if I had as long as I wanted, I would have made it a little 
*	    cooler. Any complaints should be directed to /dev/null.
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

if (!(isset($_SESSION['isroot'])))
{ // # 1

     // Redirect them back to the login page.
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
     exit();
} // Close # 1

$admin = $_SESSION['uname'];

if (isset($_POST['go']))
{

}

if (isset($_POST['submit']))
{ // # 1
	
     // Function for normalizing the input.
     function escape_data($data)
     { // # 1.1

          if (ini_get('magic_quotes_gpc'))
          { // # 1.1.1
               $data = stripslashes($data);
          } // Close # 1.1.1
          return trim(mysql_real_escape_string($data));
     } // Close # 1.1

     // Innitialize $message
     $message = NULL;

     if ($_POST['date'] == "")
     { // # 1.2
          $d = FALSE;
          $message .= '<p>You must select a date.</p>';
     } // Close # 1.2
     else
     { // # 1.3	
          $d = $_POST['date'];
     } // Close # 1.3

     if ($_POST['user'] == "")
     { // # 1.4	
          $u = FALSE;
          $message .= '<p>You must select a user.</p>';
     } // Close # 1.4
     else
     { // # 1.5
          $u = $_POST['user'];
     } // Close # 1.5

     // Do checks for commission.
     if (empty($_POST['commission']))
     { // # 1.6
          $c = FALSE;
          $message .= '<p>You must enter a commission.</p>';
     } // Close # 1.6
     else
     { // # 1.7
          $c = escape_data($_POST['commission']);


          // If they typed it in the correct syntax:

          if (!ereg("[0-9]{1,7}\.[0-9]{1,2}", $c))
          { // # 1.7.1
               $message .= "<p>Invalid currency format. Please use <i>$$$$.$$</i> format.</p>";
               if (ereg("[;:8][-+o]?([)p(|/#o?\]|])", $c))
               { // # 1.7.1.1
                    $message .= '<p><a href="http://216.66.24.2">To learn more, click here.</a></p>';
                    $c = NULL;
               } // Close # 1.7.1.1
               $c = NULL;
          } // Close # 1.7.1
          else
          { // # 1.7.2
               $c = $_POST['commission'];

               $prev_entry_check = "SELECT pay_period
                                    FROM commission
                                    WHERE pay_period = '$d'
                                    AND uname = '$u'";
               $prev_entry_check = @mysql_query($prev_entry_check)
                                   or die(mysql_error());

               if (mysql_num_rows($prev_entry_check) > 0)
               { // # 1.7.2.1
                    $overwrite = 1;
               } // # 1.7.2.1
          } // Close # 1.7.2
     } // Close # 1.7
     
     if ($c && $d && $u)
     { // # 1.10

          $grab_user = "SELECT first_name, last_name, title
                        FROM user_pass
                        WHERE uname = '$u'";
          $grab_user_query = mysql_query($grab_user);

          list($dollar, $cent) = explode('.', $c);
          $grab_user_query_result = mysql_fetch_assoc($grab_user_query);


          $first_name = $grab_user_query_result['first_name'];
          $last_name = $grab_user_query_result['last_name'];
          $title = $grab_user_query_result['title'];
          $username = $_SESSION['username'];

          // PREVENT DUPLICATE VALUES!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
          if ($overwrite == 1)
          {
               $commish = "UPDATE commission
                           SET commish_dollar = $dollar, commish_cent = $cent, supervisor = '$username'
                           WHERE uname = '$u'
                           AND pay_period = '$d'";

               $commish_query = mysql_query($commish);

               if ($commish_query)
               {
                    $message .= 'You have overwritten the old commission for ' . $u . ' with a the new commission of $' . $c;
               }
               else
               {
                    $message .= 'Your entry did not go through. Check your syntax and try again.<br>
                                 If problem persists, contact your network administrator.';
               }
          }
          else
          {
               $commish = "INSERT INTO commission (uname, first_name, last_name, title, pay_period, supervisor, commish_dollar, commish_cent)
                           VALUES ('$u', '$first_name', '$last_name', '$title', '$d', '$username', $dollar, $cent)";
               $commish_query = mysql_query($commish);

               if ($commish_query)
               {
                    $message .= 'Your commission of $' . $c . ' for ' . $u . ' has been entered.';
               }
               else
               {
                    $message .= 'Your entry did not go through. Check your syntax and try again.<br>
                                 If problem persists, contact your network administrator.';
               }

          }
     }
}
     $page_title = "Administrator - Enter a Commission";
     include ('templates/header.inc');

     // Error messages printed here
     if (isset($message))
     { // # 3
          // They are seeing this message because they have messed up somewhere along the line.
          echo '<font color = "#ef0000">', $message, '</font>';
     } // Close # 3


?>

<table width="100%">
     <tr>
          <td align="right">
               <?php
                    $access_level = 'admin';
                    $page = 'enter_commission';
                    @include('templates/tabs.inc');
               ?>
          </td>
     </tr>
</table>

<form action="<?php echo 'http://' . $_SERVER[HTTP_HOST] . '/enter_commission.php'; ?>" method="post">

<!-- The nice border around our data starts here -->
<fieldset><legend><b>Enter a Commission</b></legend>
<br>
<!-- Begin the table -->
<table width="100%" border="0" cellpadding="5">

     <!-- Password field -->
     <tr align = left>
          <td>
               <table width="300" border="0">
                    <tr>
                         <td align=left>
                              <b>
                                   Select a User:
                              </b>
                         </td>
                         <td align="right">
                              <select name="user">
                                   <option value="">Select a User:</option>
                                   <?php
                                        $get_user = "SELECT uname
                                                     FROM user_pass
                                                     WHERE 1";
                                        $get_user_query = @mysql_query($get_user)
                                                          or die(mysql_error());
                                        while ($row = mysql_fetch_array($get_user_query))
                                        {
                                             echo '<option value="' . $row[0] . '">' . $row[0] . '</option>';
                                        }
                                   ?>
                              </select>

                         </td>
                    </tr>
               </table>
          </td>
     </tr>

     <td>
     <!-- This is here for formating purposes -->
     </td>

     <!-- Here's the recommendations -->
     <th rowspan="3">
          <font color="#ef0000" size="2">
          <b><i>
          You may overwrite old commissions by entering a new one for the desired date.
          <br />Seperate dollars and cents with a decimal. Do not enter a dollar sign.
          <br />
          <br />Use this format:</i> $$$$.$$<i>
          <br />e.g. </i>1499.98
          </b>
          </font>
     </th>

     <!-- Password field -->
     <tr align = left>
          <td>
               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
    			           Select a Date:
                              </b>
                         </td>
                         <td align="right">
		     	      <select name="date">
                                   <option value="">Select a Date:</option>
                                   <?php
                                        $get_date = "SELECT cycle_date
                                                     FROM pay_cycle
                                                     WHERE cycle_date <= NOW()
                                                     ORDER BY cycle_date DESC
                                                     LIMIT 0, 52";
                                        $get_date_query = @mysql_query($get_date)
                                                          or die(mysql_error());

                                        while ($row = mysql_fetch_array($get_date_query))
                                        {
                                             echo '<option value="' . $row[0] . '">' . $row[0] . '</option>';
                                        }
                                   ?>
                              </select>
                         </td>
                    </tr>
               </table>
          </td>
     </tr>

     <!-- Radio buttons -->
     <tr align="left">
          <td>
               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
                                   Enter a Commission:
                              </b>
                         </td>
                         <td align="right">
                              <input type="text" name="commission" maxlength="10">
                         </td>
                    </tr>
               </table>
          </td>
     </tr>

     <!-- Submit button -->
     <tr align = left>
          <td>
               <input type="submit" name="submit" value="Submit">
          </td>
     </tr>
</table>
<br>
</fieldset>


<!-- Include the footer -->
<?php include "templates/footer.inc" ?>
</form>
