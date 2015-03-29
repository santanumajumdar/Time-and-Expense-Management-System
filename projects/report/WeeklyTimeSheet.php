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

require_once("$basedir/projects/model/DbObj.php");


class PageMainContent extends __PageElement
{

	public function setUIContents($modelFile=NULL) {

		$dateFormat = getUserDateFormat();

		$project = $_GET['projects_id'];
		$weekenddate = convertdate($_GET['weekenddate'], $dateFormat, 'ymd');

		$weekbegindate = tesAddDate($weekenddate, -6);

		$PH = array('projects_id' => $project,
					'weekenddate' => $weekenddate,
					'weekbegindate' => $weekbegindate);

		$ProjectData = new ProjectData();
		$companyRec = $ProjectData->getCompanyRec();

		$str = "";
		$str .= "<h1>{$companyRec['name']}</h1>";
		$str .= "<h2>{$companyRec['address1']}";
		if ($companyRec['address2'] <> '')
			$str .= ", {$companyRec['address2']}";
		$str .= "<br>{$companyRec['city']}, {$companyRec['state']}-{$companyRec['postalcode']}</h2>";

		$str .= $this->makeHeaderBlock($PH);
		$str .= $this->makeDetailBlock($PH);

		return $str;
	}



	protected function makeHeaderBlock($PH)
	{
		$dateFormat = getUserDateFormat();

		$satdate = cvtDateIso2Dsp($PH['weekenddate'], $dateFormat);
		$sundate = cvtDateIso2Dsp($PH['weekbegindate'], $dateFormat);

		$ProjectData = new ProjectData();
		$headerRec = $ProjectData->getProjectAccount($PH['projects_id']);

		$str = '';
		$str .= "<h1>".changeLiteral('Weekly Project Time Report')."</h1>";
		$str .= "\n<table class=\"report\">";
		$str .= "<tr><td class=\"noborder\">".changeLiteral('Project').":</td><td class=\"noborder\"><strong>{$headerRec['project']}</strong></td></tr>";
		$str .= "<tr><td class=\"noborder\">".changeLiteral('Client').":</td><td class=\"noborder\"><strong>{$headerRec['account']}</strong></td></tr>";
		$str .= "<tr><td class=\"noborder\">".changeLiteral('Period').":</td><td class=\"noborder\">".changeLiteral('From')." <strong>$sundate</strong> ".changeLiteral('to')." <strong>$satdate</strong></td></tr>";
		$str .= "</table>";
		return $str;
	}



	protected function makeDetailBlock($PH)
	{
		$PTotal_billablehours = 0;
		$PTotal_nonbillablehours = 0;
		$PTotal_actualhours = 0;
		
		$Total_billablehours = 0;
		$Total_nonbillablehours = 0;
		$Total_actualhours = 0;
		
		$str = '';
		$ProjectData = new ProjectData();		
		$dataset = $ProjectData->getTimeDetailsRecs($PH['projects_id'], $PH['weekbegindate'], $PH['weekenddate']);
		if ($dataset == NULL)
			return $str;

		$prvUser = $dataset[0]['user'];

		$str .= "\n<table class=\"report\">";
		$str .= "<tr><td colspan = 5 class=\"noborder\">".changeLiteral('Consultant').": <strong>{$dataset[0]['user']}</strong></td></tr>";

		$str .= $this->makeColHeading();

		foreach ($dataset as $row)
		{
			if ($prvUser <> $row['user'])
			{
				$str .= $this->makeDetailLine('', changeLiteral('Total Hours'), $Total_billablehours, $Total_nonbillablehours, $Total_actualhours, '');
				$str .= "<tr><td colspan=5 class=\"noborder\">".changeLiteral('Consultant').": <strong>{$row['user']}</strong></td></tr>";
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

		}
		$str .= $this->makeDetailLine('', changeLiteral('Total Hours'), $Total_billablehours, $Total_nonbillablehours, $Total_actualhours, '');
		$str .= $this->makeDetailLine('', changeLiteral('Project Total Hours'), $PTotal_billablehours, $PTotal_nonbillablehours, $PTotal_actualhours, '');
		$str .= "\n</table>";

		return $str;
	}


	protected function makeColHeading()
	{
		return
			"<tr>
				<td style='text-align: left;'>".changeLiteral('Date')."</td>
				<td style='text-align: center;'>".changeLiteral('Task')."</td>
				<td style='text-align: center;'>".changeLiteral('Billable Hours')."</td>
				<td style='text-align: center;'>".changeLiteral('Non-billable Hours')."</td>
				<td style='text-align: center;'>".changeLiteral('Actual Hours')."</td>
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