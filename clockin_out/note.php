<?php
/*
*        Script Title: Clockin/Out v 1.0
*        Page Title: note.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*		 This page is not fancy in the least. It simply displays plain
*		 ASCII text. When the user clicks on a note icon in their work
*		 hours list, a java window pops up and displays the contents of
*		 the note. The note is just an entry in the database. Nothing
*		 to see here. Move along.
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

if(isset($_SESSION['isroot']))
{
     $u = $_SESSION['uname'];
}
else
{
     $u = $_SESSION['username'];
}

$entry_id = $_GET['entry_id'];
$note = "SELECT notes
         FROM work_hours
         WHERE entry_id = '$entry_id'
         AND uname = '$u'";
//echo $note . '<Br>';
$note_query = @mysql_query($note) or die(mysql_error());
$note_query_result = mysql_fetch_array($note_query);

echo 'This note reads: ' . $note_query_result[0];

?>
