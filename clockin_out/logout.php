<?php
/*
*        Script Title: Clockin/Out v 1.0
*        Page Title: logout.php
*        Author:  Brian Detweiler
*        Contact: brian.detweiler@us.army.mil
*
*		 This is simply for logging the user out. It starts the session
*		 as would any other page, but then immediately unsets it and 
*		 forwards it to the login page. This is done to make sure 
*		 the session was unset cleanly. 
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


     // Log the user out by unsetting the session and
     // redirecting them to login.php
     session_start();
     session_unset();
     header("Location: http://" . $_SERVER[HTTP_HOST] . "/login.php");
     exit();
?>
