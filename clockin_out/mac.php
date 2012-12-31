<?php
               include "NtMacAddress.class.php";
               $mac = &new NtMacAddress();
               //echo 'SERVER NAME: '
               echo $mac->getMac( 'server' );//. '<br />CLIENT MAC: ' .$mac->getMac( 'client' );

?>