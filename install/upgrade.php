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

// Initial settings

date_default_timezone_set('America/New_York');
error_reporting( E_ALL );

// Setting of session

if (session_id() == "") {
	session_start(); 
	$_SESSION['name'] = "TEMS_Upgrade"; 
}
if (!isset($_SESSION['initiated'])) 
{ 
    session_regenerate_id(); 
    $_SESSION['initiated'] = TRUE; 
}

// Setting yellow background of the input field to indicate error.

function error_indicator ($field) {

	$error_field = " ";

	if ((isset($_SESSION['error']['highlight'][$field])
		and ($_SESSION['error']['highlight'][$field] == TRUE))
		or (isset($_SESSION['error'][$field])))
			$error_field = " class='highlight-error-field' ";

	return $error_field;
	
}

// Showing error below the input field.

function show_field_level_error ($field) {

	$error = "";

	if (isset($_SESSION['error'][$field])
		and trim($_SESSION['error'][$field]) != "")
		$error = "<div class='in-line-error-message'>{$_SESSION['error'][$field]}</div>";

	return $error;
	
}

// Showing the input field value.

function field_entry ($field, $value=NULL) {

	$data = "";

	if (isset($_SESSION['postdata'][$field]))
		$data = $_SESSION['postdata'][$field];
	else if ($value != NULL)
		$data = $value;
		
	return "'".$data."'";
	
}

function show_upgrade_form ()
{
	$basedir = dirname(dirname(__FILE__));
	
	$str =
	"<table id='form'>
	<tr>
	<td>
	<form name='upgrade_Form' method='post' action='UpgradeProcess.php' accept-charset='UTF-8'>
		<table>
			<tr>
				<td class='label_entry' colspan='2'>Location of Current Version: $basedir </td>
			</tr>
			<tr>
				<td class='label_entry' colspan='2'>Provide the user id and password that has a DBA level of authority on the new database.</td>
			</tr>
			<tr>
				<td class='label_entry'>DBA User ID:</td>
				<td class='field_entry'> <input". error_indicator('DBA') ."type='text' name='DBA' size='50'>". show_field_level_error ('DBA') ."</td>
			</tr>
			<tr>
				<td class='label_entry'>DBA Password:</td>
				<td class='field_entry'> <input". error_indicator('DBA_Password') ."type='password' name='DBA_Password' size='50'>". show_field_level_error ('DBA_Password') ."</td>
			<tr>
			<tr>
				<td class='label_entry'>Location of Older Version:</td>
				<td class='field_entry'> <input". error_indicator('Old_Location') ."type='text' name='Old_Location' size='100'>". show_field_level_error ('Old_Location') ."</td>
			</tr>
		</table>
		<div class='button'>
			<input type='submit' value='Upgrade'>
			<input type='button' value='Go Back' onclick='window.history.go(-1)'>
			<input type='button' value='See Help Page' onclick='window.open(\"TechnicalHelp.php\", \"_self\")'>
		</div>
	</form>
	</td>";
	
	// Setting of progress on the right most side
	
	if (isset($_SESSION['status'])){
		$str .= "<td class='side-status'>
				<table class='installation-status-table'>";
				foreach ($_SESSION['status'] as $status)
					$str .= "<tr><td>$status</td></tr>";
		$str .= "</table></td>";
	}
	
	$str .= "</tr></table>";
	
	echo $str;
	
	if (isset($_SESSION['message'])
		and trim($_SESSION['message']) != "") {
		echo ("<div class='end-of-the-form-error-message'>{$_SESSION['message']}</div>");
	}
	
	if (isset($_SESSION['error']['nocategory'])) {
		echo ($_SESSION['error']['nocategory']);
		unset ($_SESSION['error']);
	}

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
		<img class="logo" src='../images/logo.gif' alt="TEMS Logo">
		<span class="app-name">Time and Expense Management System</span>

		<div class="section-heading-layer">Upgrade Database (For Version 2.x.x Only; Not for version 3.x.x)</div>

		<?php
		
		$basedir = dirname(dirname(__FILE__));
		
		$configFile = "$basedir/config/Database.ini";
		
		if (ini_get('SAFE_MODE')) 
		{
			echo ("<p>Disable safe_mode in php.ini before proceeding</p>"); 
		}
		else 
		{
			show_upgrade_form();
		}
		
		?>
		
		<div class='Page-Footer'>
			<p>For support, create a ticket at <a href="http://sourceforge.net/tracker/?group_id=343652&amp;atid=1438060" target="_blank">TEMS Support</a></p>
			<p>If you prefer to contact us by email, our email address is <a href="mailto:contact@temsonlne.com">contact@temsonline.com</a></p>
			<p>Copyright 2009-2012 Initechs, LLC.</p>
		</div>
	</div>

</div>
<?php 
if (isset($_SESSION['program_error'])) {
	echo ($_SESSION['program_error']);
	unset ($_SESSION['program_error']);
}
?>

</body>

</html>