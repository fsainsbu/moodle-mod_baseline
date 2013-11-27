/*
http://moodle.org/mod/forum/discuss.php?d=105672
Save this as $MOODLE_BASE/auth/auth_plugin_logout_redirect/auth.php. Log in as admin and go to Users -> Authentication -> Manage authentication and enable the plugin.
*/
require_once($CFG->libdir.'/authlib.php');

class auth_plugin_logout_redirect extends auth_plugin_base {

function user_login($username, $password) {

return true;

}

function logoutpage_hook() {

global $redirect;

$redirect = 'http://www.yourdomain.ie/yourlogoutpage';

}

}


  function logoutpage_hook() {
1229          global $USER, $CFG, $redirect, $DB;
1230  
1231          if (!empty($USER->mnethostid) and $USER->mnethostid != $CFG->mnet_localhost_id) {
1232              $host = $DB->get_record('mnet_host', array('id'=>$USER->mnethostid));
1233              $redirect = $host->wwwroot.'/';
1234          }
1235      }
1236  

 function prelogout_hook() {
        global $CFG;

        if ($this->config->logoutcas) {
            $backurl = $CFG->wwwroot;
            $this->connectCAS();
            phpCAS::logoutWithURL($backurl);
        }
    }
 
