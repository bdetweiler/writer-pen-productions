<?php
/*
*        Script Title: Clockin\Out v. 1.0
*        Page Title: commission.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*        This is the commissions page for the supervisors. It allows a supervisor
*        to enter commissions for employees. It is capped at 6 digits, but I really
*        can't see anyone making over $999,999.99 on a commission. If they do, then
*        I am in the wrong field. 
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



if (isset($_POST['submit']))
{

     if(isset($_SESSION['isroot']))
     {
          $u = $_SESSION['uname'];
     }
     else
     {
          $u = $_SESSION['username'];
     }

     $date = $_POST['date'];

     $commish = "SELECT commish_dollar, commish_cent, pay_period
                 FROM commission
                 WHERE pay_period = '$date'
                 AND uname = '$u'
                 ORDER BY pay_period DESC";
     $commish_query = @mysql_query($commish) or die(mysql_error());
     $row1 = mysql_fetch_assoc($commish_query);
}
$date = "SELECT cycle_date
         FROM pay_cycle
         WHERE cycle_date <= NOW()
         ORDER BY cycle_date DESC
         LIMIT 0, 52"; // Two years worth
$date_query = @mysql_query($date) or die(mysql_error());

$page_title = 'Aurora Cooperative - View Commissions';
@include('templates/header.inc');

?>
<fieldset><legend><b>Select a Pay Cycle to View Your Commission</b></legend>
     <table width="100%">

     <tr>
          <td>
               <form action="<?php echo 'http://' . $_SERVER[HTTP_HOST] . '/commission.php'; ?>" method="post">

               <select name="date">
                    <option value="">Selct a Pay Cycle:</option>

                    <?php
                         while ($row = mysql_fetch_array($date_query))
                         {
                              echo '<option value = \'' . $row[0] . '\'>' . $row[0] . '</option>';
                         }
                    ?>
               </select>
          </td>
          <td>
               <?php
                    if(isset($_POST['submit']))
                    {
                         if (empty($row1['commish_dollar']))
                         {
                              $row1['commish_dollar'] = 0;
                         }
                         if (empty($row1['commish_cent']))
                         {
                              $row1['commish_cent'] = 0;
                         }
                         echo "$" . $row1['commish_dollar'] . "." . $row1['commish_cent'] . " for " . $_POST['date'];
                    }
               ?>
          </td>
     </tr>
     <tr>
          <td>
               <input type="submit" name="submit" value="Submit">
               </form>
          </td>
     </tr>
</table>
</fieldset>

<!-- Include the footer -->
<?php @include "templates/footer.inc" ?>
