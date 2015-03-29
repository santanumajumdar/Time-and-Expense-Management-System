<?php
 /***
 
/*********************************************************************************
 * TEMS is a Time and Expense Management program developed by XXXXXXXXX
 * Initechs, LLC. Copyright (C) 2009 - 2013 Initechs LLC.YYYYYYYY
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
 * The interactive project interfaces in modified source and object code versions
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
require_once("$basedir/projectusers/model/DbObj.php");

class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		$_GET['uid'] = (!isset($_GET['uid'])) ? "" : $_GET['uid'];

		$ProjectUserData = new ProjectUserData();
		$project_users_status_array = $ProjectUserData->getOptionArray('projects_users', 'status');
		$projectuser = $ProjectUserData->getProjectUser($_GET['uid']);
		
		$tabArray = array ('general'=>'General', 'journal'=>'Journal');
		
		$str = "";
		$str .= $this->setHeading("Browse Project User");
		$str .= $this->setPageTabsBegin($tabArray);
		
		$str .= "\n<div id='general'>";
		$str .= $this->textField("Project Code", "projects_id", $_GET['projects_id'], 25, 25)."<br/>";
		$str .= $this->optionFieldFromTable("User", "users_id", $projectuser->users_id, "Enable-On-Create", "users", "users_id", "fullname")."<br/>";
		$str .= $this->dateField("Effective From", "effective_date", $projectuser->effective_date, "Enable-On-Create Enable-On-Edit", "When the resouce is effective in this project and the corresponding rate. You may have different rates based on the effective date")."<br/>";
		$str .= $this->textField("Rate", "rate", $projectuser->rate, 25, 25, "Enable-On-Create Enable-On-Edit", "Default hourly rate that will be used to bill the client")."<br/>";
		$str .= $this->textField("Role", "role", $projectuser->role, 25, 25, "Enable-On-Create Enable-On-Edit", "Role of the user in this project; for documentation purpose only.")."<br/>";
		$str .= $this->radioField("Status", "status", $projectuser->status, "Enable-On-Create Enable-On-Edit", $project_users_status_array)."<br/>";
		
		$str .= "\n<div class='button'>
		<input id='Create-Button' type='button' value='$this->createButton' onclick=\"enableCreate('CreateProjectUser', 'Assign User to a Project')\">
		<input id='Edit-Button'   type='button' value='$this->editButton'   onclick=\"enableEdit('EditProjectUser', 'Edit User of the Project')\">
		<input id='Copy-Button'   type='button' value='$this->copyButton'   onclick=\"enableCopy('CreateProjectUser', 'Copy User to a Project')\">
		<input id='Delete-Button' type='button' value='$this->deleteButton' onclick=\"enableDelete('DeleteProjectUser')\">
		<span id='Save-Button'></span>
		<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>
		\n</div>";
		
		$str .= "\n</div>";
		
		$str .= "\n<div id='journal'>";
		$str .= $this->textField("Created By", "createby", $projectuser->createby, 25, 25)."<br/>";
		$str .= $this->textField("Created At", "createat", $projectuser->createat, 25, 25)."<br/>";
		$str .= $this->textField("Modified By", "changeby", $projectuser->changeby, 25, 25)."<br/>";
		$str .= $this->textField("Modified At", "changeat", $projectuser->changeat, 25, 25)."<br/>";
		$str .= "\n</div>";
		
		$str .= $this->endOfFormMessage();
		$str .= $this->setPageTabsEnd();			// End of Page Tab Div.;

		$str .= "<script type='text/javascript' language='JavaScript'>";
		if (empty($_GET['uid'])) {
			$str .= "enableCreate('CreateProjectUser', 'Assign User to a Project')";
		} else {
			$str .= "enableBrowse()";
		}
		
		$str .= "</script>";
		
		return $str;
	}	
}


?>