<?php
// ***********************************************************************************
// SESSION MANAGEMENT FILE - !! MUST BE USED AFTER config.php !!
// This file needs to be present on every page that needs to be protected
// ***********************************************************************************
// Things that are required for the session to be valid:
// - session cookie which carries the session id
// - valid alphanumeric session id from the session cookie which matches info
//   in the session database table
// - session get code cookie which carries the get id for the url get code
// - valid alphanumeric get code id from the get code cookie
// - valid alphanumeric get code id from the url which matches info in the session
//   database table
// - valid alphanumeric get code from the url which also matches info in the session
//   database table
// - valid alphanumeric random get id for the random get code in the url which also
//   matches info in the session database table
// - valid alphanumeric random get code from the url which also matches session
//   database table info
// - a fingerprint that has not changed since the session was started
// - activity within the session timeout duration (10 minutes)
// - additionally, if there is trusted pc information in the session database table,
//   check for a trusted pc cookie on the user's computer, and if there is one,
//	 it must have a valid alphanumeric value, it must match the session info in
//   the database, and the value must be in the trusted cookies database table
// ***********************************************************************************
// The session get code id in the get code cookie, the get code in the url,
// the random get code id in the url and the random get code in the url are all
// randomly generated and updated in the session table on each pageload. Therefore,
// the user must load every page with the updated get codes and cookies. When a page
// does load, after the session validates, all the codes and cookies are changed.

include_once('includes/functions/fingerprint.php');
include_once('includes/functions/genpass.php');
include_once('includes/functions/utf8_unicode.php');

// set inactive users to logged out
include('includes/process_set_logged_out.php');


// declare some variables
$user_logged_in = '0';
$sessioninvalid = '0';
$sessiontimeout = '0';
$pattern        = '/[^A-Za-z0-9]/';
$now            = time();
$newurl         = '';
$error          = '';
$trusted        = '0';


// ***********************************************************************************
// If COOKIES are not set, redirect back to login form
// ***********************************************************************************
if(!isset($_COOKIE[$sessgetcodecookiename]) || !isset($_COOKIE[$sesscookiename])) {
	$sessioninvalid = '1';
}


// ***********************************************************************************
// If both COOKIES are set, verify the session!!
// ***********************************************************************************
elseif(isset($_COOKIE[$sessgetcodecookiename]) && isset($_COOKIE[$sesscookiename]) && $sessioninvalid !== '1') {

	$sessgetcode = $_COOKIE[$sessgetcodecookiename];

	// validate the sesscode with the GET sess id from the cookie
	if(isset($_GET[$sessgetcode])) {
		if($_GET[$sessgetcode] == '') {
			$sessioninvalid = '1';
			$error = '1';
		}
		else {
			$checkpattern = (preg_match($pattern, $_GET[$sessgetcode]) == '1') ? '1' : '0';
			if($checkpattern == '1') {
				$sessioninvalid = '1';
				$error = '2';
			}
			else {
				$getsesscode = $_GET[$sessgetcode];
			}
		}
	}
	else {
		$sessioninvalid = '1';
		$error = '3';
	}

	// if the GET reg code is valid
	if($sessioninvalid !== '1') {

		// use GET sesscode to access database
		$connect       = mysql_connect($sqlserver, $sqlselect, $sqlpassword);
		$db            = mysql_select_db($sqldatabase, $connect);
		$query         = "SELECT * FROM mll_users_sessions WHERE mll_users_sessions.sesscode = '%s'";
		$sesscode      = mysql_real_escape_string($getsesscode, $connect);
		$checksessinfo = mysql_query(sprintf($query, $sesscode), $connect);

		// make sure something was returned
		if(mysql_num_rows($checksessinfo) == 0) {
			mysql_close($connect);
			$sessioninvalid = '1';
			$error = '4';
		}

		// if there is a row, get the other session info
		else {
			$checksessinfo_res    = mysql_fetch_assoc($checksessinfo);
			$checksesssessid      = $checksessinfo_res['sessid'];
			$checksesscode        = $checksessinfo_res['sesscode'];
			$checksessrand_id     = $checksessinfo_res['rand_id'];
			$checksessrand_code   = $checksessinfo_res['rand_code'];
			$checksessfingerprint = $checksessinfo_res['fingerprint'];
			$checksesstime        = $checksessinfo_res['time'];
			mysql_close($connect);

			// check user_logged_in value
			if($checksessinfo_res['user_logged_in'] == '0') {
				$sessioninvalid = '1';
				$error = '5';
			}

			// user_logged_in is 1, keep going
			else {

				// check GET rand code
				if(isset($_GET[$checksessrand_id])) {
					if($_GET[$checksessrand_id] == '') {
						$sessioninvalid = '1';
						$error = '6';
					}
					else {
						$checkpattern = (preg_match($pattern, $_GET[$checksessrand_id]) == '1') ? '1' : '0';
						if($checkpattern == '1') {
							$sessioninvalid = '1';
							$error = '7';
						}
						else {
							$getrand_code = $_GET[$checksessrand_id];
						}
					}
				}
				else {
					$sessioninvalid = '1';
					$error = '8';
				}

				// continue if rand code is valid
				if($sessioninvalid !== '1') {

					// check GET codes against database
					if($getsesscode !== $checksesscode || $getrand_code !== $checksessrand_code) {
						$sessioninvalid = '1';
						$error = '9';
					}

					else {

						// check cookie value from database (should be the sessid)
						if($_COOKIE[$sesscookiename] !== $checksesssessid) {
							$sessioninvalid = '1';
							$error = '10';
						}

						else {

							// get a user fingerprint and check fingerprint from database
							$fingerprint = fingerprint();
							if($fingerprint !== $checksessfingerprint) {
								$sessioninvalid = '1';
								$error = '11';
							}

							else {

								// check for session timeout
								$expired = $now - $checksesstime;
								if($expired > $sesstimeout) {
									$sessiontimeout = '1';
									$error = '12';
								}
							}
						}
					}
				}
			}
		}
	}
}
// ***********************************************************************************



// ***********************************************************************************
// No errors so far - compare timezones
// ***********************************************************************************
// The user's timezone is only updated in their session row when they login, so
// it should not change during their session. If it does change during their session,
// it would suggest someone else in a different geographic location signed in to
// their account, or for some reason their timezone changed while the user was
// logged in (daylight savings time or something, who knows). If someone else has
// access to their account from a different geographic location, and they were able
// to login, that means they were able to bypass the trusted cookie requirements
// (email code) and there really is little we can do to prevent a hacker with the
// correct information from logging in to the user's account. Of course, this check
// only works when a user is actually logged in or their session is still active.
// Otherwise, it's up to the login scripts to decide what to do.
// Maybe we should give some other kind of warning?? Maybe this could prevent
// XSS attacks when users click on malicious email links or something? Let's do it.
if($sessioninvalid !== '1' && $sessiontimeout !== '1') {

	// get the timezone in the session table
	$session_timezone = $checksessinfo_res['timezone'];

	// get the current timezone
	$current_timezone = date_default_timezone_get();

	// compare them
	if($current_timezone !== $session_timezone) {
		$sessioninvalid = '1';
		$error = '13';
	}
}



// ***********************************************************************************
// If there were no errors validating the session so far, get the user's info
// ***********************************************************************************
if($sessioninvalid !== '1' && $sessiontimeout !== '1') {
	$connect     = mysql_connect($sqlserver, $sqlselect, $sqlpassword);
	$db          = mysql_select_db($sqldatabase, $connect);
	$getuserinfo = mysql_query("SELECT * FROM mll_users WHERE mll_users.id = '".$checksessinfo_res['user_id']."'", $connect);
	$userinfores = mysql_fetch_assoc($getuserinfo);
	mysql_close($connect);
}



// ***********************************************************************************
// If there is trusted pc information, verify that as well
// ***********************************************************************************
// if sess_trusted_cookie in the mll_users_sessions table is not 'logout', 'none'
// or 'invalid', check to see if the sess_trusted_cookie code matches the trusted
// cookie that should be present on the user's machine.
if($sessioninvalid !== '1' && $sessiontimeout !== '1') {

	$hashed_email = sha1($userinfores['email']);


	// if there is data to check in the sessions table
	// 'none' - the user chose not to trust the pc they were using
	// The sess_trusted_cookie value is set when users log in, so it will always either
	// be 'none' or a trusted cookie code
	if($checksessinfo_res['sess_trusted_cookie'] !== 'none') {

		// check if there is a cookie - if there is info in the databse, the user should have one
		if(!isset($_COOKIE[$hashed_email])) {

			// the trusted cookie is missing - the user should not have a trusted cookie code in the session table
			$sessioninvalid = '1';
			$error = '14';
			$error = $hashed_email;
		}

		// there is a cookie
		else {

			// check if it matches the database code
			if($_COOKIE[$hashed_email] !== $checksessinfo_res['sess_trusted_cookie']) {
				$sessioninvalid = '1';
				$error = '15';
			}

			// they match, check to see if the code is in the mll_trusted_cookies table
			else {
				$connect      = mysql_connect($sqlserver, $sqlselect, $sqlpassword);
				$db           = mysql_select_db($sqldatabase, $connect);
				$checktrusted = mysql_query("SELECT * FROM mll_trusted_cookies WHERE mll_trusted_cookies.user_id = '".$checksessinfo_res['user_id']."' AND mll_trusted_cookies.trusted_cookie = '".$checksessinfo_res['sess_trusted_cookie']."' AND mll_trusted_cookies.cookie_name = '".$hashed_email."'", $connect);
				mysql_close($connect);

				// check if the query failed
				if($checktrusted == false) {
					$sessioninvalid = '1';
					$error = '16';
				}

				else {

					// if there are no rows, the pc is not trusted
					if(mysql_num_rows($checktrusted) == 0) {
						$sessioninvalid = '1';
						$error = '17';
					}

					// there is a row, the pc is trusted
					else {
						$trusted = '1';
					}
				}
			}
		}
	}
}



// ***********************************************************************************
// If the session check failed, destroy the session and redirect
// ***********************************************************************************
if($sessioninvalid == '1' || $sessiontimeout == '1') {

	// We can't change anything in the sessions table because we do not know
	// who the user is!! Upon login, the session data will be rewritten.

	// if the session timed out, update the database
	// this is the only time we can modify the sessions table
	if($sessiontimeout == '1') {
		$connect   = mysql_connect($sqlserver, $sqlmaster, $sqlpassword);
		$db        = mysql_select_db($sqldatabase, $connect);
		$upregsess = mysql_query("UPDATE mll_users_sessions SET mll_users_sessions.sesscode = 'invalid', mll_users_sessions.rand_id = 'invalid', mll_users_sessions.rand_code = 'invalid', mll_users_sessions.fingerprint = 'invalid', mll_users_sessions.sess_trusted_cookie = 'invalid', mll_users_sessions.directory = '".$directory."', mll_users_sessions.page = '".$page."', mll_users_sessions.user_logged_in = '0' WHERE mll_users_sessions.id = '".$checksessinfo_res['id']."'", $connect);
		mysql_close($connect);
	}

	// force cookie params for this script
	$lifetime = '0';
	$path     = '/';
	$domain   = $site_domain;
	$secure   = $site_cookies_secure;
	$httponly = true;
	session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);

	// choose the register session
	session_name($sesscookiename);

	// enter session
	session_start();

	// destroy all the register session data
	$_SESSION = array();

	// If it is desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	// One more pageload is required before the cookie will be gone
	setcookie($sesscookiename, '', time() - 42000, $path, $domain, $secure, $httponly);

	// destroy the session code get id cookie
	setcookie($sessgetcodecookiename, '', time() - 42000, $path, $domain, $secure, $httponly);

	// destroys all of the data associated with the current session
	// session_destroy() does not unset any of the global variables associated with the session, or unset the session cookie
	session_destroy();

	// get the error type
	if($sessiontimeout == '1') {
		$cookieerror = 'timeout';
	}
	else {
		$cookieerror = 'invalid';
	}

	// set the error cookie
	setcookie('error', $cookieerror, $lifetime, $path, $domain, $secure, $httponly);

	// redirect to error directory
	header("Location: ".$site_path."error/?id=".$error);
}
// ***********************************************************************************



// ***********************************************************************************
// If there were no errors, (amazing!) reenter the session!
// ***********************************************************************************
if($sessioninvalid !== '1' && $sessiontimeout !== '1') {

	// generate a new session GET id to be stored in a cookie
  $newsessgetcode = genPass($len=mt_rand(15,25), $nums='1', $lower='1', $upper='0', $spcl='0', $uniq='1');

	// generate a new GET sesscode
	$newsesscode = genPass($len='40', $nums='1', $lower='1', $upper='1', $spcl='0', $uniq='1');

	// generate a new random GET id
	$newrand_id = genPass($len='6', $nums='1', $lower='1', $upper='0', $spcl='0', $uniq='1');

	// generate a new random GET code
	$newrand_code = genPass($len='40', $nums='1', $lower='1', $upper='1', $spcl='0', $uniq='1');

	// if the user is logging in after a session error, get the page to display before updating the database
	// if the user is not logging in after an error, $page will come from the page the user is loading
	if($checksessinfo_res['relogin'] == '1') {
		$page = $checksessinfo_res['page'];
	}

	// update the mll_users_sessions table
	$connect   = mysql_connect($sqlserver, $sqlmaster, $sqlpassword);
	$db        = mysql_select_db($sqldatabase, $connect);
	$upregsess = mysql_query("UPDATE mll_users_sessions SET mll_users_sessions.sesscode = '".$newsesscode[1]."', mll_users_sessions.rand_id = '".$newrand_id[1]."', mll_users_sessions.rand_code = '".$newrand_code[1]."', mll_users_sessions.directory = '".$directory."', mll_users_sessions.page = '".$page."', mll_users_sessions.relogin = '0', mll_users_sessions.user_logged_in = '1', mll_users_sessions.time = '".$now."' WHERE mll_users_sessions.id = '".$checksessinfo_res['id']."'", $connect);
	mysql_close($connect);

	$newurl = "?".$newsessgetcode[1]."=".$newsesscode[1]."&".$newrand_id[1]."=".$newrand_code[1];

	// force cookie params for this script
	$lifetime = '0';
	$path     = '/';
	$domain   = $site_domain;
	$secure   = $site_cookies_secure;
	$httponly = true;
	session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);

	// reset the cookie so that as long as the user is using this trusted pc,
	// the cookie will not be dropped from the user's machine when the cookie
	// expires, and the code will not be deleted from the database
	if($trusted == '1') {
		$trusted_code = genPass($len='80', $nums='40', $lower='40', $upper='0', $spcl='0', $uniq='1');

		// set the trusted cookie
		setcookie($hashed_email, $trusted_code[1], time()+$trusted_cookie_timeout, $path, $domain, $secure, $httponly);

		// update the trusted_cookies table
		$connect   = mysql_connect($sqlserver, $sqlmaster, $sqlpassword);
		$db        = mysql_select_db($sqldatabase, $connect);
		$entercode = mysql_query("UPDATE mll_trusted_cookies SET mll_trusted_cookies.trusted_cookie = '".$trusted_code[1]."', mll_trusted_cookies.last_checked = '".$now."' WHERE mll_trusted_cookies.trusted_cookie = '".$checksessinfo_res['sess_trusted_cookie']."' AND mll_trusted_cookies.user_id = '".$checksessinfo_res['user_id']."' AND mll_trusted_cookies.cookie_name = '".$hashed_email."'", $connect);
		mysql_close($connect);

		// update the mll_users_sessions table
		$connect    = mysql_connect($sqlserver, $sqlmaster, $sqlpassword);
		$db         = mysql_select_db($sqldatabase, $connect);
		$setrelogin = mysql_query("UPDATE mll_users_sessions SET mll_users_sessions.sess_trusted_cookie = '".$trusted_code[1]."' WHERE mll_users_sessions.user_id = '".$checksessinfo_res['user_id']."'", $connect);
		mysql_close($connect);
	}

	// set the session get code coookie
	// ($sessgetcodecookiename comes from config.php)
	setcookie($sessgetcodecookiename, $newsessgetcode[1], $lifetime, $path, $domain, $secure, $httponly);

	// name the session
	session_name($sesscookiename);

	// enter the session
	session_start();

	// indicate the user is logged in
	$user_logged_in = '1';

	// declare the user id
	$userid = $userinfores['id'];

	// set timer
	$show_timer = '1';
	$timeout_period = 60*10; // 10 minutes in seconds
	$timer_box = 'footer_timer';
}
// ***********************************************************************************
?>
