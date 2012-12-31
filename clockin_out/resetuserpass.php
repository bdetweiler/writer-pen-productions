<?php
/*
*        Script Title: Clockin/Out v 1.0
*        Page Title: resetuserpass.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*        This page is meant for administrators to have the ability
*        to reset a user's password to their username, should they
*        forget it. It's about as simple as it gets. Select the user,
*        click "Submit", and bye-bye old, forgotten password. Hello
*        username == password. Of course, when they try to log in with
*        their new password, they will be redirected to the changepasswd.php
*        page. Ya can't have username == password! It's INSAAAAANNNEEEE.
*
*        01 AUG 2004: I just added the ability to reset the admin account
*        password. This gives the admin the power to reset the employee's password,
*        the administrator's password, or both. Even does some error checking.
*        Rock!
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
include('../conn.inc.php');
// If no session is present, redirect the user
if (!isset($_SESSION['isroot']))
{ // # 1

     // Redirect them back to the login page.
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
     exit();
} // Close # 1

// Set the page title and include the HTML header.
$page_title = "Aurora Cooperative - Reset a User's Password";
include ('templates/header.inc');

?>

<table border="0" cellspacing="0" cellpadding="0" width ="100%">
     <tr align="right">
          <td>

          <?php
               $access_level = 'admin';
               $page = 'resetuserpass';
               include('templates/tabs.inc');
          ?>
          </td>
     </tr>
</table>

<?php

// Innitialize error/success message to NULL
$message = NULL;
// Input:   A string ($u) which is the username selected
// Output:  None
// Returns: An integer (1, or 0) upon success or failure
// Notes:   This takes the username of the person who's password they are trying
//          to convert. If it is already reset, return 1 without doing anything
//          and let them think they actually reset it. Otherwise, actually
//          reset it. This is for the user_pass table.
function reset_user_pass($u)
{
     // If they try to reset a password that is already reset, it will return 0
     // rows affected. To combat this, we will first check if the password is
     // already reset.
     $password_check = "SELECT pass
                        FROM user_pass
                        WHERE uname = '$u'
                        AND pass = PASSWORD('$u')";
     $password_check_result = mysql_query($password_check)
                              or die(mysql_error());
     // If the previous check for a reset password turns up nothing, then go ahead
     // and reset it.
     if(!(mysql_num_rows($password_check_result) == 1))
     {
          $employee_reset = "UPDATE user_pass
                             SET pass = PASSWORD('$u')
                             WHERE uname = '$u'";
          $employees_reset_result = mysql_query($employee_reset)
                                    or die(mysql_error());
          $change_pass_rows = mysql_affected_rows();

          // If everything went ok, this should return 1.
          return $change_pass_rows;
     }
     else
     {
          // If we got here, the password was already reset. Return 1 and let
          // them think they did something spectacular.
          return 1;
     }
}

// Input:   A string ($u) which is the username selected
// Output:  None
// Returns: An integer (1, or 0) upon success or failure
// Notes:   This takes the username of the person who's password they are trying
//          to convert. If it is already reset, return 1 without doing anything
//          and let them think they actually reset it. Otherwise, actually
//          reset it. This is for the user_pass table.
function reset_admin_pass($u)
{
     // If they try to reset a password that is already reset, it will return 0
     // rows affected. To combat this, we will first check if the password is
     // already reset.
     $password_check = "SELECT pass
                        FROM admin
                        WHERE uname = '$u'
                        AND pass = PASSWORD('$u')";
     $password_check_result = mysql_query($password_check)
                              or die(mysql_error());
     // If the previous check for a reset password turns up nothing, then go ahead
     // and reset it.
     if(!(mysql_num_rows($password_check_result) == 1))
     {
          $administrator_reset = "UPDATE admin
                                  SET pass = PASSWORD('$u')
                                  WHERE uname = '$u'";
          $administrator_reset_result = mysql_query ($administrator_reset)
                                        or die(mysql_error());
          $admin_change_pass_rows = mysql_affected_rows();
          return $admin_change_passs_rows;
     }
     else
     {
          // If we got here, the password was already reset. Return 1 and let
          // them think they did something spectacular.
          return 1;
     }
}
if (isset($_POST['submit']))
{ // # 11

     if(isset($_POST['employee']))
     {
          $u = $_POST['employee'];
     }
     else
     {
          $u = NULL;
     }
#############################################################################
#					ONLY EMPLOYEE 								 #
#############################################################################

     // If they opted to ONLY reset the employee's password
     if($_POST['administrator'] == "user")
     {
          // If the password was reset for the employee, tell them.
          if(reset_user_pass($u) == 1)
          {
               $message .= "Employee account successfully reset.";
          }

          // If it failed, tell them also.
          else
          {
               $message .= "There was an error. Report this problem to the
                            system administrator.";
          }
     }

     // If they ONLY wanted to reset the administrator account:
     if($_POST['administrator'] == "only")
     {

#############################################################################
#					ONLY ADMIN 								 #
#############################################################################
          // If the request went through
          if(reset_admin_pass($u) == 1)
          {
               $message .= "Supervisor account reset successfully.<br \>";
          }

          // Otherwise, tell them it failed
          else
          {
               $message .= "There was an error. Please ensure that the person
                            who's account you are trying to reset is a
                            supervisor. If you still have problems, report this
                            to your system administrator.<br />";
          }
     }
     
#############################################################################
#					BOTH		 								 #
#############################################################################
	// If they wanted to reset BOTH the admin's and employee's account:
     if($_POST['administrator'] == "both")
     {
          // If it worked, tell them.
          if(reset_user_pass($u) == 1)
          {
               $message .= "Employee account reset successfully.<br />";
          }

          // If it didn't, tell them
          else
          {
               $message .= "There was an error. Employee account <i>NOT</i>
                            reset. Contact your system administrator.";
          }
          if(reset_admin_pass($u) == 1)
          {
               $message .= "Supervisor account reset successfully.<br />";
          }
          else
          {
               $message .= "There was an error. Please ensure that the person
                            who's account you are trying to reset is a
                            supervisor. If you still have problems, report this
                            to your system administrator.<br />";
          }
     }
}

echo $message;
?>
<!-- THIS IS THE FORM WHICH WILL BE DISPLAYED WHEN USER IS NOT LOGGED IN -->
<form action="<?php echo 'http://' . $_SERVER[HTTP_HOST] . '/resetuserpass.php'; ?>" method="post">

<!-- The nice border around our login data starts here -->
     <fieldset>
          <legend>
               <b>
               Reset A User's Password - Administrator
               </b>
          </legend>
          <br />

          <?php
               $employees = "SELECT uname
                             FROM user_pass
                             WHERE 1
                             ORDER BY uname";
               $employees_results = mysql_query($employees) or die(mysql_error());
               //$employees_array = mysql_fetch_array($employees_results);
          ?>

          <!-- Begin the table -->
          <table width="100%">
          <cellpadding="5">
               <tr>
                    <td>
                         <select name="employee">
                              <option value="">Reset Password:</option>
                              <?php
                                   $i = 0;
                                   while ($rows = mysql_fetch_array($employees_results))
                                   {
                                        $is_admin = "SELECT uname
                                                     FROM admin
                                                     WHERE uname = '$rows[$i]'";
                                        $is_admin_query = mysql_query($is_admin)
                                                          or die(mysql_error());
                                        $is_admin_result = mysql_fetch_array($is_admin_query);
                                        if($is_admin_result)
                                        {
                                             echo "<option value=\"$rows[$i]\">***$rows[$i]***</option>\n";
                                        }
                                        else
                                        {
                                             //echo "<option value=\"test\">test</option>\n";
                                             echo "<option value=\"$rows[$i]\">$rows[$i]</option>\n";
                                        }
                                   }
                              ?>
                         </select>
                    </td>
                    <td>
                    <?php echo '<font color="#ef0000">
                                <i>All passwords are reset to the employee\'s username.<br />
                                Astrisks (***) denote an employee who is also a supervisor.</i>';

                    ?>
                    </td>
               </tr>
               <tr>
                    <td>
                         <b>Reset Password <i>ONLY</i> for Employee Account:</b>&nbsp;
                         <input type="radio" name="administrator" value="user" checked="checked">
                    </td>
               </tr>
               <tr>
                    <td>
                         <b>Reset Password <i>ONLY</i> for Supervisor Account:</b>&nbsp;
                         <input type="radio" name="administrator" value="only">
                    </td>
               </tr>
               <tr>
                    <td>
                         <b>Reset Password for <i>BOTH</i> Supervisor and Employee Accounts:</b>&nbsp;
                         <input type="radio" name="administrator" value="both">
                    </td>
               </tr>
               <!-- Submit button -->
               <tr align="left">
                    <td>
                         <input type="submit" name="submit" value="Submit">
                    </td>
               </tr>
          </table>
          <br />
     </fieldset>
<!-- Include the footer -->
<?php include "templates/footer.inc" ?>
</form>
