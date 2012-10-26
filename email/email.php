<?php

	/**
	 * Email
	 *
	 * Contains the Email class
	 * @package Repository
	 * @subpackage Email
	 *
	 * @author Robbie Hott
	 */

	/**
	 * Email Handler
	 * 
	 * Handles emailing from the server.  Provides extra functionality to the mail() call from PHP.  Will have
	 * the ability to add headers besides the basic and to change the from email address.  
	 *
	 * @author Robbie Hott
	 * @version 1.0
	 * 
	 */
class Email {

	private $from = "noreply@server.com";
	private $headers;
	private $logfile = "/usr/local/apache2/logs/php_mailer_logfile.log";

	function __construct() {
		$this->buildHeaders();
	}
		
	/**
	 * build email headers
	 *
	 * Build the headers for the email based on information already contained in the class.  Use the update functions
	 * to update or add other headers, then rebuild the full headers by calling this function again.  Automatically
	 * called in the Constructor.
	 * 
	 */
	function buildHeaders() {
		$this->headers = "From: ".$this->from."\r\n". "Reply-To: ".$this->from."\r\n"."X-Mailer: PHP/".phpversion();
	}
	
	/**
	 * add email headers
	 *
	 * Add a header to the email.
	 * 
	 */
	function addHeader($header) {
		$this->headers .= "\r\n".$header;
	}
	
	/**
	 * send an email
	 *
	 * Sends an email to the recipient given as a parameter with the body and subject given.  Appends the headers 
	 * included in this class's object. If the mail send is unsuccessful, calls the saveEmail function
	 * to record the email for later sending.
	 * 
	 * @param string $recipient single recipient whom to send the email
	 * @param string $subject subject line of the email
	 * @param string $body body of the email
	 * @return boolean true on success, false on failure
	 */
	function sendEmail($recipient, $subject, $body, $headers) {
		$this->addHeader($headers);
		$result = mail ($recipient, $subject, $body, $this->headers);

		if ($result === false) {
			$this->saveEmail($recipient, $subject, $body, $this->headers);
		}
		return ($result);
	}


        /**
         * write an email to log file (in code)
         *
	 * Writes the php code to send an email to the logfile.  Allows resending if email goes down.
         *
         * @param string $recipient single recipient whom to send the email
         * @param string $subject subject line of the email
         * @param string $body body of the email
	 * @param string $headers headers of the email
         */
	function saveEmail($recipient, $subject, $body, $headers) {

		$towrite = '$recipient'." = \"$recipient\";\n";
		$towrite .= '$subject'." = \"$subject\";\n";
		$towrite .= '$body'." = \"".addslashes($body)."\";\n";
		$towrite .= '$headers'." = \"".addslashes($headers)."\";\n";
		$towrite .= "\n\n";
		$towrite .= 'mail($recipient, $subject, $body, $headers);'."\n\n";

		file_put_contents($this->logfile, $towrite, FILE_APPEND | FILE_TEXT | LOCK_EX);

		return;
	}

}

?>
