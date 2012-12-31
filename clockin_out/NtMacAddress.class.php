<?php
/**
 * Class NtMacAddress
 *                Returns client or server Mac Address in an intranet NT5 or NT5.1 LAN
 * -----------------------------------------------
 * EXAMPLE:
 *                $mac = &new NtMacAddress();
 *                echo 'SERVER MAC: '.$mac->getMac( 'server' ).'<br />CLIENT MAC: '.$mac->getMac( 'client' );
 *
 * NOTE: This class works only in a LAN then you will not read anything if try on localhost.
 *         So please use from another PC.
 * _______________________________________________
 *
 * @author                Andrea Giammarchi
 * @compatibility        Windows 2000 / Server , Windows XP
 *                        ( but maybe found *nix and other OS client macaddress too )
 * @site                www.3site.it
 * @date                09/10/2004
 * @version        1.0        tested only on Win 2K / XP
 */
class NtMacAddress {

        /**
         * Public method getMach. Returns client or server mac-address if is readable.
         *
         *                NtMacAddress->getMac( $what:String ):String
         *
         * @param        String                Options: 'client' or 'server'
         * @return        String                Mac-Address if is readable, 'not found' otherwise
         */
        function getMac( $what ) {
                $what = &strtolower( $what );
                if( $what == 'server' ) {
                        return $this->__server_macaddress();
                }
                elseif( $what == 'client' ) {
                        return $this->__client_macaddress();
                }
                else {
                        return '\'client\' or \'server\' ?';
                }
        }

        /**
         * Private method __server_macaddress. Returns server mac-address if is readable.
         *
         *                NtMacAddress->__server_macaddress():String
         *
         * @param        no
         * @return        String                Server Mac-Address if is readable, 'not found' otherwise
         */
        function __server_macaddress() {
                $output = Array();
                exec( 'netstat -r', $output );
                for( $a = 0, $b = &count( $output ); $a < $b; $a++ ) {
                        if( preg_match( "/(?i)([a-z0-9]{2} ){6}/", $output[$a] ) == true ) {
                                $macaddress = &$output[$a];
                                echo $macaddress . '\n';
                                $uniquekey = &md5( $macaddress );
                                echo $uniquekey . ' unique key\n';
                                $output[$a] = &preg_replace( "/(?i)([^a-z0-9]*?)([a-z0-9]{2} ){6}/i", "\\1 {$uniquekey} ", $output[$a] );
                                echo $output[1] . ' output \n';
                                $output[$a] = &explode( " {$uniquekey} ", $output[$a] );
                                echo $output[1] . ' next output \n';
                                $uniquekey = Array( trim( $output[$a][0] ), trim( $output[$a][1] ) );
                                echo $uniquekey . ' next unique key\n';
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
         * @param        no
         * @return        String                Client Mac-Address if is readable, 'not found' otherwise
         */
        function __client_macaddress() {
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
}
?>