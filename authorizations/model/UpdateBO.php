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
require_once("$basedir/authorizations/model/DbObj.php");
require_once("$basedir/authorizations/model/Validation.php");

class UpdateBO
{
	public $authorizations_id;
	public $description;
			
	public function setObj() {
		
		$this->authorizations_id = isset($_POST['authorizations_id']) ? trim($_POST['authorizations_id']) : "";
		$this->description = trim($_POST['description']);
	}


	public function CreateAuthorizationList()
	{
		try
		{
			$AuthData = new authData();
			$uid = $AuthData->createAuthorizationList($_POST);
			return $uid;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('authorizations_id', 'er0004'));
		
		} catch (Exception $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		}

	}

	public function EditAuthorizationList($uid)
	{
		$AuthData = new authData();
		$authList = $AuthData->fetchRow('authorizationlists', 'uid', $uid);
		

		try {
			
		if (($authList['authorizations_id'] == "*ALL")
			|| ($authList['createby'] == '*SYSTEM'))
			throw new iBLError('nocategory', 'er0113');

			$AuthData = new authData();
			$AuthData->updateAuthorizationList($uid, $_POST);
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		} catch (iIDInUseException $e) {
			return (convertErrorToJSONFormat('authorizations_id', 'er0004'));
		}

	}


	public function DeleteAuthorizationList($uid)
	{	
		try
		{
			$AuthData = new authData();
			$authList = $AuthData->fetchRow('authorizationlists', 'uid', $uid);
			
			if (($authList['authorizations_id'] == "*ALL")
				|| ($authList['createby'] == '*SYSTEM'))
				throw new iBLError('nocategory', 'er0113');
		
			$RowData = $AuthData->fetchRow('users', 'authorizations_id', $authList['authorizations_id']);
			if ($RowData <> NULL)
				throw new iBLError('nocategory', 'er0047');
		
			$where = "authorizations_id = '{$authList['authorizations_id']}'";
			$AuthData->deleteRows('authorizations', $where);
	
			$AuthData->deleteRow('authorizationlists', $uid);
		
			return TRUE;
			
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
			
		} catch (iFKInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', 'er0013'));
			
		} catch (iPKInUseException $e) {
			return (convertErrorToJSONFormat('nocategory', 'er0026'));
			
		}

	}

}


?>