<?php
/*
*        Script Title: Clockin\Out v. 1.0
*        Page Title: audit.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*        This is the entrance page to the Audit feature. The only thing we do here
*        is ask to start off with a username. I made this page because it saved
*        a lot of trouble (or seemed to anyway) on the main Audit page. And I think
*        it makes it easier on the admins, because they select their user here,
*        and then all they have to do is alter the hours.
*
*        The user can be changed in the main audit page, should you need to do so.
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


/*********************************************************************************
 * NOTE: Someone brought up the idea of sticking with the same person they
 * selected throughout the session. Ok, fine. That's what session variables
 * are for. But what about this page? Simple.
 * if(isset($_SESSION['current_user'])) then forward them straight on with
 * this user. If not, let them select one. Brilliant.
 *********************************************************************************/
require_once('../conn.inc.php');
session_start();

if (!(isset($_SESSION['isroot'])))
{ // # 1

     // Redirect them back to the login page.
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/logout.php");
     exit();
} // Close # 1


// Innitialize $message
$message = NULL;
if (isset($_POST['submit']) || isset($_SESSION['uname']))
{
     if(isset($_POST['submit']))
	{
	     $_SESSION['uname'] = $_POST['user'];
	}
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/audit2.php?u=" .
		  $_SESSION['uname']);
     exit();
}

$page_title = "Administrator - Audit an Account";
include ('templates/header.inc');



     // Error messages printed here
     if (isset($message))
     { // # 3
          // They are seeing this message because they have messed up somewhere along the line.
          echo '<font color = "#ef0000">', $message, '</font>';
     } // Close # 3

$admin_username = $_SESSION['admin_username'];
                         $get_user = "SELECT uname
					             FROM user_pass
							   WHERE uname != '$admin_username'";
																				 	
?>

<table width="100%" border="0">
     <tr align="right">
          <td>
          &nbsp;
          </td>
          <td align="right">
          <?php
               $page = 'audit';
               $access_level = 'admin';
			
			// This is so admins can't audit themselves.
               include ('templates/tabs.inc');
			
                         $get_user = "SELECT uname
                                      FROM user_pass
							   WHERE uname != '$admin_username'";
          ?>
          </td>
     </tr>
</table>

<form action="<?php echo 'http://' . $_SERVER[HTTP_HOST] . '/audit.php'; ?>" method="post">

<!-- The nice border around our login data starts here -->
<fieldset><legend><b>Select an Employee to Audit</b></legend>

<!-- Begin the table -->
<table width="100%" border="0" cellpadding="5">
     <tr>
          <td>
               Select a User:
          </td>
          <td>
               &nbsp;
          </td>
     <tr>

          <td align="left">
               <select name="user">
                    <?php
                         $get_user = "SELECT uname
                                      FROM user_pass
							   WHERE uname != '$admin_username'";

                         $get_user_query = @mysql_query($get_user)
                                           or die(mysql_error());
                         while ($row = mysql_fetch_array($get_user_query))
                         {
                              echo '<option value="' . $row[0] . '">' . $row[0] . '</option>';
                         }
                    ?>
               </select>
               &nbsp;
               &nbsp;
                <input type="submit" name="submit" value="Go">
          </td>
          <td>
              &nbsp;
          </td>
     </tr>
     <tr>
          <td align="left">
               &nbsp;
          </td>
          <td align="left">
                &nbsp;
          </td>
     </tr>
</table>
</fieldset>
<!-- Include the footer -->
<?php include "templates/footer.inc" ?>
