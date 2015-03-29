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


class ProjectUserTaskData extends __DBCommonFunctions
{
	public $uid;
	public $projects_id;
	public $users_id;
	public $tasks_id;
	public $effective_date;
	public $rate;
	public $status;
	public $createat;
	public $createby;
	public $changeat;
	public $changeby;
	
	public function getProjectUserTask($uid) {
	
		$projectUserTaskObj = $this->fetchRowObjforView("projects_users_tasks", "uid", $uid);
		return ($projectUserTaskObj == NULL) ? $this : $projectUserTaskObj;
		
	}
	

	public function createRow($projid, $userid, $taskid, $effdt, $rate, $status) {

		$projects_user_tasks_status_array = $this->getOptionArray('projects_users_tasks', 'status');
			
		if ($projid == ''
			or $userid == ''
			or $taskid == ''
			or $effdt == ''
			or $status == ''
			)
			throw new iInvalidArgumentException();

		if (!array_key_exists($status, $projects_user_tasks_status_array))
			throw new iInvalidDataException();

		$sprojid = $this->escapeString($projid);
		$suserid = $this->escapeString($userid);
		$staskid = $this->escapeString($taskid);
		$seffdt  = $this->escapeString($effdt);
		$srate   = $this->escapeString($rate);
		$sstatus = $this->escapeString($status);
		$loggedinUser = loggedUserID();

		try
		{
			$query = "INSERT INTO projects_users_tasks 
					(projects_id, users_id, tasks_id, effective_date, rate, status, createat, createby, changeby) 
			      VALUES('$sprojid', '$suserid', '$taskid', '$seffdt', '$srate', '$sstatus', now(), '$loggedinUser', '$loggedinUser')";
			$conn = $this->getConnection();
			$conn->query($query);
			$recid = $conn->insert_id;
			$this->chkQueryError($conn, $query);

			return $recid;
		
		} catch (Exception $e) {
			throw $e;
		}

	}


	public function updateRow($uid, $userid, $taskid, $effdt, $rate, $status) {
		
		$projects_user_tasks_status_array = $this->getOptionArray('projects_users_tasks', 'status');
		
		if ($uid == ''
			or $status == ''
			)
			throw new iInvalidArgumentException();

		if (!array_key_exists($status, $projects_user_tasks_status_array))
			throw new iInvalidDataException();

		$suserid = $this->escapeString($userid);
		$staskid = $this->escapeString($taskid);
		$seffdt  = $this->escapeString($effdt);
		$srate   = $this->escapeString($rate);
		$sstatus = $this->escapeString($status);
		$loggedinUser = loggedUserID();

		$auth = new UserActionAuthorization();
		if(!$auth->chkAuthorityLevel('EditProjectUserTask', $this->getRecordCreator('projects_users_tasks', $uid)))
			throw new iBLError('nocategory', 'er0041');

		try
		{
			$query = "UPDATE projects_users_tasks
						SET
							users_id = '$suserid', 
							tasks_id = '$staskid', 
							effective_date = '$seffdt',
							rate = '$srate',
							status = '$sstatus',
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