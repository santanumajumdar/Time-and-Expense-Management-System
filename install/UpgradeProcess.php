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

require_once("$basedir/install/UpgradeDatabase.php");

$logdir = "$basedir/log";
$logfile = "$logdir/upgrade.log";

$configFile = "$basedir/config/Database.ini";
$_POST['Old_Location'] = rtrim($_POST['Old_Location'], '/');
$_POST['Old_Location'] = rtrim($_POST['Old_Location'], '\\');
$oldConfigFile = $_POST['Old_Location']."/config/Database.ini";
$DBA_ID = $_POST['DBA'];
$DBA_Pwd = $_POST['DBA_Password'];


class UpgradeProcess
{
	
	protected function check_database_ini ($configFile)
	{
		$ini_array = parse_ini_file($configFile, TRUE);
		if (!isset($ini_array['database'])) return FALSE;

		if (!isset($ini_array['database']['db_server'])
			or !isset($ini_array['database']['db_user'])
			or !isset($ini_array['database']['db_password'])
			or !isset($ini_array['database']['db_database'])) 
				return FALSE;
	
		$db_server = $ini_array['database']['db_server'];
		$db_user = $ini_array['database']['db_user'];
		$db_password = $ini_array['database']['db_password'];
		$db_database = $ini_array['database']['db_database'];
		
		if (!($con = @mysql_connect($db_server, $db_user, $db_password))) return FALSE;
		if (!($db_selected = mysql_select_db($db_database, $con))) return FALSE;
	
		return TRUE;
	}
	

	protected function connect_database ($configFile, $user='', $pwd='')
	{
		$ini_array = parse_ini_file($configFile, TRUE);
	
		$db_server = $ini_array['database']['db_server'];
		$db_user = ($user=='') ? $ini_array['database']['db_user'] : $user;
		$db_password = ($pwd=='') ? $ini_array['database']['db_password'] : $pwd;
		$db_database = $ini_array['database']['db_database'];
		
		if (!($conn = new mysqli($db_server, $db_user, $db_password, $db_database))) return FALSE;
	
		return $conn;
	}
	
	

	protected function isValidPostData()
	{
		global $configFile, $oldConfigFile, $logdir, $logfile;
		
		$ret_val = TRUE;

		if (!chkMandatory('Old_Location')) $ret_val = FALSE;
		if (!chkMandatory('DBA')) $ret_val = FALSE;
		if (!chkMandatory('DBA_Password')) $ret_val = FALSE;

		if ($ret_val == FALSE) 
			return $ret_val;
		
		if (!file_exists($logdir)) {
			$_SESSION['message'] = "$logdir directory does not exist";
			return FALSE;
		}
		
		if (!file_exists($logfile)) {
			$loghandle = fopen($logfile, "w");
			if ($loghandle == NULL) {
				$_SESSION['message'] = "FAILED to create $logfile file";
				return FALSE;
			}
			fclose($loghandle);
		}
		
		if (!file_exists($configFile)) {
			$_SESSION['message'] = "$configFile does not exist.";
			return FALSE;
		}
		if (!$this->check_database_ini ($configFile)) {
			$_SESSION['message'] = "$configFile is corrupted.";
			return FALSE;
		}

		if (!file_exists($oldConfigFile)) {
			$_SESSION['message'] = "$oldConfigFile does not exist.";
			return FALSE;
		}
		if (!$this->check_database_ini ($oldConfigFile)) {
			$_SESSION['message'] = "$oldConfigFile is corrupted.";
			return FALSE;
		}

		return $ret_val;
	}

	protected function processIncomingFormData()
	{
		global $configFile, $oldConfigFile, $DBA_ID, $DBA_Pwd;
		
		try
		{
			
			$oldDBConn = $this->connect_database($oldConfigFile);
			$newDBConn = $this->connect_database($configFile, $DBA_ID, $DBA_Pwd);
			
			set_time_limit(600);
			$DB = new UpgradeDatabase();
			$DB->CopyDatabase($oldDBConn, $newDBConn);

			// Write the database.ini file in config folder.
				
			$_SESSION['status']['database'] = "Database was copied OK";
			unset($_SESSION['status']);
			return TRUE;
		}

		catch (iBLError $e)
		{
			convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta");
		}

	}


}


$goOnSuccess = "../index.php";
$goOnError = "upgrade.php";

$DB = new UpgradeProcess();
$DB->processRequest();


?>