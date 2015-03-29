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
require_once("$basedir/expensedetails/model/DbObj.php");
require_once("$basedir/expensedetails/model/Validation.php");


class UpdateBO extends __UpdateBORequest {

	public $usid;
	public $pjid;
	public $wedt;
	public $esdt;
	public $catg;
	public $desc;
	public $amnt;
	public $mile;
	public $cmnt;
	public $receipt;
	
	
	
	public function setObj() {
		
		$dateFormat = getUserDateFormat ();
		
		$this->usid = trim($_POST['users_id']);
		$this->pjid = trim($_POST['projects_id']);
		$this->wedt = convertdate(trim($_POST['weekenddate']), $dateFormat, 'ymd');
		$this->esdt = convertdate(trim($_POST['expensedate']), $dateFormat, 'ymd');
		$this->catg = trim($_POST['expensecategories_id']);
		$this->desc = trim($_POST['description']);
		$this->amnt = trim($_POST['amount']);
		$this->mile = trim($_POST['mile']);
		$this->cmnt = trim($_POST['comment']);
		$this->receipt = empty($_SESSION['uploadedFile']) ? "" : $_SESSION['uploadedFile'];
		unset ($_SESSION['uploadedFile']);
	}

	public function CreateExpenseDetail() {
		
		try {
			$Row = new ExpenseDetailData();
			$uid = $Row->createRow($this->usid, $this->pjid, $this->wedt, $this->esdt, $this->catg, $this->desc, $this->cmnt, $this->amnt, $this->mile, $this->receipt);
			return $uid;

		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', 'er0004', $e->getMessage()));
		
		}
	
	}
	
	
	public function EditExpenseDetail($uid) {

		try {
			$Row = new ExpenseDetailData();
			$Row->updateRow($uid, $this->esdt, $this->catg, $this->desc, $this->cmnt, $this->amnt, $this->mile, $this->receipt);
			return TRUE;

		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('esdt', 'er0017'));
		
		}
	
	}
	
	
	public function DeleteExpenseDetail($uid) {
		
		try {
			$Row = new ExpenseDetailData();
			$Row->deleteExpenseDetail($uid);
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('expensedetails_id', 'er0013'));
		
		} catch (iPKInUseException $e) {
			return (convertErrorToJSONFormat('expensedetails_id', 'er0014'));
		
		}
	
	}
	
	
}


?>