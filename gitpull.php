#!/bin/php
<?php
/*
 * Commands to be run via command line as specified user.
 */

// get script variables
require 'config.php';

// make sure that script is run from command line and as specified repo user
if (get_username_from_uid(posix_geteuid()) == $repo_user && is_cli()) {
    // now run git pull for given repo
    $output = array();
    $return_var = null;
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
 * Sometimes posix_getpwuid is not always installed, so try other methods of
 * getting username from uid.
 * 
 * http://www.php.net/manual/en/function.posix-getpwuid.php#104737
 * 
 * @param int $uid
 * @return string       Returns username for given uid. If could not find 
 *                      username, then just returns uid back again
 */
function get_username_from_uid($uid) {
    if (function_exists('posix_getpwuid')) {
        $a = posix_getpwuid($uid);
        return $a['name'];
    } elseif (strstr(php_uname('s'), 'BSD')) {
        # This works on BSD but not with GNU 
        exec('id -u ' . (int) $uid, $o, $r);

        if ($r == 0) {
            return trim($o['0']);
        }else {
            return $uid;
        }
    } elseif (is_readable('/etc/passwd')) {
        exec(sprintf('grep :%s: /etc/passwd | cut -d: -f1', (int) $uid), $o, $r);
        if ($r == 0) {
            return trim($o['0']);
        } else {
            return $uid;
        }
    } else {
        return $uid;
    }
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
