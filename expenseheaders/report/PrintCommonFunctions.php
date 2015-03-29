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

 * ****************************************************************************** */
$basedir = dirname(dirname(dirname(__FILE__)));
require_once("$basedir/expenseheaders/model/DbObj.php");


abstract class __ExpenseReportCommon extends __PageElement {

    public function printReport($uid) {
    	
        $ExpenseHeaderData = new ExpenseHeaderData();
        $where = "uid = '$uid'";
        $EH = $ExpenseHeaderData->fetchRowbyWhereClause('expenseheaders', $where);

        $str = '';

        $str .= $this->makeHeaderBlock($EH);
        $str .= "\n<table id = 'report'>";
        $str .= $this->makeColHeading($EH);
        $str .= $this->makeDetailBlock($EH);
        $str .= "\n</table>";

        return $str;
    }


    protected function makeHeaderBlock($EH) {
        $dateFormat = getUserDateFormat();

        $satdate = cvtDateIso2Dsp($EH['weekenddate'], $dateFormat);

        $sundate = tesAddDate($EH['weekenddate'], -6);
        $sundate = cvtDateIso2Dsp($sundate, $dateFormat);

        $ExpenseHeaderData = new ExpenseHeaderData();
        $headerRec = $ExpenseHeaderData->getExpenseHeaderForPrint($EH['users_id'], $EH['projects_id'], $EH['weekenddate']);

        $str = '';
        $str .= "<h1>" . changeLiteral('Weekly Expense Report') . "</h1>";

        $str .= "\n<table class=\"report\">";

        $str .= "<tr>";
        $str .= "<td class=\"noborder\">" . changeLiteral('Client') . ":</td><td class=\"noborder\"><strong>{$headerRec['account']}</strong></td>";
        $str .= "</tr>";

        $str .= "<tr>";
        $str .= "<td class=\"noborder\">" . changeLiteral('Project') . ":</td><td class=\"noborder\"><strong>{$headerRec['project']}</strong></td>";
        $str .= "</tr>";

        $str .= "<tr>";
        $str .= "<td class=\"noborder\">" . changeLiteral('Consultant') . ":</td><td class=\"noborder\"><strong>{$headerRec['UserName']}</strong></td>";
        $str .= "</tr>";

        $str .= "<tr>";
        $str .= "<td class=\"noborder\">" . changeLiteral('Period') . ":</td><td class=\"noborder\">" . changeLiteral('From') . " <strong>$sundate</strong> " . changeLiteral('to') . " <strong>$satdate</strong></td>";
        $str .= "</tr>";

        $str .= "<tr>";
        $str .= "<td class=\"noborder\">" . changeLiteral('Description') . ":</td><td class=\"noborder\">{$EH['description']}</td>";
        $str .= "</tr>";

        $str .= "</table>";

        return $str;
    }

    
    protected function makeColHeading($EH) {
        $dateFormat = getUserDateFormat();

        $str = '';

        $str .= "<tr>
				<td style='vertical-align: top; text-align: left;'>" . changeLiteral('Expense Category') . "</td>";

        for ($i=6; $i>=0; $i--) {
            $date = tesAddDate($EH['weekenddate'], -$i);
            $date = cvtDateIso2Dsp($date, $dateFormat);
            $str .= "<td style='vertical-align: top; text-align: center;'>" . changeLiteral(Date2Day($date)) . "<hr>$date</td>";
        }

		$str .= "<td style='vertical-align: top; text-align: center;'>" . changeLiteral('Total') . "</td>
				<td style='vertical-align: top; text-align: center;'>" . changeLiteral('Accounting') . "</td>
                </tr>";

        return $str;
    }
    
    

    protected function makeDetailBlock($EH) {
    	
    	$ExpenseHeaderData = new ExpenseHeaderData();
    	$dataset = $ExpenseHeaderData->getExpenseDetailsForPrint($EH['users_id'], $EH['projects_id'], $EH['weekenddate']);
        if (!isset($dataset[0]))
            return NULL;

        $str = '';
        $prvExpCat = $dataset[0]['expensecategories_id'];
        $expArray = array(0,0,0,0,0,0,0,0);
        $totalArray = array(0,0,0,0,0,0,0,0);

		foreach ($dataset as $row) {
           if ($prvExpCat <> $row['expensecategories_id']) {
               $str .= $this->makeDetailLine($prvExpCat, $expArray);
               unset($expArray);
               $expArray = array(0,0,0,0,0,0,0,0);
               $prvExpCat = $row['expensecategories_id'];
           }

            $expArray[$row['dayindex']] = $row['amount'];
            $expArray[7] += $row['amount'];
            $totalArray[$row['dayindex']] += $row['amount'];
            $totalArray[7] += $row['amount'];
        }

        $str .= $this->makeDetailLine($row['expensecategories_id'], $expArray);
        $str .= $this->makeDetailLine(changeLiteral('Total'), $totalArray);

        return $str;
    }

    protected function makeDetailLine($ExpenseCategory, $expArray) {

        $str = '';
        $str .= "<tr>
                 {$this->printField($ExpenseCategory, 'vertical-align: top; text-align: left;', 'textField')}";

        for ($i=0; $i<=8; $i++) {
            $amount = (isset ($expArray[$i]) and ($expArray[$i] != 0)) ? sprintf("%01.2f", $expArray[$i]) : '';
            $str .= "{$this->printField($amount, 'vertical-align: top; text-align: right;', 'textField')}";
        }

        $str .= "</tr>";

        return $str;
    }

}

?>