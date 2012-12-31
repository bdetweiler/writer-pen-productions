<?php
/**
 * Class computerID.class
 * Returns the following system information:
 * Mac Address
 * Computer Name
 * Workgroup Name
 * IP Address
 * -----------------------------------------------
 * EXAMPLE:
 *                $mac = &new NtMacAddress();
 *                echo 'SERVER MAC: '.$mac->getMac( 'server' ).'<br />CLIENT MAC: '.$mac->getMac( 'client' );
 *
 * NOTE: This class works only in a LAN then you will not read anything if try on localhost.
 *         So please use from another PC.
 * _______________________________________________
 *
 * Original Author     Andrea Giammarchi
 * Site                www.3site.it
 * Date                09/10/2004
 * Version             1.0 (tested only on Win 2K / XP)
 * Compatibility       Windows 2000 / Server , Windows XP
 *                        ( but may be modified for *nix and other OS client MAC address too )
 * Modified By         Brian Detweiler
 * Date                01/01/2005
 * Version             1.1 (Added IP grabbing)
 */
class computerID 
{
   /**
   * Public method getMac. Returns client or server mac-address if is readable.
   *
   *                SystemInfo->getMac($what:String):String
   *
   * Input:          String                Options: 'client' or 'server'
   * Output:         None
   * Returns:        String                Mac-Address if is readable, 'not found' otherwise
   */

   function getMac($what) 
   {
      $what = &strtolower($what);
      if($what == 'server') 
      {
         return $this->getServerMAC();
      }
      elseif($what == 'client')
      {
         return $this->getClientMAC();
      }
      else 
      {
         return '\'client\' or \'server\' ?';
      }
   }

   /**
   * Private method __server_macaddress. Returns server mac-address if is readable.
   *
   *                NtMacAddress->__server_macaddress():String
   *
   * Input:         None
   * Output:        None
   * Returns:       String                Server Mac-Address if is readable, 'not found' otherwise
   */

   function getServerMAC() 
   {
      $output = Array();
      exec( 'netstat -r', $output );
      for( $a = 0, $b = &count( $output ); $a < $b; $a++ ) 
      {
         if( preg_match( "/(?i)([a-z0-9]{2} ){6}/", $output[$a] ) == true ) 
         {
            $macaddress = &$output[$a];
            //echo $macaddress . '\n';
            $uniquekey = &md5( $macaddress );
            //echo $uniquekey . ' unique key\n';
            $output[$a] = &preg_replace( "/(?i)([^a-z0-9]*?)([a-z0-9]{2} ){6}/i", "\\1 {$uniquekey} ", $output[$a] );
            //echo $output[1] . ' output \n';
            $output[$a] = &explode( " {$uniquekey} ", $output[$a] );
            //echo $output[1] . ' next output \n';
            $uniquekey = Array( trim( $output[$a][0] ), trim( $output[$a][1] ) );
            //echo $uniquekey . ' next unique key\n';
            $macaddress = &str_replace( $uniquekey, "", $macaddress );
            return trim( $macaddress );
         }
      }
      return 'not found';
   }

   /**
   * Private method __client_macaddress. Returns client mac-address if is readable.
   *
   *                NtMacAddress->__client_macaddress():String
   *
   * Input:          None
   * Output:         None
   * Return:         String                Client Mac-Address if is readable, 'not found' otherwise
   */
   
   function getClientMAC() 
   {
      $output = Array();
      exec('nbtstat -A '.$_SERVER['REMOTE_ADDR'], $output);
      $reg = '([a-f0-9]{2}\-){5}([a-f0-9]{2})';
      for($a = 0, $b = &count( $output ); $a < $b; $a++) 
      {
         if(preg_match( "/(?i){$reg}/", $output[$a]) == true) 
         {
            return preg_replace("/(?iU)(.+)({$reg})(.*)/", "\\2", $output[$a]);
         }
      }
      return 'not found';
   }
  
   /*
   function getIP() 
   {
      $output = Array();
      $IPCommand = 'ipconfig /all | findstr /C:"IP Address. . . . . . . . . . . . :"';
      echo $IPCommand;
      exec($IPCommand, $output);
      for($a = 0 $b = &count($output); $a < $b; $a++)
      {
         $test .= echo $output[$a]; 
      }
      return $test;
   }
   */
}
?>
