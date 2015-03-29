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

require_once("$basedir/accounts/model/DbObj.php");


class PageMainContent extends __PageElement
{

	public function setUIContents($modelFile=NULL) {

		$dateFormat = getUserDateFormat();

		$account = $_GET['accounts_id'];
		$weekenddate = convertdate($_GET['weekenddate'], $dateFormat, 'ymd');

		$weekbegindate = tesAddDate($weekenddate, -6);

		$AH = array('accounts_id' => $account,
					'weekenddate' => $weekenddate,
					'weekbegindate' => $weekbegindate);
		
		$AccountData = new AccountData();
		$companyRec = $AccountData->getCompanyRec();

		$str = "";
		$str .= "<h1>{$companyRec['name']}</h1>";
		$str .= "<h2>{$companyRec['address1']}";
		if ($companyRec['address2'] <> '')
			$str .= ", {$companyRec['address2']}";
		$str .= "<br>{$companyRec['city']}, {$companyRec['state']}-{$companyRec['postalcode']}</h2>";
		
		$str .= $this->makeHeaderBlock($AH);
		$str .= $this->makeDetailBlock($AH);
		
		return $str;
	}



	protected function makeHeaderBlock($AH)
	{
		$dateFormat = getUserDateFormat();
		
		$satdate = cvtDateIso2Dsp($AH['weekenddate'], $dateFormat);
		$sundate = cvtDateIso2Dsp($AH['weekbegindate'], $dateFormat);

		$AccountData = new AccountData();
		$headerRec = $AccountData->getAccountName($AH['accounts_id']);

		$str = '';
		$str .= "\n<h1>".changeLiteral('Weekly Customer Time Report')."</h1>";
		$str .= "\n<table class=\"report\">";
		$str .= "\n<tr><td class=\"noborder\">".changeLiteral('Customer').":</td><td class=\"noborder\"><strong>{$headerRec['account']}</strong></td></tr>";
		$str .= "\n<tr><td class=\"noborder\">".changeLiteral('Period').":</td><td class=\"noborder\">".changeLiteral('From')." <strong>$sundate</strong> ".changeLiteral('to')." <strong>$satdate</strong></td></tr>";
		$str .= "\n</table>";

		return $str;
	}





	protected function makeDetailBlock($AH)
	{
		$GTotal_billablehours = 0;
		$GTotal_nonbillablehours = 0;
		$GTotal_actualhours = 0;
		
		$PTotal_billablehours = 0;
		$PTotal_nonbillablehours = 0;
		$PTotal_actualhours = 0;
		
		$Total_billablehours = 0;
		$Total_nonbillablehours = 0;
		$Total_actualhours = 0;
		
		$AccountData = new AccountData();		
		if (($dataset = $AccountData->getTimeDetailsRecs($AH['accounts_id'], $AH['weekbegindate'], $AH['weekenddate'])) == NULL)
			return changeLiteral("<p>No time record was found.</p>");
		
		$prvProject = $dataset[0]['project'];
		$prvUser = $dataset[0]['user'];
		
		$str = '';
		$str .= "\n<table class=\"report\">";
		$str .= "<tr><td class=\"noborder\">".changeLiteral('Project').":</td> <td colspan=5 class=\"noborder\"><strong>{$dataset[0]['project']}</strong></td></tr>";
		$str .= "<tr><td class=\"noborder\">".changeLiteral('Consultant').":</td> <td colspan=5 class=\"noborder\"><strong>{$dataset[0]['user']}</strong></td></tr>";

		$str .= $this->makeColHeading();

		foreach ($dataset as $row)
		{
			if ($prvProject <> $row['project'])
			{
				$str .= $this->makeDetailLine('', changeLiteral('Total Hours'), $Total_billablehours, $Total_nonbillablehours, $Total_actualhours, '');
				$str .= $this->makeDetailLine('', changeLiteral('Total Project Hours'), $PTotal_billablehours, $PTotal_nonbillablehours, $PTotal_actualhours, '');
				$str .= "<tr><td class=\"noborder\">".changeLiteral('Project').":</td> <td colspan = 4 class=\"noborder\"><strong>{$row['project']}</strong></td></tr>";
				$str .= "<tr><td class=\"noborder\">".changeLiteral('Consultant').":</td> <td colspan = 4 class=\"noborder\"><strong>{$row['user']}</strong></td></tr>";
				$str .= $this->makeColHeading();

				$prvProject = $row['project'];
				$prvUser = $row['user'];
				
				$Total_billablehours = 0;
				$Total_nonbillablehours = 0;
				$Total_actualhours = 0;
				
				$PTotal_billablehours = 0;
				$PTotal_nonbillablehours = 0;
				$PTotal_actualhours = 0;
			}

			if ($prvUser <> $row['user'])
			{
				$str .= $this->makeDetailLine('', changeLiteral('Total Hours'), $Total_billablehours, $Total_nonbillablehours, $Total_actualhours, '');
				$str .= "<tr><td class=\"noborder\">".changeLiteral('Consultant').":</td> <td class=\"noborder\"><strong>{$row['user']}</strong></td></tr>";
				$str .= $this->makeColHeading();
				$prvUser = $row['user'];
				$Total_billablehours = 0;
				$Total_nonbillablehours = 0;
				$Total_actualhours = 0;
				
			}

			$str .= $this->makeDetailLine($row['workdate'], $row['task'], $row['billablehours'], $row['nonbillablehours'], $row['actualhours'], $row['description']);

			$Total_billablehours += $row['billablehours'];
			$Total_nonbillablehours += $row['nonbillablehours'];
			$Total_actualhours += $row['actualhours'];

			$PTotal_billablehours += $row['billablehours'];
			$PTotal_nonbillablehours += $row['nonbillablehours'];
			$PTotal_actualhours += $row['actualhours'];

			$GTotal_billablehours += $row['billablehours'];
			$GTotal_nonbillablehours += $row['nonbillablehours'];
			$GTotal_actualhours += $row['actualhours'];
			

		}
		$str .= $this->makeDetailLine('', changeLiteral('Total Hours'), $Total_billablehours, $Total_nonbillablehours, $Total_actualhours, '');
		$str .= $this->makeDetailLine('', changeLiteral('Total Project Hours'), $PTotal_billablehours, $PTotal_nonbillablehours, $PTotal_actualhours, '');
		$str .= $this->makeDetailLine('', changeLiteral('Grand Total Hours'), $GTotal_billablehours, $GTotal_nonbillablehours, $GTotal_actualhours, '');
		$str .= "\n</table>";

		return $str;
	}



	protected function makeColHeading()
	{
		return
			"<tr>
				<td style='text-align: left;'>".changeLiteral('Date')."</td>
				<td style='text-align: center;'>".changeLiteral('Task')."</td>
				<td style='text-align: right;'>".changeLiteral('Billable Hours')."</td>
				<td style='text-align: right;'>".changeLiteral('Non-billable Hours')."</td>
				<td style='text-align: right;'>".changeLiteral('Actual Hours')."</td>
				<td style='text-align: center;'>".changeLiteral('Description')."</td>
			</tr>";
	}
	


	protected function makeDetailLine($workdate, $task, $billablehours, $nonbillablehours, $actualhours, $description)
	{
		$dateFormat = getUserDateFormat();

		if ($workdate <>'')
			$workdate = cvtDateIso2Dsp($workdate, $dateFormat);

		$billablehours = sprintf("%01.2f", $billablehours);
		$nonbillablehours = sprintf("%01.2f", $nonbillablehours);
		$actualhours = sprintf("%01.2f", $actualhours);
		
		if ($billablehours == 0) $billablehours = '';
		if ($nonbillablehours == 0) $nonbillablehours = '';
		if ($actualhours == 0) $actualhours = '';

		return
			"<tr>
			{$this->printField($workdate, 'text-align: left;', 'textField')}
			{$this->printField($task, 'text-align: left;', 'textField')}
			{$this->printField($billablehours, 'text-align: right;', 'textField')}
			{$this->printField($nonbillablehours, 'text-align: right;', 'textField')}
			{$this->printField($actualhours, 'text-align: right;', 'textField')}
			{$this->printField($description, 'text-align: left;', 'textField')}
			</tr>";
	}

}




?>

