<?php
/**
 * Respond to github post-receive JSON.
 * 
 * Security concerns: I wish I can restrict this script to only respond to POST
 * requests from github's servers, but that information isn't avaialble.
 */

// get script variables
require 'config.php';

// NOTE: JSON should come preinstalled with PHP starting with 5.2
if (!function_exists('json_decode')) {
    die(error_log('JSON not installed'));
}

// github sends git post-receive hooks as a single POST param called 'payload'
/* POST param is in following JSON format:
{
  :before     => before,
  :after      => after,
  :ref        => ref,
  :commits    => [{
    :id        => commit.id,
    :message   => commit.message,
    :timestamp => commit.committed_date.xmlschema,
    :url       => commit_url,
    :added     => array_of_added_paths,
    :removed   => array_of_removed_paths,
    :modified  => array_of_modified_paths,
    :author    => {
      :name  => commit.author.name,
      :email => commit.author.email
    }
  }],
  :repository => {
    :name        => repository.name,
    :url         => repo_url,
    :pledgie     => repository.pledgie.id,
    :description => repository.description,
    :homepage    => repository.homepage,
    :watchers    => repository.watchers.size,
    :forks       => repository.forks.size,
    :private     => repository.private?,
    :owner => {
      :name  => repository.owner.login,
      :email => repository.owner.email
    }
  }
}
 */

if (!empty($_POST['payload'])) {    
    // parse payload data
    $payload = json_decode($_POST['payload']);    
    // on error json_decode retuns null
    if (empty($payload)) {
        die(error_log('Invalid payload JSON sent from ' . 
                $_SERVER['SERVER_ADDR']));
    }
    
    // now make sure that the commit is for the branch we want to track
    if ($payload->ref === 'refs/heads/' . $tracking_branch) {
        // now run git pull for given repo
        $output = array(); $return_var = null;
        // sudo as repo owner and do pull command in git repo
        // see: http://stackoverflow.com/a/4415927/6001
        exec(sprintf("su -s /bin/sh %s -c 'cd %s && /usr/bin/git pull'", $repo_user, $repo_location), $output, $return_var);
        
        // if $return_var is non-zero, then an error happened
        // http://www.linuxtopia.org/online_books/advanced_bash_scripting_guide/exitcodes.html
        if (0 !== $return_var) {
            // there was an error, so email the admin
            $result = mail($admin_email, 'git_post_receive: Failed to update ' . 
                    'branch ' . $tracking_branch, sprintf("command line " . 
                    "output:\n\n%s\n\njson_payload:\n\n%s", 
                    implode("\n", $output), print_r($payload, true)));
        } else {
            // update was successful, so email committer and admin
            
            // get emails of committers
            $committers = array($admin_email);
            foreach ((array) $payload->commits as $commit) {
                $committers[] = $commit->author->email;
            }
            array_unique($committers);
            
            $result = mail(implode(';', $committers), 'git_post_receive: ' . 
                    'Updated branch ' . $tracking_branch, sprintf("Updated %s " . 
                    "branch on the server. Here is the output of the pull " . 
                    "request:\n\n%s\n\njson_payload:\n\n%s", $tracking_branch, 
                    implode("\n", $output), print_r($payload, true)));            
        }
        
        if (empty($result)) {
            error_log('Could not send email notification for git_post_receive');
        }
    }
}
?>
