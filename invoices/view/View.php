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
require_once("$basedir/invoices/model/DbObj.php");
require_once("$basedir/core/controller/ActionRegistry.php");


class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		global $basedir;
		
		$InvoiceData = new InvoiceData();
		$invoices_status_array = $InvoiceData->getOptionArray('invoiceheaders', 'status');
		$invoice = $InvoiceData->getInvoice($_GET['uid']);
		
		$timeListView = $this->timeList($invoice->invoices_id, 'times');
		$chargeListView = $this->chargeList($invoice->invoices_id, 'charges');
		$expenseListView = $this->expenseList($invoice->invoices_id, 'expenses');
		
		$tabArray = array ('general'=>'General', 'times'=>'Times', 'charges' => 'Charges', 'expenses' => 'Expenses');
		
		$str = "";
		$str .= $this->setHeading("Browse Invoice");
		$str .= $this->setPageTabsBegin($tabArray);
		
		$str .= "\n<div id='general'>";
		$str .= $this->textField("Invoice", "invoices_id", $invoice->invoices_id, 25, 25, "Enable-On-Print")."<br/>";
		$str .= $this->textField("Project", "projects_id", $invoice->projects_id, 25, 25)."<br/>";
		$str .= $this->dateField("From Date", "begindate", $invoice->begindate)."<br/>";
		$str .= $this->dateField("To Date", "enddate", $invoice->enddate)."<br/>";
		$str .= $this->textField("Billing Cycle", "billcycle", $invoice->billcycle, 25, 25)."<br/>";
		$str .= $this->textField("Billable Hours", "billablehours", $invoice->billablehours, 25, 25)."<br/>";
		$str .= $this->textField("Non-billable Hours", "nonbillablehours", $invoice->nonbillablehours, 25, 25)."<br/>";
		$str .= $this->textField("Total Hours", "totalhours", $invoice->totalhours, 25, 25)."<br/>";
		$str .= $this->textField("Total Amount", "totalamount", $invoice->totalamount, 25, 25)."<br/>";
		$str .= $this->textField("Charge", "charges", $invoice->charges, 25, 25)."<br/>";
		$str .= $this->textField("Expense", "expenses", $invoice->expenses, 25, 25)."<br/>";
		$str .= $this->textField("Invoice Amount", "invoicetotal", $invoice->invoicetotal, 25, 25)."<br/>";
		$str .= $this->textField("Invoiced On", "invoicedate", $invoice->invoicedate, 25, 25)."<br/>";
		$str .= $this->radioField("Status", "status", $invoice->status, "", $invoices_status_array)."<br/>";
		
		
		$str .= "\n<div class='button'>";
		
		if($invoice->status == '10')
			$str .= "<input id='Create-Button' type='button' value='$this->createInvoiceButton' onclick=\"enableAction('CreateInvoice')\">";
		if($invoice->status == '90')
			$str .= "<input id='Delete-Button' type='button' value='$this->undoInvoiceButton' onclick=\"enableDelete('UndoInvoice')\">";
		
		$str .= "<input id='Print-Button' type='button' value='$this->printButton'  onclick=\"updateBORequest('PrintInvoice', 'Enable-On-Print')\">";
		
		$str .= "<span id='Save-Button'></span>
				<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>";
		$str .= "\n</div>";		// End of button div.
		
		$str .= "\n</div>";		// End of general div.
		
		$str .= "\n<div id='times'>";
		$str .= $timeListView;
		$str .= "\n</div>";

		$str .= "\n<div id='charges'>";
		$str .= $chargeListView;
		$str .= "\n</div>";
		
		$str .= "\n<div id='expenses'>";
		$str .= $expenseListView;
		$str .= "\n</div>";
		
		$str .= $this->endOfFormMessage();
		
		$str .= $this->setPageTabsEnd();			// End of Page Tab Div.
		
		return $str;
	}
	
	
	protected function timeList($invoiceID, $tab) {
	
		$ActionRegistry = new ActionRegistry("ListTime");
		$modelFile = $ActionRegistry->getModelFile();
	
		$columnsArray = array(
				"users_id" => "User",
				"workdate" => "Work Date",
				"projects_id" => "Project",
				"tasks_id" => "Task",
				"description" => "Description",
				"billablehours" => "Billable Hours",
				"nonbillablehours" => "Non-billable Hours",
				"status" => "Status",
				"submitdate" => "Submit Date",
				"approvedate" => "Approve Date",
				"nextaction" => "Next Action"
				);
		
		$statusArray = array(	"table" => "times",
								"column" => "status");
		
		$columnsJSON = json_encode($columnsArray);
		$statusJSON = json_encode($statusArray);
	
		$where = "invoices_id=\"$invoiceID\"";
	
		$str = "";
		$str .= $this->setHeading("Invoice :: $invoiceID");
		$str .= $this->setDatatableLayout('list-table-time', $columnsArray);
		$str .= $this->onClickFillupList($tab, 'list-table-time', $columnsJSON, $modelFile, 'times', 'BrowseTimeCard', $where, $statusJSON);
	
		return $str;
	}
	
	
	protected function chargeList($invoiceID, $tab) {

		$ActionRegistry = new ActionRegistry("ListCharge");
		$modelFile = $ActionRegistry->getModelFile();
	
		$columnsArray = array(	"users_id" => "User",
				"projects_id" => "Project",
				"chargedate" => "Charge Date",
				"charges" => "Charges",
				"status" => "Status",
				"nextaction" => "Next Action");
	
		$statusArray = array(	"table" => "charges",
								"column" => "status");
		
		$columnsJSON = json_encode($columnsArray);
		$statusJSON = json_encode($statusArray);
		
		$where = "invoices_id=\"$invoiceID\"";
	
		$str = "";
		$str .= $this->setHeading("Invoice :: $invoiceID");
		$str .= $this->setDatatableLayout('list-table-charge', $columnsArray);
		$str .= $this->onClickFillupList($tab,'list-table-charge', $columnsJSON, $modelFile, 'charges', 'BrowseCharge', $where, $statusJSON);
	
		return $str;
	}
	
	
	protected function expenseList($invoiceID, $tab) {
	
		$ActionRegistry = new ActionRegistry("ListExpenseHeader");
		$modelFile = $ActionRegistry->getModelFile();
	
		$columnsArray = array(	"users_id" => "User",
				"projects_id" => "Project",
				"weekenddate" => "Weekend",
				"submitdate" => "Submitted On",
				"approvedate" => "Verified On",
				"invoicedate" => "Invoiced On",
				"totalamount" => "Total Amount",
				"status" => "Status",
				"nextaction" => "Next Action");
		
		$statusArray = array(	"table" => "expenseheaders",
								"column" => "status");
		
		$columnsJSON = json_encode($columnsArray);
		$statusJSON = json_encode($statusArray);
		
		$where = "eh.invoices_id=\"$invoiceID\"";
			
		$str = "";
		$str .= $this->setHeading("Invoice :: $invoiceID");
		$str .= $this->setDatatableLayout('list-table-expense', $columnsArray);	
		$str .= $this->onClickFillupList($tab, 'list-table-expense', $columnsJSON, $modelFile, 'expenseheaders', 'BrowseExpenseHeader', $where, $statusJSON);
	
		return $str;
	}
	
}


?>