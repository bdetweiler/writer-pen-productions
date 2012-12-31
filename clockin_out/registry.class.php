<?php
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// MODULE: Class Registry
// -----------------------
// Note: An API for the Win32 Registry
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// Original Author: PascalZ (www.pascalz.com)
// Modified By: Brian Detweiler
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-


// Declare Constants
if (!defined("HKCR"))
{
   define("HKCR","HKEY_CLASSES_ROOT",  TRUE);
   define("HKCU","HKEY_CURRENT_USER",  TRUE);
   define("HKLM","HKEY_LOCAL_MACHINE", TRUE);
   define("HKU", "HKEY_USERS",         TRUE);
   define("HKCC","HKEY_CURRENT_CONFIG",TRUE);
}

class Registry
{
   // Variable for the Shell (?)
   var $_Shell;

   // Constructor
   function registry()
   {
      $this->_Shell= &new COM('WScript.Shell');
   }

   // Generate Registry errors
   function regError($error)
   {
      print($error);
      error_reporting(E_ALL);
   }

   // Read key
   function read($key)
   {
      if (!$this->KeyExists($key))
      {
         $this->RegError("Key does not exist");
      }
      else 
      {
         return $this->_Shell->RegRead($key);
      }
   }

   // Verify that a key exists
   function keyExists($key)
   {
      return (@$this->_Shell->RegRead($key) != null);
   }

   /* Commented out for SAFETY! Read access only!
   // Write key
   function write($key,$value)
   {
      if (!$this->KeyExists($key))
      {
         $this->RegError("Key does not exist!");
      }
      else 
      {
         return $this->_Shell->RegWrite($key,$value);
      }
   }


   // Delete key
   function delete($key)
   {
      if (!$this->KeyExists($key))
      {
         $this->RegError("Key does not exist");
      }
      else
      {
         return $this->_Shell->RegDelete($key);
      }
   }
   */
}
?>
