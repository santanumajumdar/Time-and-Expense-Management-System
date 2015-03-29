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
 * 
 ********************************************************************************/

$basedir = dirname(dirname(dirname(__FILE__)));
require_once("$basedir/users/model/DbObj.php");
require_once("$basedir/users/model/Validation.php");


class UpdateBO
{
	
	public $id;
	public $fn;
	public $pw;
	public $em;
	public $jd;
	public $ti;
	public $us;
	public $rt;
	public $ug;
	public $mn;
	public $at;
	public $df;
	public $lan;
	public $debugLevel;
	public $dbTraceLevel;
	public $preview_receipt;
	
	
	public function setObj() {
		
		$dateFormat = getUserDateFormat();
		
		$this->id = isset($_POST['users_id']) ? trim($_POST['users_id']) : "";
		$this->fn = isset($_POST['fullname']) ? trim($_POST['fullname']) : "";
		$this->pw = isset($_POST['password1']) ? trim($_POST['password1']) : "";
		$this->em = isset($_POST['email']) ? trim($_POST['email']) : "";
		$this->jd = isset($_POST['joindate']) ? convertdate(trim($_POST['joindate']), $dateFormat, 'ymd') : "";
		$this->ti = isset($_POST['title']) ? trim($_POST['title']) : "";
		$this->us = isset($_POST['status']) ? trim($_POST['status']) : "";
		$this->rt = isset($_POST['reportto']) ? trim($_POST['reportto']) : "";
		$this->ug = isset($_POST['usergroup']) ? trim($_POST['usergroup']) : "";
		$this->mn = isset($_POST['u_menu_id']) ? trim($_POST['u_menu_id']) : "";
		$this->at = isset($_POST['authorizations_id']) ? trim($_POST['authorizations_id']) : "";
		$this->df = isset($_POST['dateformat']) ? trim($_POST['dateformat']) : "";
		$this->lan = isset($_POST['language']) ? trim($_POST['language']) : "";
		$this->debugLevel = isset($_POST['debuglevel']) ? trim($_POST['debuglevel']) : "";
		$this->dbTraceLevel = isset($_POST['dbtracelevel']) ? trim($_POST['dbtracelevel']) : "";
		$this->preview_receipt = isset($_POST['preview_receipt']) ? trim($_POST['preview_receipt']) : "";
	}

	
	public function CreateUser() {
	
		try {

			$UserData = new UserData();
			$uid = $UserData->createUser($this->id, $this->fn, $this->pw, $this->em, $this->jd, $this->ti,
								$this->us, $this->rt, $this->ug, $this->mn, $this->at, $this->df, $this->lan,
								$this->debugLevel, $this->dbTraceLevel, $this->preview_receipt);
	
			return $uid;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('users_id', 'er0004'));
		
		} catch (iDataTooLongException $e) {
			return (convertErrorToJSONFormat('nocategory', "er9998", "$e"));  // TODO Needs to be changed after researching on error handler in detail. - Kallol.
		}
	
	}
	
	
	public function EditUser($uid) {

		try {
			
			$UserData = new UserData();
			$UserData->updateUser($uid, $this->fn, $this->em, $this->jd, $this->ti, $this->us, 
							$this->rt, $this->ug, $this->mn, $this->at, $this->df, $this->lan,
							$this->debugLevel, $this->dbTraceLevel, $this->preview_receipt);
			return TRUE;
		
		} catch (iBLError $e)	{
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('users_id', 'er0004'));
		
		}
	}
	

	public function DeleteUser($uid) {
		
		try {
			
			$UserData = new UserData();
			$UserData->deleteUser('users', $uid);
			return TRUE;
		
		} catch (iBLError $e)	{
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iPKInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', 'er0022'));
		
		}

	}
	
	public function ChangeUserPassword($uid) {
		
		try {
			
			$Row = new UserData();
			$Row->updatePwd($uid, $this->pw);
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('users_id', 'er0004'));
		}
	
	}
	
	
}


?>