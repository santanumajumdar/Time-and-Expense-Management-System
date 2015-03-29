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

class AccountData extends __DBCommonFunctions
{
	public $uid;
	public $accounts_id;
	public $name;
	public $address1;
	public $address2;
	public $city;
	public $state;
	public $postalcode;
	public $country;
	public $contact;
	public $email;
	public $status;
	public $lastbilldate;
	public $createat;
	public $createby;
	public $changeat;
	public $changeby;
	
	public function getAccount($uid) {
		
		$accountObj = $this->fetchRowObjforView("accounts", "uid", $uid);
		return ($accountObj == NULL) ? $this : $accountObj;
		
	}
	

	public function createRow($aid, $aname, $ad1, $ad2, $acity, $astate, $apostal, $acountry, $acontact, $aemail, $astat) {
		
		if ($aid == ''
			or $aname == ''
			or $astat == '')
				throw new iInvalidArgumentException();

		if (!isset($astat))
			throw new iInvalidDataException();

		$said = $this->escapeString($aid);
		$saname= $this->escapeString($aname);
		$sad1= $this->escapeString($ad1);
		$sad2 = $this->escapeString($ad2);
		$sacity = $this->escapeString($acity);
		$sastate = $this->escapeString($astate);
		$sapostal = $this->escapeString($apostal);
		$sacountry = $this->escapeString($acountry);
		$sacontact= $this->escapeString($acontact);
		$saemail= $this->escapeString($aemail);
		$sastat= $this->escapeString($astat);
		$loggedinUser = loggedUserID();

		$auth = new UserActionAuthorization();
		if (!$auth->chkAuthorityLevel('CreateAccount'))
			throw new iBLError('nocategory', 'er0041');

		try
		{
			$query = "INSERT INTO accounts (accounts_id, name, address1, address2, city, state, postalcode, country, contact, email, status, createby, changeby, createat, changeat)
									VALUES('$said', '$saname', '$sad1', '$sad2', '$sacity', '$sastate', '$sapostal', '$sacountry', '$sacontact', '$saemail', '$sastat', '$loggedinUser', '$loggedinUser', now(), now())";

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


	public function updateRow($uid, $aname, $ad1, $ad2, $acity, $astate, $apostal, $acountry, $acontact, $aemail, $astat)
	{
		if ($uid == ''
		or $aname == ''
		or $astat == '')
			throw new iInvalidArgumentException();

		if (!isset($astat))
			throw new iInvalidDataException();

		$saname= $this->escapeString($aname);
		$sad1= $this->escapeString($ad1);
		$sad2 = $this->escapeString($ad2);
		$sacity = $this->escapeString($acity);
		$sastate = $this->escapeString($astate);
		$sapostal = $this->escapeString($apostal);
		$sacountry = $this->escapeString($acountry);
		$sacontact= $this->escapeString($acontact);
		$saemail= $this->escapeString($aemail);
		$sastat= $this->escapeString($astat);
		$loggedinUser = loggedUserID();

		$auth = new UserActionAuthorization();
		if (!$auth->chkAuthorityLevel('EditAccount', $this->getRecordCreator('accounts', $uid)))
			throw new iBLError('nocategory', 'er0041');

		try
		{
			$query = "UPDATE accounts
						 SET 
      						 name = '$saname',
      						 address1 = '$sad1',
      						 address2 ='$sad2',
      						 city = '$sacity',
      						 state = '$sastate',
      						 postalcode = '$sapostal',
      						 country = '$sacountry',
      						 contact = '$sacontact',
      						 email = '$saemail',
      						 status = '$sastat',
      						 changeby = '$loggedinUser',
      						 changeat = now()
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
	
	
	public function getAccountName($accounts_id)
	{
		$query = "select accounts.name as account
		from accounts
		where accounts_id = '$accounts_id'";
	
		$dataset = $this->getDatabyQuery($query);
		return $dataset[0];
	}

	
	public function getTimeDetailsRecs($accounts_id, $weekbegindate, $weekenddate)
	{
		$query = "select times.workdate as workdate,
						times.nonbillablehours as nonbillablehours,
						times.billablehours as billablehours,
						times.nonbillablehours + times.billablehours as actualhours,
						times.description as description,
						tasks.name as task,
						users.fullname as user,
						projects.name as project
					from accounts, projects, projects_users, users, times, tasks
					where accounts.accounts_id = projects.billtoaccounts_id
						and projects.projects_id = projects_users.projects_id
						and projects_users.users_id = users.users_id
						and projects_users.users_id = times.users_id
						and times.tasks_id = tasks.tasks_id
						and times.workdate >= '$weekbegindate'
						and times.workdate <= '$weekenddate'
						and accounts.accounts_id = '$accounts_id'
					order by projects.projects_id, times.users_id, times.workdate, times.tasks_id";
	
		$dataset = $this->getDatabyQuery($query);
		return $dataset;
	}
	
}


?>