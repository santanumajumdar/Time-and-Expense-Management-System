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

$basedir = dirname(dirname(dirname(__FILE__)));

require_once("$basedir/core/init/initialize.php");



define('MYSQL_DUPLICATE_ENTRY', 1062);
define('MYSQL_DATA_TOO_LONG', 1406);
define('MYSQL_PARENT_KEY_CONSTRAINT', 1451);
define('MYSQL_FOREIGN_KEY_CONSTRAINT', 1452);


abstract class __DBCoreFunctions
{
	
	private static $s_connection = NULL;

	protected function getConnection() {

		if (self::$s_connection === NULL) {
			
			$conn = @new mysqli($_SESSION['ini']['database']['db_server'], 
								$_SESSION['ini']['database']['db_user'], 
								$_SESSION['ini']['database']['db_password'], 
								$_SESSION['ini']['database']['db_database']);
			if (mysqli_connect_errno() !== 0) {
				
				$msg = mysqli_connect_error();
				throw new iDatabaseErrorException($msg, 'Connect');
			}
			self::$s_connection = $conn;
			self::$s_connection->query('SET NAMES utf8');
		}
		return self::$s_connection;
	}

	
	
	public function escapeString($in_string) {
		
		if ($in_string === NULL)
			return '';

		$conn = __DBCoreFunctions::getConnection();
		if ($conn !== NULL)
			return($conn->real_escape_string(trim($in_string)));
	}


	public function beginTransaction() {
		
		$conn = $this->getConnection();
		$conn->query('START TRANSACTION');
		if ($conn->errno != 0)
			throw new iDatabaseErrorException($conn, 'START TRANSACTION');
			
		return TRUE;
	}


	public function commitTransaction() {
		
		$conn = $this->getConnection();
		$conn->query('COMMIT');
		if ($conn->errno != 0)
			throw new iDatabaseErrorException($conn, 'COMMIT');
			
		return TRUE;
	}

	
	public function rollbackTransaction() {
		
		$conn = $this->getConnection();
		$conn->query('ROLLBACK');
		if ($conn->errno != 0)
			throw new iDatabaseErrorException($conn, 'ROLLBACK');
			
		return TRUE;
	}

	/*
	 * If you add any new exception method name here,
	 * you must add the method details in temsbean/ExpectionHandler.php
	 */
	
	protected function chkQueryError($dbConn, $query) {

		if (strtoupper($_SESSION['ini']['environment']['db_trace_level']) == 'HIGH') {
			$dbQuery = $query.PHP_EOL.'-------------------------------------------------------------------'.PHP_EOL;
			error_log($dbQuery, 3, $_SESSION['db_trace_log']);
		}
		
		if ($dbConn->errno == MYSQL_DUPLICATE_ENTRY)
			throw new iIDInUseException($dbConn);
		if ($dbConn->errno == MYSQL_PARENT_KEY_CONSTRAINT)
			throw new iPKInUseException();
		if ($dbConn->errno == MYSQL_FOREIGN_KEY_CONSTRAINT)
			throw new iFKInUseException();
		if ($dbConn->errno == MYSQL_DATA_TOO_LONG)
			throw new iDataTooLongException();

		if ($dbConn->errno != 0)
			throw new iDatabaseErrorException($dbConn, $query);
		
		return TRUE;
	}


}

?>