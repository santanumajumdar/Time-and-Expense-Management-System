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
require_once("$basedir/projects/model/DbObj.php");
require_once("$basedir/projects/model/Validation.php");

class UpdateBO
{
	public $projid;
	public $name;
	public $desc;
	public $accid;
	public $billacc;
	public $ad1;
	public $ad2;
	public $city;
	public $state;
	public $postal;
	public $country;
	public $contact;
	public $email;
	public $billcycle;
	public $billstartdate;		
	public $lastbilldate;
	public $status;		
				
	public function setObj() {
		
		$dateFormat = getUserDateFormat();
		
		$this->projid = isset($_POST['projects_id']) ? trim($_POST['projects_id']) : "";
		$this->name = trim($_POST['name']);
		$this->desc = trim($_POST['description']);
		$this->accid = trim($_POST['accounts_id']);
		$this->billacc = trim($_POST['billtoaccounts_id']);
		$this->ad1 = trim($_POST['billtoaddress1']);
		$this->ad2 = trim($_POST['billtoaddress2']);
		$this->city = trim($_POST['billtocity']);
		$this->state = trim($_POST['billtostate']);
		$this->postal = trim($_POST['billtopostalcode']);
		$this->country = trim($_POST['billtocountry']);
		$this->contact = trim($_POST['billtocontact']);
		$this->email = trim($_POST['billtoemail']);
		$this->billcycle = trim($_POST['billcycle']);
		$this->billstartdate = isset($_POST['billstartdate']) ? convertdate(trim($_POST['billstartdate']), $dateFormat, 'ymd') : "";
		$this->lastbilldate = isset($_POST['billstartdate']) ? convertdate(trim($_POST['billstartdate']), $dateFormat, 'ymd') : "";
		$this->status = trim($_POST['status']);		
	}
	
	public function CreateProject() {
	
	
		try
		{

			$Row = new ProjectData();
			$uid = $Row->createRow($this->projid, $this->name, $this->desc, $this->accid, $this->billacc, $this->ad1, $this->ad2,
									$this->city, $this->state, $this->postal, $this->country, $this->contact, $this->email,
									$this->billcycle, $this->lastbilldate, $this->status);
	
			return $uid;

		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('projects_id', 'er0004'));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('accounts_id', 'er0013'));
		}
	}
	
	public function EditProject($uid) {
	
		try
		{
			$Row = new ProjectData();
			$Row->updateRow($uid, $this->name, $this->desc, $this->ad1, $this->ad2, $this->city, $this->state,
							$this->postal, $this->country, $this->contact, $this->email, $this->billcycle, $this->status);
			return TRUE;

		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));

		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat( 'nocategory', 'er0004'));
		}
	
	}
	
	public function DeleteProject($uid)
	{
		try
		{
			$Row = new ProjectData();
			$Row->deleteRow('projects', $uid);
	
			return TRUE;

		} catch (iBLError $e) {
			convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta");
		
		} catch (iPKInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', "er0023"));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', "er0013"));
		}
	
	}
	
	
}


?>