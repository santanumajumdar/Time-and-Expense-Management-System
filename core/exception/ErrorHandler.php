<?php

/* * *******************************************************************************
 * TEMS is a Time and Expense Management program developed by
 * Initechs, LLC. Copyright (C) 2009 - 2013 Initechs LLC.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY INITECHS, INITECHS DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact Initechs headquarters at 1841 Piedmont Road, Suite 301,
 * Marietta, GA, USA. or at email address contact@initechs.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display od the "Initechs" logo.
 * If the display of the logo is not reasonably feasible for technical reasons,
 * the Appropriate Legal Notices must display the words "Powered by Initechs".

 * ****************************************************************************** */

/*
 * Software is delivered with debug mode turned ON.
 * To turn off debug, open the core/config/config.ini file and set the line "debug_level = NONE",
 * which is under the section [environment].
 */

/* 
 * This `ierrorHandler` will handle all programing errors.
 * 
 * We will discuss about four types of errors for PHP.
 * 
 * 1. User error     -- When user enters incorrect data. This error will be shown on the screen,
 * 						and those are not handled here.
 * 2. Compiler error -- These error are programming error, such as class not defined, invalid function etc.
 * 						These errors cannot be trapped by user error handler. It is taken care by php.net application.
 * 3. Run time error -- These are also programming error but happens due to data condition, such as
 * 						division by zero, subscript out of range etc. These errors will be trapped,
 * 						technical detail will be shown on the screen on the debug mode and always be 
 * 						logged in the log file.
 * 4. System error   -- System configuration error, such as php ini file set up or server mis-configuration
 * 						These errors will be trapped, technical detail will be shown always on the screen 
 * 						and always be logged in the log file.
 */

$basedir = dirname(dirname(dirname(__FILE__)));

if (!defined(E_DEPRECATED)) define(E_DEPRECATED, 8192); 
if (!defined(E_USER_DEPRECATED)) define(E_USER_DEPRECATED, 16384);

if ((isset($_SESSION['ini']['environment']['debug_level']))
	and (strtoupper($_SESSION['ini']['environment']['debug_level']) == 'NONE'))
	Define ('DEBUG', FALSE);
else 
	Define ('DEBUG', TRUE);


$logFile = "$basedir/log/log_".date("Ymd_His").".txt";

if (DEBUG)
	ini_set("display_errors", 1);
else 
	ini_set("display_errors", 0);

ini_set("log_errors", 1);
	
function ierrorHandler($errno, $errMessage, $errFile, $errLine, $errContext) {

	global $logFile;
	
	if (DEBUG)
		error_reporting(E_ALL ^ E_DEPRECATED);  
	else
		error_reporting(E_WARNING | E_USER_WARNING | E_USER_ERROR);	
	
	$errorType = array(
        E_ERROR => 'ERROR',							// 0000,0000,0000,0001 = 1
        E_WARNING => 'WARNING',						// 0000,0000,0000,0010 = 2
        E_PARSE => 'PARSING ERROR',					// 0000,0000,0000,0100 = 4
        E_NOTICE => 'NOTICE',						// 0000,0000,0000,1000 = 8
        E_CORE_ERROR => 'CORE ERROR',				// 0000,0000,0001,0000 = 16
        E_CORE_WARNING => 'CORE WARNING',			// 0000,0000,0010,0000 = 32
        E_COMPILE_ERROR => 'COMPILE ERROR',			// 0000,0000,0100,0000 = 64
        E_COMPILE_WARNING => 'COMPILE WARNING',		// 0000,0000,1000,0000 = 128
        E_USER_ERROR => 'USER ERROR',				// 0000,0001,0000,0000 = 256
        E_USER_WARNING => 'USER WARNING',			// 0000,0010,0000,0000 = 512
        E_USER_NOTICE => 'USER NOTICE',				// 0000,0100,0000,0000 = 1024
        E_STRICT => 'STRICT NOTICE',				// 0000,1000,0000,0000 = 2048
        E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',	// 0001,0000,0000,0000 = 4096
        E_DEPRECATED => 'DEPRECATED',				// 0010,0000,0000,0000 = 8192
        E_USER_DEPRECATED => 'USER DEPRECATED',		// 0100,0000,0000,0000 = 16384
        E_ALL => 'ALL',								// 1000,0000,0000,0000 = 30719
	);

    if (!(error_reporting() & $errno)) { // This error code is not included in error_reporting
        return TRUE;
    }
    
    // Log every error possible, if that was not globally ignored.
    
    $message = PHP_EOL."***ERROR***".PHP_EOL.date("Y-m-d h:m:s").PHP_EOL."Error Level: $errorType[$errno] (Code = $errno)".PHP_EOL."Description: $errMessage".PHP_EOL."File: $errFile (Line $errLine)".PHP_EOL;
    error_log($message, 3, $logFile);
    if (DEBUG) error_log(PHP_EOL."CODE TRACE".PHP_EOL.print_r(debug_backtrace(), TRUE).PHP_EOL, 3, $logFile);
    if (DEBUG) error_log(PHP_EOL."SESSION VARIABLES".PHP_EOL.print_r($_SESSION, TRUE).PHP_EOL, 3, $logFile);
    if (DEBUG) error_log(PHP_EOL."SERVER VARIABLES".PHP_EOL.print_r($_SERVER, TRUE).PHP_EOL, 3, $logFile);
    if (DEBUG) error_log(PHP_EOL."POST VARIABLES".PHP_EOL.print_r($_POST, TRUE).PHP_EOL, 3, $logFile);
    if (DEBUG) error_log(PHP_EOL."GET VARIABLES".PHP_EOL.print_r($_GET, TRUE).PHP_EOL, 3, $logFile);
    if (DEBUG) error_log(PHP_EOL."---DATA DUMP---".PHP_EOL.print_r($errContext, TRUE).PHP_EOL."---END OF DATA DUMP---", 3, $logFile);
    
    $errReport = "";
    $errReport .= ierrorOnScreenReport($errorType[$errno], $errno, $errMessage, $errFile, $errLine, $errContext);
    
    switch ($errno) {
	// This block of error codes are not processesed by user defined error. 
	// This section is here for the sake of completion only.
    	case E_ERROR:
    	case E_PARSE:
    	case E_CORE_ERROR:
    	case E_CORE_WARNING:
    	case E_COMPILE_ERROR:
    	case E_COMPILE_WARNING:
    	case E_STRICT:
        	 $_SESSION['program_error'] = $errReport;
             exit(1);
             break;

        case E_WARNING:
        case E_USER_WARNING:
        case E_USER_ERROR:
        	 $_SESSION['program_error'] = $errReport;
             break;

        case E_NOTICE:
        case E_USER_NOTICE:
             $_SESSION['program_error'] = $errReport;
             break;

        case E_RECOVERABLE_ERROR:
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
        	 $_SESSION['program_error'] = $errReport;
             break;
        	
        default:			// To catch future errors.
            echo $errReport;
            exit(1);
            break;
    }

    return TRUE; // Don't execute PHP internal error handler
}

function ierrorOnScreenReport($errType, $errno, $errMessage, $errFile, $errLine, $errContext) {

	global $logFile;
	
	$errArray = explode('#', $errMessage);
	$errMessage = implode("<br>#", $errArray);
	$errArray = explode('---ERROR DETAIL---', $errMessage);
    $errMessage = implode("<br>---ERROR DETAIL---<br>", $errArray);
    $errArray = explode('---CALL STACK---', $errMessage);
    $errMessage = implode("<br>---CALL STACK---", $errArray);
    
	$errReport = "";
    $errReport .= "<div class='end-of-the-form-error-message'>";
    if (DEBUG)
    	$errReport .= "<p>You are running TEMS in debug mode, you may turn it off the notice for non-critical error. <a href='http://temsonline.com/content/how-turn-debug-mode'>See instruction.</a></p>";
    
    $errReport .= "<p>Make sure that you are using the latest version published. Contact at <a href='mailto:support@temsonline.com'>support@temsonline.com</a> for fixes and support.</p>";
    $errReport .= "<p><b>*** Error Detected in Your Last Operation ***</b></p>";
    $errReport .= "<br><b>Error Level:</b> $errType (Code = $errno).";
    $errReport .= "<br><b>Description:</b> $errMessage<br><b>At</b> $errFile (Line Number = $errLine)";
    $errReport .= "<p>You will find much more detailed technical information at $logFile</p>";
    $errReport .= "</div>";

    return $errReport;
}

//*** This must be defined after the actual function definition.

set_error_handler('ierrorHandler', E_ALL);

?>