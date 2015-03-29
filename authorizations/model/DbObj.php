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


class authData extends __DBCommonFunctions
{
	public $uid;
	public $authorizations_id;
	public $description;
	public $createat;
	public $createby;
	public $changeat;
	public $changeby;
	
	public function getAuthorizationList($uid) {
	
		$authObj = $this->fetchRowObjforView("authorizationlists", "uid", $uid);
		return ($authObj == NULL) ? $this : $authObj;
		
	}
	
	public function getAuthorizationMatrix($authorizations_id=NULL)	{
		
		global $GLOBAL_always_available;

		$where = "ar.businessobject not in (";
		foreach ($GLOBAL_always_available as $element) {
			$where .= "'$element', ";
		}
		
		$where = substr($where, 0, strlen($where)-2) . ") ";

		if (!empty($authorizations_id))
			$where .= " and (a.authorizations_id is NULL or a.authorizations_id = '$authorizations_id')";
		
		$query = "select ar.businessobject, ar.actionauthority, a.authlevel 
					from actionregistry ar
					left outer join authorizations a
						on ar.businessobject = a.businessobject and ar.actionauthority = a.actionauthority
						where $where
					group by ar.businessobject, ar.actionauthority
					order by ar.businessobject, ar.actionauthority;";
	
		return $this->getDatabyQuery($query);
	}
	
	public function createAuthorizationList($authArray) {
		
		if ($authArray['authorizations_id'] == '')
			throw new iInvalidArgumentException();
	
		$sauth_id = $this->escapeString($authArray['authorizations_id']);
		unset($authArray['authorizations_id']);
		$sdesc = $this->escapeString($authArray['description']);
		unset($authArray['description']);
	
		$loggedinUser = loggedUserID();

		$query = "INSERT INTO authorizationlists
				(authorizations_id, description, createat, createby, changeby)
				VALUES ('$sauth_id', '$sdesc', now(), '$loggedinUser', '$loggedinUser')";
		try
		{
			$conn = $this->getConnection();
			$conn->query($query);
			$recid = $conn->insert_id;
			$this->chkQueryError($conn, $query);
			
			$this->createAuthorizationDetails ($sauth_id, $authArray);
			return $recid;

		} catch (Exception $e) {
			throw $e;
		}
	
	}
	
	
	public function updateAuthorizationList($uid, $authArray) {
		
		if ($uid == '')
			throw new iInvalidArgumentException();

		$suid = $this->escapeString($uid);
		$sdesc = $this->escapeString($authArray['description']);
		unset($authArray['description']);

		$loggedinUser = loggedUserID();

		$query = "UPDATE authorizationlists
						SET
					    	description = '$sdesc',
				    	    changeby = '$loggedinUser'
						WHERE
						    uid = '$suid'";
			
		try
		{
			$conn = $this->getConnection();
			$conn->query($query);
			$this->chkQueryError($conn, $query);
			
			$authList = $this->fetchRow('authorizationlists', 'uid', $uid);
			$where = "authorizations_id = '{$authList['authorizations_id']}'";
			$this->deleteRows('authorizations', $where);
			
			$this->createAuthorizationDetails ($authList['authorizations_id'], $authArray);

			return TRUE;
		
		} catch (Exception $e) {
			throw $e;
		}

	}
	
	protected function createAuthorizationDetails ($authorizations_id, $authArray) {
		
		$loggedinUser = loggedUserID();
		$conn = $this->getConnection();
				
		foreach ($authArray as $key=>$value) {
			if (($key=='action')
				|| ($key=='uid')) 
				continue;
			$dbFld = explode('___', $key);
			
			try {
				$query = "INSERT INTO authorizations
						(authorizations_id, businessobject, actionauthority, authlevel, createat, createby, changeby)
						VALUES ('$authorizations_id', '$dbFld[0]', '$dbFld[1]', '$value', now(), '$loggedinUser', '$loggedinUser')";
				$conn->query($query);
				$recid = $conn->insert_id;
				$this->chkQueryError($conn, $query);
			
			} catch (Exception $e) {
				throw $e;
			
			}
		}
		
		return TRUE;
	}



}


?>