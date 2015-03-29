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
require_once("$basedir/projects/model/DbObj.php");
require_once("$basedir/core/controller/ActionRegistry.php");

class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		$_GET['uid'] = (!isset($_GET['uid'])) ? "" : $_GET['uid'];

		$ProjectData = new ProjectData();
		$projects_status_array = $ProjectData->getOptionArray('projects', 'status');
		$projects_billcycle_array = $ProjectData->getOptionArray('projects', 'billcycle');
		$project = $ProjectData->getProject($_GET['uid']);
		
		$projectUsersTasksList = $this->project_users_tasks_List($project->projects_id, 'tasks');
		$projectUsersList = $this->project_users_List($project->projects_id, 'users');		
		
		$tabArray = array ('general'=>'General', 'billto'=>'Billing Info', 'journal'=>'Journal', 'users'=>'Users', 'tasks'=>'Tasks');
		
		$str = "";
		$str .= $this->setHeading("Browse Project");
		$str .= $this->setPageTabsBegin($tabArray);
		
		$str .= "\n<div id='general'>";
		$str .= $this->textField("Project Code", "projects_id", $project->projects_id, 25, 25, "Enable-On-Create Show-On-Print", "Project code that will be used to identify this project")."<br/>";
		if (!empty($_GET['accounts_id']))
			$str .= $this->textField("Client Code", "accounts_id", $_GET['accounts_id'], 25, 25, "Show-On-Create Enable-On-Print")."<br/>";
		else
			$str .= $this->optionFieldFromTable("Client Code", "accounts_id", $project->accounts_id, "Enable-On-Create Enable-On-Edit Hide-On-Print", "accounts", "accounts_id", "name")."<br/>";
		$str .= $this->optionFieldFromTable("Bill To Account", "billtoaccounts_id", $project->billtoaccounts_id, "Enable-On-Create Enable-On-Edit Hide-On-Print", "accounts", "accounts_id", "name")."<br/>";
		$str .= $this->textField("Project Name", "name", $project->name, 50, 50, "Enable-On-Create Enable-On-Edit", "Descriptive name of the project")."<br/>";
		$str .= $this->textField("Description", "description", $project->description, 100, 100, "Enable-On-Create Enable-On-Edit Hide-On-Print", "Detail desctiption of the project, for document purpose.")."<br/>";
		$str .= $this->dateField("Weekend Date", "weekenddate", "", "Hide-On-All Enable-On-Print Enable-On-Print")."<br/>";
		$str .= $this->showButtons();
		$str .= "\n</div>";
		
		$str .= "\n<div id='billto'>";
		$str .= $this->textField("Address line 1", "billtoaddress1", $project->billtoaddress1, 20, 20, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("Address line 2", "billtoaddress2", $project->billtoaddress2, 20, 20, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("City", "billtocity", $project->billtocity, 20, 20, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("State", "billtostate", $project->billtostate, 20, 20, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("Postal Code", "billtopostalcode", $project->billtopostalcode, 50, 50, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("Country", "billtocountry", $project->billtocountry, 50, 50, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("Contact Name", "billtocontact", $project->billtocontact, 50, 50, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("Contact E-mail", "billtoemail", $project->billtoemail, 50, 50, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->optionField("Bill Cycle", "billcycle", $project->billcycle, "Enable-On-Create Enable-On-Edit", $projects_billcycle_array)."<br/>";
		$str .= $this->dateField("Bill Start Date", "billstartdate", $project->lastbilldate, "Enable-On-Create Hide-On-All")."<br/>";
		$str .= $this->dateField("Last Bill Date", "lastbilldate", $project->lastbilldate, "Hide-On-Create")."<br/>";
		$str .= $this->radioField("Status", "status", $project->status, "Enable-On-Create Enable-On-Edit", $projects_status_array)."<br/>";
		$str .= "\n</div>";
		
		$str .= "\n<div id='journal'>";
		$str .= $this->textField("Created By", "createby", $project->createby, 25, 25)."<br/>";
		$str .= $this->textField("Created At", "createat", $project->createat, 25, 25)."<br/>";
		$str .= $this->textField("Modified By", "changeby", $project->changeby, 25, 25)."<br/>";
		$str .= $this->textField("Modified At", "changeat", $project->changeat, 25, 25)."<br/>";
		$str .= "\n</div>";
		
		$str .= "\n<div id='users'>";
		$str .= $projectUsersList;
		$str .= "\n</div>";
		
		$str .= "\n<div id='tasks'>";
		$str .= $projectUsersTasksList;
		$str .= "\n</div>";
		
		$str .= $this->endOfFormMessage();
		$str .= $this->setPageTabsEnd();			// End of Page Tab Div.;
		
		$str .= "<script type='text/javascript' language='JavaScript'>";
		if (empty($_GET['uid'])) {
			$str .= "enableCreate('CreateProject', 'Create Project')";
		} else {
			$str .= "enableBrowse()";
		}
		
		$str .= "</script>";
		
		return $str;
	}
	
	
	private function showButtons() {
		
		$str = "";
		
		$str .= "\n<div class='button'>
		<input id='Create-Button' type='button' value='$this->createButton' onclick=\"enableCreate('CreateProject', 'Create Project')\">
		<input id='Edit-Button'   type='button' value='$this->editButton'   onclick=\"enableEdit('EditProject', 'Edit Project')\">
		<input id='Copy-Button'   type='button' value='$this->copyButton'   onclick=\"enableCopy('CreateProject', 'Copy Project')\">
		<input id='Delete-Button' type='button' value='$this->deleteButton' onclick=\"enableDelete('DeleteProject')\">
		<input id='Print-Button'  type='button' value='$this->printButton'  onclick=\"enablePrint('PrintProjectWeeklyTimeSheet', 'Print Weekly Time Sheet')\">
		<span id='Save-Button'></span>
		<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>
		\n</div>";
		
		return $str;
	}
	
	
	protected function project_users_List($projectID, $tab) {
		
		$ActionRegistry = new ActionRegistry("ListProjectUser");
		$modelFile = $ActionRegistry->getModelFile();
	
		$columnsArray = array(
				"users_id" => "User",
				"effective_date" => "Effective From",
				"rate" => "Rate",
				"status" => "Status");
		
		$statusArray = array(	"table" => "projects_users",
								"column" => "status");
		
		$columnsJSON = json_encode($columnsArray);
		$statusJSON = json_encode($statusArray);
	
		$where = "projects_id=\"$projectID\"";
		$parm = "&projects_id=$projectID";
		
		$str = "";
		$str .= $this->setHeading("Project :: $projectID");
		$str .= $this->setDatatableLayout('list-table-pu', $columnsArray);
		
		$str .= $this->onClickFillupList($tab,'list-table-pu', $columnsJSON, $modelFile, 'projects_users', 'BrowseProjectUser', $where, $statusJSON);
		$str .= "<input id='Create-Button' class='Add-Button-Footer' type='button' value='$this->addUserToProjectButton' onclick=\"actionURL('action=CreateProjectUser$parm')\">";

		return $str;
	}
	
	
	protected function project_users_tasks_List($projectID, $tab) {
		
		$ActionRegistry = new ActionRegistry("ListProjectUserTask");
		$modelFile = $ActionRegistry->getModelFile();
		
		$columnsArray = array(
				"users_id" => "User",
				"tasks_id" => "Task",
				"effective_date" => "Effective From",
				"rate" => "Rate",
				"status" => "Status");
		
		$statusArray = array(	"table" => "projects_users_tasks",
								"column" => "status");
		
		$columnsJSON = json_encode($columnsArray);
		$statusJSON = json_encode($statusArray);
	
		$where = "projects_id=\"$projectID\"";
		$parm = "&projects_id=$projectID";
	
		$str = "";
		$str .= $this->setHeading("Project :: $projectID");
		$str .= $this->setDatatableLayout('list-table-put', $columnsArray);
		
		$str .= $this->onClickFillupList($tab,'list-table-put', $columnsJSON, $modelFile, 'projects_users_tasks', 'BrowseProjectUserTask', $where, $statusJSON);
		$str .= "<input id='Create-Button' class='Add-Button-Footer' type='button' value='$this->addTaskToProjectButton' onclick=\"actionURL('action=CreateProjectUserTask$parm')\">";
		
		return $str;
	}
	
}


?>