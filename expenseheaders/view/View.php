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
require_once("$basedir/expenseheaders/model/DbObj.php");
require_once("$basedir/core/controller/ActionRegistry.php");


class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		global $basedir;
		
		$tabArray = array ('general'=>'General', 'journal'=>'Journal', 'expenseDetails' => 'Expense Details');
		
		$_GET['uid'] = (!isset($_GET['uid'])) ? "" : $_GET['uid'];

		$ExpenseHeaderData = new ExpenseHeaderData();
		$Expense_Header_status_array = $ExpenseHeaderData->getOptionArray('ExpenseHeaders', 'status');

		$ExpenseHeader = $ExpenseHeaderData->getExpenseHeader($_GET['uid']);
		$ExpenseHeader->totalamount = $ExpenseHeaderData->GetTotalExpense($_GET['uid']);
		
		$expenseDetailsView = $this->expenseDetailList($ExpenseHeader->users_id, $ExpenseHeader->projects_id, $ExpenseHeader->weekenddate, 'expenseDetails');
		
		$str = "";
		$str .= $this->setHeading("Browse Expense Header");
		$str .= $this->setPageTabsBegin($tabArray);
		
		$str .= "\n<div id='general'>";
		$str .= $this->optionFieldFromTable("User", "users_id", $ExpenseHeader->users_id, "Enable-On-Create Show-On-Print", "users", "users_id", "fullname")."<br/>";
		$query = "SELECT projects_users.projects_id as value, projects.name as text FROM projects_users INNER JOIN projects ON projects_users.projects_id = projects.projects_id";
		$str .= $this->cascadeOptionFieldFromTable("Project", "projects_id", $ExpenseHeader->projects_id, "Enable-On-Create Enable-On-Edit Show-On-Print", "projects", "users_id", "users_id", "projects_users.users_id", $query, "projects.projects_id")."<br/>";
		$str .= $this->dateField("Weekend Date", "weekenddate", $ExpenseHeader->weekenddate, "Enable-On-Create Show-On-Print", "Mention the weekend date of the expense, not the actual date of the expense; that will be in the expense detail")."<br/>";
		$str .= $this->textField("Description", "description", $ExpenseHeader->description, 100, 100, "Enable-On-Create Enable-On-Edit", "Short description, such as, purpose; of this expense. Your client will see this description")."<br/>";
		$str .= $this->textField("Comment", "comment", $ExpenseHeader->comment, 100, 100, "Enable-On-Create Enable-On-Edit", "This is for your internal use, your company people will see this but not your client")."<br/>";
		$str .= $this->textField("Location", "location", $ExpenseHeader->location, 25, 25, "Enable-On-Create Enable-On-Edit", "Location such as, country, state, city etc.; can be mentioned for tax purposes. This is purly for documentation purpose.")."<br/>";
		$str .= $this->textField("Total", "totalamount", $ExpenseHeader->totalamount, 25, 25, "Hide-On-Create")."<br/>";
		$str .= $this->radioField("Status", "status", $ExpenseHeader->status, "Hide-On-Create", $Expense_Header_status_array)."<br/>";
		
		$str .= "\n<div class='button'>";
		$str .= "<input id='Create-Button' type='button' value='$this->createButton' onclick=\"enableCreate('CreateExpenseHeader', 'Create Expense Report')\">";
		
		if($ExpenseHeader->status<'30')
			$str .= "<input id='Edit-Button'   type='button' value='$this->editButton' onclick=\"enableEdit('EditExpenseHeader', 'Edit Expense Report')\">";
		
		$str .= "<input id='Copy-Button'   type='button' value='$this->copyButton' onclick=\"enableCopy('CreateExpenseHeader', 'Copy Expense Report')\">";
		
		if($ExpenseHeader->status<'30')
			$str .= "<input id='Delete-Button' type='button' value='$this->deleteButton' onclick=\"enableDelete('DeleteExpenseHeader')\">";
		
		if($ExpenseHeader->status=='10')
			$str .= "<input id='Submit-Button' type='button' value='$this->submitButton' onclick=\"enableAction('SubmitExpenseHeader', 'expenseuid={$_GET['uid']}')\">";
		
		if($ExpenseHeader->status=='20')
			$str .= "<input id='Approve-Button' type='button' value='$this->verifyButton' onclick=\"enableAction('VerifyExpenseHeader', 'expenseuid={$_GET['uid']}')\">
			<input id='Hold-Button' type='button' value='$this->holdButton' onclick=\"enableAction('HoldExpenseHeader', 'expenseuid={$_GET['uid']}')\">";
		
		if($ExpenseHeader->status=='80')
			$str .= "<input id='Release-Button' type='button' value='$this->releaseButton' onclick=\"enableAction('ReleaseExpenseHeader', 'expenseuid={$_GET['uid']}')\">";
		
		$str .= "<input id='Print-Button' type='button' value='$this->printButton'  onclick=\"updateBORequest('PrintExpenseReport', 'Show-On-Print Enable-On-Print')\">";
		
		$str .= "<span id='Save-Button'></span>
				<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>";
		$str .= "\n</div>";		// End of button div.
		
		$str .= "\n</div>";		// End of general tab div.
		
		$str .= "\n<div id='journal'>";
		$str .= $this->dateField("Submit Date", "submitdate", $ExpenseHeader->submitdate)."<br/>";
		$str .= $this->dateField("Approve Date", "approvedate", $ExpenseHeader->approvedate)."<br/>";
		$str .= $this->dateField("Invoice Date", "invoicedate", $ExpenseHeader->invoicedate)."<br/>";
		
		$str .= $this->textField("Created By", "createby", $ExpenseHeader->createby, 25, 25)."<br/>";
		$str .= $this->textField("Created At", "createat", $ExpenseHeader->createat, 25, 25)."<br/>";
		$str .= $this->textField("Modified By", "changeby", $ExpenseHeader->changeby, 25, 25)."<br/>";
		$str .= $this->textField("Modified At", "changeat", $ExpenseHeader->changeat, 25, 25)."<br/>";
		$str .= "\n</div>";
		
		$str .= "\n<div id='expenseDetails'>";
		$str .= $expenseDetailsView;
		$str .= "\n</div>";

		$str .= $this->endOfFormMessage();
		
		$str .= $this->setPageTabsEnd();			// End of Page Tab Div.

		if (empty($_GET['uid'])) {
			$str .= "<script type='text/javascript' language='JavaScript'>
					enableCreate('CreateExpenseHeader', 'Create Expense Report')
					</script>";
		}
		
		return $str;
	}
	
	
	protected function expenseDetailList($userId, $projectId, $weekenddate, $tab) {
			
		$ActionRegistry = new ActionRegistry("ListExpenseDetail");
		$modelFile = $ActionRegistry->getModelFile();
	
		$columnsArray = array(
				"expensedate" => "Expense Date",
				"expensecategories_id" => "Category",
				"description" => "Description",
				"amount" => "Amount",
				"receipt" => "Receipt");
	
		$columnsJSON = json_encode($columnsArray);
	
		$where = "users_id=\"$userId\" and projects_id=\"$projectId\" and weekenddate=\"$weekenddate\" ";
		
		$parm = "&users_id=$userId&projects_id=$projectId&weekenddate=$weekenddate";
	
		$str = "";
		$str .= $this->setHeading("User :: $userId, Project :: $projectId, Weekend :: $weekenddate");
		$str .= $this->setDatatableLayout('list-table-ed', $columnsArray);
		$str .= "<input id='Create-Button' class='Add-Button-Footer' type='button' value='$this->addExpenseDetailButton' onclick=\"actionURL('action=CreateExpenseDetail$parm')\">";
	
		$str .= $this->onClickFillupList($tab, 'list-table-ed', $columnsJSON, $modelFile, 'expensedetails', 'BrowseExpenseDetail', $where);
	
		return $str;
	}
	
}


?>