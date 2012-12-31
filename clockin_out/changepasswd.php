<?php
/*
*        Script Title: Clockin\Out v. 1.0
*        Page Title: changepasswd.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*        This page allows the users to change their passwords. If their password
*        matches their username, they will be redirected here and must change it
*        upon first login.
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

if (!(isset($_SESSION['first_name'])))
{ // # 1

       // Redirect them back to the login page.
       header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
       exit();
} // Close # 1

$u = $_SESSION['username'];

$redirect = 0;


if (isset($_POST['submit']))
{ // # 1

require_once('../conn.inc.php');

        // Function for normalizing the input.
        function escape_data($data)
        { // # 1.1

                // This is in conn.inc.php
                global $dbc;

                if (ini_get('magic_quotes_gpc'))
                { // # 1.1.1
                        $data = stripslashes($data);
                } // Close # 1.1.1

                return trim(mysql_real_escape_string($data));

        } // Close # 1.1

        // Innitialize $message
        $message = NULL;
###########################################################################
#                   OLD PASSWORD                                          #
###########################################################################
        
	   if (empty($_POST['old_password']))
        { // # 1.3
                $op = NULL;
                $message .= '<p>You forgot to enter your old password!</p>';
        } // Close # 1.3
        else
        { // # 1.4

                $op = escape_data($_POST['old_password']);

        } // Close # 1.4
###########################################################################
#                  NEW PASSWORD                                           #
###########################################################################
        if (empty($_POST['new_password']))
        { // # 1.5
                $np = NULL;
                $message .= '<p>You forgot to enter your new password!<p>';
        } // Close # 1.5

        else
        { // # 1.6
                $np = escape_data($_POST['new_password']);
			 if(strlen($np) < 6)
			 { 
			      $np = NULL;
			 	 $message .= '<p>Your new password must be at least 6
					characters long.<p>'; 
			 }
						 
        } // Close # 1.6
	   
###########################################################################
#                CONFIRM PASSWORD                                         #
###########################################################################
        if (empty($_POST['confirm_password']))
        { // # 1.7

                $cp = NULL;
                $message .= '<p>You forgot to confirm your password!<p>';

        } // Close # 1.7

        else
        { // # 1.8

                $cp = escape_data($_POST['confirm_password']);
			 if(strlen($cp) < 6)
			 { 
			      $cp = NULL;
			 }

        } // Close # 1.8

        if ($np != $cp)
        { // # 1.9
                $np = NULL;
                $cp = NULL;
                $message .= '<p>Your New Password field does not match your Confirm Password field.';
        } // Close # 1.9
        
	   // If the USERNAME and PASSWORD are both not blank
##############################################################################
#                   IF ALL TRUE                                              #
##############################################################################
        if ($op && $np && $cp)
        { // # 1.10
                // Retrieve USERNAME and PASSWORD from the database
                $query = "SELECT pass
                          FROM user_pass
                          WHERE uname = '$u' AND pass = PASSWORD('$op')";
                $q_result = mysql_query($query) or die(mysql_error());

                // If we found a match:
                if (mysql_num_rows($q_result) == 1)
                { // # 1.10.1

                   $change_pass = "UPDATE user_pass
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

?>

<?php

  // Redirect after Password change
  if ($redirect == 1)
  { // # 2
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
        echo '<meta http-equiv="REFRESH" content="5; URL=http://' . $_SERVER[HTTP_HOST] . '/logout.php">';
        echo '</head>';
        echo '<div align=center><br><br><br>
              Your password has been changed and you will be redirected to the login page.</div>';

        exit();
  } // Close # 2

$page_title = "Aurora Cooperative - Change Your Password";
@include ('templates/header.inc');

// Error messages printed here
if (isset($message))
{ // # 3
     // They are seeing this message because they have messed up somewhere along the line.
     echo '<font color = "red">', $message, '</font>';
} // Close # 3
?>


<?php
if ($_SESSION['expired_pass'] == 1)
{
     echo '<TR><TD align=left><font color=#ef0000>';
     echo 'Your password has expired. You must create a new password before you can enter your account.';
     echo '</font></TD></TR>';
}
?>
<table width="100%" border="0">
<tr align="right">
<td>
<?php
     $access_level = 'user';
     $page = 'changepasswd';
     @include('templates/tabs.inc');
?>
</td>
</tr>
</table>
<!-- THIS IS THE FORM WHICH WILL BE DISPLAYED WHEN USER IS NOT LOGGED IN -->
<form action="<?php echo "http://" . $_SERVER[HTTP_HOST] . "/changepasswd.php"; ?>" method="post">

<!-- The nice border around our login data starts here -->
<fieldset><legend><b>Change Your Password</b></legend>
<br>

<!-- Begin the table -->
<table width ="100%" border=0>
<cellpadding = 5>

<!-- Password field -->
<tr align = left>
   <td>
      <table width = 300 border=0>
        <tr>
        <td align=left>
          <b>

         Old Password:
         </b>
        </td>
        <td align=right>
        <input type="password" name="old_password" maxlength="15">
        </td>
        </tr>
      </table>
      </b>
   </td>
</tr>

<td>
<!-- This is here for formating purposes -->
</td>

<!-- Here's the password recommendations -->
<th rowspan="3">
   <font color="ef0000" size="2">
   <b><i>
   Username and Password are <i>Case Sensitive</i>.
   <br>Passwords can be all letters, all numbers, or a combination,
   <br>but cannot include any special characters.
   <br>Username consists of your last name and first innitial.
   <br>Passwords must be at least 6 characters long.
   </i></b>
   </font>
</th>

<!-- Password field -->
<tr align = left>
   <td>
            <table width = 300 border=0>
        <tr>
        <td align=left>
          <b>

         New Password:
         </b>
        </td>
        <td align=right>
        <input type="password" name="new_password" maxlength="15">
        </td>
        </tr>
      </table>
      </b>
   </td>
</tr>

<!-- Radio buttons -->
<tr align = left>
   <td>
<table width = 300 border=0>
        <tr>
        <td align=left>
          <b>

         Confirm Password:
         </b>
        </td>
        <td align=right>
        <input type="password" name="confirm_password" maxlength="15">
        </td>
        </tr>
</table>
      </b>
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
<?php @include "templates/footer.inc" ?>
</form>
