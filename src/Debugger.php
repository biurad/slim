<?php
/*
        This code is under MIT License
        
        +--------------------------------+
        |   DO NOT MODIFY THIS HEADERS   |
        +--------------------------------+-----------------+
        |   Created by BiuStudio                           |
        |   Email: support@biuhub.net                      |
        |   Link: https://www.biurad.tk                    |
        |   Source: https://github.com/biustudios/biurad   |
        |   Real Name: Divine Niiquaye - Ghana             |
        |   Copyright Copyright (c) 2018-2019 BiuStudio    |
        |   License: https://biurad.tk/LICENSE.md          |
        +--------------------------------------------------+
        
        +--------------------------------------------------------------------------------+
        |   Version: 0.0.1.1, Relased at 18/02/2019 13:13 (GMT + 1.00)                       |
        +--------------------------------------------------------------------------------+
        
        +----------------+
        |   Tested on    |
        +----------------+-----+
        |  APACHE => 2.0.55    |
        |     PHP => 5.4       |
        +----------------------+
        
        +---------------------+
        |  How to report bug  |
        +---------------------+-----------------------------------------------------------------+
        |   You can e-mail me using the email addres written above. That email is also my msn   |
        |   contact, so you can use it for contact me on MSN.                                   |
        +---------------------------------------------------------------------------------------+
        
        +-----------+
        |  Notes    |
        +-----------+------------------------------------------------------------------------------------------------+
        |   - BiuRad's simple-as-possible architecture was inspired by several conference talks, slides              |
        |     and articles about php frameworks that - surprisingly and intentionally -                              |
        |     go back to the basics of programming, using procedural programming, static classes,                    |
        |     extremely simple constructs, not-totally-DRY code etc. while keeping the code extremely readable.      |
        |   - Features of Biuraad Php Framework
        |     +--> Proper security features, like CSRF blocking (via form tokens), encryption of cookie contents etc.|
        |     +--> Built with the official PHP password hashing functions, fitting the most modern password          |
                   hashing/salting web standards.                                                                    |
        |     +--> Uses [Post-Redirect-Get pattern](https://en.wikipedia.org/wiki/Post/Redirect/Get)                 |
        |     <--+ Uses URL rewriting ("beautiful URLs").                                                            |
        |   - Masses of comments                                                                                     |                                                                              |
        |     +--> Uses Libraries including Composer to load external dependencies.                                  |
        |     <--+ Proper security features, like CSRF blocking (via form tokens), encryption of cookie contents etc.|
        |   - Fits PSR-0/1/2/4 coding guideline.                                                                     |
        +------------------------------------------------------------------------------------------------------------+
        
        +------------------+
        |  Special Thanks  |
        +------------------+-----------------------------------------------------------------------------------------+
        |  I always thank the HTML FORUM COMMUNITY (http://www.html.it) for the advice about the regular expressions |
        |  A special thanks at github.com(http://www.github.com), because they provide me the list of php libraries, |
        |  snippets, and any more...                                                                                 |
        |  I thanks Php.net and Sololearn.com for its guildline in PHP Programming                                   |
        |  Finally, i thank Wikipedia for the countries's icons 20px                                                 |
        +------------------------------------------------------------------------------------------------------------+
*/
namespace Radion;

use Exception as BaseException;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Handler\JsonResponseHandler;
/**
 *  The Debugger
 * -----------------------------------------------------------------------
 * 
 * Provides the developer with useful messages in case of an exception or
 * errors happen. 
 *
 */
class Debugger extends \Exception {
	private static $profiles = [];
	private static $time_start = 0;
	private static $profilerStartTime = 0;

 /**
 * Registering the debugger to log exceptions locally or transfer them to 
 * external services.
 * 
 * Depends on the settings in config/env.php:
 *
 * + 0: Shows "Something went wrong" message ambiguously (handled locally)
 *
 * + 1:	Shows simple error message, file and the line occured (handled 
 *			locally)
 *
 * + 2: Shows advanced debugging with code snippet, stack frames, and 
 *			envionment details, handled by Flip\Whoops 
 *
 * @static
 * @access public
 * @since Method available since Release 0.1.0
 */
static function start()
{
	if(getenv('DEBUG') === '0' || getenv('DEBUG') === '1'){register_shutdown_function('Radion\Debugger::error_handler');
	}else if(getenv('DEBUG') === '2')
	{ 	$whoops = new \Whoops\Run;
      	$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                
      	if (\Whoops\Util\Misc::isAjaxRequest()) {
            $jsonHandler = new JsonResponseHandler();
            $jsonHandler->setJsonApi(true);
            $whoops->pushHandler($jsonHandler);
        }

        $whoops->register();
	}else if(getenv('DEBUG') === '-1'){}session_name('RADIONSESSID');session_start();

if (getenv('ENVIRONMENT') == 'development') {
    ini_set('display_errors', 0);
    error_reporting(-1);
}
else if (getenv('ENVIRONMENT') == 'testing') {
    error_reporting(-1);
    ini_set('display_errors', 1);
}
else if (getenv('ENVIRONMENT') == 'maintainance') {
	error_reporting(0);
    ini_set('display_errors', 0);
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    Debugger::display('wrong','Maintainance Mode',"Sorry but the application is being maintained, we'll back shortly");
    echo "<script>document.title = 'Maintainance Mode';</script>";
    exit(1);
}
else if (getenv('ENVIRONMENT') == 'production') {
    ini_set('display_errors', 0);
    if (version_compare(PHP_VERSION, '5.6', '>='))
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
    }
    else
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
    }
}
else {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    Debugger::display('wrong','Environment not defined','The application environment is not set correctly');
    exit(1); // EXIT_ERROR
}
}

/**
 * Sets the header of the HTTP request and then display the
 * HTTP error codes. 
 *
 * @param string	$code				The HTTP error code
 * @param bool		$terminate	Terminate the entire script execution
 *
 * @static
 * @see Debugger::set_header(), Debugger::display()
 * @access public
 * @since Method available since Release 0.1.0
 */
static function report($code, $terminate = false){
	switch ($code) {
		case '404':
			self::set_header('404', 'Internal Server Error');
			self::display('simple', '404 Not Found', 'The requested URL was not found on this server.');	
			break;
		case '500':
			self::set_header('500', 'Internal Server Error');
			self::display('simple', 'Something went wrong');	
			break;
		default:
			self::set_header('500', 'Internal Server Error');
			self::display('simple');	
			break;
	}
	
	if($terminate){
		die();
	}
}

/**
 * Sets the header of the HTTP request
 *
 * @static
 * @access public
 * @since Method available since Release 0.1.0
 */
static function set_header($code, $error){
	header($_SERVER['SERVER_PROTOCOL']. ''. $code . '' . $error);
}

/**
 * The error handler which is called by register_shutdown_function()
 * in event of exceptions, syntax errors, warning and notices.
 *
 * @static
 * @see Debugger::start(), Debugger::display()
 * @access public
 * @since Method available since Release 0.1.0
 */
static function error_handler(){
	$error = error_get_last();
	$message = $error['message'];
	if($error){
		if(getenv('DEBUG') == 0){
			self::display('wrong', 'Something went wrong');
		}else{
			self::display('full', $error);
		}
	}
}

/**
 * Display error messages
 *
 * @param string $name		error page name
 * @param string @message	error messages
 *
 * @static
 * @access public
 * @since Method available since Release 0.1.0
 */
static function display($name, $message = '', $description = ''){
	self::set_header('500', 'Internal Server Error');
	include('Resources/'.Config::get('theme','storage_path').'/errors/html/'. $name .'.php');
}

/**
 * Calculate a precise time difference.
 *
 * @param string $start result of microtime()
 * @param string $end 	result of microtime(); if NULL/FALSE/0/'' then it's now
 *
 * @return flat difference in seconds, calculated with minimum precision loss
 *
 * @static
 * @see Debugger::exec_time()
 * @access public
 * @since Method available since Release 0.1.0
 */
static private function microtime_diff($start)
{
	$duration = microtime(true) - $start;
	$hours = (int)($duration/60/60);
	$minutes = (int)($duration/60)-$hours*60;
	$seconds = $duration-$hours*60*60-$minutes*60;
	return number_format((float)$seconds, 5, '.', '');
}

/**
 * Display execution time (start time - finish time) in human readable form
 * (milliseconds).
 *
 *
 * @static
 * @see Debugger::microtime_diff()
 * @access public
 * @since Method available since Release 0.1.0
 */
static function exec_time(){
	echo ('<span class="ss_exec_time" style="display: table;margin: 0 auto;font-size: 20px;color: #333;">Request takes '.(self::microtime_diff(BR_START) * 1000 ) . ' milliseconds</span>');
}

static function startProfiling() {
	if(self::$profilerStartTime == 0){
			self::$profilerStartTime = microtime(true);
		}

	self::$time_start = microtime(true);
}

static function addProfilingData($point_name = '', $point_type = 'others'){
		$profileData =
					[
							'name' => $point_name,
							'time' => ( self::microtime_diff(self::$time_start) * 1000 ),
							'unit' => 'ms',
							'type' => $point_type
					];

			array_push(self::$profiles, $profileData);

			self::$time_start = microtime(true);

		return $profileData;
	}

	static function endProfiling(){
		$timeIncludingAutoloader = self::microtime_diff(BR_START) * 1000;
			$timeProfiled = self::microtime_diff(self::$profilerStartTime) * 1000;
		$timeMinusAutoloader = $timeIncludingAutoloader - $timeProfiled;

			$profileData =
					[
							'name' => 'Starting Autoloader',
							'time' => ($timeMinusAutoloader),
							'unit' => 'ms',
							'type' => 'system'
					];

			array_unshift(self::$profiles, $profileData);
			self::$time_start = 0;
			self::$profilerStartTime = 0;

			return
					[
							'Total Time' => ( $timeIncludingAutoloader ),
							'unit' => 'ms',
							'profiles' => self::$profiles,
					];
	}
} 