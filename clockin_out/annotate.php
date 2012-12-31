<?php
/*
*        Script Title: Clockin\Out v. 1.0
*        Page Title: annotate.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*        This page allows users to make notes about their work hours. For instance,
*        if they leave themselves clocked in overnight, they will be able to make
*        a note to their supervisors about it.
*
*        It seems that MySQL truncates the text at 180 characters, so I have
*        limited input to that. This may be something I'm doing wrong, but I
*        haven't found any work arounds, and 180 characters should be enough to
*        leave a short note such as: "I forgot to clock out. Sorry." Or "Hey, I
*        really DID work 18 and a half hours!"
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
session_start();
@include('../conn.inc.php');

// If no session is present, redirect the user
if (!(isset($_SESSION['first_name'])))
{ // # 1

     // Redirect them back to the login page.
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
     exit();
} // Close # 1

// Set the page title and include the HTML header.
$page_title = "Aurora Cooperative - Make a Note";
include ('templates/header.inc');

// input:  a string
// output: none
// return: a string
// notes: This function normalizes the input in the Username and Password fields.
function escape_data($data)
{ // # 2

     if (ini_get('magic_quotes_gpc'))
     { // # 2.1
          $data = stripslashes($data);
     } // Close # 2.1
     return trim(mysql_real_escape_string($data));
} // Close # 2

$u = $_SESSION['username'];

if (isset($_POST['submit']))
{ // # 3

     // Innitialize variables
     $comments = escape_data($_POST['comments']);
     $date = $_POST['date'];
     $message = NULL;
     $a = 1;

     // Check if the Comments field is empty.
     if (empty($comments))
     { // # 3.1
          $message .= "<p>Enter a comment.</p>";
          $comments = NULL;
     } // Close # 3.1

     // Check length of Comments field: not more than 180 chars.
     if (strlen($comments) >= 180)
     { // # 3.2
          $message .= "<p>Please edit your comment to under 180 characters.</p>";
          $a = NULL;
     } // Close # 3.2

     // Check if the date field is empty.
     if ($date == "")
     { // # 3.3
          $message .= "<p>Select a date to annotate.</p>";
          $date = NULL;
     } // Close # 3.3

     // If it's all kosher:
     if ($comments && $date && $a)
     { // # 3.4
          $query = "UPDATE work_hours
                    SET notes = '$comments'
                    WHERE clockin_time = '$date'
				AND uname = '$u'";
          $query_result = @mysql_query($query) or die(mysql_error());

          $message = "Your note for . " . $date . " has been updated successfully.";
     } // Close # 3.4
} // Close # 3
if (isset($message))
{ // # 4

     // They are seeing this message because they have messed up somewhere along the line.
     echo '<font color="#ef0000">', $message, '</font>';
} // Close # 4

?>

<table width="100%" border="0">
     <tr>
          <td align="right">
               <?php

                    // Set the tabs
                    $access_level = 'user';
                    $page = 'annotate';
                    @include ('templates/tabs.inc');
               ?>
          </td>
     </tr>
</table>


<form action=<?php echo '"http://' . $_SERVER[HTTP_HOST] . '/annotate.php"'; ?> method="post">

<!-- The nice border around our login data starts here -->
<fieldset><legend><b>Make a Note About an Entry</b></legend>

<table width="100%" border="0">
     <tr>
          <td align="left">
               <br />
               <b>Select an entry to annotate:</b><br><br>
               <select name="date">
                    <option value="">&nbsp;<-Clock In Time->
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<-Clock Out Time->
                    <?php
                         // This piece of code borrowed from audit2.php
                         $get_clockin  = "SELECT clockin_time, clockout_time, flag
                                         FROM work_hours
                                         WHERE uname = '$u'
                                         AND clockin_time >= '$limit_query_result[0]'
                                         AND clocked_in = 0
                                         ORDER BY clockin_time DESC";
                         $get_clockin_query = @mysql_query($get_clockin)
                                              or die(mysql_error());

                         while ($row  = mysql_fetch_array($get_clockin_query))
                         { // # 8
                                                // If there is a flag, make the entry noticable
                                             if($row[2] == 1)
                                                {
                                                     echo '<option value="' . $row[0] . '">'
                                                            . $row[0] .
                                                            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'  .
                                                              $row[1] . ' ***</option>';
                                                }

                                                // Otherwise just print the normal date-time
                                                else
                                                {
                                                     echo '<option value="' . $row[0] . '">'
                                                            . $row[0] .
                                                            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
                                                            $row[1] .  '</option>';
                                                }

                         } // Close # 8
                    ?>
               </select>
          <br />
          <p><b>Comments:</b></p>
          <textarea rows="5" cols="50" name="comments" wrap="soft"></textarea></p>
          <br />
          <br />

          <!-- Submit button -->
          <input type="submit" name="submit" value="Submit">


          </td>
          <td align="left">
               <font color="#ef0000">Please keep comments to under 180 characters.
               <br><br>
               Entries marked with three astrisks (***) are entries that have
               been flagged with an error.</font>
          </td>
     </tr>
</table>
</fieldset>
<br />
</form>

<!-- Include the footer -->
<?php @include "templates/footer.inc" ?>
