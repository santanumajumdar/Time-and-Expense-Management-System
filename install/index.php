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
 * 
 ********************************************************************************/

$basedir = dirname(dirname(__FILE__));
require_once("$basedir/core/config/GlobalVariables.php");

$db_server	 = NULL;
$db_user	 = NULL;
$db_password = NULL;
$db_database = NULL;

// Initial settings

error_reporting( E_ALL );

// Setting of session

@session_start(); 
unset($_SESSION['ini']);

function field_td ($label, $field, $type, $size, $text=NULL) {
	
	$highlight_error = (isset($_SESSION['error'][$field])) ? " class='highlight-error-field' " : "";
	$error = (!empty($_SESSION['error'][$field])) ? "<div class='in-line-error-message'>{$_SESSION['error'][$field]}</div>" : "";
	$value = (!empty($_SESSION['postdata'][$field])) ? $_SESSION['postdata'][$field] : "";
		
	$str = "";	
	$str .= "\n<tr>";
	$str .="\n<td class='label_entry'> $label: </td>";
	$str .= "\n<td class='field_entry'> <input " . $highlight_error ." type='$type' name='$field' size='$size' value='$value'> ". $error;
	$str .= $text;
	$str .= "</td>";
	$str .= "\n</tr>";
	return $str;
}

function show_installation_form () {
	
	$phpversion = phpversion();

	$str = "";
	$str .= "<table class='form'><tr><td>";
	$str .= "<form name='install_Create_Form' method='post' action='InstallProcess.php' enctype='multipart/form-data' accept-charset='UTF-8'>";
	$str .= "<table>";
	$str .= field_td('Database Server', 'Server', 'text', 50);
	$str .= field_td('Database Name', 'DatabaseName', 'text', 50, '<br>The database must exist (particularly on a Hosted or Shared servers), and must be empty.
							<br>For a `localhost`, TEMS Installation process generally creates the database, and tables. You may not have create the database in advance.');
	$str .= field_td('DBA User Id', 'DB_Admin_id', 'text', 25, '<br>User must have DBA authority on the database specified above.');
	$str .= field_td('DBA Password', 'DB_Admin_Password', 'password', 25);
	$str .= field_td('Database User', 'DB_User_id', 'text', 25, '(Optional, but recommended)
						<br>User must exists and have SELECT, INSERT, DELETE, UPDATE authorities.');
	$str .= field_td('Database User Password', 'DB_User_Password', 'password', 25);
	$str .= "</table>";
	$str .= "<div>
			<input type='submit' value='Install'>
			<input type='button' value='Go Back' onclick='window.history.go(-1)'>
			<input type='button' value='See Help Page' onclick='window.open(\"TechnicalHelp.php\", \"_self\")'>
			<p>Your PHP version is $phpversion</p>
		</div>";
	$str .= "</form>";
	
	$str .= "<hr>
	<b><u>If you are installing for the first time, you may ignore the following information.</u></b>
	<p>If you are upgrading from an older version, you MUST install the newer version in a seperate location and in a seperate database/schema.</p>
	<p>After installing the new version, you migrate your existing data to the new database and test the application first, before using for your business.</p>
	<p>Upgrade process will not modify your existing database. It will copy the data from the old database to the new database and modify the them as required by this new version.</p>
	<p>We recommend that you keep your old system as it is, until you are satisfied with your new version to use it for your production.</p>
	<p>To upgrade the existing old database to the current version, click on <a href='upgrade.php'>Database upgrade</a></p>";
	$str .= "</td>";
	
	// Setting of progress on the right most side
	
	if (isset($_SESSION['status'])){
		$str .= "<td class='side-status'>
				<table class='installation-status-table'>";
				foreach ($_SESSION['status'] as $status)
					$str .= "<tr><td>$status</td></tr>";
		$str .= "</table>
				</td>";
	}
	
	$str .= "</tr></table>";
	
	echo $str;
	
	if (!empty($_SESSION['message'])) {
		echo ("<div class='end-of-the-form-error-message'>{$_SESSION['message']}</div>");
	}
}


function check_database_ini_health() {
	
	global $DBconfigFile, $db_server, $db_user, $db_password, $db_database;

	if (isset($message)) unset ($message);
	$ini_array = parse_ini_file($DBconfigFile, TRUE);
	
	if (!isset($ini_array['database'])) {
		$message[] = "<p><b>$DBconfigFile</b> file exists, but it is corrupted, no [database] section was found.</p>";
		return $message;
	}
	
	if (!isset($ini_array['database']['db_server'])
			or !isset($ini_array['database']['db_user'])
			or !isset($ini_array['database']['db_password'])
			or !isset($ini_array['database']['db_database'])) {
		$message[] = "<p><b>$DBconfigFile</b> file exists, but it is corrupted, four components are not found or not properly defined.</p>";
		return $message;
	}
	
	$db_server = $ini_array['database']['db_server'];
	$db_user = $ini_array['database']['db_user'];
	$db_password = $ini_array['database']['db_password'];
	$db_database = $ini_array['database']['db_database'];
	
	if (($con = @mysql_connect($db_server, $db_user, $db_password)) == FALSE)	{			// Connect to database server name and check credentials
		$message[] = "<p>Server <b>$db_server</b>, User <b>$db_user</b>, and Password <b>(Non Printed Due to Security Reason)</b> combinations are incorrect.</p>";
		return $message;
	}
	
	if (($db_selected = mysql_select_db($db_database, $con)) == FALSE) {				// Check if database exists
		$message[] = "<p>Database <b>$db_database</b> does not exist.</p>";
		return $message;
	}
	
	return TRUE;
}



function check_database_ini () {
	
	global $DBconfigFile, $db_server, $db_user, $db_password, $db_database;

	if (!file_exists($DBconfigFile)) 
		return TRUE;
	
	if (($healthReport = check_database_ini_health()) === TRUE)
	 	return TRUE;

	$message[] = "<div class='end-of-form-notes'>";
	$message[] = "<p class='config-error-in-box'>\"<b>Database.ini</b>\" file exists in <b>".dirname($DBconfigFile)."</b> directory. TEMS cannot be installed.</p>";
	$message[] = "<p class='futher-error-analysis'>Error found in Database.ini file:</p>";
	$message   = array_merge($message, $healthReport);
	$message[] = "<p><b>Solution:</b> Delete $DBconfigFile and reinstall TEMS, or manually fix the Database.ini file.</p>";
	$message[] = "<p>For technical detail, follow <a href='http://temsonline.com/content/how-can-i-configure-databaseini-file-manually'>How to re-configure Database.ini manually</a>.</p>";
	$message[] = "<p>To upgrade TEMS from older version, contact <a href=\"mailto:contact@temsonlne.com\">contact@temsonline.com</a></p>";
	$message[] = "</div>";
	$message[] = "<div class='button'>";
	$message[] = "<input type='button' value='Go Back' onclick='window.history.go(-1)'>";
	$message[] = "<input type='button' value='See Help Page' onclick='window.open(\"TechnicalHelp.php\", \"_self\")'>";
	$message[] = "</div>";
	
	return $message;
}


?>



<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">

<link rel="shortcut icon" type="image/x-icon" href="../images/favicon.ico">
<link rel="stylesheet" type="text/css" media="all" href="../core/css/temsbase.css">
<link rel="stylesheet" type="text/css" media="all" href="../core/css/temsinstall.css">

<title>Time and Expense Management System</title>

</head>

<body>

<div class="base-layer">

	<div id="Page-Header">
		<img class="logo" src='../images/Initechs_Logo.png' alt="TEMS Logo">
		<span class="app-name">Time and Expense Management System</span>
		<span class='app-logo'><a href='http://www.temsonline.com'><img class='logo' src='../images/TEMS_Logo.png' alt='TEMS Logo'/></a></span>

		<div class="section-heading-layer">Install Database</div>

		<?php
		
		if (ini_get('SAFE_MODE')) {
			echo ("<p>Disable safe_mode in php.ini before proceeding</p>"); 
		}
		else if (($message = check_database_ini()) !== TRUE) {
			foreach ($message as $msg) echo ($msg);
		} else {
			show_installation_form();
		}

		?>
		
		<div class='Page-Footer'>
		<p>For support, create a ticket at <a href="http://sourceforge.net/tracker/?group_id=343652&amp;atid=1438060" target="_blank">TEMS Support</a>
		<span class='On-Right'><?php echo $GLOBAL_copyRightText ?></span></p>
		</div>


	</div>

</div>

</body>

</html>