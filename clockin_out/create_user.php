<?php
/*
*        Script Title: Clockin\Out v. 1.0
*        Page Title: create_user.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*        This is an Admin Only page. The Administrator can use this to create
*        or delete employees as needed. Keep in mind, deleting an employee will
*        completely wipe them out of the database. It gone. Bye bye. Make sure
*        you've severed all ties before doing so.
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

// Set the page title and include the HTML header.
// $page_title = 'Register';
include ('templates/header.inc');

if (isset($_POST['submit']))
{ // Handle the form.

     $message = NULL; // Create an empty new variable.

     // Check for a first name.
     if (empty($_POST['first_name']))
     {
          $fn = FALSE;
          $message .= '<p>You forgot to enter a first name!</p>';
     }
     else
     {
          $fn = $_POST['first_name'];
     }

     // Check for a last name.
     if (empty($_POST['last_name']))
     {
          $ln = FALSE;
          $message .= '<p>You forgot to enter a last name!</p>';
     }
     else
     {
          $ln = $_POST['last_name'];
     }

     // Check for a job title.
     if (empty($_POST['title']))
     {
          $t = FALSE;
          $message .= '<p>You forgot to enter a job title!</p>';
     }
     else
     {
          $t = $_POST['title'];
     }

     // Check for a username.
     if (empty($_POST['username']))
     {
          $u = FALSE;
          $message .= '<p>You forgot to enter a username!</p>';
     }
     else
     {
          $u = $_POST['username'];
     }

     // Check for a password and match against the confirmed password.
     if (empty($_POST['password1']))
     {
          $p = FALSE;
          $message .= '<p>You forgot to enter your password!</p>';
     }
     else
     {
          if ($_POST['password1'] == $_POST['password2'])
          {
               $p = $_POST['password1'];
          }
          else
          {
               $p = FALSE;
               $message .= '<p>Your password did not match the confirmed password!</p>';
          }
     }

     if ($fn && $ln && $t && $u && $p)
     { // If everything's OK.

          // Register the user in the database.
          require_once ('./connect/conn.inc.php'); // Connect to the db.

          // Make the query.
          $query = "INSERT INTO user_pass (uname, password, first_name, last_name, title, date_created)
                    VALUES ('$u', '$fn', '$ln', '$t', PASSWORD('$p'), NOW() )";
          $result = @mysql_query ($query); // Run the query.
          if ($result)
          { // If it ran OK.
               echo '<p><b>You have been registered!</b></p>';
               include ('./footer.inc'); // Include the HTML footer.
               exit(); // Quit the script.

          }
          else
          { // If it did not run OK.
               $message = '<p>You could not be registered due to a system error. We apologize for any inconvenience.</p><p>' . mysql_error() . '</p>';
          }

          mysql_close(); // Close the database connection.

     }
     else
     {
          $message .= '<p>Please try again.</p>';
     }

} // End of the main Submit conditional.

// Print the message if there is one.
if (isset($message))
{
     echo '<font color="red">', $message, '</font>';
}
?>


<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<fieldset><legend>Enter your information in the form below:</legend>

<p><b>First Name:</b>
<input type="text" name="first_name" size="15" maxlength="15" value="<?php if (isset($_POST['first_name'])) echo $_POST['first_name']; ?>" /></p>

<p><b>Last Name:</b>
<input type="text" name="last_name" size="30" maxlength="30" value="<?php if (isset($_POST['last_name'])) echo $_POST['last_name']; ?>" /></p>

<p><b>Job Title:</b>
<input type="text" name="title" size="40" maxlength="40" value="<?php if (isset($_POST['title'])) echo $_POST['title']; ?>" /> </p>

<p><b>User Name:</b>
<input type="text" name="username" size="10" maxlength="20" value="<?php if (isset($_POST['username'])) echo $_POST['username']; ?>" /></p>

<p><b>Password:</b>
<input type="password" name="password1" size="20" maxlength="20" /></p>

<p><b>Confirm Password:</b>
<input type="password" name="password2" size="20" maxlength="20" /></p>

</fieldset>

<div align="center"><input type="submit" name="submit" value="Register" /></div>

</form><!-- End of Form -->

// Include the HTML footer.
<?php include ('templates/footer.inc'); ?>

