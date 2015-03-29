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
require_once("$basedir/core/controller/ActionRegistry.php");


class UserActionAuthorization extends __DBCommonFunctions 
{
	private static $s_authority = 'none';
	private static $s_action = NULL;
	private static $authId;
	private static $actionRegistry;
	
	public function __construct() {
		
		$this->setAuthId();
	}

	
	public function chkUserActionAuthority($ActionRegistry) {
		
		global $GLOBAL_authLevelArray;
		
		if (strtolower(loggedUserID()) == 'admin')
			return('all');
		
		if ($this->checkOpenAuthority($ActionRegistry->getAction()))
			return('all');
		
		if (self::$s_action == $ActionRegistry->getAction())
			return(self::$s_authority);
	
		$authLevel = ($this->getAuthId() == NULL) ? '0' : $this->getAuthorityLevel($this->getAuthId(), $ActionRegistry->getBusinessObject(), $ActionRegistry->getActionAuthority());
	
		self::$s_action = $ActionRegistry->getAction();
		self::$actionRegistry = $ActionRegistry;
		self::$s_authority = $GLOBAL_authLevelArray[$authLevel];
	
		return(self::$s_authority);
	}
	
	
	public function chkAuthorityLevel($action, $createby=NULL, $transactionUserId=NULL) {
		
		if ((isset(self::$actionRegistry))
			and (self::$actionRegistry->action == $action)) {
			$ActionRegistry = self::$actionRegistry;
		} else {
			$ActionRegistry = new ActionRegistry($action);		
		}
		
		$authLevelValue = $this->chkUserActionAuthority($ActionRegistry);
		
		switch ($authLevelValue) {
			
			case NULL:
			case "none":
				return FALSE;
				break;
				
			case "all":
				return TRUE;
				break;
				
			case "group":
				$currentUserGroup = getUserGroup();
				if (empty($currentUserGroup)) {
					return FALSE;
					break;
				}
				$tranUserGroup = "";
				$creatUserGroup = "";
				if (!empty($transactionUserId)) {
					$User = $this->fetchRow('users', 'users_id', $transactionUserId);
					$tranUserGroup = (!empty($User)) ? $User['usergroup'] : "";
				}
				if (!empty($createby)) {
					$User = $this->fetchRow('users', 'users_id', $createby);
					$creatUserGroup = (!empty($User)) ? $User['usergroup'] : "";
				}
				if ($createby == NULL)												// Indicates it is a case of record creation.
					return ($tranUserGroup != $currentUserGroup) ? FALSE : TRUE;
				else 																// Is created by the user of the same group ?
					return (($creatUserGroup != $currentUserGroup) and ($tranUserGroup != $currentUserGroup)) ? FALSE : TRUE;
					break;
				
			case "own":
				if ($createby == NULL)												// Indicates it is a case of record creation.
					return ($transactionUserId != loggedUserID()) ? FALSE : TRUE;
				else if (($createby != loggedUserID())								// Not created by the user
						and ($transactionUserId != loggedUserID()))					// Not the user's record
					return FALSE;
				else
					return TRUE;
				break;
			
			default:
				return FALSE;
				break;
		}
		
	}
	
	
	/**
	 * @return the $authId
	 */
	private function getAuthId() {
		return self::$authId;
	}
	
	
	/**
	 * @param field_type $authId
	 */
	private function setAuthId() {
		self::$authId = trim(getUserAuthID());
	}



}

?>