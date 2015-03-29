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

$basedir = dirname(dirname(dirname(__FILE__)));

require_once("$basedir/core/view/PageElement.class.php");
require_once("$basedir/expensedetails/model/DbObj.php");

class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		global $basedir;
		
		$tabArray = array ('general'=>'General', 'journal'=>'Journal');
		
		$_GET['uid'] = (!isset($_GET['uid'])) ? "" : $_GET['uid'];

		$ExpenseDetailData = new ExpenseDetailData();
		$Expense_Header_status_array = $ExpenseDetailData->getOptionArray('ExpenseHeaders', 'status');		
		$ExpenseDetail = $ExpenseDetailData->getExpenseDetail($_GET['uid']);
		if (empty($_GET['uid'])) {
			$ExpenseDetail->users_id = $_GET['users_id'];
			$ExpenseDetail->projects_id = $_GET['projects_id'];
			$ExpenseDetail->weekenddate = $_GET['weekenddate'];
		}
		
		$str = "";
		$str .= $this->setHeading("Browse Expense Detail");
		$str .= $this->setPageTabsBegin($tabArray);
		
		$str .= "\n<div id='general'>";
		$str .= $this->textField("User", "users_id", $ExpenseDetail->users_id, 25, 25, "Show-On-Create Show-On-Edit")."<br/>";
		$str .= $this->textField("Project", "projects_id", $ExpenseDetail->projects_id, 25, 25, "Show-On-Create Show-On-Edit")."<br/>";
		$str .= $this->dateField("Weekend Date", "weekenddate", $ExpenseDetail->weekenddate, "Show-On-Create Show-On-Edit")."<br/>";
		$str .= $this->dateField("Expense Date", "expensedate", $ExpenseDetail->expensedate, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->optionFieldFromTable("Category", "expensecategories_id", $ExpenseDetail->expensecategories_id, "Enable-On-Create Enable-On-Edit", "expensecategories", "expensecategories_id", "description")."<br/>";
		$str .= $this->textField("Amount", "amount", $ExpenseDetail->amount, 25, 25, "Enable-On-Create Enable-On-Edit", "Enter the expense amount if this is not a milage claim")."<br/>";
		$str .= $this->textField("Miles", "mile", $ExpenseDetail->mile, 25, 25, "Enable-On-Create Enable-On-Edit", "Just enter the miles, system will calculate the amount. Make you do not enter anything in the amount if it is a mileage expense")."<br/>";
		$str .= $this->textField("Description", "description", $ExpenseDetail->description, 100, 100, "Enable-On-Create Enable-On-Edit", "Description of this expense, client may see this description")."<br/>";
		$str .= $this->textField("Comments", "comment", $ExpenseDetail->comment, 100, 100, "Enable-On-Create Enable-On-Edit", "This is for internal use, this comment will never go to your client, but others within your company may see this")."<br/>";
		if (file_exists("$basedir/premium/expensedetails")) {
			$preview = (getUserReceiptPreviewPreference() == '1') ? 'yes': 'no';
			$str .= $this->fileField("Receipt", "receipt", $ExpenseDetail->receipt, "Enable-On-Create Enable-On-Edit Hide-On-Browse", 'image|pdf', 'uploaded-image-list', 'uploadReceipts', $preview);
		}
		$str .= $this->radioField("Status", "status", $ExpenseDetail->status, "Show-On-Browse Hide-On-Edit Hide-On-Create", $Expense_Header_status_array);
		
		$str .= $this->showButtons($ExpenseDetail);
		
		$str .= "<ul id='uploaded-image-list'></ul>";
		
		if (file_exists("$basedir/premium/expensedetails")) {
			$str .= $this->embedImage($ExpenseDetail->receipt);
		}
					
		$str .= "\n</div>";
		
		$str .= "\n<div id='journal'>";
		$str .= $this->textField("Created By", "createby", $ExpenseDetail->createby, 25, 25)."<br/>";
		$str .= $this->textField("Created At", "createat", $ExpenseDetail->createat, 25, 25)."<br/>";
		$str .= $this->textField("Modified By", "changeby", $ExpenseDetail->changeby, 25, 25)."<br/>";
		$str .= $this->textField("Modified At", "changeat", $ExpenseDetail->changeat, 25, 25)."<br/>";
		$str .= "\n</div>";
		
		$str .= $this->endOfFormMessage();
		
		$str .= $this->setPageTabsEnd();			// End of Page Tab Div.

		$str .= "<script type='text/javascript' language='JavaScript'>";
		if (empty($_GET['uid'])) {
			$str .= "enableCreate('CreateExpenseDetail', 'Create Expense Detail');";
		} else {
			$str .= "enableBrowse();";
		}
		$str .= "</script>";
		
		
		return $str;
	}
	
	
	
	private function showButtons($ExpenseDetail) {
		
		$str = "";
		
		$str .= "\n<div class='button'>
		<input id='Create-Button' type='button' value='$this->createButton' onclick=\"enableCreate('CreateExpenseDetail', 'Create Expense Detail')\">";
		
		if($ExpenseDetail->status<'30')
			$str .= "<input id='Edit-Button'   type='button' value='$this->editButton'   onclick=\"enableEdit('EditExpenseDetail', 'Edit Expense Detail')\">";
		
		$str .= "<input id='Copy-Button'   type='button' value='$this->copyButton'   onclick=\"enableCopy('CreateExpenseDetail', 'Copy Expense Detail')\">";
		
		if($ExpenseDetail->status<'30')
			$str .= "<input id='Delete-Button' type='button' value='$this->deleteButton' onclick=\"enableDelete('DeleteExpenseDetail')\">";
		
		$str .= "<span id='Save-Button'></span>
		<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>
		\n</div>";

		return $str;
	}
	
}


?>