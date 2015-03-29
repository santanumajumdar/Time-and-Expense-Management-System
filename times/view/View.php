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
require_once("$basedir/times/model/DbObj.php");

class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		$_GET['uid'] = (!isset($_GET['uid'])) ? "" : $_GET['uid'];
		
		$TimeData = new TimeData();
		$times_status_array = $TimeData->getOptionArray('times', 'status');
		$TimeData = $TimeData->getTime($_GET['uid']);
		
		$tabArray = array ('general'=>'General', 'journal'=>'Journal');
		
		$str = "";
		$str .= $this->setHeading("Browse Time");
		$str .= $this->setPageTabsBegin($tabArray);
		
		$str .= "\n<div id='general'>";
		$str .= $this->optionFieldFromTable("User", "users_id", $TimeData->users_id, "Enable-On-Create Enable-On-Edit", "users", "users_id", "fullname")."<br/>";
		$str .= $this->dateField("Work Date", "workdate", $TimeData->workdate, "Enable-On-Create Enable-On-Edit")."<br/>";
		$query = "SELECT projects.projects_id as value, projects.name as text FROM projects INNER JOIN projects_users ON projects_users.projects_id = projects.projects_id";
		$str .= $this->cascadeOptionFieldFromTable("Project", "projects_id", $TimeData->projects_id, "Enable-On-Create Enable-On-Edit", "projects", "users_id", "users_id", "projects_users.users_id", $query, "projects.projects_id")."<br/>";

// Making projects_users_tasks optional as long as task is valid from the master task list.  -- Kallol.
//		$query = "SELECT projects_users_tasks.tasks_id as value, tasks.name as text FROM projects_users_tasks INNER JOIN tasks ON projects_users_tasks.tasks_id = tasks.tasks_id";
//		$str .= $this->cascadeOptionFieldFromTable("Task", "tasks_id", $TimeData->tasks_id, "Enable-On-Create Enable-On-Edit", "projects_id", "users_id projects_id", $query)."<br/>";
		$str .= $this->optionFieldFromTable("Task", "tasks_id", $TimeData->tasks_id, "Enable-On-Create Enable-On-Edit", "tasks", "tasks_id", "name")."<br/>";
		
		$str .= $this->textField("Description", "description", $TimeData->description, 100, 100, "Enable-On-Create Enable-On-Edit", "This will be printed in your timesheet and invoices that your client will see.")."<br/>";
		$str .= $this->textField("Comments", "comments", $TimeData->comments, 100, 100, "Enable-On-Create Enable-On-Edit", "This will never go to your client, this comment is for your internal reference.")."<br/>";
		$str .= $this->textField("Billable Hours", "billablehours", $TimeData->billablehours, 5, 5, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("Non-Billable Hours", "nonbillablehours", $TimeData->nonbillablehours, 5, 5, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("Work Location", "location", $TimeData->location, 25, 25, "Enable-On-Create Enable-On-Edit", "State or place of the work; sometimes it is used for tax purpose.")."<br/>";
		$str .= $this->radioField("Status", "status", $TimeData->status, "Hide-On-Create Hide-On-Edit", $times_status_array)."<br/>";

		$str .= "\n<div class='button'>
		<input id='Create-Button' type='button' value='$this->createButton' onclick=\"enableCreate('CreateTimeCard', 'Create Time Card')\">";
		
		if($TimeData->status<'30')
			$str .= "<input id='Edit-Button'   type='button' value='$this->editButton'   onclick=\"enableEdit('EditTimeCard', 'Edit Time Card')\">";
			
		$str .= "<input id='Copy-Button'   type='button' value='$this->copyButton'   onclick=\"enableCopy('CreateTimeCard', 'Copy Time Card')\">";
			
		if($TimeData->status<'30')
			$str .= "<input id='Delete-Button' type='button' value='$this->deleteButton' onclick=\"enableDelete('DeleteTimeCard')\">";
		
		if($TimeData->status=='10')
			$str .= "<input id='Submit-Button' type='button' value='$this->submitButton' onclick=\"enableAction('SubmitTimeCard')\">";
		
		if($TimeData->status=='20')
		$str .= "<input id='Approve-Button' type='button' value='$this->approveButton' onclick=\"enableAction('ApproveTimeCard')\">
		<input id='Hold-Button' type='button' value='$this->holdButton' onclick=\"enableAction('HoldTimeCard')\">";
			
		if($TimeData->status=='80')
			$str .= "<input id='Release-Button' type='button' value='$this->releaseButton' onclick=\"enableAction('ReleaseTimeCard')\">";
				
			$str .= "<span id='Save-Button'></span>
			<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>
			\n</div>";
			
		$str .= "\n</div>";
		
		$str .= "\n<div id='journal'>";
		$str .= $this->textField("Weekend", "weekenddate", $TimeData->weekenddate, 25, 25)."<br/>";
		$str .= $this->textField("Submitted On", "submitdate", $TimeData->submitdate, 25, 25)."<br/>";
		$str .= $this->textField("Approved On", "approvedate", $TimeData->approvedate, 25, 25)."<br/>";
		$str .= $this->textField("Invoice", "invoices_id", $TimeData->invoices_id, 25, 25)."<br/>";
		$str .= $this->textField("Created By", "createby", $TimeData->createby, 25, 25)."<br/>";
		$str .= $this->textField("Created At", "createat", $TimeData->createat, 25, 25)."<br/>";
		$str .= $this->textField("Modified By", "changeby", $TimeData->changeby, 25, 25)."<br/>";
		$str .= $this->textField("Modified At", "changeat", $TimeData->changeat, 25, 25)."<br/>";
		$str .= "\n</div>";
		
		$str .= $this->endOfFormMessage();
		$str .= $this->setPageTabsEnd();			// End of Page Tab Div.;

		$str .= "<script type='text/javascript' language='JavaScript'>";
		if (empty($_GET['uid'])) {
			$str .= "enableCreate('CreateTimeCard', 'Create Time Card')";
		} else {
			$str .= "enableBrowse()";
		}
		
		$str .= "</script>";
		
		return $str;
	}
	
}


?>