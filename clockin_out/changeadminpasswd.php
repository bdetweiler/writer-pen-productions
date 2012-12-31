<?php
/*		
*        Script Title: Clockin\Out v. 1.0
*        Page Title: changeadminpasswd.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*        This page will allow the administrator and supervisors to change their
*        passwords. If their passwords match their usernames, they must be changed
*        on first login.
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

if (!(isset($_SESSION['loggedin'])))
{ // # 1
 	
     // Redirect them back to the login page.
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
     exit();
} // Close # 1

$u = $_SESSION['admin_username'];
$redirect = 0;

if (isset($_POST['submit']))
{ // # 1

    require_once('../conn.inc.php');

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

     // Make sure OLD PASSWORD is not null
     if (empty($_POST['old_password']))
     { // # 1.3
          $op = FALSE;
          $message .= '<p>You forgot to enter your old password!</p>';
     } // Close # 1.3
     else
     { // # 1.4
          $op = escape_data($_POST['old_password']);
     } // Close # 1.4

     // Make sure NEW PASSWORD is not null
     if (empty($_POST['new_password']))
     { // # 1.5
          $np = FALSE;
          $message .= '<p>You forgot to enter your new password!<p>';
     } // Close # 1.5
     else
     { // # 1.6
          $np = escape_data($_POST['new_password']);
     } // Close # 1.6

     // Make sure CONFIRM PASSWORD is not null

     if (empty($_POST['confirm_password']))
     { // # 1.7
          $cp = FALSE;
          $message .= '<p>You forgot to confirm your password!<p>';
     } // Close # 1.7
     else
     { // # 1.8
          $cp = escape_data($_POST['confirm_password']);
     } // Close # 1.8
     if ($np != $cp)
     { // # 1.9
          $np = FALSE;
          $cp = FALSE;
          $message .= '<p>Your New Password field does not match your Confirm Password field.';
     } // Close # 1.9
     
	 // If the USERNAME and PASSWORD are both not blank
     if ($op && $np && $cp)
     { // # 1.10
          
	     // Retrieve USERNAME and PASSWORD from the database
          $query = "SELECT pass
                    FROM admin
                    WHERE uname = '$u' AND pass = PASSWORD('$op')";
          $q_result = mysql_query($query) or die(mysql_error());
		
		// If we found a match:
          if (mysql_num_rows($q_result))
          { // # 1.10.1
               $change_pass = "UPDATE admin
                               SET pass = PASSWORD('$np')
                               WHERE uname = '$u'";

               $change_pass_result = mysql_query($change_pass) or die(mysql_error());
               $change_pass_rows = mysql_affected_rows();

               if ($change_pass_rows == 1)
               { // # 1.10.1.1
                    $redirect = 1;
               } // Close # 1.10.1.1


          } // Close # 1.10.1
          else
          { // # 1.10.2
               
		     // If the user name and passwords don't match, tell them.
               $message = '<p>The Old Password you supplied does not match
                              the one on file.</p>';
          } // Close # 1.10.2
          mysql_close(); // Close the MySQL connection after we're done.
     } // Close # 1.10
     else
     { // # 1.11

          // If they messed up along the way by not entering something,
          // store their mistakes in a variable and print it later.
          $message .= '<p>Please try again.</p>';
     } // Close # 1.11
} // Close # 1

if ($redirect == 1)
{ // # 2
     echo '<head>';
     echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
     echo '<meta http-equiv="REFRESH" content="7; URL=http://' . $_SERVER[HTTP_HOST] . '/logout.php">';
     echo '</head>';
     echo '<div align=center><br><br><br>
           Your password has been changed. If you are not redirected, click <a
		 href="<?php echo $_SERVER[HTTP_HOST]; ?>/logout.php">HERE</a>';
     exit();
} // Close # 2

$page_title = "Administrator - Change Your Password";
include ('templates/header.inc');

// Error messages printed here
if (isset($message))
{ // # 3
     // They are seeing this message because they have messed up somewhere along the line.
     echo '<font color = "red">', $message, '</font>';
} // Close # 3
?>

<table width="100%">
<tr>
<td align="right">
<?php
     // Include the tabs
	$access_level = 'admin';
     $page = 'change_admin_pass';
     @include('templates/tabs.inc');
?>
</td>
</tr>
</table>

<form action="<?php echo ('http://' . $_SERVER[HTTP_HOST] . '/changeadminpasswd.php'); ?>" method="post">

<!-- The nice border around our login data starts here -->
<fieldset><legend><b>Change Your Password</b></legend>
<br>

<!-- Begin the table -->
<table width="100%" border="0" cellpadding="5">

     <!-- Password field -->
     <tr align="left">
          <td>
               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
                                   Old Password:
                              </b>
                         </td>
                         <td align="right">
                              <input type="password" name="old_password" maxlength="15">
                         </td>
                    </tr>
               </table>
          </td>
     </tr>

     <!-- <tr> ???-->
     <td>
     <!-- This is here for formating purposes -->
     </td>

     <!-- Here's the password recommendations -->
     <th rowspan="3">
          <font color="ef0000" size="2">
          <b><i>
          Username and Password are <u>Case Sensitive</u>.
          <br />Passwords can be all letters, all numbers, or a combination.
          <br />Passwords must be at least 6 characters long. 
          </i></b>
          </font>
     </th>

     <!-- Password field -->
     <tr align="left">
         <td>
              <table width="300" border="0">
                   <tr>
                        <td align="left">
                             <b>
                                  New Password:
                             </b>
                        </td>
                        <td align="right">
                             <input type="password" name="new_password" maxlength="15">
                        </td>
                   </tr>
              </table>
     </tr>

     <!-- Radio buttons -->
     <tr align="left">
          <td>
               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
                                   Confirm Password:
                              </b>
                         </td>
                         <td align="right">
                              <input type="password" name="confirm_password" maxlength="15">
                         </td>
                    </tr>
               </table>
          </td>
     </tr>

     <!-- Submit button -->
     <tr align="left">
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
