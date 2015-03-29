<?php

/* * *******************************************************************************
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

 * ****************************************************************************** */

$basedir = dirname(dirname(dirname(__FILE__)));

require_once("$basedir/core/model/DBCommonFunctions.class.php");
require_once("$basedir/invoices/model/DbObj.php");
require_once("$basedir/core/controller/UserActionAuthorization.php");
require_once("$basedir/users/model/User.php");


class TimeData extends __DBCommonFunctions {
	
	public $uid;
	public $workdate;	
	public $weekenddate;
	public $description;
	public $comments;
	public $location;
	public $nonbillablehours;
	public $billablehours;
	public $status;
	public $submitdate;
	public $approvedate;
	public $rate;
	public $createat;
	public $createby;
	public $changeat;
	public $changeby;
	public $users_id;
	public $projects_id;
	public $tasks_id;
	public $invoices_id;
	
	public function getTime($uid) {
	
		$timeCardObj = $this->fetchRowObjforView("times", "uid", $uid);
		return ($timeCardObj == NULL) ? $this : $timeCardObj;
		
	}
	
	public function getWeeklyTimeSummary($users_id, $weekenddate) {
		
		if ($users_id == '' or $weekenddate == '')
			throw new iInvalidArgumentException();
	
		$sql = "SELECT t.users_id, t.weekenddate,
					(select sum(t1.billablehours) from times t1
						where t1.users_id = t.users_id and t1.weekenddate = t.weekenddate
							and t1.status < '20') as billablehsrstobesubmitted,
					(select sum(t1.nonbillablehours) from times t1
						where t1.users_id = t.users_id and t1.weekenddate = t.weekenddate
							and t1.status < '20') as nonbillablehsrstobesubmitted,
					(select sum(t1.billablehours) from times t1
						where t1.users_id = t.users_id and t1.weekenddate = t.weekenddate
							and t1.status >= '20' and t1.status < '30') as billablehsrstba,
					(select sum(t1.nonbillablehours) from times t1
						where t1.users_id = t.users_id and t1.weekenddate = t.weekenddate
							and t1.status >= '20' and t1.status < '30') as nonbillablehsrstba,
					(select sum(t1.billablehours) from times t1
						where t1.users_id = t.users_id and t1.weekenddate = t.weekenddate
							and t1.status >= '30' and t1.status != '80') as billablehsrsapproved,
					(select sum(t1.nonbillablehours) from times t1
						where t1.users_id = t.users_id and t1.weekenddate = t.weekenddate
							and t1.status >= '30' and t1.status != '80') as nonbillablehsrsapproved,
					(select sum(t1.billablehours) from times t1
						where t1.users_id = t.users_id and t1.weekenddate = t.weekenddate
							and t1.status = '80') as billablehsrsheld,
					(select sum(t1.nonbillablehours) from times t1
						where t1.users_id = t.users_id and t1.weekenddate = t.weekenddate
							and t1.status = '80') as nonbillablehsrsheld,
					sum(t.billablehours+nonbillablehours) as totalhours
				FROM times t
				where t.users_id = '$users_id'
					and t.weekenddate = '$weekenddate';";
		
		return $this->getDataObj($sql)->fetch_object();
	}
	
	
    public function createRow($us, $projid, $taskid, $wdate, $tdesc, $tcomment, $billhrs, $nbhrs, $locn) {
    	
        $weekendday = $_SESSION['company']['weekendday'];

        if ($us == ''
                or $projid == ''
                or $taskid == ''
                or $wdate == '')
            throw new iInvalidArgumentException();

        if ($billhrs == '')
            $billhrs = 0;
        if ($nbhrs == '')
            $nbhrs = 0;

        if (($billhrs + $nbhrs) == 0) {
            throw new iBLError('billablehours', 'er0087');
        }

        if (!isValidDate($wdate, 'ymd'))
            throw new iInvalidDataException();

        $sus = $this->escapeString($us);
        $sprojid = $this->escapeString($projid);
        $staskid = $this->escapeString($taskid);
        $stdesc = $this->escapeString($tdesc);
        $stcomment = $this->escapeString($tcomment);
        $snbhrs = $this->escapeString($nbhrs);
        $sbillhrs = $this->escapeString($billhrs);
        $slocn = $this->escapeString($locn);
        $swdate = $this->escapeString($wdate);
        $user = loggedUserID();

        $where = " users_id= '$sus' and workdate = '$swdate' ";
        $Data = new TimeData();
        $othernbhours = $Data->getColumnSum('times', 'billablehours', $where);
        $otherbhours = $Data->getColumnSum('times', 'nonbillablehours', $where);
        if (($sbillhrs + $snbhrs + $otherbhours + $othernbhours) > 24) {
            throw new iBLError('nocategory', 'er0058');
        }

        $RowData = $this->fetchRow('projects', 'projects_id', $sprojid);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0043');

        $RowData = $this->fetchRow('accounts', 'accounts_id', $RowData['accounts_id']);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0044');

        $RowData = $this->fetchRow('tasks', 'tasks_id', $staskid);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0018');

        $whereclause = "projects_id = '$sprojid' and users_id = '$sus'";
        $RowData = $this->fetchRowbyWhereClause('projects_users', $whereclause);
        if ($RowData == NULL)
            throw new iBLError('users_id', 'er0019');

//	Task may not be validated against users_projects_tasks as long as tasks are valid from the master list.  - Kallol.
//                        
//        $whereclause = "projects_id = '$sprojid' and users_id = '$sus' and tasks_id = '$staskid'";
//        $RowData = $this->fetchRowbyWhereClause('projects_users_tasks', $whereclause);
//        if ($RowData == NULL)
//            throw new iBLError('tasks_id', 'er0091');

        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('CreateTimeCard', "", $sus))
            throw new iBLError('users_id', 'er0080');
            
        $query = "select put.effective_date as effdt,
						 put.rate as rate
					from projects_users_tasks put
				   where put.projects_id = '$sprojid'
					 and put.users_id = '$sus'
					 and put.tasks_id = '$staskid'
					 and put.effective_date <= '$swdate'
					 and put.status = '10'
				order by put.effective_date desc";
        $dataset = $this->getDatabyQuery($query);
        
//	Task may not be validated against users_projects_tasks as long as tasks are valid from the master list.  - Kallol.
//        
//        if (!isset($dataset[0]['effdt']))
//            throw new iBLError('workdate', 'er0076');
            
        $rate = (!empty($dataset[0]['rate'])) ? $dataset[0]['rate'] : 0;
        
        if ($rate == 0) {
	        $query = "select pu.effective_date as effdt,
							 pu.rate as rate
						from projects_users pu
					   where pu.projects_id = '$sprojid'
						 and pu.users_id = '$sus'
						 and pu.effective_date <= '$swdate'
					     and pu.status = '10'
					order by pu.effective_date desc";
    	    $dataset = $this->getDatabyQuery($query);
        	if (!isset($dataset[0]['effdt']))
            	throw new iBLError('workdate', 'er0076');

	        $rate = $dataset[0]['rate'];
        }

		$wedate = getWeekEndDate($swdate, $weekendday);

        $Invoicehdr = new InvoiceData();
        $invoiceid = $Invoicehdr->createOpenInvoice($sprojid);
        try {
            $query = "INSERT INTO times (users_id, projects_id, tasks_id, workdate, weekenddate, description,
                                comments, billablehours, nonbillablehours, location, status, rate, invoices_id,
                                createat, createby, changeby )
                        VALUES('$sus', '$sprojid', '$staskid', '$swdate','$wedate', '$stdesc',
                                '$stcomment', '$sbillhrs', '$snbhrs', '$slocn', '10', $rate, '$invoiceid',
                                now(), '$user', '$user')";

            $conn = $this->getConnection();
            $conn->query($query);
            $recid = $conn->insert_id;
            $this->chkQueryError($conn, $query);
            return $recid;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    

    public function updateRow($uid, $us, $projid, $taskid, $wdate, $tdesc, $tcomment, $billhrs, $nbhrs, $locn) {

        $weekendday = $_SESSION['company']['weekendday'];

        if ($us == ''
             or $projid == ''
             or $taskid == ''
             or $wdate == '')
                throw new iInvalidArgumentException();

        if (!isValidDate($wdate, 'ymd'))
            throw new iInvalidDataException();

        if (($billhrs + $nbhrs) == 0)
            throw new iBLError('billablehours', 'er0087');

        $sus = $this->escapeString($us);
        $sprojid = $this->escapeString($projid);
        $staskid = $this->escapeString($taskid);
        $stdesc = $this->escapeString($tdesc);
        $stcomment = $this->escapeString($tcomment);
        $sbillhrs = $this->escapeString($billhrs);
        $snbhrs = $this->escapeString($nbhrs);
        $slocn = $this->escapeString($locn);
        $swdate = $this->escapeString($wdate);
        $user = loggedUserID();
        $wedate = getWeekEndDate($swdate, $weekendday);

        $where = " users_id= '$sus' and workdate = '$swdate' and uid <> '$uid'";
        $Data = new TimeData();
        $othernbhours = $Data->getColumnSum('times', 'billablehours', $where);
        $otherbhours = $Data->getColumnSum('times', 'nonbillablehours', $where);
        if (($sbillhrs + $snbhrs + $otherbhours + $othernbhours) > 24)
            throw new iBLError('nocategory', 'er0058');

        $RowData = $this->fetchRow('projects', 'projects_id', $sprojid);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0043');

        $billToAccountId = $RowData['billtoaccounts_id'];

        $RowData = $this->fetchRow('accounts', 'accounts_id', $RowData['accounts_id']);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0044');

        $RowData = $this->fetchRow('tasks', 'tasks_id', $staskid);
        if ($RowData['status'] <> '10')
            throw new iBLError('tasks_id', 'er0018');

        $whereclause = "projects_id = '$sprojid' and users_id = '$sus'";
        $RowData = $this->fetchRowbyWhereClause('projects_users', $whereclause);
        if ($RowData == NULL)
            throw new iBLError('users_id', 'er0019');
                        
//        $whereclause = "projects_id = '$sprojid' and users_id = '$sus' and tasks_id ='$staskid'";
//        $RowData = $this->fetchRowbyWhereClause('projects_users_tasks', $whereclause);
//        if ($RowData == NULL)
//            throw new iBLError('tasks_id', 'er0091');

        $User = $this->fetchRow('users', 'users_id', $sus);
        if ($User['status'] <> '10')
        	throw new iBLError('users_id', 'er0013');
        
        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('EditTimeCard', $RowData['createby'], $sus))
        	throw new iBLError('users_id', 'er0080');

        $where = "uid = '$uid' ";
        $TR = $this->fetchRowbyWhereClause('times', $where);

        if ($TR['status'] >= '30')
            throw new iBLError('nocategory', 'er0072');
            
        $query = "select put.effective_date as effdt,
						 put.rate as rate
					from projects_users_tasks put
				   where put.projects_id = '$sprojid'
					 and put.users_id = '$sus'
					 and put.tasks_id = '$staskid'
					 and put.effective_date <= '$swdate'
					 and put.status = '10'
				order by put.effective_date desc";
        $dataset = $this->getDatabyQuery($query);
//        if (!isset($dataset[0]['effdt']))
//            throw new iBLError('workdate', 'er0076');

        $rate = (!empty($dataset[0]['rate'])) ? $dataset[0]['rate'] : 0;
        
        if ($rate == 0) {
	        
	        $query = "select pu.effective_date as effdt,
							 pu.rate as rate
						from projects_users pu
					   where pu.projects_id = '$sprojid'
						 and pu.users_id = '$sus'
						 and pu.effective_date <= '$swdate'
					     and pu.status = '10'
					order by pu.effective_date desc";

	        $dataset = $this->getDatabyQuery($query);
    	    if (!isset($dataset[0]['effdt']))
    	        throw new iBLError('workdate', 'er0076');

        	$rate = $dataset[0]['rate'];
        }

        try {
            $query = "UPDATE times
			 SET
                             users_id = '$sus',
                             projects_id = '$sprojid',
                             tasks_id = '$staskid',
                             workdate ='$swdate',
                             weekenddate ='$wedate',
                             rate = '$rate',
                             description ='$stdesc',
                             comments ='$stcomment',
                             nonbillablehours ='$snbhrs',
                             billablehours ='$sbillhrs',
                             location = '$slocn',
                             changeby = '$user'
                       WHERE uid = '$uid'";

            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);
        
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function deleteRow($table, $uid) {
    	
        if ($table == ''
                or $uid == '')
            throw new iInvalidArgumentException();

        $suid = $this->escapeString($uid);

        $where = "uid = '$suid' ";
        $TR = $this->fetchRowbyWhereClause('times', $where);

        if ($TR['status'] >= '30')
            throw new iBLError('nocategory', 'er0071');

        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('DeleteTimeCard', $TR['createby'], $TR['users_id']))
        	throw new iBLError('users_id', 'er0080');

        parent::deleteRow($table, $uid);
    }
    

    public function submitWeeklyTime($us, $weekenddate) {

        if ($us == ''
                or $weekenddate == '')
            throw new iInvalidArgumentException();

        if (!isValidDate($weekenddate, 'ymd'))
            throw new iInvalidDataException();

        $sus = $this->escapeString($us);
        $swdate = $this->escapeString($weekenddate);
        $user = loggedUserID();

        $a_date = explode('-', $swdate);
        $mn = $a_date[1];
        $dy = $a_date[2];
        $yr = $a_date[0];
        $ToDate = mktime(0, 0, 0, $mn, $dy, $yr);
        $FromDate = $ToDate - (7 * 24 * 60 * 60); //Subtract 7 days
        $Todate = strftime("%Y%m%d", $ToDate);
        $Fromdate = strftime("%Y%m%d", $FromDate);

        try {
             $query = "UPDATE times
                        SET
							submitdate = now(),
							status = '20',
							changeby = '$user'
						WHERE workdate >= $Fromdate 
            				and workdate <= $Todate
            				and status = '10'
            				and users_id = '$sus' ";

            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);

        } catch (Exception $e) {
            throw $e;
        }
    }
    
    
    public function submitTimeCard($uid) {
    
    	if ($uid == '')
    		throw new iInvalidArgumentException();
    	
    	$suid = $this->escapeString($uid);    	
    	$TR = $this->fetchRow('times', 'uid', $suid);
    	
    	if ($TR['status'] != '10')     // If Time is in entered status, cannot be submitted.
    		throw new iBLError('status', 'er0105');

    	$user = loggedUserID();
    	
    	$UserActionAuthorization = new UserActionAuthorization();
    	if (!$UserActionAuthorization->chkAuthorityLevel('SubmitTimeCard', $TR['createby'], $TR['users_id']))
    		throw new iBLError('users_id', 'er0080');
    
    	try {
    		$query = "UPDATE times
    					SET
    						submitdate = now(),
    						status = '20',
    						changeby = '$user'
    					WHERE uid = '$suid'";
    
    		$conn = $this->getConnection();
    		$conn->query($query);
    		$this->chkQueryError($conn, $query);
    
    	} catch (Exception $e) {
    		throw $e;
    	}
    }
    
    public function approveTimeCard($uid) {
    
    	if ($uid == '')
    		throw new iInvalidArgumentException();
    
    	$suid = $this->escapeString($uid);
    	$TR = $this->fetchRow('times', 'uid', $suid);
    	 
    	if ($TR['status'] != '20')     // If Time is in submitted status, cannot be approved.
    		throw new iBLError('status', 'er0106');
    	
    	$UserActionAuthorization = new UserActionAuthorization();
    	if (!$UserActionAuthorization->chkAuthorityLevel('ApproveTimeCard', $TR['createby'], $TR['users_id']))
    		throw new iBLError('users_id', 'er0080');
    	
    	$user = loggedUserID();
    
    	try {
    		$query = "UPDATE times
    					SET
    						approvedate = now(),
    						status = '30',
    						changeby = '$user'
    					WHERE uid = '$suid'";
    
    		$conn = $this->getConnection();
    		$conn->query($query);
    		$this->chkQueryError($conn, $query);
    
    	}
    
    	catch (Exception $e) {
    		throw $e;
    	}
    }
    

    public function approveWeeklyTime($us, $weekenddate) {
    
    	if ($us == ''
    			or $weekenddate == '')
    		throw new iInvalidArgumentException();
    
    	if (!isValidDate($weekenddate, 'ymd'))
    		throw new iInvalidDataException();
    	
    	$UserActionAuthorization = new UserActionAuthorization();
    	if (!$UserActionAuthorization->chkAuthorityLevel('ApproveTimeCard', $us, $us))
    		throw new iBLError('users_id', 'er0080');
    
    	$sus = $this->escapeString($us);
    	$swdate = $this->escapeString($weekenddate);
    	$user = loggedUserID();
    
    	$a_date = explode('-', $swdate);
    	$mn = $a_date[1];
    	$dy = $a_date[2];
    	$yr = $a_date[0];
    	$ToDate = mktime(0, 0, 0, $mn, $dy, $yr);
    	$FromDate = $ToDate - (7 * 24 * 60 * 60); //Subtract 7 days
    	$Todate = strftime("%Y%m%d", $ToDate);
    	$Fromdate = strftime("%Y%m%d", $FromDate);
    
    	try {
    		$query = "UPDATE times
	    					SET
    							approvedate = now(),
    							status = '30',
    							changeby = '$user'
    						WHERE workdate >= $Fromdate
    							and workdate <= $Todate
    							and status = '20'
    							and users_id = '$sus' ";
    
			$conn = $this->getConnection();
			$conn->query($query);
			$this->chkQueryError($conn, $query);

    	} catch (Exception $e) {
    		throw $e;
    	}
	}

    
    public function HoldTimeCard($uid) {
    	
        $suid = $this->escapeString($uid);

        $TR = $this->fetchRow('times', 'uid', $suid);

        if ($TR['status'] >= '30')     // If Time is already approved, cannot hold.
            throw new iBLError('status', 'er0055');

        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('HoldTimeCard', $TR['createby'], $TR['users_id']))
        	throw new iBLError('users_id', 'er0080');

        $user = loggedUserID();
        
        try {
        	$query = "UPDATE times
        				SET
        					status = '80',
        					changeby = '$user'
        					WHERE uid ='$suid' ";
        
        	$conn = $this->getConnection();
        	$conn->query($query);
        	$this->chkQueryError($conn, $query);
        
        } catch (Exception $e) {
        	throw $e;
        }
    }

    public function ReleaseTimeCard($uid) {
    	
        $suid = $this->escapeString($uid);

        $TR = $this->fetchRow('times', 'uid', $suid);

        if ($TR['status'] <> '80') // If Time is not held, cannot be released.
            throw new iBLError('status', 'er0070');

        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('ReleaseTimeCard', $TR['createby'], $TR['users_id']))
        	throw new iBLError('users_id', 'er0080');
        
        $user = loggedUserID();
        try {
        	$query = "UPDATE times
        				SET
        					status = '10',
        					changeby = '$user'
        					WHERE uid ='$suid' ";
        
        	$conn = $this->getConnection();
        	$conn->query($query);
        	$this->chkQueryError($conn, $query);
        
        } catch (Exception $e) {
        	throw $e;
        }

    }
    

    public function countHours($sumVariable, $where='') {
    	
        $query = "SELECT sum($sumVariable) FROM times";
        if ($where <> '')
            $query .= " WHERE $where";

        $conn = $this->getConnection();
        $results = $conn->query($query);
        $this->chkQueryError($conn, $query);

        $row = $results->fetch_array();
        $results->close();
        return $row[0];
    }

    public function getStatus($uid) {
    	
        $query = "SELECT status FROM times";
        $query .= " WHERE uid =$uid";

        $conn = $this->getConnection();
        $results = $conn->query($query);
        $this->chkQueryError($conn, $query);

        $row = $results->fetch_array();
        $results->close();
        return $row[0];
    }


    public function getEligibleusers($filter) {
    	
        if ($filter == '')
            $filter = '*';

        $loggedinUser = loggedUserID();

        if (($filter == '*')
                and (strtolower($loggedinUser) == 'admin'))
            return '';

        $where = '';

        // Include or list Current User
        if (($filter == '10')
                or ($filter == '20')
                or ($filter == '*'))
            $where .= "users_id = '$loggedinUser'";

        // Include or list all users who are having same user group
        //Get User group First
        if (($filter == '20')
                or ($filter == '*')) {
            $query = "select usergroup
						from users
                       	where users_id = '$loggedinUser' ";

            $dataset = $this->getDatabyQuery($query);
            if ($dataset <> NULL)
                $usergroup = $dataset[0]['usergroup'];

            if ($usergroup <> NULL) {
                $query = "select users_id
                            from users
                           	where usergroup = '$usergroup' ";

                $dataset = $this->getDatabyQuery($query);
                if ($dataset <> NULL)
                    foreach ($dataset as $dataIndex => $dataRow)
                        $where .= " or users_id = '{$dataset[$dataIndex]['users_id']}'";
            }
        }


        // Include or list all users who are reporting to current user
        if (($filter == '30')
                or ($filter == '*')) {

            $query = "select users_id
						from users
                       	where reportto = '$loggedinUser' ";

            $dataset = $this->getDatabyQuery($query);
            if ($dataset <> NULL) {
                foreach ($dataset as $dataIndex => $dataRow) {
                    if ($where <> NULL)
                        $where .= " or ";
                    $where .= "users_id = '{$dataset[$dataIndex]['users_id']}'";
                }
            }
        }

        if ($where <> '')
            $where = "($where)";

        return $where;
    }
    
    
    
    public function getUserFullName($users_id) {
    	
    	$User = new User($users_id);
    	return $User->getFullName();
    }
    
    
    public function getTimeDetailRecs($projects_id, $users_id, $weekbegindate, $weekenddate) {
    	
    	if ($projects_id == '')
			$query = "select times.workdate as workdate,
    						projects.name as project,
    						tasks.name as task,
    						times.nonbillablehours as nonbillablehours,
    						times.billablehours as billablehours,
    						times.nonbillablehours + times.billablehours as totalhours,
    						times.description as description
    					from times, projects, tasks
    					where times.projects_id = projects.projects_id
    						and times.tasks_id = tasks.tasks_id
    						and times.workdate >= '$weekbegindate'
    						and times.workdate <= '$weekenddate'
							and times.users_id = '$users_id'
    					order by times.workdate, times.projects_id, times.tasks_id";
    	else
    		$query = "select times.workdate as workdate,
    						projects.name as project,
    						tasks.name as task,
    						times.nonbillablehours as nonbillablehours,
    						times.billablehours as billablehours,
    						times.nonbillablehours + times.billablehours as totalhours,
    						times.description as description
    						from times, projects, tasks
    					where times.projects_id = projects.projects_id
    						and times.tasks_id = tasks.tasks_id
    						and times.projects_id = '$projects_id'
    						and times.workdate >= '$weekbegindate'
							and times.workdate <= '$weekenddate'
							and times.users_id = '$users_id'
    					order by times.workdate, times.projects_id, times.tasks_id";
    
    		$dataset = $this->getDatabyQuery($query);
    		return $dataset;
    }
    
    
    

}

?>