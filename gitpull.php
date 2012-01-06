#!/bin/php
<?php
/*
 * Commands to be run via command line as specified user.
 */

// get script variables
require 'config.php';

// make sure that script is run from command line and as specified repo user
$process_user = posix_getpwuid(posix_geteuid());
if ($process_user == $repo_user && is_cli()) {
    // now run git pull for given repo
    $output = array(); $return_var = null;
    $cmd = sprintf("cd %s && /usr/bin/git pull", $repo_user, $repo_location);    
    exec($cmd, $output, $return_var);   
    
    // output command results
    echo(implode("\n", $output));
    
    exit($return_var);  // exit with command error code
}

// else exit with bad error code
echo 'Script needs to be run on command line as specified user';
exit(1);

/**
 * Determines if script is run from command line.
 * http://www.codediesel.com/php/quick-way-to-determine-if-php-is-running-at-the-command-line/
 * 
 * @return boolean
 */
function is_cli() {
 
     if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
          return true;
     } else {
          return false;
     }
}
?>
