<?php
/*
*        Script Title: Clockin\Out v. 1.0
*        Page Title: addremoveusers.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*        This page allows the Administrator to add users into and
*        remove users from the database. When either is submitted
*        the users are returned to the page with the appropriate message
*        telling them if they were successful or not.
*
*        Of course, they may not remove Root. It would not be a total disaster
*        if they did, as one could go into PHPmyAdmin or MySQL moniter and put
*        Root back in manually. But reall, that would be a pain. Oh, and Root is
*        the only one allowed on this page. We don't want just anyone doing
*        stuff like this.
*
*        There is no double-checkin-hand-holding-babying-protecting here. If you
*        choose to remove a user, buh-bye. They're gone. Want it back? Hope you
*        make regular back-ups of your database. It's hard to do something like
*        this on accident, though. It really does take a conscious effort.
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
@require_once('../conn.inc.php');

// If no session is present, redirect the user
if ($_SESSION['isroot'] != 1)
{ // # 1
     // Redirect them back to the login page.
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
     exit();
} // Close # 1

// Include the header whenever the page loads
$page_title = "Aurora Cooperative - Add/Remove Users";
include ('templates/header.inc');

################################################################################
#                         ADD USER                                             #
################################################################################

if (isset($_POST['add']))
{ // # 2

     // Function for normalizing the input.
     function escape_data($data)
     { // # 2.1

          if (ini_get('magic_quotes_gpc'))
          { // # 2.1.1
               $data = stripslashes($data);
          } // Close # 2.1.1

          return trim(mysql_real_escape_string($data));

     } // Close # 2.1

     // Innitialize $message to NULL
     $message = NULL;


     // Make sure USERNAME is not empty
     if (empty($_POST['username']))
     { // # 2.2

          // If it is, stop the script and issue and error msg.
          $u = FALSE;
          $message .= '<p>You forgot to enter a username!<p>';

     } // Close # 2.2

     else
     { // # 2.3
          $u = escape_data($_POST['username']);
     } // Close # 2.3

     /* Currently commented out per Jeff's preference.
     // Make sure TITLE is not null
     if (empty($_POST['title']))
     { // # 2.4
          $title = FALSE;
          $message .= '<p>You forgot to enter a title!</p>';
     } // Close # 2.4
     else
     { // # 2.5
          $title = escape_data($_POST['title']);
     } // Close # 2.5
     */
     $title = TRUE; // This is to comply with the above changes

     // Make sure FIRST NAME is not null
     if (empty($_POST['first_name']))
     { // # 2.6
          $fn = FALSE;
          $message .= '<p>You forgot to enter a username!</p>';
     } // Close # 2.6
     else
     { // # 2.7
          $fn = escape_data($_POST['first_name']);
     } // Close # 2.7

     // Make sure LAST NAME is not null
     if (empty($_POST['last_name']))
     { // # 2.8
          $ln = FALSE;
          $message .= '<p>You forgot to enter a last name!<p>';
     } // Close # 2.8

     else
     { // # 2.9
          $ln = escape_data($_POST['last_name']);
     } // Close # 2.9

     if ($u && $title && $fn && $ln)
     { // # 2.10

          // Was Supervisor selected?
          if ($_POST['supervisor'])
          {
               $query = "INSERT INTO admin
                         SET uname = '$u', pass = PASSWORD('$u'),
                         first_name = '$fn', last_name = '$ln'";
               $q_result = @mysql_query($query)
                                               or die(mysql_error() . '<br>Go <a href="/addremoveusers.php"> back</a>.');
               $success = mysql_affected_rows();

               // If the entry was successful;
               if($success)
               {
                    echo '<br>User <font color="#EF0000">' . $u . '</font>
                                 (supervisor) successfully added.<br>';
                          $admin = '<br>This user has been added as an administrator but
                                 not as a user.<br>';
               }
               else
               {
                    // If the request did not go through.
                    $message = '<p>Your request did not go through
                                check your database connectivity.</p>';
               }
          }
          // Insert USERNAME, PASSWORD, etc, into the database
          $query = "INSERT INTO user_pass
                    SET uname = '$u', pass = PASSWORD('$u'),
                    first_name = '$fn', last_name = '$ln', date_created = NOW()";
          $q_result = @mysql_query($query)
                                          or die(mysql_error() . $admin .  '<br>Go <a href="/addremoveusers.php"> back</a>.');
          $success = mysql_affected_rows();

          // If we found a match:
          if ($success)
          { // # 2.10.1
               echo 'User <font color="#EF0000">' . $u . '</font> successfully added.';
          } // Close # 2.10.1
          else
          { // # 2.10.2

               // If the it didn't go through, tell them.
               $message = '<p>Your request did not go through. Check your
                           database connectivity.</p>';
          } // Close # 2.10.2

     } // Close # 2.10
     else
     { // # 2.11

          // If they messed up along the way by not entering something,
          // store their mistakes in a variable and print it later.
          $message .= '<p>Please try again.</p>';
     } // Close # 2.11
} // Close # 2

################################################################################
#                             MODIFY USER                                      #
################################################################################

if (isset($_POST['modify']))
{ // # 2

// Anything left blank will just be left the same and not changed.

     // Function for normalizing the input.
     function escape_data($data)
     { // # 2.1

          if (ini_get('magic_quotes_gpc'))
          { // # 2.1.1
               $data = stripslashes($data);
          } // Close # 2.1.1

          return trim(mysql_real_escape_string($data));
     } // Close # 2.1

     // Innitialize $vars to NULL
     $message  = NULL;
     $employee = NULL;
     $u        = NULL;
     $fn       = NULL;
     $ln       = NULL;

     if($_POST['employeeMod'] == "")
     {
          // This is the only requirement
          $employee = FALSE;

          // Didn't select a user to modify
          $message .= "You must select a user to modify first.";
     }
     else
     { // # 4.1.2

          // If it has been set, then they're good to go. Anything they have done
          // will be set. Blank fields will be left as-is.
          $employee = $_POST['employeeMod'];
     } // Close # 4.1.2

     // Assign and check username
     $u = escape_data($_POST['usernameMod']);
     if (empty($u))
     { // # 2.2

          // If it is, stop the script and issue an error msg.
          $u = NULL;
     } // Close # 2.2

     $fn = escape_data($_POST['first_nameMod']);
     // Make sure FIRST NAME is not null
     if (empty($fn))
     { // # 2.6
          $fn = NULL;
     } // Close # 2.6

     $ln = escape_data($_POST['last_nameMod']);
     // Make sure LAST NAME is not null
     if (empty($ln))
     { // # 2.8
          $ln = NULL;

     } // Close # 2.8


     if ($employee)
     { // # 2.10
          // Welcome to if(isset()) hell

          // Insert USERNAME, PASSWORD, etc, into the database
          $query = "UPDATE user_pass
                    SET";
          // This will fix ALL the entries in the work_hours table
          $work_hours_query = "UPDATE work_hours
                               SET";
          $admin = "UPDATE admin
                    SET";

          if(isset($u))
          {
               $query .= " uname = '$u'";
               $work_hours_query .= " uname = '$u'";
               if(isset($_POST['supervisorMod']))
               {
                    $admin .= " uname = '$u'";
               }
          }

          if(isset($fn))
          {
               // The difference between the two is a comma and a space
               if(isset($u))
               {
                    $query .= ", first_name = '$fn'";
                    $work_hours_query .= ", first_name = '$fn'";
                    if(isset($_POST['supervisorMod']))
                    {
                         $admin .= ", first_name = '$fn'";
                    }
               }
               else
               {
                    $query .= " first_name = '$fn'";
                    $work_hours_query .= " first_name = '$fn'";
                    if(isset($_POST['supervisorMod']))
                    {
                         $admin .= " first_name = '$fn'";
                    }
               }
          }

          if(isset($ln))
          {
               // Again, the differences are a comma and a space
               if(isset($u) || isset($fn))
               {
                    $query .= ", last_name = '$ln'";
                    $work_hours_query .= ", last_name = '$ln'";
                    if(isset($_POST['supervisorMod']))
                    {
                         $admin .= ", last_name = '$ln'";
                    }
               }
               else
               {
                    $query .= " last_name = '$ln'";
                    $work_hours_query .= " last_name = '$ln'";
                    if(isset($_POST['supervisorMod']))
                    {
                         $admin .= " last_name = '$ln'";
                    }
               }
          }

          $query .= ", date_created = NOW()
                     WHERE uname = '$employee'";
          $admin .= " WHERE uname = '$employee'";
          $work_hours_query .= " WHERE uname = '$employee'";

          // Run the user_pass query
          $q_result = @mysql_query($query)
                      or die(mysql_error() . '<br>Go <a href="/addremoveusers.php"> back</a>.');
          // Was it successful?
          $success = mysql_affected_rows();

          // If we found a match:
          if ($success)
          { // # 2.10.1
               echo 'User <font color="#EF0000">' . $u . '</font> successfully modified.';
          } // Close # 2.10.1
          else
          { // # 2.10.2

               // If the it didn't go through, tell them.
               $message = '<p>Your request did not go through. Contact the system
                           administrator.</p>';
          } // Close # 2.10.2
          if(isset($_POST['supervisorMod']))
          {
               $q_result      = @mysql_query($admin)
                                or die(mysql_error() . '<br>Go <a href="/addremoveusers.php"> back</a>.');

               $admin_success = mysql_affected_rows();
               if($admin_success)
               {
                    $message = '<br>Administrator <font color="#EF0000">' . $u . '</font>
                                successfully modified.<br>';
               }

           }


     } // Close # 2.10
     else
     { // # 2.11

          // If they messed up along the way by not entering something,
          // store their mistakes in a variable and print it later.
          $message .= '<p>Please try again.</p>';
     } // Close # 2.11

} // Close # 2

################################################################################
#                             REMOVE USER                                      #
################################################################################

// If they pressed the Remove button:
if(isset($_POST['remove']))
{ // # 4
     $u = $_POST['employee'];

     // Make sure they're not trying to delete Root:
     if ($u != 'root')
     { // Close # 4.1

          // Sounds good. Let's delete all the entries out of the database for $u.
          $remove = "DELETE FROM user_pass
                     WHERE uname = '$u'
                     LIMIT 1";

          $remove_query = @mysql_query($remove);
          $remove_success = mysql_affected_rows();

          $remove2 = "DELETE FROM work_hours
                      WHERE user_id = '$u'";
          $remove2_query = @mysql_query($remove2);
          $remove2_success = mysql_affected_rows();

          $remove3 = "DELETE FROM commission
                      WHERE uname = '$u'";
          $remove3_query = @mysql_query($remove3);
          $remove3_success = mysql_affected_rows();

                // This will not always be true. Not everyone is an administrator.
                $remove4 = "DELETE FROM admin
                                    WHERE uname = '$u'";
             $remove4_query = @mysql_query($remove4);
                $remove4_success = mysql_affected_rows();

          // mysql_affected_rows uses the last connection.
          if ($remove_success && $remove2_success && remove3_success)
          { // # 4.1.1

               // All is good. Everything worked.
               echo "User " . $u . " successfully removed from the database.";
          } // Close # 4.1.1

          else
          { // # 4.1.2

               // Something or everything is not good.
               $message .= "You query did not execute successfully. Check the database and try again.";
          } // Close # 4.1.2
     } // Close # 4.1
     else
     { // # 4.2

          // Trying to delete root. Tsk tsk.
          $message .= "You can't delete root.";
     } // Close # 4.2
} // Close # 4

// Error messages printed here if there are any present.
if (isset($message))
{ // # 3

     // They are seeing this message because they have messed up somewhere along the line.
     echo '<font color = "#ef0000">', $message, '</font>';
} // Close # 3

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional/EN"
"http://www.w3.org/TR/2000/REC-xhtml-20000126/DTD.xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<meta http-equiv=Content-Type content="text/html; charset=windows-1252">

<table width="100%">
     <tr>
          <td align="right">
               <?php
                    $access_level = 'admin';
                    $page = 'add_remove_users';
                    @include('templates/tabs.inc');
               ?>
          </td>
     </tr>
</table>

<!-- THIS IS THE FORM WHICH WILL BE DISPLAYED WHEN USER IS NOT LOGGED IN -->
<form action="<?php echo("http://" . $_SERVER[HTTP_HOST] . "/addremoveusers.php"); ?>" method="post">

<!-- The nice border around our login data starts here -->
<fieldset><legend><b>Add New User</b></legend>

<!------------------Add User Section ------------------------------------------>
<table width="100%" border="0" cellpadding="5">
     <tr>
          <td>
               <!-- Username Field -->     <!--   -->
               <tr align="left">
                    <td>
                         <table width="300" border="0">
                              <tr>
                                   <td align="left">
                                        <b>
                                        Username:
                                        </b>
                                   </td>
                                   <td align="right">
                                        <input type="text" name="username" maxlength="40">
                                   </td>
                              </tr>
                         </table>
                    </td>
               </tr>
          </td>
     </tr>

     <!-- First Name Field -->
     <tr align="left">
          <td>
               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
                                   First Name:
                              </b>
                         </td>
                         <td align="right">
                              <input type="text" name="first_name" maxlength="400">
                         </td>
                    </tr>
              </table>

          </td>
     </tr>

     <!-- Last Name Field -->
     <tr align="left">
          <td>
               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
                                   Last Name:
                              </b>
                         </td>
                         <td align="right">
                              <input type="text" name="last_name" maxlength="40">
                         </td>
                    </tr>
               </table>
          </td>
     </tr>

     <!-- Is Supervisor? -->
     <tr align="left">
          <td>
               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
                                   Supervisor:
                              </b>
                         </td>
                          <td align="right">
                              <input type="checkbox" name="supervisor" value="Supervisor">
                         </td>
                    </tr>
               </table>
          </td>
     </tr>

     <!-- Submit button -->
     <tr align="left">
          <td>
               <input type="submit" name="add" value="Add User">
          </td>

     </tr>
</table>
</fieldset>
<br>
<form action="<?php echo ("Location: http://" . $_SERVER[HTTP_HOST] . "/addremoveusers.php"); ?>" method="post">
<!-- The nice border around our login data starts here -->
<fieldset><legend><b>Modify Username</b></legend>
<font color="#ef0000"><strong>Any fields left blank will not be modified. If the person is<br>
                              a supervisor, make sure to check the SUPERVISOR box.</strong></font>
<!------------------Modify User Section --------------------------------------->
<table width="100%" border="0" cellpadding="5">
     <tr>
          <td>
               <?php
                    require_once('../conn.inc.php');
                    $employees = "SELECT uname
                                  FROM user_pass
                                  WHERE 1
                                  ORDER BY uname";
                    $employees_results = @mysql_query($employees)
                                         or die(mysql_error());

               ?>

               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
                                   Select Username:
                              </b>
                         </td>
                         <td align="right">
                              <select name="employeeMod">
                                   <option value="">Modify Username:</option>
                                   <?php
                                        while ($rows = mysql_fetch_array($employees_results))
                                        {
                                             // Create drop down list of names.
                                             echo "<option value=\"$rows[0]\">$rows[0]</option>\n";
                                        }
                                   ?>
                              </select>
                         </td>
                    </tr>
               </table>
          </td>
     </tr>
     <tr>
          <td>
               <!-- Username Field -->
               <tr align="left">
                    <td>
                         <table width="300" border="0">
                              <tr>
                                   <td align="left">
                                        <b>
                                        Username:
                                        </b>
                                   </td>
                                   <td align="right">
                                        <input type="text" name="usernameMod" maxlength="40">
                                   </td>
                              </tr>
                         </table>
                    </td>
               </tr>
          </td>
     </tr>

     <!-- First Name Field -->
     <tr align="left">
          <td>
               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
                                   First Name:
                              </b>
                         </td>
                         <td align="right">
                              <input type="text" name="first_nameMod" maxlength="400">
                         </td>
                    </tr>
              </table>

          </td>
     </tr>

     <!-- Last Name Field -->
     <tr align="left">
          <td>
               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
                                   Last Name:
                              </b>
                         </td>
                         <td align="right">
                              <input type="text" name="last_nameMod" maxlength="40">
                         </td>
                    </tr>
               </table>
          </td>
     </tr>

     <!-- Is Supervisor? -->
     <tr align="left">
          <td>
               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
                                   Supervisor:
                              </b>
                         </td>
                          <td align="right">
                              <input type="checkbox" name="supervisorMod" value="Supervisor">
                         </td>
                    </tr>
               </table>
          </td>
     </tr>


     <!-- Submit button -->
     <tr align="left">
          <td>
               <input type="submit" name="modify" value="Modify">
          </td>
     </tr>
</table>
</fieldset>
<br>

<!-------------------------- Remove User Section ------------------------------>
<fieldset><legend><b>Remove User</b></legend>
<!-- Begin the table -->
<table width="100%" border="0" cellpadding="5">
     <tr>
          <td>
               <?php
                    require_once('../conn.inc.php');
                    $employees = "SELECT uname
                                  FROM user_pass
                                  WHERE 1
                                  ORDER BY uname";
                    $employees_results = @mysql_query($employees)
                                         or die(mysql_error());

               ?>

               <form action="<?php echo ("Location: http://" . $_SERVER[HTTP_HOST] . "/addremoveusers.php"); ?>" method="post">
               <table width="300" border="0">
                    <tr>
                         <td align="left">
                              <b>
                                   Username:
                              </b>
                         </td>
                         <td align="right">
                              <select name="employee">
                                   <option value="">Remove User:</option>
                                   <?php
                                        while ($rows = mysql_fetch_array($employees_results))
                                        {
                                             // Create drop down list of names.
                                             echo "<option value=\"$rows[0]\">$rows[0]</option>\n";

                                        }
                                   ?>
                              </select>
                         </td>
                    </tr>
               </table>

          </td>
     </tr>

     <!-- Submit button -->
     <tr align="left">
          <td>
               <input type="submit" name="remove" value="Remove User">
          </td>
     </tr>
</table>
</fieldset>


<!-- Include the footer -->
<?php include "templates/footer.inc" ?>
</form>
</html>
