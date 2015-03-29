<?php
/* ********************************************************************************
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
 * ****************************************************************************** */

$basedir = dirname(dirname(dirname(__FILE__)));
require_once("$basedir/core/view/PageElement.class.php");
require_once("$basedir/expenseheaders/report/PrintCommonFunctions.php");
require_once("$basedir/expenseheaders/model/DbObj.php");
require_once("$basedir/projects/model/DbObj.php");
if (file_exists("$basedir/premium/expensedetails/ReceiptReport.php"))
	require_once("$basedir/premium/expensedetails/ReceiptReport.php");



class PageMainContent extends __ExpenseReportCommon
{

	public function setUIContents($modelFile=NULL) {
		
		global $basedir;

        $dateFormat = getUserDateFormat();
        $users_id = $_GET['users_id'];
        $projects_id = $_GET['projects_id'];
        $weekenddate = $_GET['weekenddate'];
        $weekenddate = convertdate($_GET['weekenddate'], $dateFormat, 'ymd');
        
        $where = " users_id='$users_id' and projects_id='$projects_id' and weekenddate='$weekenddate' ";
        $ExpenseHeaderData = new ExpenseHeaderData();
		$EH = $ExpenseHeaderData->fetchRowbyWhereClause('expenseheaders', $where);

        $ProjectData = new ProjectData();
        $CA = $ProjectData->ProjectCompanyAddress($EH['projects_id']);
        
		$str = "";

        $str .= "<h1>{$CA['name']}</h1>";
        $str .= "<h2>{$CA['address1']}";
        if (!empty($CA['address2']))
            $str .= ", {$CA['address2']}";
        $str .= "<br>{$CA['city']}, {$CA['state']}-{$CA['postalcode']}</h2>";

        $str .= $this->makeHeaderBlock($EH);
        $str .= "\n<table class=\"report\">";
        $str .= $this->makeColHeading($EH);
        $str .= $this->makeDetailBlock($EH);
        $str .= "\n</table>";

        if (file_exists("$basedir/premium/expensedetails/ReceiptReport.php")) {
        	$ReceiptReport = new ReceiptReport();
        	$str .= $ReceiptReport->getExpenseDetailsReceipts($users_id, $projects_id, $weekenddate);
        }
        return $str;
    }

}


?>


