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

class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		$columnsArray = array(	"invoices_id" => "Invoice",
								"projects_id" => "Project",
								"begindate" => "From",
								"enddate" => "To",
								"billablehours" => "Billable Hours",
								"nonbillablehours" => "Non-Billable Hours",
								"totalhours" => "Total Hours",
								"totalamount" => "Total Amount",
								"charges" => "Charges",
								"expense" => "Expense",
								"invoiceamount" => "Invoice Amount",
								"billingcycle" => "Billing Cycle",
								"status" => "Status");
		
		$statusArray = array(	"table" => "invoiceheaders",
								"column" => "status");
		
		$advSearchArray = array("ih.invoices_id" => "Invoice",
								"ih.projects_id" => "Project",
								"ih.begindate" => "From",
								"ih.enddate" => "To",
								"billablehours" => "Billable Hours",
								"nonbillablehours" => "Non-Billable Hours",
								"totalhours" => "Total Hours",
								"totalamount" => "Total Amount",
								"charges" => "Charges",
								"expense" => "Expense",
								"invoiceamount" => "Invoice Amount",
								"billingcycle" => "Billing Cycle",
								"ih.status" => "Status");
		
		$columnsJSON = json_encode($columnsArray);
		$statusJSON = json_encode($statusArray);

		$str = "";
		$str .= $this->setHeading("List Invoices", $advSearchArray);
		$str .= $this->setDatatableLayout('list-table', $columnsArray);
		$str .= $this->endOfFormMessage();
		$str .= $this->fillupList('list-table', $columnsJSON, $modelFile, 'invoices', 'BrowseInvoice', '', $statusJSON);
		
		return $str;
	}
	
}


?>