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

class ExpenseHeaderData extends __DBCommonFunctions {
	
	public $uid;
	public $weekenddate;
	public $description;
	public $comment;
	public $location;
	public $status;
	public $submitdate;
	public $approvedate;
	public $invoicedate;
	public $invoices_id;
	public $projects_id;
	public $users_id;
	public $createat;
	public $createby;
	public $changeat;
	public $changeby;
	
	public function getExpenseHeader($uid) {
			
		$expHeaderObj = $this->fetchRowObjforView("expenseheaders", "uid", $uid);
		return ($expHeaderObj == NULL) ? $this : $expHeaderObj;
		
	}

	
    public function createRow($usid, $pjid, $wedt, $desc, $cmnt, $locn) {

        $weekendday = $_SESSION['company']['weekendday'];

        if ($usid == ''
           or $pjid == ''
           or $wedt == ''
           or $desc == ''
           )
            throw new iInvalidArgumentException();

        if (!validWeekendDateIso($wedt, $weekendday))
            throw new iInvalidDataException();

        $susid = $this->escapeString($usid);
        $spjid = $this->escapeString($pjid);
        $swedt = $this->escapeString($wedt);
        $sdesc = $this->escapeString($desc);
        $scmnt = $this->escapeString($cmnt);
        $slocn = $this->escapeString($locn);
        $loggedinUser = loggedUserID();

        $where = "projects_id = '$spjid' and users_id = '$susid' ";
        $RowData = $this->fetchRowbyWhereClause('projects_users', $where);
        if ($RowData['status'] <> '10')
            throw new iBLError('users_id', 'er0034');

        $RowData = $this->fetchRow('projects', 'projects_id', $spjid);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0043');

        $RowData = $this->fetchRow('accounts', 'accounts_id', $RowData['billtoaccounts_id']);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0044');

        $Invoicehdr = new InvoiceData();
        $invoiceid = $Invoicehdr->createOpenInvoice($spjid);
        
        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('CreateExpenseHeader', "", $susid))
        	throw new iBLError('users_id', 'er0080');
        
        try {
            $query = "INSERT INTO expenseheaders
                                        (users_id, projects_id, weekenddate, description, comment, location,
                                        status, invoices_id, createby, changeby, createat, changeat)
                                VALUES('$susid', '$spjid', '$swedt', '$sdesc', '$scmnt', '$slocn',
                                        '10', '$invoiceid', '$loggedinUser', '$loggedinUser', now(), now())";

            $conn = $this->getConnection();
            $conn->query($query);
            $recid = $conn->insert_id;
            $this->chkQueryError($conn, $query);

            return $recid;

        } catch (Exception $e) {
            throw $e;
        }
    }

    
    public function updateRow($uid, $desc, $cmnt, $locn) {

        if ($uid == ''
           or $desc == ''
           )
            throw new iInvalidArgumentException();

        $suid = $this->escapeString($uid);
        $sdesc = $this->escapeString($desc);
        $scmnt = $this->escapeString($cmnt);
        $slocn = $this->escapeString($locn);

        $where = "uid = '$suid' ";
        $EH = $this->fetchRowbyWhereClause('expenseheaders', $where);
        if ($EH['status'] >= '30')
            throw new iBLError('nocategory', 'er0066');

        $loggedinUser = loggedUserID();

        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('EditExpenseHeader', $EH['createby'], $EH['users_id']))
        	throw new iBLError('users_id', 'er0080');

        try {
            $query = "UPDATE expenseheaders
                         SET
                            description = '$sdesc',
                            comment = '$scmnt',
                            location = '$slocn',
                            changeby ='$loggedinUser',
                            changeat= now()
                      WHERE uid = '$uid'";

            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);

        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function deleteExpense($uid) {
    
    	if ($uid == '')
    		throw new iInvalidArgumentException();
    
    	$suid = $this->escapeString($uid);
    
    	$where = "uid = '$suid' ";
    	$EH = $this->fetchRowbyWhereClause('expenseheaders', $where);
    
    	if ($EH['status'] >= '30')
    		throw new iBLError('nocategory', 'er0071');
    
    	$UserActionAuthorization = new UserActionAuthorization();
    	if (!$UserActionAuthorization->chkAuthorityLevel('DeleteExpenseHeader', $EH['createby'], $EH['users_id']))
    		throw new iBLError('users_id', 'er0080');
    
    	parent::deleteRow('expenseheaders', $uid);
    }

    
    public function submitExpense($uid) {
    	
        if ($uid == '')
            throw new iInvalidArgumentException();

        $suid = $this->escapeString($uid);
        $where = "uid = '$suid' ";
        $RowData = $this->fetchRowbyWhereClause('expenseheaders', $where);
        if ($RowData['status'] >= '20')
            throw new iBLError('nocategory', 'er0035');
        
        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('SubmitExpenseHeader', $RowData['createby'], $RowData['users_id']))
        	throw new iBLError('users_id', 'er0080');

        $where = "users_id = '{$RowData['users_id']}' and projects_id = '{$RowData['projects_id']}' and weekenddate = '{$RowData['weekenddate']}'";
        $detailCount = $this->countRows('expensedetails', $where);
        if ($detailCount == 0)
            throw new iBLError('nocategory', 'er0064');

        $RowData = $this->fetchRow('projects', 'projects_id', $RowData['projects_id']);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0043');

        $RowData = $this->fetchRow('accounts', 'accounts_id', $RowData['accounts_id']);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0044');

        $loggedinUser = loggedUserID();

        try {
            $query = "UPDATE expenseheaders
						SET
                             status = '20',
                             submitdate= now(),
                             changeby ='$loggedinUser',
                             changeat= now()
                       WHERE uid = '$uid'";

            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);

        } catch (Exception $e) {
            throw $e;
        }
    }

    
    public function verifyExpense($uid) {

        if ($uid == '')
            throw new iInvalidArgumentException();

        $suid = $this->escapeString($uid);
        $where = "uid = '$suid' ";
        $RowData = $this->fetchRowbyWhereClause('expenseheaders', $where);
        if ($RowData['status'] >= '30')
            throw new iBLError('nocategory', 'er0040');
        
        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('VerifyExpenseHeader', $RowData['createby'], $RowData['users_id']))
        	throw new iBLError('users_id', 'er0080');

        if ($RowData['status'] <> '20')
            throw new iBLError('nocategory', 'er0037');

        $where = "users_id = '{$RowData['users_id']}' and projects_id = '{$RowData['projects_id']}' and weekenddate = '{$RowData['weekenddate']}'";
        $detailCount = $this->countRows('expensedetails', $where);
        if ($detailCount == 0)
            throw new iBLError('nocategory', 'er0065');

        $RowData = $this->fetchRow('projects', 'projects_id', $RowData['projects_id']);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0043');

        $RowData = $this->fetchRow('accounts', 'accounts_id', $RowData['accounts_id']);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0044');

        $loggedinUser = loggedUserID();


        try {
            $query = "UPDATE expenseheaders
						 SET
       						 status = '30',
       						 approvedate = now(),
       						 changeby = '$loggedinUser',
       						 changeat= now()
       				   WHERE uid = '$uid'";

            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);

        } catch (Exception $e) {
            throw $e;
        }
    }

    
    public function HoldExpense($uid) {
        $suid = $this->escapeString($uid);

        $RowData = $this->fetchRow('expenseheaders', 'uid', $suid);
        
        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('HoldExpenseHeader', $RowData['createby'], $RowData['users_id']))
        	throw new iBLError('users_id', 'er0080');

        if ($RowData['status'] >= '30')         // If Expense is already approved, cannot hold.
            throw new iBLError('nocategory', 'er0074');

        $this->UpdateStatusByUid($suid, '80');

        return;
    }

    
    public function ReleaseExpense($uid) {
        $suid = $this->escapeString($uid);

        $RowData = $this->fetchRow('expenseheaders', 'uid', $suid);
        
        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('ReleaseExpenseHeader', $RowData['createby'], $RowData['users_id']))
        	throw new iBLError('users_id', 'er0080');

        if ($RowData['status'] <> '80')         // If Expense is not held, cannot be released.
            throw new iBLError('nocategory', 'er0075');

        $this->UpdateStatusByUid($suid, '10');

        return;
    }

    
    public function UpdateStatusByUid($uid, $newStatus) {
        $suid = $this->escapeString($uid);
        $user = loggedUserID();

        try {
            $query = "UPDATE expenseheaders
			 			SET
                            status = $newStatus,
			     			changeby = '$user'
                       WHERE uid ='$suid' ";

            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);

        } catch (Exception $e) {
            throw $e;
        }
    }

    
    public function GetTotalExpense($uid) {

        $suid = $this->escapeString($uid);
        $query = "select sum(ed.amount) as totalamount
					from expensedetails ed, expenseheaders eh
					where eh.users_id = ed.users_id
					  and eh.projects_id = ed.projects_id
					  and eh.weekenddate = ed.weekenddate
					  and eh.uid = '$suid'";

        $RowData = $this->getDatabyQuery($query);
        return ($RowData == NULL) ? 0 : $RowData[0]['totalamount'];
    }
    
    
    public function getExpenseHeaderForPrint($users_id, $projects_id, $weekenddate) {
    	
    	$query = "select users.fullname as UserName,
						projects.name as project,
						accounts.name as account
					from expenseheaders, users, projects, accounts
					where expenseheaders.users_id = users.users_id
						and expenseheaders.projects_id = projects.projects_id
						and projects.accounts_id = accounts.accounts_id
						and expenseheaders.users_id = '$users_id'
						and expenseheaders.projects_id = '$projects_id'
						and expenseheaders.weekenddate = '$weekenddate'";
    
    	$dataset = $this->getDatabyQuery($query);
    	return $dataset[0];
    }
    
    
    public function getExpenseDetailsForPrint($users_id, $projects_id, $weekenddate) {
    	
    	$query = "select expensecategories.seq,
    					expensecategories.expensecategories_id,
    					datediff(expensedate, date('$weekenddate')) + 6 as dayindex,
    					sum(amount) as amount
    				from expensecategories
    					left outer join expensedetails
    						on expensecategories.expensecategories_id = expensedetails.expensecategories_id
    				where expensedetails.users_id = '$users_id'
    					and expensedetails.projects_id = '$projects_id'
    					and expensedetails.weekenddate = '$weekenddate'
    				group by expensecategories.seq, expensedetails.expensedate
    				order by expensecategories.seq, dayindex";
    
    	$dataset = $this->getDatabyQuery($query);
    	return $dataset;
    }
    

}

?>