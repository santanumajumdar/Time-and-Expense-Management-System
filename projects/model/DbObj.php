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

require_once("$basedir/core/model/DBCommonFunctions.class.php");
require_once("$basedir/core/controller/UserActionAuthorization.php");


class ProjectData extends __DBCommonFunctions
{
	
	public $uid;
	public $projects_id;
	public $name;
	public $description;
	public $accounts_id;
	public $billtoaccounts_id;  
	public $billtoaddress1;
	public $billtoaddress2;
	public $billtocity;
	public $billtostate;
	public $billtopostalcode; 
	public $billtocountry;
	public $billtocontact;
	public $billtoemail;
	public $billcycle;
	public $lastbilldate;
	public $status;
	public $createat;
	public $createby;
	public $changeat;
	public $changeby;

	public function getProject($uid) {
	
		$projectObj = $this->fetchRowObjforView("projects", "uid", $uid);
		return ($projectObj == NULL) ? $this : $projectObj;
		
	}
	
	public function createRow($projid, $name, $desc, $accid, $abilltoccid, $ad1, $ad2, $acity, $astate, $apostal, $acountry, $acontact, $aemail, $abillcycle, $alastbilldate, $status) {

		if ($projid == ''
			or $name == ''
			or $desc == ''
			or $accid == ''
			or $abilltoccid == ''
			or $abillcycle == ''
	        or $alastbilldate == ''
	        or $status == '')
				throw new iInvalidArgumentException();


		if (!isset($status))
			throw new iInvalidDataException();

		$sprojid = $this->escapeString($projid);
		$sname = $this->escapeString($name);
		$sdesc = $this->escapeString($desc);
		$saccid = $this->escapeString($accid);
		$sbilltoaccid = $this->escapeString($abilltoccid);
		$sad1= $this->escapeString($ad1);
		$sad2 = $this->escapeString($ad2);
		$sacity = $this->escapeString($acity);
		$sastate = $this->escapeString($astate);
		$sapostal = $this->escapeString($apostal);
		$sacountry = $this->escapeString($acountry);
		$sacontact= $this->escapeString($acontact);
		$saemail= $this->escapeString($aemail);
		$sabillcycle = $this->escapeString($abillcycle);
		$alastbilldate = tesAddDate($alastbilldate, -1);
		$salastbilldate = $this->escapeString($alastbilldate);
		$sstatus = $this->escapeString($status);
		$user = loggedUserID();
		
		if (($saemail <> '')
				and (filter_var($saemail, FILTER_VALIDATE_EMAIL) === FALSE))
			throw new iBLError ('billtoemail', 'er0086');

		try
		{
			$query = "INSERT INTO projects 
						(projects_id, name, description, accounts_id, billtoaccounts_id,  
						billtoaddress1, billtoaddress2, billtocity, billtostate, billtopostalcode, 
						billtocountry, billtocontact, billtoemail, billcycle, lastbilldate, status, 
						createat, createby, changeby )
      				VALUES('$sprojid', '$sname', '$sdesc', '$saccid', '$sbilltoaccid', 
      					'$sad1', '$sad2', '$sacity', '$sastate', '$sapostal', 
      					'$sacountry', '$sacontact', '$saemail', '$sabillcycle', '$salastbilldate', '$sstatus', 
      					now(), '$user', '$user')";

			$conn = $this->getConnection();
			$conn->query($query);
			$recid = $conn->insert_id;
			$this->chkQueryError($conn, $query);

			return $recid;
		}
		catch (Exception $e)
		{
			throw $e;
		}

	}


	public function updateRow($uid, $name, $desc, $ad1, $ad2, $city, $state, $postal, $country, $contact, $email, $billcycle, $status) {
			
		if ($uid == ''
			or $name == ''
			or $desc == ''
			or $status == '')
				throw new iInvalidArgumentException();

		if (!isset($status))
			throw new iInvalidDataException();

		
		$sname = $this->escapeString($name);
		$sdesc = $this->escapeString($desc);
		$sdesc = $this->escapeString($desc);
		$sad1 = $this->escapeString($ad1);
		$sad2 = $this->escapeString($ad2);
		$scity = $this->escapeString($city);
		$sstate = $this->escapeString($state);
		$spostal = $this->escapeString($postal);
		$scountry = $this->escapeString($country);
		$scontact = $this->escapeString($contact);
		$semail = $this->escapeString($email);
		$sbillcycle = $this->escapeString($billcycle);
		$sstatus = $this->escapeString($status);
		$loggedinUser = loggedUserID();
		
		if (($semail <> '')
				and (filter_var($semail, FILTER_VALIDATE_EMAIL) === FALSE))
			throw new iBLError ('billtoemail', 'er0086');

		$auth = new UserActionAuthorization();
		if (!$auth->chkAuthorityLevel('EditProject', $this->getRecordCreator('projects', $uid)))
			throw new iBLError('nocategory', 'er0041');

		try
		{
			$query = "UPDATE projects
						SET 
							name = '$sname',
							description = '$sdesc',
							billtoaddress1 = '$sad1',
							billtoaddress2 = '$sad2',
							billtocity = '$scity',
							billtostate = '$sstate',
							billtopostalcode = '$spostal',
							billtocountry = '$scountry',
							billtocontact = '$scontact',
							billtoemail = '$semail',
							billcycle = '$sbillcycle',
							status = '$sstatus',
							changeby = '$loggedinUser'
						WHERE uid = '$uid'";

			$conn = $this->getConnection();
			$conn->query($query);
			$this->chkQueryError($conn, $query);

		}
		catch (Exception $e)
		{
			throw $e;
		}
	}


	public function getBillbydate($projects_id) {
		
		$weekendday = $_SESSION['company']['weekendday'];
		$whereclause = "projects_id = '$projects_id' ";
		$rowData = $this->fetchRowbyWhereClause('projects', $whereclause);
		if ($rowData == NULL)
			return NULL;

		//Set the lastbilldate if it is not set....
		$lastbilldate = (($rowData['lastbilldate'] == NULL) or ($rowData['lastbilldate'] == 0)) ? ' ' : $rowData['lastbilldate'];
			
		$billcycle =$rowData['billcycle'];
			
		If ($lastbilldate == ' ')
		{
			switch ($billcycle)
			{
				case 'Weekly':
					$wedate= getWeekEndDate('',$weekendday);
					$billbydate= tesAddDate($wedate, -7);
					break;
				case 'Bi-Weekly':
					$wedate= getWeekEndDate('',$weekendday);
					$billbydate= tesAddDate($wedate, -14);
					break;
				case 'Monthly':
					$billbydate= getMonthEnd('','Prev');
					$billbydate= getMonthEnd($billbydate, 'Prev');
					break;
				case 'Bi-Monthly':
					$billbydate= getBiMonthlyDate('','Prev');
					$billbydate= getBiMonthlyDate($billbydate,'Prev');
					break;
				default:
					$billbydate= tesAddDate('today' ,-1);
			}
		}
		else
		{
			switch ($billcycle)
			{
				case 'Weekly':
					$billbydate= tesAddDate($lastbilldate, 7);
					break;
				case 'Bi-Weekly':
					$billbydate= tesAddDate($lastbilldate, +14);
					break;
				case 'Monthly':
					$billbydate= getMonthEnd($lastbilldate, 'Next');
					break;
				case 'Bi-Monthly':
					$billbydate= getBiMonthlyDate($lastbilldate,'Prev');
					break;
				default:
					$billbydate= tesAddDate('today' ,-1);
			}
		}

		Return $billbydate;
	}

	
	public function getLastBilldate($projects_id) {

		$whereclause = "projects_id = '$projects_id' ";
		$rowData = $this->fetchRowbyWhereClause('projects', $whereclause);
		if ($rowData == NULL)
			return NULL;

		//Set the lastbilldate if it is not set....
		$lastbilldate = (($rowData['lastbilldate'] == NULL) or ($rowData['lastbilldate'] == 0)) ? ' ' : $lastbilldate = $rowData['lastbilldate'];
		Return $lastbilldate;	
	}
	
	
	public function ProjectCompanyAddress($projects_id) {
		
		$CA = '';
	
		$query = "select accounts.name as name,
						accounts.address1 as address1,
						accounts.address2 as address2,
						accounts.city as city,
						accounts.state as state,
						accounts.postalcode as postalcode
					from projects, accounts
					where projects.projects_id = '$projects_id'
						and projects.billtoaccounts_id = accounts.accounts_id
						and projects.billtoaccounts_id <> projects.accounts_id";
		
		$dataset = $this->getDatabyQuery($query);
		
		if ((!isset($dataset[0])) or ($dataset[0] == NULL)) {
			$companyRec = $this->getCompanyRec();
			$CA['name'] = $companyRec['name'];
			$CA['address1'] = $companyRec['address1'];
			$CA['address2'] = $companyRec['address2'];
			$CA['city'] = $companyRec['city'];
			$CA['state'] = $companyRec['state'];
			$CA['postalcode'] = $companyRec['postalcode'];
		} else {
			$CA['name'] = $dataset[0]['name'];
			$CA['address1'] = $dataset[0]['address1'];
			$CA['address2'] = $dataset[0]['address2'];
			$CA['city'] = $dataset[0]['city'];
			$CA['state'] = $dataset[0]['state'];
			$CA['postalcode'] = $dataset[0]['postalcode'];
		}
		
		return $CA;
	}
	
	
	public function getProjectAccount($projects_id) {
		
		$query = "select projects.name as project,
						accounts.name as account
					from projects, accounts
					where projects_id = '$projects_id'
						  and projects.accounts_id = accounts.accounts_id";
	
		  $dataset = $this->getDatabyQuery($query);
		  return $dataset[0];
	}
	
	
	public function getTimeDetailsRecs($projects_id, $weekbegindate, $weekenddate) {
		
		$query = "select times.workdate as workdate,
						times.billablehours as billablehours,
						times.nonbillablehours as nonbillablehours,
						times.billablehours + times.nonbillablehours as actualhours,
						times.description as description,
						tasks.name as task,
						users.fullname as user
					from times, tasks, users
					where times.users_id = users.users_id
						and times.tasks_id = tasks.tasks_id
						and times.workdate >= '$weekbegindate'
						and times.workdate <= '$weekenddate'
						and times.projects_id = '$projects_id'
					order by times.users_id, times.workdate, times.tasks_id";
	
		$dataset = $this->getDatabyQuery($query);
		return $dataset;
	}
	
}


?>