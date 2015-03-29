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

$basedir = dirname ( dirname ( dirname ( __FILE__ ) ) );

require_once("$basedir/core/controller/UpdateBORequest.class.php");
require_once("$basedir/expenseheaders/model/DbObj.php");
require_once("$basedir/expenseheaders/model/Validation.php");


class UpdateBO extends __UpdateBORequest {

	public $usid;
	public $pjid;
	public $wedt;
	public $desc;
	public $cmnt;
	public $locn;
	
	public function setObj() {
		
		$dateFormat = getUserDateFormat ();
		
		$this->usid = isset($_POST['users_id']) ? trim($_POST['users_id']) : "";
		$this->pjid = isset($_POST['projects_id']) ? trim($_POST['projects_id']) : "";
		$this->wedt = isset($_POST['weekenddate']) ? convertdate(trim($_POST['weekenddate']), $dateFormat, 'ymd') : "";
		$this->desc = trim($_POST['description']);
		$this->cmnt = trim($_POST['comment']);
		$this->locn = trim($_POST['location']);
	}

	public function CreateExpenseHeader() {
		
		try {
			$Row = new ExpenseHeaderData();
			$uid = $Row->createRow($this->usid, $this->pjid, $this->wedt, $this->desc, $this->cmnt, $this->locn);

			return $uid;

		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', 'er0004', $e->getMessage()));

		}
	
	}
	
	
	public function EditExpenseHeader($uid) {

		try {
			$Row = new ExpenseHeaderData();
			$Row->updateRow($uid, $this->desc, $this->cmnt, $this->locn);
			return TRUE;

		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('seq', 'er0017'));

		}
	
	}
	
	
	public function DeleteExpenseHeader($uid) {
		
		try {
			$Row = new ExpenseHeaderData();
			$Row->deleteExpense($uid);
			return TRUE;

		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', 'er0013'));
		
		} catch (iPKInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', 'er0027'));
		
		}
	
	}
	
	
	public function SubmitExpenseHeader($uid) {
		
		try {
			$Row = new ExpenseHeaderData();
			$Row->submitExpense($uid);
			return TRUE;

		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		}
	}
	
	
	public function VerifyExpenseHeader($uid) {
	
		try {
			$Row = new ExpenseHeaderData();
			$Row->verifyExpense($uid);
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		}		
	}
		
	
	public function HoldExpenseHeader($uid) {
		
		try {
			$Row = new ExpenseHeaderData();
			$Row->HoldExpense($uid);
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		}
	}
	
	
	public function ReleaseExpenseHeader($uid) {
		
		try {
			$Row = new ExpenseHeaderData();
			$Row->ReleaseExpense($uid);
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		}		
		
	}	
	
}


?>