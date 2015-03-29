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
require_once("$basedir/core/controller/ActionRegistry.php");

class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		$users_id = $_GET['users_id'];
		$weekenddate = $_GET['weekenddate'];
		
		$TimeData = new TimeData();
		$times = $TimeData->getWeeklyTimeSummary($users_id, $weekenddate);
		
		$timeListView = $this->getTimeCards($users_id, $weekenddate, 'times');

		$tabArray = array ('general'=>'General', 'times'=>'Time Cards');

		$str = "";
		$str .= $this->setHeading("Weekly Time Sumamry");
		$str .= $this->setPageTabsBegin($tabArray);

		$str .= "\n<div id='general'>";
		$str .= $this->textField("User", "users_id", $times->users_id, 25, 25)."<br/>";
		$str .= $this->dateField("Weekend", "weekenddate", $times->weekenddate)."<br/>";
		$str .= changeLiteral("--- Hours To Be Submitted ---")."<br/>";
		$str .= $this->textField("Billable", "billablehsrstobesubmitted", $times->billablehsrstobesubmitted, 10, 10)."<br/>";
		$str .= $this->textField("Non-billable", "nonbillablehsrstobesubmitted", $times->nonbillablehsrstobesubmitted, 10, 10)."<br/>";
		$str .= changeLiteral("--- Hours To Be Approved ---")."<br/>";
		$str .= $this->textField("Billable", "billablehsrstba", $times->billablehsrstba, 10, 10)."<br/>";
		$str .= $this->textField("Non-billable", "nonbillablehsrstba", $times->nonbillablehsrstba, 10, 10)."<br/>";
		$str .= changeLiteral("--- Already Approved Hours ---")."<br/>";		
		$str .= $this->textField("Billable", "billablehsrsapproved", $times->billablehsrsapproved, 10, 10)."<br/>";
		$str .= $this->textField("Non-billable", "nonbillablehsrsapproved", $times->nonbillablehsrsapproved, 10, 10)."<br/>";
		$str .= changeLiteral("--- Hours Held ---")."<br/>";
		$str .= $this->textField("Billable", "billablehsrsheld", $times->billablehsrsheld, 10, 10)."<br/>";
		$str .= $this->textField("Non-billable", "nonbillablehsrsheld", $times->nonbillablehsrsheld, 10, 10)."<br/>";
		$str .= $this->textField("Total Hours", "totalhours", $times->totalhours, 10, 10)."<br/>";

		$str .= "\n<div class='button'>";
		if (substr($_GET['action'], 0, 6) == 'Submit')
			$str .= "<input id='Submit-Button' type='button' value='$this->submitButton' onclick=\"enableAction('SubmitWeeklyTime')\">";
		
		if (substr($_GET['action'], 0, 6) == 'Approv')
			$str .= "<input id='Approve-Button' type='button' value='$this->approveButton' onclick=\"enableAction('ApproveWeeklyTime')\">";
		
		$str .= "\n<span id='Save-Button'></span>
		<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>
		\n</div>";		
		
		$str .= "\n</div>";		
		
		$str .= "\n<div id='times'>";
		$str .= $timeListView;
		$str .= "\n</div>";
		
		$str .= $this->endOfFormMessage();
		$str .= $this->setPageTabsEnd();			// End of Page Tab Div.;

		$str .= "<script type='text/javascript' language='JavaScript'>";
		$str .= "enableBrowse()";
		$str .= "</script>";
		
		return $str;
	}
	
	protected function getTimeCards($users_id, $weekenddate, $tab) {
		
		$ActionRegistry = new ActionRegistry("ListTime");
		$modelFile = $ActionRegistry->getModelFile();
		
		$columnsArray = array(
				"users_id" => "User",
				"workdate" => "Work Date",
				"projects_id" => "Project",
				"tasks_id" => "Task",
				"billablehours" => "Billable Hours",
				"nonbillablehours" => "Non-Billable Hours",
				"totalhours" => "Total Hours",
				"status" => "Status",
				"nextaction" => "Next Action");
		
		$statusArray = array(	"table" => "times",
								"column" => "status");
		
		$columnsJSON = json_encode($columnsArray);
		$statusJSON = json_encode($statusArray);
		
		$where = " users_id=\'$users_id\' and weekenddate=\'$weekenddate\' ";
		
		$str = "";
		$str .= $this->setHeading("List Time Cards");
		$str .= $this->setDatatableLayout('list-table-time', $columnsArray);
		$str .= "<input id='Create-Button' type='button' value='$this->enterTimeCardButton' onclick=\"actionURL('action=CreateTimeCard')\">";
		
		$str .= $this->onClickFillupList($tab,'list-table-time', $columnsJSON, $modelFile, 'times', 'BrowseTimeCard', $where, $statusJSON);
		
		return $str;
		
	}
	
}


?>