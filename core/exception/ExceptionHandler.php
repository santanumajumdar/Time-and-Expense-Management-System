<?php

/*********************************************************************************
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

 ********************************************************************************/

/*
 * This `iexceptionHandler` exception handler is called if the exception was not caught in the try...catch block.
 * 
 * 
 * For MySQL we will discuss about three types of errors.
 * 
 * 1. Compile error --  These errors are progamming errors, like incorrect sql statement
 * 						These errors will be trapped, technical detail will be shown always on the screen 
 * 						and always be logged in the log file.
 * 2. Run time error -- These erors are also programming error but happens due to data condition, such as
 * 						passing a NULL value in a not-NULL field, entering wrong value in the date field etc.
 * 						These errors will be trapped, technical detail will be shown always on the screen 
 * 						and always be logged in the log file.
 * 3. System error   -- System configuration error, such as database down or server mis-configuration
 * 						These errors will be trapped, technical detail will be shown always on the screen 
 * 						and always be logged in the log file.
 */

$basedir = dirname(dirname(dirname(__FILE__)));

function iexceptionHandler($exception) {
    
    $errorDetail =  "<b>DATABASE EXCEPTION:</b>"
    				."<br>---ERROR DETAIL---<br>"
    				.$exception->getMessage()
    				."<br><b>Code:</b> ".$exception->getCode()
    				."<br>---SOURCE DETAIL---<br>"
    				."<b>Program: </b>".$exception->getFile()."<br><b>Line: </b>".$exception->getLine()
    				."<br>---CALL STACK---<br>"
    				.$exception->getTraceAsString()
    				."<p><b>Note: </b>This is typically happens when program attempts to update database with invalid data. Based on the normal and intended situations this should never happen. In all most in all cases, this is a programming logic error. See the program name and line number, and also the call stack above for more details.</p>";
    
    trigger_error ($errorDetail, E_USER_ERROR);
    
    echo (json_encode(array('nocategory' => $errorDetail)));

}

// TODO Find the list of all database error list and write a generic error handler routine - Kallol.

class iInvalidDataException extends Exception {}
class iInvalidArgumentException extends Exception {}
class iFKInUseException extends Exception {}
class iPKInUseException extends Exception {}
class iDataTooLongException extends Exception {}

class iIDInUseException extends Exception {
	
	public function __construct($dbConn) {}
}


class iDatabaseErrorException extends Exception
{
	public function __construct($dbConn, $query) {
		
		throw new Exception("MySQL Error: {$dbConn->error}.<br>SQL Statement:".PHP_EOL.$query.PHP_EOL);
	}
}


class iBLError extends Exception
{
	public $errField = '';
	public $messages_id = '';
	public $msgdta = '';

	public function __construct($errField='', $messages_id='', $msgdta='') {
		
		$this->errField = $errField;
		$this->messages_id = $messages_id;
		$this->msgdta = $msgdta;
		parent::__construct();
	}
}


//*** This must be defined after the actual function definition.

set_exception_handler('iexceptionHandler');
?>