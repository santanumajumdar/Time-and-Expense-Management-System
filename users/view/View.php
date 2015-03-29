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
require_once("$basedir/core/view/PageElement.class.php");
require_once("$basedir/users/model/DbObj.php");

class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		$_GET['uid'] = (!isset($_GET['uid'])) ? "" : $_GET['uid'];
		
		$UserData = new UserData();
		$users_status_array = $UserData->getOptionArray('users', 'status');
		$users_language_array = $UserData->getOptionArray('users', 'language');
		$users_dateformat_array = $UserData->getOptionArray('users', 'dateformat');
		$users_debug_level_array = $UserData->getOptionArray('users', 'debuglevel');
		$users_dbtrace_level_array = $UserData->getOptionArray('users', 'dbtracelevel');
		$users_generic_yesno_array = $UserData->getOptionArray('generic', 'yes_no');
		
		$user = $UserData->getuser($_GET['uid']);

		$tabArray = array ('general'=>'General', 'journal'=>'Journal', 'technical'=>'Technical');
		
		$str = "";
		$str .= $this->setHeading("Browse User");
		$str .= $this->setPageTabsBegin($tabArray);
		
		$str .= "\n<div id='general'>";
		$str .= $this->textField("User", "users_id", $user->users_id, 25, 25, "Enable-On-Create Show-On-ChangePassword", "User ID to login and identify the user")."<br/>";
		$str .= $this->passwordField("Password", "password1", 25, 25, "Enable-On-Create Hide-On-Browse Enable-On-ChangePassword", "Password to login")."<br/>";
		$str .= $this->passwordField("Password", "password2", 25, 25, "Enable-On-Create Hide-On-Browse Enable-On-ChangePassword", "Confirm password, must be the same as what you entered above")."<br/>";
		$str .= $this->textField("Full Name", "fullname", $user->fullname, 50, 50, "Enable-On-Create Enable-On-Edit", "Full name of the user; this will be printed on the reports")."<br/>";
		$str .= $this->textField("Email", "email", $user->email, 50, 50, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("Title", "title", $user->title, 50, 50, "Enable-On-Create Enable-On-Edit", "Job title for documentation purpose only")."<br/>";
		$str .= $this->dateField("Join Date", "joindate", $user->joindate, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("Report To", "reportto", $user->reportto, 50, 50, "Enable-On-Create Enable-On-Edit", "Name of the manager, who will approve the time cards, expenses, and charges before it goes to the final invoices and clients", "users", "users_id")."<br/>";
		$str .= $this->textField("User Group", "usergroup", $user->usergroup, 50, 50, "Enable-On-Create Enable-On-Edit", "Group of the user; if group authority is given, all members of the group can see and maintain this users time cards, expenses, and charges", "users", "usergroup")."<br/>";
		if (loggedUserID() == 'admin') {
			$menuQuery = "select m.u_menu_id, p.label
						from menu m
						inner join programtransactions p
							on m.programtransactions_id = p.programtransactions_id
						where m.parent_menu_id is NULL
						order by p.label";
			$str .= $this->optionFieldByQuery("Menu", "u_menu_id", $user->u_menu_id, "Enable-On-Create Enable-On-Edit", $menuQuery, "u_menu_id", "label")."<br/>";
			$str .= $this->optionFieldFromTable("Authorization", "authorizations_id", $user->authorizations_id, "Enable-On-Create Enable-On-Edit", "authorizationlists", "authorizations_id", "description")."<br/>";
		}
		$str .= $this->optionField("Language", "language", $user->language, "Enable-On-Create Enable-On-Edit", $users_language_array)."<br/>";
		$str .= $this->radioField("Date Format", "dateformat", $user->dateformat, "Enable-On-Create Enable-On-Edit", $users_dateformat_array)."<br/>";
		$str .= $this->radioField("Status", "status", $user->status, "Enable-On-Create Enable-On-Edit", $users_status_array)."<br/>";
		
		$str .= "\n<div class='button'>
		<input id='Create-Button' type='button' value='$this->createButton' onclick=\"enableCreate('CreateUser', 'Create User')\">
		<input id='Edit-Button'   type='button' value='$this->editButton'   onclick=\"enableEdit('EditUser', 'Edit User')\">
		<input id='Copy-Button'   type='button' value='$this->copyButton'   onclick=\"enableCopy('CreateUser', 'Copy User')\">
		<input id='Delete-Button' type='button' value='$this->deleteButton' onclick=\"enableDelete('DeleteUser')\">
		<input id='ChangePassword-Button' type='button' value='$this->changePasswordButton' onclick=\"enableChangePassword('ChangeUserPassword', 'Change Password')\">
		<span id='Save-Button'></span>
		<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>
		\n</div>";
		
		$str .= "\n</div>";
		
		$str .= "\n<div id='journal'>";
		$str .= $this->textField("Last Login", "lastloginat", $user->lastloginat, 25, 25)."<br/>";
		$str .= $this->textField("Access Count", "access_count", $user->access_count, 25, 25)."<br/>";
		$str .= $this->textField("Created By", "createby", $user->createby, 25, 25)."<br/>";
		$str .= $this->textField("Created At", "createat", $user->createat, 25, 25)."<br/>";
		$str .= $this->textField("Modified By", "changeby", $user->changeby, 25, 25)."<br/>";
		$str .= $this->textField("Modified At", "changeat", $user->changeat, 25, 25)."<br/>";
		$str .= "\n</div>";
		
		$str .= "\n<div id='technical'>";
		$str .= $this->optionField("Debug Level", "debuglevel", $user->debuglevel, "Enable-On-Create Enable-On-Edit", $users_debug_level_array)."<br/>";
		$str .= $this->optionField("Database Trace Level", "dbtracelevel", $user->dbtracelevel, "Enable-On-Create Enable-On-Edit", $users_dbtrace_level_array)."<br/>";
		$str .= $this->optionField("Receipt Auto Preview", "preview_receipt", $user->preview_receipt, "Enable-On-Create Enable-On-Edit", $users_generic_yesno_array)."<br/>";
		$str .= "\n</div>";
		
		$str .= $this->endOfFormMessage();
		$str .= $this->setPageTabsEnd();				// End of Page Tab Div.;

		$str .= "<script type='text/javascript' language='JavaScript'>";
		if (empty($_GET['uid'])) {
			$str .= "enableCreate('CreateUser', 'Create User')";
		} else {
			$str .= "enableBrowse()";
		}
		
		$str .= "</script>";
		
		return $str;
	}
	
}


?>