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
require_once("$basedir/core/controller/UserActionAuthorization.php");

class InvoiceData extends __DBCommonFunctions {
	
	
	public function getInvoice($uid) {
		
		$where = (empty($uid)) ? "i.uid is NULL" : "i.uid=$uid";
		
		$query = "select i.invoices_id, i.invoicedate, i.projects_id, i.begindate, i.enddate, p.billcycle, i.status,
						ifNULL((select sum(t.billablehours) from times t where t.invoices_id = i.invoices_id), 0) as billablehours,
						ifNULL((select sum(t.nonbillablehours) from times t where t.invoices_id = i.invoices_id), 0) as nonbillablehours,
						ifNULL((select billablehours + nonbillablehours), 0) as totalhours,
						ifNULL((select sum(t.billablehours * t.rate) from times t where t.invoices_id = i.invoices_id), 0) as ta,
						format((select ta), 2) as totalamount,
						ifNULL((select sum(c.charges) from charges c where c.invoices_id = i.invoices_id), 0) as charges,
						ifNULL((select sum(ed.amount) from expensedetails ed where ed.invoices_id = i.invoices_id), 0) as expenses,
						format((select ta + charges + expenses), 2) as invoicetotal
					from invoiceheaders i, projects p
					where i.projects_id = p.projects_id
						and $where";
	
		$invoiceData = $this->getDataObj($query)->fetch_object();
		return $invoiceData;

	}

	
    public function CreateInvoice($projects_id) {

        if ($projects_id == '')
            throw new iInvalidArgumentException();

        $recid = '';
        $invoicePeriod = array(
                        'begindate' => NULL,
                        'enddate' => NULL);

        $sAccountId = $this->escapeString($projects_id);
        $InvoiceId = "@OpenInv" . $projects_id;
        $loggedinUser = loggedUserID();

        do {
            $invoicePeriod = $this->findInvoicePeriod($projects_id, $invoicePeriod['enddate']);
            if ($invoicePeriod['enddate'] == NULL)
                throw new iBLError('nocategory', 'er0077');;

            $where = "invoices_id = '$InvoiceId' and weekenddate <= '{$invoicePeriod['enddate']}' and status <= '30'";
            $expRecs = $this->countRows('expenseheaders', $where);
            $timeRecs = $this->countRows('times', $where);
            $chargeRecs = $this->countRows('charges', $where);
        } while (($expRecs + $timeRecs + $chargeRecs) == 0);

        //See if any time or expense records are not approved and not on hold.

        $errorid = $this->checkUnapprovedEntry($projects_id, $invoicePeriod['enddate']);
        if ($errorid <> NULL)
            throw new iBLError('nocategory', $errorid);

        try {

        	$this->beginTransaction();
        	if (($recid = $this->CreateNewInvoice($projects_id, $invoicePeriod)) == NULL)
				return NULL;
				
            // Update projects for last bill date
            $query = "UPDATE projects
                      SET
							lastbilldate = '{$invoicePeriod['enddate']}',
							changeat = now(),
							changeby = '$loggedinUser'
                    WHERE projects_id = '$projects_id'";

            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);
            
            $query = "select a.lastbilldate 
            			from accounts a 
            			where a.accounts_id = (select p1.billtoaccounts_id 
            									from projects p1 
            									where p1.projects_id = '$projects_id')";
            $accountData = $this->getDatabyQuery($query);
            if ($invoicePeriod['enddate'] > $accountData[0]['lastbilldate']) {
	            $query = "UPDATE accounts a
    		                  SET
								a.lastbilldate = '{$invoicePeriod['enddate']}',
								a.changeat = now(),
								a.changeby = '$loggedinUser'
                    	WHERE a.accounts_id = (select p.billtoaccounts_id from projects p where p.projects_id = '$projects_id')";

	            $conn = $this->getConnection();
    	        $conn->query($query);
        	    $this->chkQueryError($conn, $query);
            }
            
            $this->DeleteOpenInvoice($InvoiceId);

            $this->commitTransaction();
        }
        
        catch (Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }

        return $recid;
    }

    protected function CreateNewInvoice($projects_id, $invoicePeriod) {

        // First check if there is any record exist in this time period to be invoiced

        $where = " projects_id = '$projects_id' and weekenddate <= '{$invoicePeriod['enddate']}' and status = '30'";

        if ($this->countRows('times', $where) 
        	+ $this->countRows('charges', $where)
        	+ $this->countRows('expenseheaders', $where) == 0)
            return NULL;

        // Elligible record exists to be invoiced -- so proceed further

        $loggedinUser = loggedUserID();
        $InvoiceId = "@OpenInv" . $projects_id;
        
        // Determine the next invoice number.
        $newInvoiceid = $this->nextInvoiceNumber($projects_id);

        // Write new Invoice Header
        $query = "INSERT INTO invoiceheaders (projects_id, invoices_id, begindate,
                                              enddate, invoicedate, status,
                                              createat, createby, changeat, changeby)
				VALUES('$projects_id', '$newInvoiceid', '{$invoicePeriod['begindate']}',
                                       '{$invoicePeriod['enddate']}', curdate(), '90',
                                       now(), '$loggedinUser', now(), '$loggedinUser')";

        $this->beginTransaction();
        try {
        	$conn = $this->getConnection();
    	    $conn->query($query);
   	    	$recid = $conn->insert_id;
  	      	$this->chkQueryError($conn, $query);

        	//update times records

        	$query = "UPDATE times t
                      left join projects_users pu
                             on pu.projects_id = t.projects_id
                            and pu.users_id = t.users_id
                            and pu.effective_date = (select max(pu1.effective_date) from projects_users pu1
                                                                  	where pu1.projects_id = pu.projects_id
                            										  and pu1.users_id = pu.users_id
                            										  and pu1.status = '10'
                                                                      and pu1.effective_date <= t.workdate)
                      left join projects_users_tasks put
                             on put.projects_id = t.projects_id
                            and put.users_id = t.users_id
                            and put.tasks_id = t.tasks_id
                            and put.effective_date = (select max(put1.effective_date) from projects_users_tasks put1
                                                                  	where put1.projects_id = put.projects_id
                            										  and put1.users_id = put.users_id
                            										  and put1.status = '10'
                            										  and put1.tasks_id = t.tasks_id
                                                                      and put1.effective_date <= t.workdate)
                                                                      
			SET
				t.invoices_id = '$newInvoiceid',
				t.rate = (select if (put.rate > 0, put.rate, pu.rate)),
				t.status = '90',
				t.changeat = now(),
				t.changeby = '$loggedinUser'
			WHERE t.invoices_id = '$InvoiceId'
			  and t.workdate <= '{$invoicePeriod['enddate']}'
			  and t.status = '30'";

        	$conn->query($query);
        	$this->chkQueryError($conn, $query);
        
        	// Update charges

        	$query = "UPDATE charges
			 		SET
						invoices_id = '$newInvoiceid',
						InvoiceDate = curdate(),
						status = '90',
						changeat = now(),
						changeby = '$loggedinUser'
                    WHERE invoices_id = '$InvoiceId'
                        and chargedate <= '{$invoicePeriod['enddate']}'
                        and status = '30'";

        	$conn->query($query);
        	$this->chkQueryError($conn, $query);

     	   	// Update expense Header

        	$query = "UPDATE expenseheaders
						 SET
                            invoices_id = '$newInvoiceid',
                            InvoiceDate = curdate(),
                            status = '90',
                            changeat = now(),
                            changeby = '$loggedinUser'
                    WHERE invoices_id = '$InvoiceId'
                        and weekenddate <= '{$invoicePeriod['enddate']}'
                        and status = '30'";

        	$conn->query($query);
        	$this->chkQueryError($conn, $query);

       		// Update expense Details

        	$query = "UPDATE expensedetails ED
                        SET
                            ED.invoices_id = '$newInvoiceid',
                            ED.changeat = now(),
                            ED.changeby = '$loggedinUser'
                      WHERE exists (select * from expenseheaders EH
                                    where EH.users_id = ED.users_id
                                        and EH.projects_id = ED.projects_id
                                        and EH.weekenddate = ED.weekenddate
                                        and EH.invoices_id = '$newInvoiceid')";

        	$conn->query($query);
        	$this->chkQueryError($conn, $query);
        	
        	$this->commitTransaction();
        	 
        } catch (Exception $e) {
        		$this->rollbackTransaction();
        		throw $e;
        }

        return $recid;
    }

    public function createOpenInvoice($invFor) {

        $user = loggedUserID();

        $openInvoiceId = '@OpenInv' . $invFor;

        //See if open Invoice record exist or not, if not create one

        $whereclause = "invoices_id = '$openInvoiceId'";
        $rowData = $this->fetchRowbyWhereClause('invoiceheaders', $whereclause);

        If ($rowData == NULL) {
            try {
                $query = "INSERT INTO invoiceheaders (invoices_id, projects_id, status, createat, createby, changeby)
                                        VALUES ('$openInvoiceId', '$invFor', '10', now(), '$user', '$user')";

                $conn = $this->getConnection();
                $conn->query($query);
                $this->chkQueryError($conn, $query);

            } catch (Exception $e) {
                throw $e;
            }
        }
        return $openInvoiceId;
    }


    public function DeleteOpenInvoice($InvoiceId) {

        $loggedinUser = loggedUserID();

        //See if any times, charges and  expenses records exists.

        $where = "invoices_id = '$InvoiceId'";

        $RowData = $this->fetchRowbyWhereClause('invoicetimedetails', $where);
        $OpenTimeData = $RowData <> NULL ? TRUE : FALSE;

        $RowData = $this->fetchRowbyWhereClause('invoiceexpensedetails', $where);
        $OpenExpenseDetail = $RowData <> NULL ? TRUE : FALSE;

        $RowData = $this->fetchRow('expenseheaders', 'invoices_id', $InvoiceId);
        $OpenExpenseHeader = $RowData <> NULL ? TRUE : FALSE;
        
        $RowData = $this->fetchRowbyWhereClause('charges', $where);
        $OpenChargeData = $RowData <> NULL ? TRUE : FALSE;

        if (!$OpenTimeData and !$OpenExpenseDetail and !$OpenExpenseHeader and !$OpenChargeData) {
            $where = "invoices_id = '$InvoiceId'";
            $RowData = $this->fetchRowbyWhereClause('invoiceheaders', $where);
            if ($RowData <> NULL) {
                $uid = $RowData['uid'];
                $this->deleteRow('invoiceheaders', $uid);
            }
        }
    }

    public function checkUnapprovedEntry($projects_id, $invoiceEndDate) {

        $where = "projects_id = '$projects_id' and workdate <= '{$invoiceEndDate}' and status < '30'";
        $RowData = $this->fetchRowbyWhereClause('times', $where);
        $UneligibleTimeData = $RowData <> NULL ? 'Yes' : 'No';

        $where = "projects_id = '$projects_id' and chargedate <= '{$invoiceEndDate}' and status < '30'";
        $RowData = $this->fetchRowbyWhereClause('charges', $where);
        $UneligibleChargeData = $RowData <> NULL ? 'Yes' : 'No';
        
        $where = "projects_id = '$projects_id' and weekenddate <= '{$invoiceEndDate}' and status < '30'";
        $RowData = $this->fetchRowbyWhereClause('invoiceexpensedetails', $where);
        $UneligibleExpenseDetail = $RowData <> NULL ? 'Yes' : 'No';

        $where = "projects_id = '$projects_id' and weekenddate <= '{$invoiceEndDate}' and status < '30'";
        $RowData = $this->fetchRowbyWhereClause('invoiceexpenseheaders', $where);
        $UneligibleExpenseHeader = $RowData <> NULL ? 'Yes' : 'No';


        if ($UneligibleTimeData == 'Yes')
            return 'er0073';
		if ($UneligibleChargeData == 'Yes')
            return 'er0095';
        if ($UneligibleExpenseDetail == 'Yes')
            return 'er0057';
        if ($UneligibleExpenseHeader == 'Yes')
            return 'er0057';

        return NULL;
    }

    protected function findInvoicePeriod($projects_id, $lastBillDate=NULL) {

        $weekendday = $_SESSION['company']['weekendday'];
        $invoicePeriod = array('begindate' => '',
            					'enddate' => '');

        $query = "select p.lastbilldate,
			 			 p.billcycle,
			 			 p.createat
                    from projects p
		  			where p.projects_id = '$projects_id'";

        $dataset = $this->getDatabyQuery($query);
        if ($dataset == NULL)
            return NULL;

        if ($lastBillDate <> NULL)
            $dataset[0]['lastbilldate'] = $lastBillDate;

        if ($dataset[0]['lastbilldate'] == NULL)
            $dataset[0]['lastbilldate'] = tesAddDate($dataset[0]['createat'], -1);

        $invoicePeriod['begindate'] = tesAddDate($dataset[0]['lastbilldate'], 1);

        $dataset[0]['billcycle'] = strtolower($dataset[0]['billcycle']);

        switch ($dataset[0]['billcycle']) {
            case 'weekly':
                $invoicePeriod['enddate'] = getWeekEndDate($invoicePeriod['begindate'], $weekendday);
                break;

            case 'bi-weekly':
                $invoicePeriod['enddate'] = getWeekEndDate($invoicePeriod['begindate'], $weekendday);
                $invoicePeriod['enddate'] = tesAddDate($invoicePeriod['enddate'], 7);
                break;

            case 'fortnightly':
                $invoicePeriod['enddate'] = getBiMonthlyDate($invoicePeriod['begindate'], 'Next');
                break;

            case 'monthly':
                $invoicePeriod['enddate'] = getMonthEnd($invoicePeriod['begindate'], 'Next');
                break;

            default:
                $invoicePeriod['enddate'] = getMonthEnd($invoicePeriod['begindate'], 'Next');
                break;
        }
        $today = tesAddDate('today');
        if ($invoicePeriod['enddate'] > $today)
            $invoicePeriod['enddate'] = NULL;

        return $invoicePeriod;
    }

    public function undoInvoice($uid) {

        if ($uid == '')
            throw new iInvalidArgumentException();

        $loggedinUser = loggedUserID();

        $suid = $this->escapeString($uid);

        $InvRec = $this->fetchRow('invoiceheaders', 'uid', $suid);

        $this->beginTransaction();
        try {
            // Create Open Invoice

            $openInvoiceId = $this->createOpenInvoice($InvRec['projects_id']);

            // Update expense Details

            $query = "UPDATE expensedetails ED
			 			SET
                             ED.invoices_id = '$openInvoiceId',
                             ED.changeat = now(),
                             ED.changeby = '$loggedinUser'
		       		WHERE ED.invoices_id = '{$InvRec['invoices_id']}'";
            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);

            // Update expense Header

            $query = "UPDATE expenseheaders
			 			SET
                             invoices_id = '$openInvoiceId',
                             InvoiceDate = NULL,
                             status = '30',
                             changeat = now(),
                             changeby = '$loggedinUser'
                       WHERE invoices_id = '{$InvRec['invoices_id']}'";
            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);
            
            //Update charge records
            
            $query = "UPDATE charges c
            			SET
            				c.invoices_id = '$openInvoiceId',
            				c.status = '30',
            				c.changeat = now(),
            				c.changeby = '$loggedinUser'
            				WHERE c.invoices_id = '{$InvRec['invoices_id']}'";
            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);

            //Update times records

            $query = "UPDATE times t
			 			SET
                            t.invoices_id = '$openInvoiceId',
                            t.status = '30',
                            t.changeat = now(),
                            t.changeby = '$loggedinUser'
                      WHERE t.invoices_id = '{$InvRec['invoices_id']}'";
            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);

            $endDate = $this->findLastInvoicePeriod($InvRec['invoices_id'], $InvRec['projects_id']);

            // Update projects for last bill date

            if ($endDate != NULL) {
                $query = "UPDATE projects
                             SET
                                 lastbilldate = '$endDate',
                                 changeat = now(),
                                 changeby = '$loggedinUser'
                           WHERE projects_id = '{$InvRec['projects_id']}'";
                $conn = $this->getConnection();
                $conn->query($query);
                $this->chkQueryError($conn, $query);
                
                $query = "UPDATE accounts
                      		SET
							lastbilldate = '$endDate',
							changeat = now(),
							changeby = '$loggedinUser'
                    WHERE accounts_id = (select billtoaccounts_id from projects where projects_id = '{$InvRec['projects_id']}')";

	            $conn = $this->getConnection();
    	        $conn->query($query);
        	    $this->chkQueryError($conn, $query);
                
            }

            // Delete Invoice Header

            $query = "DELETE From invoiceheaders WHERE invoices_id = '{$InvRec['invoices_id']}'";
            $conn = $this->getConnection();
            $conn->query($query);
            $this->chkQueryError($conn, $query);

            $this->commitTransaction();
            
        } catch (Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    protected function findLastInvoicePeriod($invoices_id, $projects_id) {

        // If this is not the last invoice of the projects, we cannot change the last invoice date, hence send NULL.

        $query = "SELECT max(cast(invoices_id as unsigned)) as lastinv
                    FROM invoiceheaders
                   where projects_id = '$projects_id'
                     and invoices_id not like '@%'";

        $dataset = $this->getDatabyQuery($query);

        if ($dataset[0]['lastinv'] == NULL)
            return NULL;

        if (sprintf("%05s", $dataset[0]['lastinv']) != sprintf("%05s", $invoices_id))
            return NULL;

        // Find the last bill date of the last invoice of this project prior to this invoice.
        $query = "SELECT max(enddate) as newlastbilldate
                    FROM invoiceheaders
                   where projects_id = '$projects_id'
                     and invoices_id < '$invoices_id'
                     and invoices_id not like '@%'";

        $dataset = $this->getDatabyQuery($query);

        if ($dataset[0]['newlastbilldate'] == NULL) {    // Make last bill date as one day prior to project create date
            $query = "SELECT date_sub(createat, INTERVAL 1 DAY) as newlastbilldate
                        FROM projects
                       where projects_id = '$projects_id'";

            $dataset = $this->getDatabyQuery($query);
        }
        if ($dataset[0]['newlastbilldate'] == NULL)
            return NULL;

        return $dataset[0]['newlastbilldate'];
    }

    protected function nextInvoiceNumber($projects_id) {

        // Determine the next invoice number.

        $query = "select max(cast(invoices_id as unsigned)) as lastInvoiceId
                    from invoiceheaders
		   where invoices_id not like '@%'";
        $RowData = $this->getDatabyQuery($query);
        $newInvoiceid = $RowData[0]['lastInvoiceId'] == NULL ? 1 : $RowData[0]['lastInvoiceId'] + 1;
        $newInvoiceid = sprintf("%05s", $newInvoiceid);

        return $newInvoiceid;
    }
    
    public function getInvoiceHeaderRec($uid) {
    	
    	$query = "select ih.invoices_id as invoices_id,
				    	pj.billtoaccounts_id as billaccount_id,
				    	ac.name as Account_name,
    					ih.begindate as begindate,
    					ih.enddate as enddate,

				    	pj.billtoaddress1 as billtoaddress1,
    					pj.billtoaddress2 as billtoaddress2,
    					pj.billtocity as billtocity,
   					 	pj.billtostate as billtostate,
    					pj.billtopostalcode as billtopostalcode,
    					pj.billtocountry as billtocountry,
    					pj.billtocontact as billtocontact,
    					pj.billtoemail as billtoemail,
    	
    					ac.address1 as address1,
    					ac.address2 as address2,
    					ac.city as city,
    					ac.state as state,
    					ac.postalcode as postalcode,
    					ac.country as country,
    					ac.contact as contact,
  					  	ac.email as email
    	 
   				 	from invoiceheaders ih, projects pj, accounts ac
    				where ih.uid = $uid
    					and	ih.projects_id = pj.projects_id
    					and pj.billtoaccounts_id = ac.accounts_id";
    
    	$dataset = $this->getDatabyQuery($query);
    	return $dataset[0];
    }
    
    
    public function getInvoiceChargeDetailRec($invoices_id, $users_id) {
    	
    	$query = "select c.chargedate as chargedate,
    					c.charges as charges,
    					c.description as description
    				from charges c
    				where c.invoices_id = '$invoices_id'
    					and c.users_id = '$users_id'
    				order by c.chargedate";
    
    	$dataset = $this->getDatabyQuery($query);
    	return $dataset;
    }
    
    
    public function getInvoiceTimeSummaryRec($invoices_id) {
    	
    	$query = "select i.projects_id as projects_id,
   					 	i.project_name as project_name,
  					  	i.users_id as user_id,
  					  	i.User_name as user_name,
    					i.rate as rate,
   					 	sum(i.billablehours) as billablehours,
   					 	sum(i.nonbillablehours) as nonbillablehours,
   					 	sum(i.actualhours) as actualhours,
   					 	sum(i.billamount) as billamount
   				 	from invoicetimedetails i
   				 	where i.invoices_id = '$invoices_id'
   				 	group by i.projects_id, i.users_id, i.rate
    				order by i.projects_id, i.user_name";
    
    	$dataset = $this->getDatabyQuery($query);
    	return $dataset;
    }
    
    
    public function getInvoiceTimeGroupRec($invoices_id) {
    	
    	$query = "select i.projects_id as projects_id,
    					i.Project_name as Project_name,
    					i.users_id as user_id,
    					i.User_name as user_name,
    					i.rate as rate,
    					sum(i.billablehours) as billablehours,
    					sum(i.nonbillablehours) as nonbillablehours,
    					sum(i.actualhours) as actualhours,
    					sum(i.billamount) as billamount
    				from invoicetimedetails i
    				where i.invoices_id = '$invoices_id'
    				group by i.projects_id, i.users_id
    				order by i.projects_id, i.user_name";
    
    	$dataset = $this->getDatabyQuery($query);
    	return $dataset;
    }
    
    
    public function getInvoiceExpSummaryRec($invoices_id) {
    	
    	$query = "select i.users_id as user_id,
    					i.User_name as user_name,
    					i.weekenddate as weekendDate,
    					sum(i.amount) as expAmount,
    					i.Expense_uid as Expense_uid
    				from invoiceexpensedetails i
    				where i.invoices_id = '$invoices_id'
    				group by i.users_id, i.weekenddate
    				order by i.user_name, i.weekenddate";
    
    	$dataset = $this->getDatabyQuery($query);
    	return $dataset;
    }
    
    
    public function getInvoiceTimeDetailRec($invoices_id, $users_id) {
    	
	    $query = "select i.workdate as workdate,
    					i.billablehours as billablehours,
    					i.nonbillablehours as nonbillablehours,
    					i.actualhours as actualhours,
    					i.billamount as billamount,
    					i.description as description
    				from invoicetimedetails i
    				where i.invoices_id = '$invoices_id'
    					and i.users_id = '$users_id'
    				order by i.workdate";
    
		$dataset = $this->getDatabyQuery($query);
		return $dataset;
    }

    
    public function getInvoiceChargeSummaryRec($invoices_id)
    {
    	$query = "select c.users_id as user_id,
				    	u.fullname as user_name,
    					sum(c.charges) as charges
    				from charges c, users u
    				where c.users_id = u.users_id
    					and c.invoices_id = '$invoices_id'
    				group by c.users_id
    				order by u.fullname";
    
    	$dataset = $this->getDatabyQuery($query);
    	return $dataset;
    }
    
    
    public function getExpenses($uid) {
    	
    	$query = "select ih.invoices_id as invoices_id
			    	from invoiceheaders ih
    				where ih.uid = $uid";
    	
    	$dataset = $this->getDatabyQuery($query);
    	return ($dataset);
    	    	
    }

}

?>