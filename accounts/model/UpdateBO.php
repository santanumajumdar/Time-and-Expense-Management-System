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
require_once("$basedir/accounts/model/DbObj.php");
require_once("$basedir/accounts/model/Validation.php");


class UpdateBO
{
	public $id;
	public $name;
	public $address1;
	public $address2;
	public $city;
	public $state;
	public $postal;
	public $country;
	public $contact;
	public $email;
	public $status;
	
	public function setObj() {
		
		$this->id = isset($_POST['accounts_id']) ? trim($_POST['accounts_id']) : "";
		$this->name = trim($_POST['name']);
		$this->address1 = trim($_POST['address1']);
		$this->address2 = trim($_POST['address2']);
		$this->city = trim($_POST['city']);
		$this->state = trim($_POST['state']);
		$this->postal = trim($_POST['postalcode']);
		$this->country = trim($_POST['country']);
		$this->contact = trim($_POST['contact']);
		$this->email = trim($_POST['email']);
		$this->status = trim($_POST['status']);
	}
	
	public function CreateAccount() {

		try
		{
			$Row = new AccountData();
			$uid = $Row->createRow($this->id, $this->name, $this->address1, $this->address2, $this->city,
									$this->state, $this->postal, $this->country, $this->contact, $this->email, $this->status);
			return $uid;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('accounts_id', 'er0004'));
		}

	}
	
	public function EditAccount($uid) {
	
		try
		{
			$Row = new AccountData();
			$Row->updateRow($uid, $this->name, $this->address1, $this->address2, $this->city, $this->state, $this->postal,
									$this->country, $this->contact, $this->email, $this->status);
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', 'er0004'));
		}
	
	}
	
	
	public function DeleteAccount($uid) {
	
		try
		{
			$Row = new AccountData();
			$Row->deleteRow('accounts', $uid);
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', 'er0013'));
		
		} catch (iPKInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', 'er0014'));

		}
	
	}
	
	
	
}


?>