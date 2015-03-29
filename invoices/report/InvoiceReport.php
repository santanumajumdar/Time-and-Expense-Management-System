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
require_once("$basedir/expenseheaders/report/PrintCommonFunctions.php");
require_once("$basedir/expenseheaders/model/DbObj.php");
require_once("$basedir/invoices/model/DbObj.php");
if (file_exists("$basedir/premium/expensedetails/ReceiptReport.php"))
	require_once("$basedir/premium/expensedetails/ReceiptReport.php");



class PageMainContent extends __ExpenseReportCommon
{

	public function setUIContents($modelFile=NULL) {
		
		global $basedir;
		
		$dateFormat = getUserDateFormat();
		
		$InvoiceData = new InvoiceData();
		$companyRec = $InvoiceData->getCompanyRec();

		$uid = $_GET['uid'];
		$headerRec = $InvoiceData->getInvoiceHeaderRec($uid);

		$ExpReport = new ExpReport();
		$ExpenseReport = $ExpReport->printReport($uid);
		
		$str = '';
		$str .= $this->makeInvoiceHeaderBlock($companyRec, $headerRec);
		$str .= $this->makeInvoiceDetailBlock($headerRec['invoices_id']);
		$str .= $ExpenseReport;
		
		if (($_GET['action'] == 'PrintInvoiceWithReceipt')
				&& (file_exists("$basedir/premium/expensedetails/ReceiptReport.php"))) {
			$ReceiptReport = new ReceiptReport();
			$str .= $ReceiptReport->getExpenseDetailsReceiptsByInvoice($headerRec['invoices_id']);
		}
				
		return $str;
	}


	protected function makeInvoiceHeaderBlock($companyRec, $headerRec) {
		
		$dateFormat = getUserDateFormat();

		$begindate = cvtDateIso2Dsp($headerRec['begindate'], $dateFormat);
		$enddate = cvtDateIso2Dsp($headerRec['enddate'], $dateFormat);

		$str = '';
		$str .= "<h1>".changeLiteral('Invoice')."</h1>";
		
		$str .= "<h2>{$companyRec['name']}"."<br>";
		$str .= "{$companyRec['address1']}";
		if ($companyRec['address2'] <> '')
			$str .= ", {$companyRec['address2']}";
		$str .= "<br>{$companyRec['city']}, {$companyRec['state']}-{$companyRec['postalcode']}</h2>";
		
		$str .= "<h4>";
		$contact = ($headerRec['billtocontact'] != '') ? $headerRec['billtocontact'] : $headerRec['contact'];
		$str .= ($contact != '') ? "To: {$contact}<br>" : '';
		$str .= $headerRec['Account_name']."<br>";
		$str .= ($headerRec['billtoaddress1'] != '') ? $headerRec['billtoaddress1']."<br>" : $headerRec['address1']."<br>";
		$address2 = ($headerRec['billtoaddress1'] != '') ? $headerRec['billtoaddress2'] : $headerRec['address2'];
		$str .= ($address2 != '') ? $address2."<br>" : '';
		$str .= ($headerRec['billtoaddress1'] != '') ? $headerRec['billtocity'].", " : $headerRec['city'].", ";
		$str .= ($headerRec['billtoaddress1'] != '') ? $headerRec['billtostate']." - " : $headerRec['state']." - ";
		$str .= ($headerRec['billtoaddress1'] != '') ? $headerRec['billtopostalcode']."<br>" : $headerRec['postalcode']."<br>";
		$str .= ($headerRec['billtoaddress1'] != '') ? $headerRec['billtocountry']."<br>" : $headerRec['country']."<br>";
		$email = ($headerRec['billtoemail'] != '') ? $headerRec['billtoemail'] : $headerRec['email'];
		$str .= ($email != '') ? "Email: $email" : '';
		$str .= "</h4>";
		
		if ($begindate == NULL
			or $enddate == NULL)
			$str .= "<h4>".changeLiteral('Period').": ".changeLiteral('Open Invoice')."</h4>";
		else
			$str .= "<h4>".changeLiteral('Period').": ".changeLiteral('From')." $begindate ".changeLiteral('to')." $enddate</h4>";

		return $str;
	}
	

	protected function makeInvoiceDetailBlock($invoices_id) {
		
		$dateFormat = getUserDateFormat();

		$InvoiceData = new InvoiceData();
		$summaryTimeRecs = $InvoiceData->getInvoiceTimeSummaryRec($invoices_id);
		$summaryChargeRecs = $InvoiceData->getInvoiceChargeSummaryRec($invoices_id);
		$summaryExpRecs = $InvoiceData->getInvoiceExpSummaryRec($invoices_id);

		$currentProject = '';
		$totalBillAmount = 0;
		$totalChargeAmount = 0;
		$totalExpAmount = 0;
		$grandInvoiceAmont = 0;

		$str = '';

		$str .= "\n".'<table class="report">'."\n";

		if (count($summaryTimeRecs) > 0) {
			
			$str .= "<tr>";
			$str .= "<td colspan=6><strong>".changeLiteral('Time Summary')."</strong></td>";
			$str .= "</tr>\n";
			$str .= "<tr>";
			$str .= "<td>".changeLiteral('Consultant')."</td><td>".changeLiteral('Billable')."<br>".changeLiteral('Hours')."</td><td>".changeLiteral('Non-billable')."<br>".changeLiteral('Hours')."</td><td>".changeLiteral('Total')."<br>".changeLiteral('Hours')."</td><td>".changeLiteral('Rate')."</td><td>".changeLiteral('Billed')."<br>".changeLiteral('Amount')."</td>";
			$str .= "</tr>\n";
			foreach ($summaryTimeRecs as $summaryTimeRec) {
				
				$str .= "<tr>";
				$str .= "<td>{$summaryTimeRec['user_name']}</td>";
				$str .= "<td align='right'>{$summaryTimeRec['billablehours']}</td>";
				$str .= "<td align='right'>{$summaryTimeRec['nonbillablehours']}</td>";
				$str .= "<td align='right'>{$summaryTimeRec['actualhours']}</td>";
				$str .= "<td align='right'>{$summaryTimeRec['rate']}</td>";
				$billamount = number_format($summaryTimeRec['billamount'], 2);
				$str .= "<td align='right'>$billamount</td>";
				$str .= "</tr>\n";
				$totalBillAmount += $summaryTimeRec['billamount'];
				$grandInvoiceAmont += $summaryTimeRec['billamount'];
			}

			$totalBillAmount = number_format($totalBillAmount, 2);
			$str .= "<tr>";
			$str .= "<td><strong>".changeLiteral('Project Total')."</strong></td>";
			$str .= "<td colspan=5 align='right'>$totalBillAmount</td>";
			$str .= "</tr>\n";
		}
		
		
		
		if (count($summaryChargeRecs) > 0) {
			
			$str .= "<tr>";
			$str .= "<td colspan=6><strong>".changeLiteral('Charge Summary')."</strong></td>";
			$str .= "</tr>\n";
			$str .= "<tr>";
			$str .= "<td>".changeLiteral('Consultant')."</td>";
			$str .= "<td colspan=5 align='right'>".changeLiteral('Amount')."</td>";
			$str .= "</tr>\n";
			foreach ($summaryChargeRecs as $summaryChargeRec) {
				
				$str .= "<tr>";
				$str .= "<td>{$summaryChargeRec['user_name']}</td>";
				$str .= "<td colspan=5 align='right'>{$summaryChargeRec['charges']}</td>";
				$str .= "</tr>\n";
				$totalChargeAmount += $summaryChargeRec['charges'];
				$grandInvoiceAmont += $summaryChargeRec['charges'];
			}

			$totalChargeAmount = number_format($totalChargeAmount, 2);
			$str .= "<tr>";
			$str .= "<td><strong>".changeLiteral('Project Total')."</strong></td>";
			$str .= "<td colspan=5 align='right'>$totalChargeAmount</td>";
			$str .= "</tr>\n";
		}
		
		
		If (Count($summaryExpRecs) > 0) {
			
			$str .= "<tr>";
			$str .= "<td colspan=6><strong>".changeLiteral('Expense Summary')."</strong></td>";
			$str .= "</tr>\n";
			$str .= "<tr>";
			$str .= "<td>".changeLiteral('Consultant')."</td><td colspan=2>".changeLiteral('From')."</td><td colspan=2>".changeLiteral('To (W/E)')."</td><td align='right'>".changeLiteral('Amount')."</td>";
			$str .= "</tr>\n";

			foreach ($summaryExpRecs as $summaryExpRec) {
				
				$str .= "<tr>";
				$str .= "<td>{$summaryExpRec['user_name']}</td>";
				$weekBeginDate = tesAddDate($summaryExpRec['weekendDate'], -6);
				$weekBeginDate = cvtDateIso2Dsp($weekBeginDate, $dateFormat);
				$str .= "<td colspan=2>$weekBeginDate</td>";
				$weekendDate = cvtDateIso2Dsp($summaryExpRec['weekendDate'], $dateFormat);
				$str .= "<td colspan=2>$weekendDate</td>";
				$expAmount = number_format($summaryExpRec['expAmount'], 2);
				$str .= "<td align='right'>$expAmount</td>";
				$str .= "</tr>\n";

				$grandInvoiceAmont += $summaryExpRec['expAmount'];
			}
		}


		$grandInvoiceAmont = number_format($grandInvoiceAmont, 2);
		$str .= "<tr>";
		$str .= "<td><strong>".changeLiteral('Invoice Total')."</strong></td>";
		$str .= "<td colspan=5 align='right'><strong>$grandInvoiceAmont</strong></td>";
		$str .= "</tr>\n";

		$str .= "</table>";

		// End of Invoice summary

		$str .= $this->printTimeDetails($invoices_id);			// Print detail time records
		$str .= $this->printChargeDetails($invoices_id);		// Print detail charge records

		return $str;
	}
	
	
	
	protected function printTimeDetails($invoices_id) {
	
		$str = '';
		$dateFormat = getUserDateFormat();
		
		$InvoiceData = new InvoiceData();
		$groupTimeRecs = $InvoiceData->getInvoiceTimeGroupRec($invoices_id);

		if (count($groupTimeRecs) == 0)
			return $str;

		$str .= '<p class="breakhere">';
		$str .= "<h1>".changeLiteral('Time Details')."</h1>";

		$str .= "\n".'<table class="report">';

		foreach ($groupTimeRecs as $groupTimeRec) {
			
			$detailTimeRecs = $InvoiceData->getInvoiceTimeDetailRec($invoices_id, $groupTimeRec['user_id']);

			$str .= "<tr>";
			$str .= "<td><strong>".changeLiteral('Consultant')."</strong></td>";
			$str .= "<td colspan=5><strong>{$groupTimeRec['user_name']}</strong></td>";
			$str .= "</tr>\n";

			$str .= "<tr>";
			$str .= "<td>".changeLiteral('Date')."</td>";
			$str .= "<td>".changeLiteral('Billable Hours')."</td>";
			$str .= "<td>".changeLiteral('Non-billable Hours')."</td>";
			$str .= "<td>".changeLiteral('Total Hours')."</td>";
			$str .= "<td>".changeLiteral('Billed Amount')."</td>";
			$str .= "<td>".changeLiteral('Description')."</td>";
			$str .= "</tr>\n";

			foreach ($detailTimeRecs as $detailTimeRec) {
				
				$str .= "<tr>";
				$workDate = cvtDateIso2Dsp($detailTimeRec['workdate'], $dateFormat);
				$str .= "<td>$workDate</td>";
				$str .= "<td align='right'>{$detailTimeRec['billablehours']}</td>";
				$str .= "<td align='right'>{$detailTimeRec['nonbillablehours']}</td>";
				$str .= "<td align='right'>{$detailTimeRec['actualhours']}</td>";
				$billamount = number_format($detailTimeRec['billamount'], 2);
				$str .= "<td align='right'>$billamount</td>";
				$str .= "<td>{$detailTimeRec['description']}</td>";
				$str .= "</tr>\n";
			}

			$str .= "<tr>";
			$str .= "<td>".changeLiteral('Total')."</td>";
			$str .= "<td align='right'>{$groupTimeRec['billablehours']}</td>";
			$str .= "<td align='right'>{$groupTimeRec['nonbillablehours']}</td>";
			$str .= "<td align='right'>{$groupTimeRec['actualhours']}</td>";
			$totalbillamount = number_format($groupTimeRec['billamount'], 2);
			$str .= "<td align='right'>$totalbillamount</td>";
			$str .= "<td>&nbsp;</td>";
			$str .= "</tr>\n";
		}
		
		$str .= "</table>";

		return $str;
	}

	
	protected function printChargeDetails($invoices_id) {
	
		$dateFormat = getUserDateFormat();
		
		$str = '';
		$consultant = '';
		
		$InvoiceData = new InvoiceData();
		$groupChargeRecs = $InvoiceData->getInvoiceChargeSummaryRec($invoices_id);

		if (count($groupChargeRecs) == 0)
			return $str;

		$str .= '<p class="breakhere">';
		$str .= "<h1>".changeLiteral('Charge Details')."</h1>";

		$str .= "\n".'<table class="report">';

		foreach ($groupChargeRecs as $groupChargeRec) {
			
			$str .= "<tr>";
			$str .= "<td><strong>".changeLiteral('Consultant')."</strong></td>";
			$str .= "<td colspan=2><strong>{$groupChargeRec['user_name']}</strong></td>";
			$str .= "</tr>\n";

			$str .= "<tr>";
			$str .= "<td>".changeLiteral('Date')."</td>";
			$str .= "<td>".changeLiteral('Charge')."</td>";
			$str .= "<td>".changeLiteral('Description')."</td>";
			$str .= "</tr>\n";
				
			$detailChargeRecs = $InvoiceData->getInvoiceChargeDetailRec($invoices_id, $groupChargeRec['user_id']);
			
			foreach ($detailChargeRecs as $detailChargeRec) {
				
				$str .= "<tr>";
				$workDate = cvtDateIso2Dsp($detailChargeRec['chargedate'], $dateFormat);
				$str .= "<td>$workDate</td>";
				$chargeAmount = number_format($detailChargeRec['charges'], 2);
				$str .= "<td align='right'>$chargeAmount</td>";
				$str .= "<td>{$detailChargeRec['description']}</td>";
				$str .= "</tr>\n";
			}

			$str .= "<tr>";
			$str .= "<td>".changeLiteral('Total')."</td>";
			$totalchargeamount = number_format($groupChargeRec['charges'], 2);
			$str .= "<td align='right'>$totalchargeamount</td>";
			$str .= "<td>&nbsp;</td>";
			$str .= "</tr>\n";
		}

		$str .= "</table>";
		return $str;
	}
	
	


}

class ExpReport extends __ExpenseReportCommon
{
	public function printReport($uid) {
		
		$str = '';

		$InvoiceData = new InvoiceData();
		$dataset = $InvoiceData->getExpenses($uid);

		$summaryExpRecs = $InvoiceData->getInvoiceExpSummaryRec($dataset[0]['invoices_id']);
		foreach ($summaryExpRecs as $summaryExpRec) {
			
			$where = "uid = '{$summaryExpRec['Expense_uid']}'";
			$EH = $InvoiceData->fetchRowbyWhereClause('expenseheaders', $where);

			$str .= '<p class="breakhere">';
			$str .= $this->makeHeaderBlock($EH);
			$str .= "\n".'<table class="report">';
			$str .= $this->makeColHeading($EH);
			$str .= $this->makeDetailBlock($EH);
			$str .= "\n</table>";
		}
		return $str;
	}
}


?>