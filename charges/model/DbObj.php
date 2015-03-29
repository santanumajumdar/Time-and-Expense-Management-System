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

class ChargeData extends __DBCommonFunctions {
	
	public $uid;
	public $weekenddate;
	public $chargedate;
	public $description;
	public $comments;
	public $charges;
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

	public function getCharge($uid)	{
	
		$chargeObj = $this->fetchRowObjforView("charges", "uid", $uid);
		return ($chargeObj == NULL) ? $this : $chargeObj;
		
	}
	
	
	public function createRow($us, $projid, $cdate, $charges, $tdesc, $tcomment) {
        
        $times_status_array = $this->getOptionArray('times', 'status');
        
        $weekendday = $_SESSION['company']['weekendday'];

        if ($us == ''
            or $projid == ''
			or $cdate == ''
			or $charges == ''
			or $tdesc == '')
				throw new iInvalidArgumentException();

        if ($charges == '') $charges = 0;

        if (($charges) == 0) {
            throw new iBLError('charges', 'er0087');
        }

        if (!isValidDate($cdate, 'ymd'))
            throw new iInvalidDataException();

        $sus = $this->escapeString($us);
        $sprojid = $this->escapeString($projid);
        $scharges = $this->escapeString($charges);
        $stdesc = $this->escapeString($tdesc);
        $stcomment = $this->escapeString($tcomment);
        $scdate = $this->escapeString($cdate);
        $user = loggedUserID();

        $RowData = $this->fetchRow('projects', 'projects_id', $sprojid);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0043');

        $RowData = $this->fetchRow('accounts', 'accounts_id', $RowData['accounts_id']);
        if ($RowData['status'] <> '10')
            throw new iBLError('projects_id', 'er0044');

        $whereclause = "projects_id = '$sprojid' and users_id = '$sus'";
        $RowData = $this->fetchRowbyWhereClause('projects_users', $whereclause);
        if ($RowData == NULL)
            throw new iBLError('users_id', 'er0019');                      
        
        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('CreateCharge', "", $sus))
        	throw new iBLError('users_id', 'er0080');

		$wedate = getWeekEndDate($scdate, $weekendday);

        $Invoicehdr = new InvoiceData();
        $invoiceid = $Invoicehdr->createOpenInvoice($sprojid);
        try {
            $query = "INSERT INTO charges (weekenddate, chargedate, description, comments, charges, status, 
            					 createby, createat, changeby, changeat, invoices_id, projects_id, users_id)
                        VALUES('$wedate', '$scdate', '$stdesc', '$stcomment', '$scharges', '10', 
                        		'$user', now(), '$user', now(), '$invoiceid', '$sprojid', '$sus')";

            $conn = $this->getConnection();
            $conn->query($query);
            $recid = $conn->insert_id;
            $this->chkQueryError($conn, $query);
            return $recid;
            
        } 
        catch (Exception $e) {
            throw $e;
        }
    }

    public function updateRow($uid, $cdate, $desc, $charges, $comment)
    {
        $weekendday = $_SESSION['company']['weekendday'];

        if ($uid == ''
        	or $cdate == ''
            or $desc == ''
            or $charges == '')
                throw new iInvalidArgumentException();

        if (!isValidDate($cdate, 'ymd'))
            throw new iInvalidDataException();

        if ($charges == 0)
            throw new iBLError('charges', 'er0087');

        $scdate = $this->escapeString($cdate);
        $sdesc = $this->escapeString($desc);
        $scharges = $this->escapeString($charges);
        $scomment = $this->escapeString($comment);
        $user = loggedUserID();
        $weekenddate = getWeekEndDate($scdate, $weekendday);


        $where = "uid = '$uid' ";
        $charge = $this->fetchRowbyWhereClause('charges', $where);

        if ($charge['status'] >= '30')
            throw new iBLError('nocategory', 'er0072');

        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('EditCharge', $charge['createby'], $charge['users_id']))
        	throw new iBLError('users_id', 'er0080');

        try {
            $query = "UPDATE charges
			 SET
				chargedate ='$scdate',
				weekenddate ='$weekenddate',
				description ='$sdesc',
				comments ='$scomment',
				charges ='$scharges',
				changeby = '$user'
			WHERE uid = '$uid'";

            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);
        } 
        
        catch (Exception $e) {
            throw $e;
        }
    }

    public function deleteCharge($uid) {
        
    	if ($uid == '')
            throw new iInvalidArgumentException();

        $suid = $this->escapeString($uid);

        $where = "uid = '$suid' ";
        $charge = $this->fetchRowbyWhereClause('charges', $where);

        if ($charge['status'] >= '30')
            throw new iBLError('nocategory', 'er0071');
        
        $UserActionAuthorization = new UserActionAuthorization();
        if (!$UserActionAuthorization->chkAuthorityLevel('DeleteCharge', $charge['createby'], $charge['users_id']))
        	throw new iBLError('users_id', 'er0080');

        parent::deleteRow('charges', $uid);
    }

    
    public function SubmitCharge($uid) {
    	
    	$suid = $this->escapeString($uid);
    
    	$charge = $this->fetchRow('charges', 'uid', $suid);
    
    	if ($charge['status'] >= '20')     // If it is already submitted
    		throw new iBLError('status', 'er0101');

    	$UserActionAuthorization = new UserActionAuthorization();
    	if (!$UserActionAuthorization->chkAuthorityLevel('SubmitCharge', $charge['createby'], $charge['users_id']))
    		throw new iBLError('users_id', 'er0080');
    
    	$this->UpdateStatusByUid($suid, '20');
    }

    
    public function ApproveCharge($uid) {
    	$suid = $this->escapeString($uid);
    
    	$charge = $this->fetchRow('charges', 'uid', $suid);
    
    	if ($charge['status'] >= '30')     // If it is already approved
    		throw new iBLError('status', 'er0102');
    
    	$UserActionAuthorization = new UserActionAuthorization();
    	if (!$UserActionAuthorization->chkAuthorityLevel('ApproveCharge', $charge['createby'], $charge['users_id']))
    		throw new iBLError('users_id', 'er0080');
    
    	$this->UpdateStatusByUid($suid, '30');
    }
    
    public function HoldCharge($uid) {
    	
        $suid = $this->escapeString($uid);

        $charge = $this->fetchRow('charges', 'uid', $suid);

        if ($charge['status'] >= '30')     // If it is already approved, cannot hold.
            throw new iBLError('status', 'er0055');

    	$UserActionAuthorization = new UserActionAuthorization();
    	if (!$UserActionAuthorization->chkAuthorityLevel('HoldCharge', $charge['createby'], $charge['users_id']))
    		throw new iBLError('users_id', 'er0080');

        $this->UpdateStatusByUid($suid, '80');
    }
    

    public function ReleaseCharge($uid) {
    	
        $suid = $this->escapeString($uid);

        $charge = $this->fetchRow('charges', 'uid', $suid);

        if ($charge['status'] <> '80') // If it is not held, cannot be released.
            throw new iBLError('status', 'er0070');

    	$UserActionAuthorization = new UserActionAuthorization();
    	if (!$UserActionAuthorization->chkAuthorityLevel('ReleaseCharge', $charge['createby'], $charge['users_id']))
    		throw new iBLError('users_id', 'er0080');

        $this->UpdateStatusByUid($suid, '10');
    }
    

    public function UpdateStatusByUid($uid, $newStatus) {
        $suid = $this->escapeString($uid);
        $user = loggedUserID();

        try {
            $query = "UPDATE charges
						SET
                             status = $newStatus,
                             changeby = '$user'
						WHERE uid ='$suid' ";

            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);
        } 
        
        catch (Exception $e) {
            throw $e;
        }
    }


    public function getStatus($uid) {
        $query = "SELECT status FROM charges";
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

}

?>