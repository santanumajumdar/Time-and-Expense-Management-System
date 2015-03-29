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
require_once("$basedir/projects/model/DbObj.php");


class PageMainContent extends __PageElement
{
	
	public function setUIContents($modelFile=NULL) {

		$dateFormat = getUserDateFormat();
		
		$user = $_GET['users_id'];
		$project = $_GET['projects_id'];
		$weekenddate = convertdate($_GET['weekenddate'], $dateFormat, 'ymd');
		
		$weekbegindate = tesAddDate($weekenddate, -6);

		$TH = array('users_id' => $user,
					'projects_id' => $project,
					'weekenddate' => $weekenddate,
					'weekbegindate' => $weekbegindate);

		$str = "";

		$str .= $this->makeHeaderBlock($TH);
		$str .= "\n<table class=\"report\">";
		$str .= $this->makeColHeading($TH);
		$str .= $this->makeDetailBlock($TH);
		$str .= "\n</table>";

		return $str;
	}


	protected function makeHeaderBlock($TH)
	{
		$dateFormat = getUserDateFormat();

		$satdate = cvtDateIso2Dsp($TH['weekenddate'], $dateFormat);
		$sundate = cvtDateIso2Dsp($TH['weekbegindate'], $dateFormat);
		
		$TimeData = new TimeData();
		$userFullName = $TimeData->getUserFullName($TH['users_id']);
		$ProjectData = new ProjectData();
		$CA = $ProjectData->ProjectCompanyAddress($TH['projects_id']);

		$str = '';
		$str .= "<h1>{$CA['name']}</h1>";
		$str .= "<h2>{$CA['address1']}";
		if ($CA['address2'] <> '')
			$str .= ", {$CA['address2']}";
		$str .= "<br />{$CA['city']}, {$CA['state']}-{$CA['postalcode']}</h2>";
		
		$str .= "<h1>".changeLiteral('Weekly Personal Time Report')."</h1>";
		$str .= "\n<table class=\"report\">";

		$str .= "<tr>";
		$str .= "<td class=\"noborder\">".changeLiteral('Consultant').":</td><td class=\"noborder\"><strong>$userFullName</strong></td>";
		$str .= "</tr>";

		$str .= "<tr>";
		$str .= "<td class=\"noborder\">".changeLiteral('Period').":</td><td class=\"noborder\">".changeLiteral('From')." <strong>$sundate</strong> ".changeLiteral('to')." <strong>$satdate</strong></td>";
		$str .= "</tr>";

		$str .= "</table>";

		return $str;
	}


	protected function makeColHeading($EH)
	{
		return
			"\n<tr>
				<td style='text-align: left;'>".changeLiteral('Date')."</td>
				<td style='text-align: center;'>".changeLiteral('Project')."</td>
				<td style='text-align: center;'>".changeLiteral('Task')."</td>
				<td style='text-align: center;'>".changeLiteral('Billable<br>Hours')."</td>
				<td style='text-align: center;'>".changeLiteral('Non-billable<br>Hours')."</td>
				<td style='text-align: center;'>".changeLiteral('Total<br>Hours')."</td>
				<td style='text-align: center;'>".changeLiteral('Description')."</td>
			</tr>";
	}


	protected function makeDetailBlock($TH)
	{
		$Total_nonbillablehours = 0;
		$Total_billablehours = 0;
		$Total_totalhours = 0;

		$str = '';
		$TimeData = new TimeData();
		$dataset = $TimeData->getTimeDetailRecs($TH['projects_id'], $TH['users_id'], $TH['weekbegindate'], $TH['weekenddate']);

		foreach ($dataset as $row)
		{
			$str .= $this->makeDetailLine($row['workdate'], $row['project'], $row['task'], $row['nonbillablehours'], $row['billablehours'], $row['totalhours'], $row['description']);

			$Total_nonbillablehours += $row['nonbillablehours'];
			$Total_billablehours += $row['billablehours'];
			$Total_totalhours += $row['totalhours'];

		}

		$str .= $this->makeDetailLine('', changeLiteral('Weekly Total'), '', $Total_nonbillablehours, $Total_billablehours, $Total_totalhours, '');

		return $str;
	}


	protected function makeDetailLine($workdate, $project, $task, $nonbillablehours, $billablehours, $totalhours, $description)
	{
		$dateFormat = getUserDateFormat();

		if ($workdate <> '')
		$workdate = cvtDateIso2Dsp($workdate, $dateFormat);

		$nonbillablehours = sprintf("%01.2f", $nonbillablehours);
		$billablehours = sprintf("%01.2f", $billablehours);
		$totalhours = sprintf("%01.2f", $totalhours);

		if ($nonbillablehours == 0) $nonbillablehours = '';
		if ($billablehours == 0) $billablehours = '';
		if ($totalhours == 0) $totalhours = '';
		
		
		return
			"<tr>
			{$this->printField($workdate, 'text-align: left;', 'textField')}
			{$this->printField($project, 'text-align: left;', 'textField')}
			{$this->printField($task, 'text-align: left;', 'textField')}
			{$this->printField($billablehours, 'text-align: right;', 'textField')}
			{$this->printField($nonbillablehours, 'text-align: right;', 'textField')}
			{$this->printField($totalhours, 'text-align: right;', 'textField')}
			{$this->printField($description, 'text-align: left;', 'textField')}
			</tr>";
	}



}



?>
