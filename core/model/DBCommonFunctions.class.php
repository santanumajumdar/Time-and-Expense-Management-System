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

require_once("$basedir/core/model/DBCoreFunctions.class.php");


abstract class __DBCommonFunctions extends __DBCoreFunctions
{
	
	public function deleteRow($table, $uid) {
		
		if ($table == ''
			or $uid == '')
			throw new iInvalidArgumentException();

		$suid = $this->escapeString($uid);
		
		$auth = new UserActionAuthorization();
		if (!$auth->chkAuthorityLevel($_POST['action'], $this->getRecordCreator($table, $suid)))
			throw new iBLError('nocategory', 'er0048');

		$where = "uid = '$suid'";
		$this->deleteRows($table, $where);
		return;
	}
	

	public function deleteRows($table, $where) {
		
		if ($table == ''
			or $where == '')
			throw new iInvalidArgumentException();

		try {
			$query = "DELETE FROM $table WHERE $where";
			$conn = $this->getConnection();
			$conn->query($query);
			$this->chkQueryError($conn, $query);
		}
		catch (Exception $e) {
			throw $e;
		}
	}

	
	public function listRows($table, $where='', $ord_by='uid', $ascdec='', $start=0, $limit=99999, $distinct_field='') {

		$query = "";
		if ($distinct_field <> '')
			$query = "select distinct $table.$distinct_field from $table";
		else
			$query = "SELECT $table.* FROM $table";

		if ($where <> '')
			$query .= " WHERE $where";
		if ($ord_by <> '')
			$query .= " ORDER BY `$ord_by`";
		if (strtolower($ascdec) == 'desc')
			$query .= " $ascdec";
		if (($start !== NULL)
				and ($limit > 0))
			$query .= " LIMIT $start, $limit";
		
		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);

		$output = array();
		while (($row = $results->fetch_assoc()) !== NULL)
			$output[] = $row;

		$results->close();
		return $output;
	}
	
	
	public function listRowsSelectiveFields($table, $fieldArray, $where='', $ord_by='uid', $ascdec='', $start=0, $limit=99999, $distinct_field='') {

		$query = $this->buildQueryString($table, $fieldArray, $distinct_field, $where, $ord_by, $ascdec, $start, $limit);
	
		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);
	
		$output = array();
		while (($row = $results->fetch_assoc()) !== NULL)
			$output[] = $row;

		$results->close();
		return $output;
	}

	/*
	 * This buildQueryString function is mainly used to build the data grid of the view.
	 */
	protected function buildQueryString($table, $fieldArray, $distinct_field='', $where='', $ord_by='', $ascdec='', $start='', $limit='') {
		
		$fieldStr = "";
		foreach ($fieldArray as $field=>$description) {
			$fieldStr .= empty($fieldStr) ? "$field" : ", $field";
		}
		
		$query = "";
		$query = $distinct_field <> '' ? "select distinct $distinct_field from $table" : "SELECT $fieldStr FROM $table";
		
		$where = $this->addAuthorityRestriction($table, $where);
			
		if ($where <> '')
			$query .= " WHERE $where";
		if ($ord_by <> '')
			$query .= " ORDER BY `$ord_by`";
		if (strtolower($ascdec) == 'desc')
			$query .= " $ascdec";
		if (($start !== NULL)
				and ($limit > 0))
			$query .= " LIMIT $start, $limit";
		
		return($query);		
	}
	

	protected function addAuthorityRestriction($table, $where, $createby=NULL, $users_id=NULL) {
	
		$restictedTableArray = array("users", "times", "expenseheaders", "charges");
		$loggedUserID = loggedUserID();
		
		$createby = (empty($createby)) ? "createby" : $createby;
		$users_id = (empty($users_id)) ? "users_id" : $users_id;
		
		// This section is to restrict user access in the list.
	
		if (array_search($table, $restictedTableArray) !== FALSE) {
	
			switch ($_SESSION['auth']) {
	
				case "own":
					if ($where != '') $where .= " and ";
					$where .= " ($createby='$loggedUserID' or $users_id='$loggedUserID') ";
					break;
						
				case "group":
					$userGroup = getUserGroup();
					$sql = "select users_id from users where usergroup='$userGroup' and usergroup != '' and usergroup is not NULL";
					$userQueryList = "";
					if (($userList = $this->getDatabyQuery($sql)) != NULL) {
						foreach ($userList as $user) {
							$userQueryList .= (empty($userQueryList)) ? "'{$user['users_id']}'" : ", '{$user['users_id']}'";
						}
					}
					if ($where != '') $where .= " and ";
					$where .= " ($createby='$loggedUserID' or $users_id='$loggedUserID') ";
					if (!empty($userQueryList))
						$where .= " or ($createby in ($userQueryList) or $users_id in ($userQueryList)) ";
					break;
						
				case "all":
					break;
						
				default:
					if ($where != '') $where .= " and ";
					$where .= " $users_id is NULL ";				// This one way make no selections to the Query.
					break;
			}
		}
			
		return($where);
	}

	
	
	public function modifyWithHR($dataset, $fieldArray) {
		
		if (empty($fieldArray))
			return $dataset;
		
		$hrArray = array();
		$hrRefField = $fieldArray['table'].'_'.$fieldArray['column'];
		$conn = $this->getConnection();
		
		foreach ($fieldArray as $fieldName) {
			$query = "SELECT code, code_value, code_hr 
						from human_readable_codes
						where level = 0
						  and system = '*'
						  and code = '$hrRefField'";
		
			$results = $conn->query($query);
			$this->chkQueryError($conn, $query);
			while (($row = $results->fetch_assoc()) !== NULL) {
				$hrArray[$row['code']][$row['code_value']] = changeLiteral($row['code_hr']);
			}
		}
		$results->close();
		
		for ($i=0; $i<count($dataset); $i++) { 
			foreach ($dataset[$i] as $fieldName=>$fieldValue) {
				if (isset($hrArray[$hrRefField][$fieldValue])) {
					$dataset[$i][$fieldName] = $hrArray[$hrRefField][$fieldValue];
				}
			}
		}
		return $dataset;
	}
	

	public function getOptionArray($table, $fieldName) {
		
		$optionArray = array();
		$conn = $this->getConnection();
	
		$query = "SELECT code_value, code_hr
					from human_readable_codes
					where level = 0
					  and system = '*'
					  and code = '" . $table."_".$fieldName."'
					order by appearance_order, code_hr";
	
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);
		while (($row = $results->fetch_assoc()) !== NULL) {
			$optionArray[$row['code_value']] = $row['code_hr'];
		}

		$results->close();
	
		return $optionArray;
	}
	
	
	public function getDataSetObj($table, $columns=NULL, $where=NULL, $sortby=NULL) {
	
		$columns = (empty($columns)) ? "*" : $columns;
		$where = (empty($where)) ? NULL : " WHERE $where";
		$sortby = (empty($sortby)) ? NULL : " ORDER BY $sortby";
	
		$query = "SELECT $columns FROM $table $where $sortby";
	
		return $this->getDataObj($query);
	}
	
	
	public function getDatabyQuery($query) {
		
		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);

		$dataSet = array();
		while (($row = $results->fetch_assoc()) !== NULL)
			$dataSet[] = $row;

		$results->close();
		return $dataSet;
	}


	public function getDataObj($sql) {
		
		$conn = $this->getConnection();
		$results = $conn->query($sql);
		$this->chkQueryError($conn, $sql);

		return $results;
	}

	
	
	public function fetchRowObjforView($table, $field_name, $field_value) {
		
		if ($field_name == '')
			throw new iInvalidArgumentException();
		$_GET['uid'] = (!isset($_GET['uid'])) ? "" : $_GET['uid'];
	
		$auth = new UserActionAuthorization();
		if ((!$auth->chkAuthorityLevel($_GET['action'], $this->getRecordCreator($table, $_GET['uid'])))
			and (!$auth->chkAuthorityLevel($_GET['action'], $this->getRecordUserid($table, $_GET['uid']))))
			throw new iBLError('nocategory', 'er0042');
	
		return $this->fetchRowObj($table, $field_name, $field_value);
	}
	
	

	public function fetchRowObj($table, $field_name, $field_value) {
		
		if ($field_name == '')
			throw new iInvalidArgumentException();
		
		$value = (is_string($field_value)) ? "'" . $field_value . "'" : $field_value;
		
		$where = ($field_value == NULL) ? "$field_name is NULL" : "$field_name = $value";
	
		$query = "SELECT {$table}.*
					FROM {$table}
					WHERE $where";
	
		return $this->getDataObj($query)->fetch_object();
	}


	public function fetchRow($table, $field_name, $field_value) {
		
		if ($field_name == '')
			throw new iInvalidArgumentException();

		$value = (is_string($field_value)) ? "'" . $field_value . "'" : $field_value;
		
		$where = ($field_value == NULL) ? "$field_name is NULL" : "$field_name = $value";

		$query = "SELECT {$table}.*
					FROM {$table}
					WHERE $where";

		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);

		$row = $results->fetch_assoc();
		$results->close();
		return $row;
	}

	
	public function fetchRowbyWhereClause($table, $whereclause) {
		
		if ($whereclause == '' )
			throw new iInvalidArgumentException();

		$query = "SELECT {$table}.*
  					FROM {$table}
 				   WHERE {$whereclause}";

		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);

		$row = $results->fetch_assoc();
		$results->close();
		return $row;
	}

	
	public function fetchRowsbyWhereClause($table, $whereclause) {
		
		if ($whereclause == '' )
			throw new iInvalidArgumentException();

		$query = "SELECT {$table}.*
					FROM {$table}
				   WHERE {$whereclause}";

		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);

		$output = array();
		while (($row = $results->fetch_assoc()) !== NULL)
		$output[] = $row;

		$results->close();
		return $output;
	}

	
	public function countRows($table, $where='') {
		
		$query = "SELECT COUNT(*) FROM $table";
		if ($where <> '')
			$query .= " WHERE $where";
	
		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);
	
		$row = $results->fetch_array();
		$results->close();
		return $row[0];
	}
	

	public function getRecordCreator($table, $uid='') {
		
		if ($uid == '' )
			return '';
			
		if (($table == 'literals')
			or ($table == 'messages'))
			return 'admin';

		$query = "SELECT {$table}.createby
					FROM {$table}
				   WHERE uid = '{$uid}'";

		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);

		$row = $results->fetch_assoc();
		$results->close();
		return $row['createby'];
	}

	
	public function getRecordUserid($table, $uid=NULL) {
		
		if ($uid == NULL )
			return loggedUserID();
		
		if (($table != 'users')
			and ($table != 'times')
			and ($table != 'expenseheaders')
			and ($table != 'expensedetails'))
			return loggedUserID();

		$query = "SELECT {$table}.users_id
					FROM {$table}
				   WHERE uid = '{$uid}'";

		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);

		$row = $results->fetch_assoc();
		$results->close();
		return $row['users_id'];
	}
	
	
	public function getAuthorityLevel($authId, $businessObject, $actionAuthority) {
		
		if (($authId == '')
			or ($businessObject == '')
			or ($actionAuthority == ''))
			return '0';
		
		$query = "select a.authlevel from authorizations a
						where a.authorizations_id = '$authId'
							and a.businessobject = '$businessObject'
							and a.actionauthority = '$actionAuthority'";
	
		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);
	
		if (($row = $results->fetch_assoc()) == NULL)
			$row['authlevel'] = '0';
		$results->close();
		return $row['authlevel'];
	}
	
	
	public function checkOpenAuthority($action=NULL) {
	
		if (empty($action))
			return TRUE;
	
		$query = "select ar.actionauthority 
					from actionregistry ar
					where ar.action = '$action'";
	
		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);
	
		$row = $results->fetch_assoc();
		$results->close();
		return ($row['actionauthority'] == '*ALL');
	}
	
	public function isValidAction($action=NULL) {
	
		if (empty($action))
			return FALSE;
	
		$where = "action = '$action'";	
		$count = $this->countRows('actionregistry', $where);
		return ($count == 0) ? FALSE : TRUE;
	}
	

	public function getColumnSum($table, $sumVariable, $where='') {
		
		$query = "SELECT sum($sumVariable) FROM $table";
		if ($where <> '')
			$query .= " WHERE $where";

		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);

		$row = $results->fetch_array();
		$results->close();
		return $row[0];
	}


	
	public function getCompanyRec($uid=1) {
		
		$query = "select *
					from company
				   where uid = $uid";

		$dataset = $this->getDatabyQuery($query);
		return $dataset[0];
	}



}

?>