<?php
	/**
	 * User Class File
	 *
	 * Contains User class
	 * @package Repository
	 * @subpackage Login
	 *
	 * @author Robbie Hott
	 */
	 
	/**
	 * User Handler
	 * 
	 * Handles all login and session-keeping functionalities.  Allows for sessions to permeate through page loads
	 * and allows user logout functionality.
	 *
	 * @version 1.0
	 * 
	 */
	class User {
		
		var $data;
		var $errors;
		var $sessionname = 'session-name';
		var $loginscript = ''; // should be secure
		var $token = 'invalid';
		
		
		/**
		 * Default Constructor
		 *
		 * This is a powerful constructor.  If a session already exists, it pulls the user information out
		 * of the session and into this object.  If we receive a token from the secure login page, we've just
		 * logged in successfully, and the constructor will process the token and ensure it is valid, then save
		 * user information from the database, destroy the token, and assume the user is logged in.  If the
		 * GET parameter logout is set to 1, the constructor will call the logout() function, logging the user out.
		 *
		 * Information saved in the database from the login return (when a token is returned) includes user information
		 * and sanity information (IP address and timestamp of login) to make sure it is the same user giving us
		 * the token.
		 */
		function __construct() {
			$this->data = array();
			session_start($this->sessionname);
			if (isset($_SESSION['site_id'])) {
				$this->data['id'] = $_SESSION['site_id'];
			} else if (isset($_GET['z'])) {
				//include the database functions if we don't already have them
	                        include_once('db/db.php');
	                        $logindb = new Database();
	                        $logindb->connect2(array('server'=>'localhost', 'userid'=>'user_id', 'passwd'=>'password', 'dbname'=>'session_db'));


				$res = $logindb->select('login_tokens', '*', "token = '".$_GET['z']."'");
				if (empty($res))
					return;
				$logindb->query("delete from login_tokens where token = '".$_GET['z']."'");
				if ($_GET['z'] != $res[0]['token'])
					return;
				if ($_SERVER['REMOTE_ADDR'] != $res[0]['ip'])
					return;

				//successful login
        	                $logindb->disconnect();

				$keys = explode("||", $res[0]['keys']);
				$vals = explode("||", $res[0]['vals']);
				foreach ($keys as $i => $key) 
					$this->data[$key] = $vals[$i];

			}

			if (isset($_GET['logout']))
				$this->logout();
		}
		/**
		 * Default Destructor
		 *
		 * Saves all of the information stored in this object into the session so that when the user
		 * changes pages, their information persists.
		 */
		function __destruct() {
			if (isset($this->data) && isset($this->data['id'])) {
				$_SESSION['site_id'] = $this->data['id'];
			}
		}
				
		/**
		 * handle login
		 *
		 * If the users information is not available, then if we have received a login attempt on
		 * POST, try to login the user (call loginUser), or else draw the login box (call loginBox).
		 */
		function handleLogin() {
			if (!isset($this->data['Name'])) {
				if (!isset($_POST['userid'])) 
					$this->loginBoxStandard();
				else
					$this->loginUser();
			}
		}
		
		/**
		 * create login box
		 *
		 * Prints out a standard login box, created using divs, allowing for complete customization
		 * through CSS styling.
		 */
		function loginBox() {
			if (func_num_args() == 1)
				$referrer = func_get_arg(0);
			else {
				if ($_SERVER['SERVER_PORT'] == "80")
					$referrer = "http://";
				else // we will just redirect to https and err on the side of caution
					$referrer = "https://";
				$referrer .= $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
			}
			$html = "<div id='loginbox'><form action=\"".$this->loginscript."\" method='post'>";
			$html .= "<h3 class='loginheader'>Login</h3>";
			$html .= "<div id='loginicon'></div>";
			$html .= "<div id='loginname'><p><label for='userid'>User ID:</label> <input type='text' name='userid' width='10' id='loginnameinput'></p></div>";
			$html .= "<div id='loginpass'><p><label for='pass'>password:</label> <input type='password' name='pass' width='9' id='loginpassinput'></p></div>";
			$html .= "<div id='loginsub'><p><input type='image' src='/gif/login/login.png' name='submit' value='Submit'></p></div>";
			$html .= "<input type='hidden' name='referrer' value='".$referrer."'>";
			$html .= "</form></div>";
			
			echo $html;
		}
		
		/**
		 * create login box
		 *
		 * Prints out a standard login box, created using divs, allowing for complete customization through CSS styling.
		 * This version prints the login button as a standard button input, not a custom image.
		 * 
		 */
		function loginBoxStandard() {
			if (func_num_args() == 1)
				$referrer = func_get_arg(0);
			else {
				if ($_SERVER['SERVER_PORT'] == "80")
					$referrer = "http://";
				else // we will just redirect to https and err on the side of caution
					$referrer = "https://";
				$referrer .= $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
			}
			$html = "<div id='loginbox'><form action=\"".$this->loginscript."\" method='post'>";
			$html .= "<h3 class='loginheader'>Login</h3>";
			$html .= "<div id='loginicon'></div>";
			$html .= "<div id='loginname'><p><label for='userid'>User ID:</label> <input type='text' name='userid' width='10' id='loginnameinput'></p></div>";
			$html .= "<div id='loginpass'><p><label for='pass'>password:</label> <input type='password' name='pass' width='9' id='loginpassinput'></p></div>";
			$html .= "<div id='loginsub'><p><input type='submit' name='submit' value='login' id='loginsubinput'></p></div>";
			$html .= "<input type='hidden' name='referrer' value='".$referrer."'>";
			$html .= "</form></div>";
			
			echo $html;
		}
		
		/**
		 * log in the user
		 *
		 * Calls the authenticate() method to login the user.  On successful, returns the user to the
		 * referring page.  On failure, reprints the login page.
		 */
		function loginUser() {
			$userid = $_POST['userid'];
			$passwd = $_POST['pass'];

			
			$this->errors = $this->authenticate($userid, $passwd);
			
			if ($this->errors === true) {
				if (strpos($_POST['referrer'], '?') !== false )
					header("Location: ".$_POST['referrer']."&z=".$this->token);
				else
					header("Location: ".$_POST['referrer']."?z=".$this->token);

			} else {
				$this->loginPage($_POST['referrer']);
			}

		}
		
		/**
		 * log in the user automatically
		 *
		 * Calls the authenticate() method to login the user.  On successful, returns the user to the
		 * referring page.  On failure, reprints the login page.
		 */
		function autoLoginUser($info) {

			
			$this->data['id'] = $info['id'];

		}
		
		/**
		 * create full login page
		 *
		 * Create a complete page with the login box in the middle.  Determines if the user is on
		 * staff or on a public page, then uses the template appropriate for that page.
		 */
		function loginPage() {
			if (func_num_args() == 1)
				$referrer = func_get_arg(0);
			else {
				$referrer = false;
			}
				//include_once("template/template_page.php");
				//print_template_head("VCU Libraries Login | Login Page");
			echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"css/pages.css.php\"><style>body { color: #ffffff; }</style><title>Login</title></head><body color='white'>
<br><br><br><br><br><center>";
			if ($referrer)
				echo "<div>".$this->errors."</div>" . $this->loginBoxStandard($referrer);
			else
				echo "<div>".$this->errors."</div>" . $this->loginBoxStandard();
			echo "</body></html>";

				die();
		}
		
		/**
		 * require login on page
		 *
		 * Require a user to be logged in to access the current page.  If no argument is passed, any logged in user may
		 * access the page.  To require certain users, pass an array of userid strings to the function.  To lockout all users,
		 * pass an empty array.
		 *
		 *  @param array $userids array of userids to allow access to the page.
		 */
		function require_login() {
			global $login_page_url, $logout_page_url;
			if (func_num_args() == 1)
				$required_users = func_get_arg(0);
			else
				$required_users = false;

			if (!$this->isLoggedIn()) {
				$this->loginPage();
			}
			
			if ($required_users !== false && !in_array($this->get('userid'), $required_users)) {
				echo "<html><head><title>Unauthorized</title></head><body><h1>Unauthorized User</h1><p>You are currently logged in as a user that is unauthorized to view this content.  Please logout using the link below, then log back in as an authorized user.</p><p><a href=\"".$logout_page_url."\">Logout</a></body></html>";
				die();
			}

			//we're allowed in :)
			return;
		}
		
		/**
		 * require user permissions on page
		 *
		 * Require a user to be logged in to access the current page.  
		 *
		 *  @param int $i permission level required for page
		 */
		function require_permissions($i) {
			global $login_page_url, $logout_page_url;

			if (!$this->isLoggedIn()) {
				$this->loginPage();
			}
			
			if ($this->data['Permissions'] < $i) {
				echo "<html><head><title>Unauthorized</title></head><body><h1>Unauthorized User</h1><p>You are currently logged in as a user that is unauthorized to view this content.  Please logout using the link below, then log back in as an authorized user.</p><p><a href=\"".$logout_page_url."\">Logout</a></body></html>";
				die();
			}

			//we're allowed in :)
			return;
		}
			
		/**
		 * authenticate a user
		 *
		 * Authenticates the user and passowrd given against LDAP.  If successful, it saves information
		 * about the user in the database and assigns that user a token, which will be passed back to the
		 * referring page to complete the login process.  Also stores IP and timestamp information to
		 * prevent hackers.
		 *
		 *  @param string $userid userid to test
		 *  @param string $passwd password to test
		 *  @return string error if problem or boolean true on success
		 */
		function authenticate($userid, $passwd) {
			if (!isset($passwd) || $passwd == "") {
        			return "Error: User ID and Password incorrect.";
			}

			//include the database functions if we don't already have them
			
                        include_once("db/db.php");
                        $logindb = new Database();
                        $logindb->connect2(array('server'=>'localhost', 'userid'=>'user_id', 'passwd'=>'password', 'dbname'=>'user_db'));
                        
                        $res = $logindb->select("users", "name, password, affiliation, email, permissions", "userid = '$userid'");
                        print_r($res);
                        if (!$res || empty($res))
                        	return "Error: User ID and Password incorrect.";
                        	
                        if ($res[0]['password'] != md5($passwd))
                        	return "Error: User ID and Password incorrect.";
                        
                        // we have a match!
                        
                        $logindb->disconnect();
                        $logindb = new Database();
						$logindb->connect2(array('server'=>'localhost', 'userid'=>'user_id', 'passwd'=>'password', 'dbname'=>'session_db'));

                        $tdata = array();
                        $tdata['Name'] =  $res[0]['name'];
                        $tdata['Affiliation'] = $res[0]['affiliation'];
                        $tdata['Email'] = $res[0]['email'];
                        $tdata['Permissions'] = $res[0]['permissions'];
                        $tdata['userid'] = $userid;

                        $this->token = md5($tdata['Name'].time().$userid);
                        $ipaddr = $_SERVER['REMOTE_ADDR'];
			$keys = implode("||", array_keys($tdata));
			$vals = implode("||", $tdata);	

			$logindb->insert('login_tokens', array( 'ip'=>$ipaddr, 'token'=>$this->token, 'keys'=>$keys, 'vals'=>$vals));
			$logindb->disconnect();
			
			return true;
		} // authenticate()
		
		
		/**
		 * logout the user
		 *
		 * Logs the user associated with this object out of the website.  Destroys their
		 * session data and deletes the data stored about them in this object.  Then creates
		 * a fresh empty session.
		 *
		 */
		function logout() {
			session_destroy();
			unset($this->data);
			session_start($this->sessionname);
			header("Location: ".$_SERVER['PHP_SELF']);
		}
		
		/**
		 * get user's information
		 *
		 * Returns information about the current logged-in user.
		 *
		 *  @param string $info information to return: "Name", "Affiliation", "Email", or "userid"
		 *  @return string information requested
		 */
		function get($info) {
			switch($info) {
				case "id":
					return $this->data['id'];
				default:
					return "";
			}
		}
		
				/**
		 * get user's permissions
		 *
		 * Returns information about the current logged-in user.
		 *
		 *  @param string $info information to return: "Name", "Affiliation", "Email", or "userid"
		 *  @return string information requested
		 */
		function getPermission() {
					return $this->data["Permissions"];
		}
		
		/**
		 * is the user logged in
		 *
		 * Tests to see if the user is logged into the system.
		 *
		 *  @return boolean true if user is logged in, false if not
		 */
		function isLoggedIn() {
			if (isset($this->data['id']) || isset($_SESSION['site_id']))
				return true;
			else
				return false;
		}
		
	} // class Login
?>
