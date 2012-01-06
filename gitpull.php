#!/usr/bin/php
<?php
/*
 * Commands to be run via command line as specified user.
 */

// get script variables
require 'config.php';
require 'functions.php';

// make sure that script is run from command line and as specified repo user
if (get_script_user() == $repo_user && is_cli()) {
    // now run git pull for given repo
    $output = array();
    $return_var = null;
    $cmd = sprintf("cd %s && /usr/bin/git pull", $repo_location);
    debug('executing command: ' . $cmd);
    exec($cmd, $output, $return_var);

    // output command results
    echo(implode("\n", $output));
    exit($return_var);  // exit with command error code
}

// else exit with bad error code
debug('gitpull.php called invalidly');
echo 'Script needs to be run on command line as specified user';
exit(1);

/**
 * For some reason php-posix is not installed by default in our server, so just 
 * run command line command 'whoami' and get result
 */
function get_script_user()
{
    return exec('whoami');
}

/**
 * Determines if script is run from command line.
 * http://www.codediesel.com/php/quick-way-to-determine-if-php-is-running-at-the-command-line/
 * 
 * @return boolean
 */
function is_cli() {

    if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
        return true;
    } else {
        return false;
    }
}
?>
