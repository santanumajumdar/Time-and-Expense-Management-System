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
require_once("$basedir/projectusertasks/model/DbObj.php");
require_once("$basedir/projectusertasks/model/Validation.php");

class UpdateBO
{
	public $projid;
	public $userid;
	public $taskid;
	public $effdt;
	public $rate;
	public $status;
				
	public function setObj() {
		
		$dateFormat = getUserDateFormat();
		
		$this->projid = isset($_POST['projects_id']) ? trim($_POST['projects_id']) : "";
		$this->userid = trim($_POST['users_id']);
		$this->taskid = trim($_POST['tasks_id']);
		$this->effdt = convertdate(trim($_POST['effective_date']), $dateFormat, 'ymd');
		$this->rate = (trim($_POST['rate'])=='') ? '0.00' : trim($_POST['rate']);
		$this->status = trim($_POST['status']);
		
	}
	
	public function CreateProjectUserTask() {
	
		try
		{
			$Row = new ProjectUserTaskData();
			$uid = $Row->createRow($this->projid, $this->userid, $this->taskid, $this->effdt, $this->rate, $this->status);
			return $uid;

		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', 'er0015'));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('accounts_id', 'er0013'));
		
		}
	}

	
	public function EditProjectUserTask($uid) {
	
		try {
			$Row = new ProjectUserTaskData();
			$Row->updateRow($uid, $this->userid, $this->taskid, $this->effdt, $this->rate, $this->status);
			return TRUE;

		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat( 'users_id', 'er0004'));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('users_id', 'er0016'));
		}
			
	}
	
	public function DeleteProjectUserTask($uid)
	{
		
		try {
			$Row = new ProjectUserTaskData();
			$Row->deleteRow('projects_users_tasks', $uid);
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iPKInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', "er0024"));
		
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', "er0024"));
		
		}

	}
	
	
}


?>