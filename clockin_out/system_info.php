<?php
   include "computerID.class.php";
   //include "NtMacAddress.class.php";
   //include "registry.class.php";
   //$windowsInfoFolder = 'HKLM\SOFTWARE\Microsoft\WindowsNT\CurrentVersion';
   //$mac = &new computerID();
   
   /** ****************** THIS IS BROKE. FIX IT! **********************************/
   /*
   function getMac($what) 
   {
      $what = &strtolower($what);
      if($what == 'server') 
      {
         return getServerMAC();
      }
      elseif($what == 'client')
      {
         return getClientMAC();
      }
      else 
      {
         return '\'client\' or \'server\' ?';
      }
   }
*/
/*
   function getClientMAC() {
          $output = Array();
                exec( 'nbtstat -A '.$_SERVER['REMOTE_ADDR'], $output );
                $reg = '([a-f0-9]{2}\-){5}([a-f0-9]{2})';
                for( $a = 0, $b = &count( $output ); $a < $b; $a++ ) {
                        if( preg_match( "/(?i){$reg}/", $output[$a] ) == true ) {
                                return preg_replace( "/(?iU)(.+)({$reg})(.*)/", "\\2", $output[$a] );
                        }
                }
                return 'not found';
        }
*/
/*
   function getServerMac() 
   {
      
      $output = Array();
      exec( 'netstat -r', $output );
      for( $a = 0, $b = &count( $output ); $a < $b; $a++ ) 
      {
         if( preg_match( "/(?i)([a-z0-9]{2} ){6}/", $output[$a] ) == true ) 
         {
            $macaddress = &$output[$a];
            echo $macaddress . '<br>';
            $uniquekey = &md5( $macaddress );
            echo $uniquekey . ' unique key<br>';
            $output[$a] = &preg_replace( "/(?i)([^a-z0-9]*?)([a-z0-9]{2} ){6}/i", "\\1 {$uniquekey} ", $output[$a] );
            echo $output[1] . ' output<br>';
            $output[$a] = &explode( " {$uniquekey} ", $output[$a] );
            echo $output[1] . ' next output<br>';
            $uniquekey = Array( trim( $output[$a][0] ), trim( $output[$a][1] ) );
            echo $uniquekey . ' next unique key<br>';
            $macaddress = &str_replace( $uniquekey, "", $macaddress );
            return trim( $macaddress );
         }
      }
      
      return 'not found';
   }
*/
   
   //echo 'SERVER NAME:<br> ';
   $mac = &new computerID();
   echo $mac->getMac('server'); 
   //echo $mac;
   //$mac->getIP();
   //echo 'This is the IP ';// . $mac;
   //$reg = &new Registry();
   //$CSDVersion = $reg->read('HKey_Local_Machine\SOFTWARE\Microsoft\WindowsNT\CurrentVersion\ProductId');
   //echo $CSDVersion;
?>
