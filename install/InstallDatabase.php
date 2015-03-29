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
$basedir = dirname(dirname(__FILE__));
require_once("$basedir/core/config/GlobalVariables.php");
require_once("$basedir/install/InstallDatabaseSQL.php");


abstract class InstallDatabase
{
    
    
	public function CreateNewDatabase($db_server, $db_database, $db_admin_user, $db_admin_password, $db_user, $db_password)
	{

		global $installationLogFile;
		

		if (($installationLogFileHandle = fopen($installationLogFile, "a+")) == NULL) {
			$this->issueMessage("FAILED to create $installationLogFile file. Installation aborted! Check permission of log directory.");
			return FALSE;
		}
		
		fwrite($installationLogFileHandle, PHP_EOL.PHP_EOL. date('c'). " $db_database database installation begins.");
		
		// If this is a hosted server, the database must exist and user must exist with all necessary permissions.

		if ((strtolower($db_server) != 'localhost')
			and ($db_server != '127.0.0.1')
			and ($db_user != '')) {
			if (($con = mysql_connect($db_server, $db_user, $db_password)) == FALSE) {
				$msg = "Installation aborted! Check database server name ($db_server), user id ($db_user), and its password. This user must exist in the database. <a href=\"TechnicalHelp.php#hosted-server-installation\">See help.</a>";
				$this->writeLog($installationLogFileHandle, $msg);
				return FALSE;
			}
		}
		
		// Access database server with admin ID.

		if (($con = mysql_connect($db_server, $db_admin_user, $db_admin_password,1,65536)) == FALSE) {
			$msg = "FAILED to connect $db_server. Installation aborted! Check database server name, Admin user id $db_admin_user, and its password. <a href=\"TechnicalHelp.php#hosted-server-installation\">See help.</a>";
			$this->writeLog($installationLogFileHandle, $msg);
			return FALSE;
		}
		
		fwrite($installationLogFileHandle, PHP_EOL . date('c'). " $db_server server was accessed Successfully.");
		
		// If this is a localhost, it will attempt to create the database if it does not already exist.
		
		if ((strtolower($db_server) == 'localhost')
				or ($db_server != '127.0.0.1')) {
			if (($db_selected = mysql_select_db($db_database, $con)) == FALSE) {
				if (($querySuccessful = mysql_query("CREATE DATABASE $db_database", $con)) === FALSE) {
					$msg = "Could not create $db_database. $db_admin_user may not have database create authority. <a href=\"TechnicalHelp.php#hosted-server-installation\">See help.</a>";
					$this->writeLog($installationLogFileHandle, $msg);
					mysql_close($con);
					return FALSE;
				}
				fwrite($installationLogFileHandle, PHP_EOL . date('c'). " empty $db_database database was created Successfully.");
			}
		}
		
		// Database should have NO tables.
		$sql = "SELECT table_name from information_schema.tables WHERE table_schema = '$db_database'";
		if (($sqlResult = mysql_query("$sql", $con)) == FALSE) {
			$msg = "Database $db_database exists, but failed to find number of tables in the database." . PHP_EOL . "Query = $sql";
			$this->writeLog($installationLogFileHandle, $msg);
			mysql_close($con);
			return FALSE;
		}
		

		if (mysql_num_rows($sqlResult) > 0) {
			$msg = "Database $db_database already has tables in it; TEMS cannot be installed in this database.";
			$this->writeLog($installationLogFileHandle, $msg);
			mysql_close($con);
			return FALSE;
		} 

		
		if (($db_selected = mysql_select_db($db_database, $con)) == TRUE) {		// database exist
			fwrite($installationLogFileHandle, PHP_EOL . date('c'). " Check database $db_database successful. We will check for contents, before proceeding.");
		}

		$_SESSION['status']['database'] = "Database created Successfully.";
			
		$this->GrantDatabaseAuthority($con, $db_server, $db_database, $db_admin_user, $db_admin_password, $db_user, $db_password, $installationLogFileHandle);
			
		if ($this->BuildNewDB($con, $db_database, $installationLogFileHandle) == FALSE) {			// Build new database
			fwrite($installationLogFileHandle, PHP_EOL.PHP_EOL. date('c'). " $db_database database installation ended Unsuccessfully.");
			mysql_close($con);
			fclose($installationLogFileHandle);
			return FALSE;
		} else {
			fwrite($installationLogFileHandle, PHP_EOL.PHP_EOL. date('c'). " $db_database database installation completed Successfully.");
			mysql_close($con);
			fclose($installationLogFileHandle);
			return TRUE;
		}
		return TRUE;
	}
		
	
	public function BuildNewDB($con, $db_database, $installationLogFile)
	{
		global $table, $viewFile, $tableData;
		
		$db_selected = mysql_select_db($db_database, $con);			// Set current database
		if (!$db_selected) {
			$msg = "Could not connect $db_database database.";
			$this->issueMessage($msg);
			fwrite($installationLogFile, PHP_EOL. date('c').' '.$msg);
			return FALSE;
		}
			
		foreach ($table as $tablename => $sql)			// Create tables
		{
			fwrite($installationLogFile, PHP_EOL. date('c').' '.$sql);
			if (mysql_query($sql, $con) == FALSE)
			{
				$msg = "$tablename table was not created.";
				$this->issueMessage($msg);
				fwrite($installationLogFile, PHP_EOL. date('c').' '.$msg);
				return FALSE;
			}
			fwrite($installationLogFile, PHP_EOL . date('c'). " $tablename table was created Successfully.");	
		}
		
		$_SESSION['status']['table'] = "Tables were created Successfully.";

		foreach ($viewFile as $viewFilename => $sql)			// Create views
		{
			fwrite($installationLogFile, PHP_EOL. date('c').' '.$sql);
			if (mysql_query($sql, $con) == FALSE)
			{
				$msg = "$viewFilename view was not created.";
				$this->issueMessage($msg);
				fwrite($installationLogFile, PHP_EOL. date('c').' '.$msg);
				return FALSE;
			}
			fwrite($installationLogFile, PHP_EOL . date('c'). " $viewFilename view was created Successfully.");
		}
		
		$_SESSION['status']['viewFile'] = "Views were created Successfully.";

		foreach ($tableData as $tableName => $fieldnvalues)		// Insert records to the system tables.
		{
			$sql = "INSERT INTO $tableName ({$fieldnvalues['fields']}) VALUES {$fieldnvalues['values']}";
			fwrite($installationLogFile, PHP_EOL. date('c').' '.$sql);
			if (mysql_query($sql, $con) == FALSE)
			{
				$msg = "Data could not be inserted in $tableName.";
				$this->issueMessage($msg);
				fwrite($installationLogFile, PHP_EOL. date('c').' '.$msg);
				return FALSE;
			}
			fwrite($installationLogFile, PHP_EOL . date('c'). " Data was inserted Successfully in $tableName.");
		}
		
		$_SESSION['status']['data'] = "Initial Data was inserted Successfully.";
		
		return TRUE;
	}
	
	
	public function GrantDatabaseAuthority($connection, $db_server, $db_database, $db_admin_user, $db_admin_password, $db_user, $db_password, $installationLogFile)
	{		

		//Don't attempt to do it in hosted server
		if  ((strtolower($db_server) != 'localhost')
			and ($db_server != '127.0.0.1')) {
				fwrite($installationLogFile, PHP_EOL . date('c'). " Since this is not a local server, assigning authority was skipped.");
				return TRUE;
			}
		
		// Grant appropriate authorities to the database admin user
		$authority = "GRANT ALL
					ON $db_database.*
					TO '$db_admin_user'@'$db_server'
					IDENTIFIED BY '$db_admin_password'";
		
		fwrite($installationLogFile, PHP_EOL. date('c').' '.$authority);
		if (mysql_query($authority, $connection) == FALSE) {
			$msg = "Could not grant authority to $db_admin_user on $db_database database. <a href=\"TechnicalHelp.php#hosted-server-installation\">See help.</a>";
			$this->issueMessage($msg);
			fwrite($installationLogFile, PHP_EOL. date('c').' '.$msg);
			return FALSE;
		}
		
		fwrite($installationLogFile, PHP_EOL . date('c'). " $db_admin_user was given all accesses to $db_database.");
			
		if ($db_user == NULL) {
			fwrite($installationLogFile, PHP_EOL . date('c'). " Database user was not provided. Admin user \"$db_admin_user\" will is be used.");
		} else {
			// Grant appropriate authorities to the normal database user
			$authority = "GRANT SELECT, UPDATE, INSERT, DELETE
						ON $db_database.*
						TO '$db_user'@'$db_server'
						IDENTIFIED BY '$db_password'";
			
			fwrite($installationLogFile, PHP_EOL. date('c').' '.$authority);	
			if (mysql_query($authority, $connection) == FALSE) {	// Grant appropriate authorities to the database user
				$msg = "Could not grant authority to $db_user on $db_database database. <a href=\"TechnicalHelp.php#hosted-server-installation\">See help.</a>";
				$this->issueMessage($msg);
				fwrite($installationLogFile, PHP_EOL. date('c').' '.$msg);
				return FALSE;
			}
			
		fwrite($installationLogFile, PHP_EOL . date('c'). " $db_user was given working access to $db_database.");
		
		}
	
		$_SESSION['status']['access'] = "Accesses were granted Successfully.";
	
		return TRUE;

	}
	
	protected function writeLog($installationLogFileHandle, $msg) {
		
		$this->issueMessage($msg);
		fwrite($installationLogFileHandle, PHP_EOL. date('c').' '.$msg);
		fclose($installationLogFileHandle);
		return;		
	}
	
	
}



?>