<?php
/*
*        Script Title: Clockin/Out v 1.0
*        Page Title: admin.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*        This is the login page for the Administrator/Supervisor. It provides for
*        minor error checking, mostly just making sure neither fields are blank.
*
*        It will also let admin_page.php (the page it redirects to on success) know
*        if the password is the same as the username. If so, they must change their
*        password.
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

// If the Submit button has been pressed:
if (isset($_POST['submit']))
{ // # 1
     @require_once('../conn.inc.php');
     session_start();

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

     // Make sure USERNAME is not null
     if (empty($_POST['username']))
     { // # 1.3
          $u = FALSE;
          $message .= '<p>You forgot to enter your username!</p>';
     } // Close # 1.3
     else
     { // # 1.4

          $u = escape_data($_POST['username']);
          $_SESSION['admin_username'] = $u;
     } // Close # 1.4

     // Make sure PASSWORD is not null
     if (empty($_POST['password']))
     { // # 1.5
          $p = FALSE;
          $message .= '<p>You forgot to enter your password!<p>';
     } // Close # 1.5
     else
     { // # 1.6
          $p = escape_data($_POST['password']);
     } // Close # 1.6

     // If the USERNAME and PASSWORD are both not blank
     if ($u && $p)
     { // # 1.7

          $_SESSION['expired_pass'] = NULL;

          if ($u == $p)
          { // # 1.7.1
               $_SESSION['expired_pass'] = 1;
          } // Close # 1.7.1
          // Retrieve USERNAME and PASSWORD from the database
          $query = "SELECT uname, first_name, last_name, isroot
                    FROM admin
                    WHERE uname = '$u' AND pass = PASSWORD('$p')";
          $q_result = @mysql_query($query) or die(mysql_error());
          $row = mysql_fetch_assoc($q_result);
          
		// If we found a match:
          if (mysql_num_rows($q_result) == 1)
          { // # 1.7.2
               $_SESSION['loggedin'] = 1;
               $_SESSION['isroot'] = $row['isroot'];
               $_SESSION['first_name'] = $row['first_name'];
               $_SESSION['last_name'] = $row['last_name'];
               header("Location: http://" . $_SERVER[HTTP_HOST] . "/admin_page.php");
               exit();
          } // Close # 1.7.2

          else
          { // # 1.7.3

               // If the user name and passwords don't match, tell them.
               $message = '<p>The username and password entered do not
                           match those on file.</p>';
          } // Close # 1.7.3

          // Close the MySQL connection after we're done.
          mysql_close();
     } // Close # 1.7
     else
     { // # 1.8

          // If they messed up along the way by not entering something,
          // store their mistakes in a variable and print it later.
          $message .= '<p>Please try again.</p>';
     } // Close # 1.8
} // Close # 1

// The title of the page will be Login, and sent into header.inc,
// which is declared next.
$page_title = 'Aurora Cooperative - Login Administrator';
@include ('templates/header.inc');

// Error messages printed here
if (isset($message))
{ // # 2

     // They are seeing this message because they have messed up somewhere along the line.
     echo '<font color = "#ef0000">', $message, '</font>';
} // Close # 2
?>

<!-- THIS IS THE FORM WHICH WILL BE DISPLAYED WHEN USER IS NOT LOGGED IN -->
<form action=<?php echo "'http://" . $_SERVER[HTTP_HOST] . "/admin.php'"; ?> method="post">

<!-- The nice border around our login data starts here -->
<fieldset><legend><b>Sign In - Administrator</b></legend>
<br>

<!-- Begin the table -->
<table width="100%" cellpadding="5">

     <!-- Username field -->
     <tr align="left">
          <td>
               <b>
               Username:&nbsp;
               <input type="text" name="username" maxlength="40">
               </b>
          </td>
     </tr>

     <td>
     <!-- This is here for formating purposes -->
     </td>


     <!-- Password field -->
     <tr align="left">
          <td>
               <b>
               Password:&nbsp;&nbsp;
               <input type="password" name="password" maxlength="15">
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
</form>
<!-- Include the footer -->
<?php @include "templates/footer.inc" ?>
