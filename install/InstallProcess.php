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
require_once("$basedir/install/InstallDatabase.php");

session_start();
$goOnSuccess = "../index.php";
$goOnError = "index.php";

unset($_SESSION['status']);
unset($_SESSION['message']);
unset($_SESSION['error']);
unset($_SESSION['program_error']);


class InstallProcess extends InstallDatabase
{

	public function processRequest() {
		
		global $goOnSuccess;
		global $goOnError;
	
		$_SESSION['postdata'] = $_POST;
		if (!$this->isDataValid()) {
			$this->redirectToPage($goOnError);
			return;
		}
	
		if (!$this->processFormData()){
			$this->redirectToPage($goOnError);
			return;
		}
	
		$this->redirectToPage($goOnSuccess);
		unset($_SESSION['postdata']);
		return;
	}
	

	protected function isDataValid() {
		
		global $DBconfigFile, $installationLogFile;
		
		foreach (Array("Server", "DatabaseName", "DB_Admin_id", "DB_Admin_Password") as $field)
			if (empty($_POST[$field])) 
				$this->issueMessage('Cannot be blank', $field);

		if (!empty($_POST['DB_User_id']) 
			and empty($_POST['DB_User_Password'])) 
				$this->issueMessage('Cannot be blank', 'DB_User_Password');
				
		if (isset($_SESSION['error'])) 
			return FALSE;
		
		if (file_exists($DBconfigFile)) {
			$this->issueMessage("Database configuration file already exists; you cannot install TEMS.");
			return FALSE;
		}
		
		if (!file_exists(dirname($DBconfigFile))) {
			$this->issueMessage(dirname($DBconfigFile) ." directory (to store Database.ini) does not exist");
			return FALSE;
		}
		
		if (($confighandle = fopen($DBconfigFile, "w")) == NULL) {
			$this->issueMessage("FAILED to create/write $DBconfigFile file. Check permission of config directory, see help for more information.");
			return FALSE;
		}
		fclose($confighandle);
		unlink($DBconfigFile);

		if (!file_exists(dirname($installationLogFile))) {
			$this->issueMessage(dirname($installationLogFile) . " directory (to store the logs) does not exist");
			return FALSE;
		}
		
		if (!file_exists($installationLogFile)) {
			if (($loghandle = fopen($installationLogFile, "w")) == NULL) {
				$this->issueMessage("FAILED to create $installationLogFile file. Check permission of ". dirname($installationLogFile) ." directory, see help for more information.");
				return FALSE;
			}
			fclose($loghandle);
		}
		
		// Connect to database server name and check Admin credentials
		if (($con = mysql_connect(trim($_POST['Server']), trim($_POST['DB_Admin_id']), trim($_POST['DB_Admin_Password']))) === FALSE) {
			$this->issueMessage("Failed to connect to database server. Recheck the server name, Admin user id and password", "DatabaseName");
			return FALSE;
		}
		
		$_SESSION['status']['server'] = "Database server access Successfully.";

		return TRUE;
	}

	
	protected function processFormData() {
		
		global $DBconfigFile, $installationLogFile;

		$db_server = trim($_POST['Server']);
		$db_database = trim($_POST['DatabaseName']);
		$db_admin_user = trim($_POST['DB_Admin_id']);
		$db_admin_password = trim($_POST['DB_Admin_Password']);
		$db_user = trim($_POST['DB_User_id']);
		$db_password = trim($_POST['DB_User_Password']);

		if ($this->CreateNewDatabase($db_server, $db_database, $db_admin_user, $db_admin_password, $db_user, $db_password) == FALSE)
			return FALSE;
				
		// Write the database.ini file in config folder.
				
		if (($fhandle = fopen($DBconfigFile, "w")) == NULL) {

			if (($logfilehandle = fopen($installationLogFile, "a+")) == NULL) {
				$this->issueMessage("Could not open file $installationLogFile for writing installation log.");
				return FALSE;
			}
			fwrite($logfilehandle, PHP_EOL . date('c'). "Database installed but $DBconfigFile file could not be created.");
			fclose($logfilehandle);
			$_SESSION['status']['database_ini'] = "Database was installed Successfully,<br>but failed to create Database.ini file.
					<br>Follow the direction as described at<br><a href='http://temsonline.com/content/how-can-i-configure-databaseini-file-manually'>How to configure Database.ini manually.";
			return FALSE;
		} else {
			fwrite($fhandle, "[database]".PHP_EOL);
			fwrite($fhandle, "db_server = $db_server".PHP_EOL);
			fwrite($fhandle, "db_database = $db_database".PHP_EOL);
			fwrite($fhandle, "db_user = " . ($db_user == ''? $db_admin_user: $db_user).PHP_EOL);
			fwrite($fhandle, "db_password = " . ($db_password == ''? $db_admin_password: $db_password).PHP_EOL);
			fclose($fhandle);
			$_SESSION['status']['database_ini'] = "Database.ini file was created Successfully.";
		}
		unset($_SESSION['status']);
		unset($_SESSION['message']);
		unset($_SESSION['error']);
		unset($_SESSION['program_error']);
		return TRUE;
	}
	

	protected function redirectToPage($in_url, $in_contentType = 'text/html') {
	
		$in_url = trim($in_url);
		if ($in_url <> '') {
			header("Location: $in_url");
			header("Content-Type: $in_contentType");
		}
	}
	
	
	protected function issueMessage ($msg, $field='') {
	
		empty($field) ? $_SESSION['message'] = $msg : $_SESSION['error'][$field] = $msg;
	}

}




$InstallProcess = new InstallProcess();
$InstallProcess->processRequest();


?>