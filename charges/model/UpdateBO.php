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
require_once("$basedir/charges/model/DbObj.php");
require_once("$basedir/charges/model/Validation.php");


class UpdateBO
{
	public $us;
	public $projid;
	public $cdate;
	public $desc;
	public $comment;
	public $charges;
		
	public function setObj() {
		
		$dateFormat = getUserDateFormat();
		
		$this->us = isset($_POST['users_id']) ? trim($_POST['users_id']) : "";
		$this->projid = isset($_POST['projects_id']) ? trim($_POST['projects_id']) : "";
		$this->cdate = convertdate(trim($_POST['chargedate']), $dateFormat, 'ymd');
		$this->desc = trim($_POST['description']);
		$this->comment = trim($_POST['comments']);
		$this->charges = trim($_POST['charges']);
	}
	
	public function CreateCharge() {
	
		try
		{
			$Row = new ChargeData();
			$uid = $Row->createRow($this->us, $this->projid, $this->cdate, $this->charges, $this->desc, $this->comment);

			return $uid;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));

		}
	}	

	
	public function EditCharge($uid)
	{		
		try
		{
			$Row = new ChargeData();
			$Row->updateRow($uid, $this->cdate, $this->desc, $this->charges, $this->comment);
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat( 'projects_id', 'er0004'));
		
		}
	}
	
	public function DeleteCharge($uid)
	{
		try
		{
			$Row = new ChargeData();
			$Row->deleteCharge($uid);
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iPKInUseException $e) {
			return (convertErrorToJSONFormat('projects_id', "er0014"));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('projects_id', "er0013"));
		}
	}
	
	public function SubmitCharge($uid)
	{
		try
		{
			$Row = new ChargeData();
			$Row->SubmitCharge($uid);
	
			return TRUE;
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('projects_id', 'er0004'));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('users_id', 'er0013'));
		
		}	
	}
	
	public function ApproveCharge($uid)
	{
		try
		{
			$Row = new ChargeData();
			$Row->ApproveCharge($uid);
	
			return TRUE;
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('projects_id', 'er0004'));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('users_id', 'er0013'));
		}
	}
	
	
	public function HoldCharge($uid)
	{
		try
		{
			$Row = new ChargeData();
			$Row->HoldCharge($uid);
	
			return TRUE;
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('projects_id', 'er0004'));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('users_id', 'er0013'));
		
		}
	}
	
	
	public function ReleaseCharge($uid)
	{
		try
		{
			$Row = new ChargeData();
			$Row->ReleaseCharge($uid);
	
			return TRUE;
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('projects_id', 'er0004'));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('users_id', 'er0013'));
		
		}
	
	}
	
	
	
}


?>