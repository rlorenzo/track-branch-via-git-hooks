<?php
/*
 * Script functions.
 */

function debug($msg) {
   global $debug;
   if ($debug) {
       error_log($msg);
   }
}
?>
