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


class UserData extends __DBCommonFunctions
{
	public $uid;
	public $users_id;
	public $fullname;
	public $password;
	public $email;
	public $joindate;
	public $title;
	public $status;
	public $reportto;
	public $usergroup;
	public $u_menu_id;
	public $authorizations_id;
	public $dateformat;
	public $language;
	public $lastloginat;
	public $access_count;
	public $debuglevel;
	public $dbtracelevel;
	public $preview_receipt;
	public $createat;
	public $createby;
	public $changeat;
	public $changeby;
	
	public function getUser($uid) {
			
		$userObj = $this->fetchRowObjforView("users", "uid", $uid);
		return ($userObj == NULL) ? $this : $userObj;
	}


	public function getUserbyUserId($users_id) {
	
		return $this->fetchRowObj("users", "users_id", $users_id);
	}


	public function createUser($id, $fn, $pw, $em, $jd, $ti, $us, $rt, $ug, $mn, $at, $df, $lan, $debugLevel=3, $dbTraceLevel=0, $preview_receipt=1) {
		
		if ($id == ''
			or $fn == ''
			or $pw == ''
			or $jd == ''
			or $us == ''
			or $rt == '')
			throw new iInvalidArgumentException();

		if (!isValidDate($jd, 'ymd'))
			throw new iInvalidDataException();
		
		$users_status_array = $this->getOptionArray('users', 'status');
		if (!array_key_exists($us, $users_status_array))
			throw new iInvalidDataException();
		
		$users_dateformat_array = $this->getOptionArray('users', 'dateformat');
		if (!array_key_exists($df, $users_dateformat_array))
			throw new iInvalidDataException();
		
		$users_language_array = $this->getOptionArray('users', 'language');
		if (!array_key_exists($lan, $users_language_array))
			throw new iInvalidDataException();
		
		$users_debug_level_array = $this->getOptionArray('users', 'debuglevel');
		if (!array_key_exists($debugLevel, $users_debug_level_array))
			throw new iInvalidDataException();
		
		$users_dbtrace_level_array = $this->getOptionArray('users', 'dbtracelevel');
		if (!array_key_exists($dbTraceLevel, $users_dbtrace_level_array))
			throw new iInvalidDataException();
		
		$generic_yesno_array = $this->getOptionArray('generic', 'yes_no');
		if (!array_key_exists($preview_receipt, $generic_yesno_array))
			throw new iInvalidDataException();

		$sid = $this->escapeString($id);
		$sfn = $this->escapeString($fn);
		$spw = md5($this->escapeString($pw));
		$sem = $this->escapeString($em);
		$sjd = $this->escapeString($jd);
		$sti = $this->escapeString($ti);
		$sus = $this->escapeString($us);
		$srt = $this->escapeString($rt);
		$sug = $this->escapeString($ug);
		$smn = $this->escapeString($mn);
		$sat = $this->escapeString($at);
		$sdf = $this->escapeString($df);
		$slan = $this->escapeString($lan);
		$debugLevel = $this->escapeString($debugLevel);
		$dbTraceLevel = $this->escapeString($dbTraceLevel);
		$preview_receipt = $this->escapeString($preview_receipt);		

		$loggedinUser = loggedUserID();
		if (empty($loggedinUser)) $loggedinUser = '*SYSTEM';
		
		if (($sem <> '')
			and (filter_var($sem, FILTER_VALIDATE_EMAIL) === FALSE))
			throw new iBLError ('email', 'er0086');

		if (strtolower($sid) <> strtolower($srt)) {
			$RowData = $this->fetchRow('users', 'users_id', $srt);
			if ($RowData == NULL)							// Not a valid user id
				throw new iBLError('reportto', 'er0052');
			if ($RowData['status'] <> '10')					// User is not active
				throw new iBLError('reportto', 'er0050');
		}

		if (!empty($sat)) {
			$RowData = $this->fetchRow('authorizationlists', 'authorizations_id', $sat);
			if ($RowData == NULL)							// Not a valid authorization List
				throw new iBLError('authorizations_id', 'er0046');
		}
		
		if (!empty($smn)) {
			$where = "u_menu_id=$smn and parent_menu_id is NULL";
			$RowData = $this->fetchRowbyWhereClause('menu', $where);
			if ($RowData == NULL)							// Not a valid menu
				throw new iBLError('u_menu_id', 'er0099');
		}
		
		$smn = (empty($smn))? 'NULL' : $smn;

		try {
			$query = "INSERT INTO users (users_id, fullname, password, email, joindate, title, status, reportto, 
										usergroup, createat, createby, changeby, u_menu_id, authorizations_id, dateformat,
										language, debuglevel, dbtracelevel, preview_receipt)
			           VALUES ('$sid', '$sfn', '$spw', '$sem', '$sjd', '$sti', '$sus', '$srt', '$sug', now(), '$loggedinUser',
							 '$loggedinUser', '$smn', '$sat', '$sdf', '$slan', '$debugLevel', '$dbTraceLevel', '$preview_receipt')";
			$conn = $this->getConnection();
			$conn->query($query);
			$recid = $conn->insert_id;
			$this->chkQueryError($conn, $query);

			return $recid;
		
		} catch (Exception $e) {
			throw $e;
		}

	}


	public function updateUser($uid, $fn, $em, $jd, $ti, $us, $rt, $ug, $mn, $at, $df, $lan, $debugLevel, $dbTraceLevel, $preview_receipt) {
		
		if ($uid == ''
			or $fn == ''
			or $jd == ''
			or $us == ''
			or $rt == '')
				throw new iInvalidArgumentException();

		if (!isValidDate($jd, 'ymd'))
			throw new iInvalidDataException();
		
		$users_status_array = $this->getOptionArray('users', 'status');
		if (!array_key_exists($us, $users_status_array))
			throw new iInvalidDataException();
		
		$users_dateformat_array = $this->getOptionArray('users', 'dateformat');
		if (!array_key_exists($df, $users_dateformat_array))
			throw new iInvalidDataException();
		
		$users_language_array = $this->getOptionArray('users', 'language');
		if (!array_key_exists($lan, $users_language_array))
			throw new iInvalidDataException();
		
		$users_debug_level_array = $this->getOptionArray('users', 'debuglevel');
		if (!array_key_exists($debugLevel, $users_debug_level_array))
			throw new iInvalidDataException();
		
		$users_dbtrace_level_array = $this->getOptionArray('users', 'dbtracelevel');
		if (!array_key_exists($dbTraceLevel, $users_dbtrace_level_array))
			throw new iInvalidDataException();
		
		$generic_yesno_array = $this->getOptionArray('generic', 'yes_no');
		if (!array_key_exists($preview_receipt, $generic_yesno_array))
			throw new iInvalidDataException();

		$sfn = $this->escapeString($fn);
		$sem = $this->escapeString($em);
		$sjd = $this->escapeString($jd);
		$sti = $this->escapeString($ti);
		$sus = $this->escapeString($us);
		$srt = $this->escapeString($rt);
		$sug = $this->escapeString($ug);
		$smn = $this->escapeString($mn);
		$sat = $this->escapeString($at);
		$sdf = $this->escapeString($df);
		$slan = $this->escapeString($lan);
		$debugLevel = $this->escapeString($debugLevel);
		$dbTraceLevel = $this->escapeString($dbTraceLevel);
		$preview_receipt = $this->escapeString($preview_receipt);
		
		$loggedinUser = loggedUserID();

		$auth = new UserActionAuthorization();
		if ((!$auth->chkAuthorityLevel('EditUser', $this->getRecordCreator('users', $uid)))
			and (!$auth->chkAuthorityLevel('EditUser', $this->getRecordUserid('users', $uid))))
			throw new iBLError('nocategory', 'er0041');
		
		if (($sem <> '')
			and (filter_var($sem, FILTER_VALIDATE_EMAIL) === FALSE))
			throw new iBLError ('email', 'er0086');

		$UserData = $this->fetchRow('users', 'uid', $uid);

		if (($UserData['users_id']) != $srt)
		{
			$RowData = $this->fetchRow('users', 'users_id', $srt);
			if ($RowData == NULL) 				// Not valid user id
				throw new iBLError('reportto', 'er0052');
			if ($RowData['status'] != '10')		// User is not active
				throw new iBLError('manager', 'er0050');
		}

		If ($sus != '10') {
			$userid = $UserData['users_id'];
			// If user is manager or Admin, and if he has reporting users..then he cannot be in-active
			$where = " reportto = '$userid' and uid != '$uid'";
			$RowData = $this->fetchRowbyWhereClause('users', $where);
			if ($RowData != NULL)
				throw new iBLError('status', 'er0051');
		}

		if (!empty($sat)) {
			$RowData = $this->fetchRow('authorizationlists', 'authorizations_id', $sat);
			if ($RowData == NULL)							// Not a valid authorization List
				throw new iBLError('authorizations_id', 'er0046');
		}
		
		if (!empty($smn)) {
			$where = "u_menu_id=$smn and parent_menu_id is NULL";
			$RowData = $this->fetchRowbyWhereClause('menu', $where);
			if ($RowData == NULL)							// Not a valid menu
				throw new iBLError('u_menu_id', 'er0099');
		}
		
		$smn = (empty($smn))? 'NULL' : $smn;
		
		if (loggedUserID() == 'admin') {
			$query = "UPDATE users
						SET
							fullname = '$sfn',
							email = '$sem',
							joindate = '$sjd',
							title = '$sti',
							status = '$sus',
							reportto = '$srt',
							usergroup = '$sug',
							u_menu_id = $smn,
							authorizations_id = '$sat',
							dateformat = '$sdf',
							language = '$slan',
							debuglevel = '$debugLevel',
							dbtracelevel = '$dbTraceLevel',
							preview_receipt = '$preview_receipt',
							changeby = '$loggedinUser'
						WHERE uid = '$uid'";		
		} else {
			$query = "UPDATE users
						SET
							fullname = '$sfn',
							email = '$sem',
							joindate = '$sjd',
							title = '$sti',
							status = '$sus',
							reportto = '$srt',
							usergroup = '$sug',
							dateformat = '$sdf',
							language = '$slan',
							debuglevel = '$debugLevel',
							dbtracelevel = '$dbTraceLevel',
							preview_receipt = '$preview_receipt',
							changeby = '$loggedinUser'
						WHERE uid = '$uid'";
		}
		try {
			$conn = $this->getConnection();
			$conn->query($query);
			$this->chkQueryError($conn, $query);

		} catch (Exception $e) {
			throw $e;
		}
	}


	public function updateLoginTime($id) {
	
		if ($id == '')
			throw new iInvalidArgumentException();
	
		$loggedinUser = loggedUserID();
		
		$query = "UPDATE users
					SET
						lastloginat = now(),
						access_count = ifnull((access_count + 1), 1),
						changeby = '$loggedinUser'
						WHERE users_id = '$id'";
		try {
			$conn = $this->getConnection();
			$conn->query($query);
			$this->chkQueryError($conn, $query);
	
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	public function deleteUser($table, $uid) {
		
		$user = $this->fetchRowObj($table, 'uid', $uid);

		if ($user->users_id == 'admin') { // this is admin user id. cannot be deleted
			throw new iBLError('nocategory', 'er0088');
		}
		
		$where = " reportto = '{$user->users_id}'"; // User is a reported users
		$RowData = $this->fetchRowbyWhereClause('users', $where);
		if ($RowData <> NULL) {
			throw new iBLError('nocategory', 'er0056');
		}
		parent::deleteRow($table, $uid);
	}


	public function updatePwd($uid, $pw) {
		
		if ($uid == ''
			or $pw == '')
				throw new iInvalidArgumentException();
		$_POST['email'] = isset($_POST['email']) ? $_POST['email'] : "";

		$user = $this->fetchRowObj('users', 'uid', $uid);
		$spw = md5($this->escapeString($pw));

		$loggedinUser = loggedUserID();

		if (($user->users_id != $loggedinUser)
			and (($user->createby != '*SYSTEM')
				and ($user->users_id != $_POST['email']))) {
			$auth = new UserActionAuthorization();
			if (!$auth->chkAuthorityLevel('ChangeUserPassword', $this->getRecordCreator('users', $uid)))
				throw new iBLError('nocategory', 'er0041');
		}

		try {
			$query = "UPDATE users
						SET
       						password = '$spw',
       						changeby = '$loggedinUser'
 						WHERE uid = '$uid'";
			$conn = $this->getConnection();
			$conn->query($query);
			$this->chkQueryError($conn, $query);

		} catch (Exception $e) {
			throw $e;
		}
	}


}


?>