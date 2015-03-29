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

class ExpenseDetailData extends __DBCommonFunctions {

	public $uid;
	public $users_id;
	public $projects_id;
	public $weekenddate;
	public $expensedate;
	public $expensecategories_id;
	public $description;
	public $amount;
	public $invoices_id;
	public $mile;
	public $comment;
	public $receipt;
	public $status;
	public $createat;
	public $createby;
	public $changeat;
	public $changeby;
		
	public function getExpenseDetail($uid) {

		$where = (empty($uid)) ? "ed.uid is NULL" : "ed.uid=$uid";
		
		$query = "select ed.*, eh.status from expensedetails ed, expenseheaders eh
					where $where
					  and ed.users_id = eh.users_id
					  and ed.projects_id = eh.projects_id
					  and ed.weekenddate = eh.weekenddate";
		
		$expDetailObj = $this->getDataObj($query)->fetch_object();
		return ($expDetailObj == NULL) ? $this : $expDetailObj;
		
	}
	

    public function createRow($usid, $pjid, $wedt, $esdt, $catg, $desc, $cmnt, $amnt, $mile, $receipt) {

    	global $basedir;
    	
    	if ($usid == ''
			or $pjid == ''
			or $wedt == ''
			or $esdt == ''
			or $catg == ''
        	)
            throw new iInvalidArgumentException();

        if (!isValidDate($wedt, 'ymd'))
            throw new iInvalidDataException();

        if (!isValidDate($esdt, 'ymd'))
            throw new iInvalidDataException();

        $susid = $this->escapeString($usid);
        $spjid = $this->escapeString($pjid);
        $swedt = $this->escapeString($wedt);
        $sesdt = $this->escapeString($esdt);
        $scatg = $this->escapeString($catg);
        $sdesc = $this->escapeString($desc);
        $samnt = $this->escapeString($amnt);
        $srcpt = $this->escapeString($receipt);
        
        if ($samnt == '') $samnt = '0';
        $smile = $this->escapeString($mile);
        
        if ($smile == '') $smile = '0';
        $scmnt = $this->escapeString($cmnt);
        $loggedinUser = loggedUserID();

        $wsdt = tesAddDate($swedt, -6);
        
        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('CreateExpenseDetail', "", $susid))
        	throw new iBLError('users_id', 'er0080');

        if (($sesdt < $wsdt)
                or ($sesdt > $swedt))
            throw new iBLError('expensedate', 'er0045');

        $RowData = $this->fetchRow('expensecategories', 'expensecategories_id', $scatg);
        if ($RowData['status'] <> '10')
            throw new iBLError('expensecategories_id', 'er0033', $scatg);

        if (($RowData['ismileage'] == '1')
                and ($smile == '0')) {
            throw new iBLError('mile', 'er0031');
        } else if (($RowData['ismileage'] == '0')
                and ($samnt == '0')) {
            throw new iBLError('amount', 'er0032');
        }

        if ($RowData['ismileage'] == '1')
            $samnt = $smile * $RowData['mileagerate'];

        $RowData = $this->fetchRow('projects', 'projects_id', $spjid);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0043');

        $RowData = $this->fetchRow('accounts', 'accounts_id', $RowData['billtoaccounts_id']);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0044');

        $where = "users_id = '$susid' and projects_id = '$spjid' and weekenddate = '$swedt' ";
        $RowData = $this->fetchRowbyWhereClause('expenseheaders', $where);
        if ($RowData['status'] >= '30')
            throw new iBLError('nocategory', 'er0036');
		
        $Invoicehdr = new InvoiceData();
        $invoiceid = $Invoicehdr->createOpenInvoice($spjid);

        try {
            $query = "INSERT INTO expensedetails
                                  (users_id, projects_id, weekenddate, expensedate, expensecategories_id, description,
                                    amount, mile, comment, receipt, invoices_id, createby, changeby, createat, changeat)
                            VALUES('$susid', '$spjid', '$swedt', '$sesdt', '$scatg', '$sdesc',
                                    '$samnt', '$smile', '$scmnt', '$srcpt', '$invoiceid', '$loggedinUser', '$loggedinUser', now(), now())";
            $conn = $this->getConnection();
            $conn->query($query);
            $recid = $conn->insert_id;
            $this->chkQueryError($conn, $query);

            return $recid;
            
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateRow($uid, $esdt, $catg, $desc, $cmnt, $amnt, $mile, $receipt) {
    	
    	global $basedir;
    	
        if ($uid == ''
                or $esdt == ''
                or $catg == ''
        )
            throw new iInvalidArgumentException();

        $suid = $this->escapeString($uid);
        $sesdt = $this->escapeString($esdt);
        $scatg = $this->escapeString($catg);
        $sdesc = $this->escapeString($desc);
        $scmnt = $this->escapeString($cmnt);
        $samnt = $this->escapeString($amnt);
        $srcpt = $this->escapeString($receipt);
        
        if ($samnt == '')
            $samnt = '0';
        $smile = $this->escapeString($mile);
        if ($smile == '')
            $smile = '0';
        $loggedinUser = loggedUserID();

        $ED = $this->fetchRow('expensedetails', 'uid', $suid);
        
        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('EditExpenseDetail', $ED['createby'], $ED['users_id']))
        	throw new iBLError('users_id', 'er0080');
 
        $wsdt = tesAddDate($ED['weekenddate'], -6);

        if (($sesdt < $wsdt)
                or ($sesdt > $ED['weekenddate']))
            throw new iBLError('expensedate', 'er0045');

        $RowData = $this->fetchRow('expensecategories', 'expensecategories_id', $scatg);
        if ($RowData['status'] <> '10')
            throw new iBLError('expensecategories_id', 'er0033', $scatg);

        if (($RowData['ismileage'] == '1')
                and ($smile == '0')) {
            throw new iBLError('mile', 'er0031');
        } else if (($RowData['ismileage'] == '0')
                and ($samnt == '0')) {
            throw new iBLError('amount', 'er0032');
        }

        if ($RowData['ismileage'] == '1')
            $samnt = $smile * $RowData['mileagerate'];

        $where = "users_id = '{$ED['users_id']}' and projects_id = '{$ED['projects_id']}' and weekenddate = '{$ED['weekenddate']}' ";
        $EH = $this->fetchRowbyWhereClause('expenseheaders', $where);
        if ($EH['status'] >= '30')
            throw new iBLError('nocategory', 'er0036');
		
        if (($srcpt != NULL) && ($ED['receipt'] != NULL) && file_exists("$basedir/{$ED['receipt']}"))
        	unlink("$basedir/{$ED['receipt']}");
        
        $srcpt = (($srcpt == NULL) && ($ED['receipt'] != NULL)) ? $ED['receipt'] : $srcpt;
        
        try {
            $query = "UPDATE expensedetails
                         SET
                            expensedate = '$sesdt',
                            expensecategories_id = '$scatg',
                            description = '$sdesc',
                            comment = '$scmnt',
                            amount = '$samnt',
                            mile = '$smile',
                            receipt = '$srcpt',
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
    

    public function deleteExpenseDetail($uid) {
    	
    	global $basedir;
        
    	if ($uid == '')
            throw new iInvalidArgumentException();

        $suid = $this->escapeString($uid);

        $where = "uid = '$suid' ";
        $ED = $this->fetchRowbyWhereClause('expensedetails', $where);        

        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('DeleteExpenseDetail', $ED['createby'], $ED['users_id']))
        	throw new iBLError('users_id', 'er0080');

        $where = "users_id = '{$ED['users_id']}' and projects_id = '{$ED['projects_id']}' and weekenddate = '{$ED['weekenddate']}' ";
        $EH = $this->fetchRowbyWhereClause('expenseheaders', $where);
        if ($EH['status'] >= '30')
            throw new iBLError('nocategory', 'er0036');
        
        if (($ED['receipt'] != NULL) && file_exists("$basedir/{$ED['receipt']}"))
        	unlink("$basedir/{$ED['receipt']}");

        parent::deleteRow('expensedetails', $uid);
    }
    

    public function GetTotalExpense($users_id, $projects_id, $weekenddate) {
        $query = "select sum(ed.amount) as totalamount
				from expensedetails ed
				where ed.users_id = '$users_id'
				  and ed.projects_id = '$projects_id'
				  and ed.weekenddate = '$weekenddate'";

        $RowData = $this->getDatabyQuery($query);
        return ($RowData == NULL) ? 0 : $RowData[0]['totalamount'];
    }

    public function GetStatus($users_id, $projects_id, $weekenddate) {
        $query = "select status
                    from expenseheaders
                    where users_id = '$users_id'
			  and projects_id = '$projects_id'
			  and weekenddate = '$weekenddate'";

        $RowData = $this->getDatabyQuery($query);
        return ($RowData == NULL) ? 0 : $RowData[0]['status'];
    }

}

?>