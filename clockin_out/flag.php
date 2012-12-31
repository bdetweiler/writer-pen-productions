<?php
	// Schedule to run every Sunday so it will
	// set a flag every other Sunday and the script
	// will know to run the reports
/* *************************************************
   * Need to know if paycycle is greater than one  *
   * week from now(). If Paycycle is greater than  *
   * one week from now (sunday), then we do nothing*
   * If it is less than, then we set the flag      *
   *************************************************/

	@include('../conn.inc.php');
	$pay_cycle_check = "SELECT cycle_date
                         FROM pay_cycle
				     WHERE cycle_date >= NOW()
                         ORDER BY entry_id
                         LIMIT 0, 1";	  
	$pay_cycle_check_query = mysql_query($pay_cycle_check);
	$pay_cycle_check_result = mysql_fetch_array($pay_cycle_check_query);

	$next_pay_cycle = (strtotime($pay_cycle_check_result[0])); // - (strtotime("now"));

	echo strtotime($pay_cycle_check_result[0]) . " pay cycle check result\n ";

	$one_week_from_now = (strtotime("+1 week")); // - (strtotime("now"));

	echo strtotime("+1 week") . " Plus one week\n ";

	if($next_pay_cycle > $one_week_from_now)
	{
	 	echo 'Nothing to do.';
	}
	else
	{
		touch("/var/www/coop/Report/flag.on");
	}

?>
