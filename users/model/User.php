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
require_once("$basedir/users/model/DbObj.php");

class User extends UserData
{

	public $Uid;
	public $Userid;
	public $FullName;
	public $Password;
	public $EmailAddress;
	public $JoinDate;
	public $UserStatus;
	public $AuthId;
	public $UserGroup;
	public $DateFormat;
	public $Language;
	public $MenuId;
	public $debugLevel;
	public $dbTraceLevel;
	public $preview_receipt;
	


	public function __construct($users_id) {
		
		if ($users_id == '')
			throw new iInvalidDataException();
		
		try {

			$UserData = new UserData();
			$user_row = $UserData->fetchRow('users', 'users_id', $users_id);
			
			if ($user_row != NULL) {
				$this->Uid = $user_row['uid'];
				$this->Userid = $user_row['users_id'];
				$this->FullName = $user_row['fullname'];
				$this->Password = $user_row['password'];
				$this->EmailAddress = $user_row['email'];
				$this->JoinDate = $user_row['joindate'];
				$this->UserStatus = $user_row['status'];
				$this->AuthId = $user_row['authorizations_id'];
				$this->UserGroup = $user_row['usergroup'];
				$this->DateFormat = $user_row['dateformat'];
				$this->Language = $user_row['language'];
				$this->MenuId = $user_row['u_menu_id'];
				$this->debugLevel = isset($user_row['debuglevel']) ? $user_row['debuglevel'] : 0;
				$this->dbTraceLevel = isset($user_row['dbtracelevel']) ? $user_row['dbtracelevel'] : 0;
				$this->preview_receipt = isset($user_row['preview_receipt']) ? $user_row['preview_receipt'] : 1;				
			}
		
		} catch (Exception $e){
			throw $e;
		}
	}
	
	

	public function registerUser($ui, $pw) {
		
		$levelArray = array('NONE', 'LOW', 'MED', 'HIGH');
		
		if (($ui == '')
			or ($pw == '')
			or !$this->checkPassword($pw))
				return FALSE;

		if ($this->UserStatus <> '10')
			throw new iBLError('users_id', 'er0039');
		
		if (empty($this->MenuId))
			throw new iBLError('users_id', 'er0100');

		$_SESSION['user']['uid'] = $this->Uid;
		$_SESSION['user']['userid'] = $this->Userid;
		$_SESSION['user']['fullname'] = $this->FullName;
		$_SESSION['user']['authid'] = $this->AuthId;
		$_SESSION['user']['usergroup'] = $this->UserGroup;
		$_SESSION['user']['dateformat'] = $this->DateFormat;
		$_SESSION['user']['language'] = $this->Language;
		
		if (!isset($_SESSION['ini']['environment']['debug_level']))
			$_SESSION['ini']['environment']['debug_level'] = 'NONE';
		
		if (array_search($_SESSION['ini']['environment']['debug_level'], $levelArray) === FALSE)
			$_SESSION['ini']['environment']['debug_level'] = 'NONE';
		
		$_SESSION['ini']['environment']['debug_level'] =
			$levelArray[max($this->debugLevel, array_search($_SESSION['ini']['environment']['debug_level'], $levelArray))];
		
		if (!isset($_SESSION['ini']['environment']['db_trace_level']))
			$_SESSION['ini']['environment']['db_trace_level'] = 'NONE';
		
		if (array_search($_SESSION['ini']['environment']['db_trace_level'], $levelArray) === FALSE)
			$_SESSION['ini']['environment']['db_trace_level'] = 'NONE';
		
		$_SESSION['ini']['environment']['db_trace_level'] = 
			$levelArray[max($this->dbTraceLevel, array_search($_SESSION['ini']['environment']['db_trace_level'], $levelArray))];
		
		if ($_SESSION['ini']['environment']['db_trace_level'] != 'NONE') {
			$logLevel = "Database Trace Level: ".$_SESSION['ini']['environment']['db_trace_level'].PHP_EOL;
			error_log($logLevel, 3, $_SESSION['db_trace_log']);
		}
		
		$_SESSION['user']['preview_receipt'] = $this->preview_receipt;
		
		$this->updateLoginTime($this->Userid);
		
		$this->loadLiteral($this->getLanguage());
		$_SESSION['menu'] = array();
		$this->loadMenuArray($this->MenuId, $_SESSION['menu']);
		
		return TRUE;
	}

	
	public function loadLiteral($language='ENG') {
		
		$language = strtoupper($language);
		$where = "language = '$language'";
		$literal_rows = $this->fetchRowsbyWhereClause('literals', $where);
		if ($literal_rows == NULL)
			return;
		foreach ($literal_rows as $literal_row) {
				
			$_SESSION['literal'][strtolower($literal_row['literals_id'])] = $literal_row['label'];
		}
		return;
	}
	
	
	public function checkPassword($pw_to_check) {
		
		return (md5($pw_to_check) == $this->Password) ? TRUE : FALSE;
	}
	
	
    public function loadMenuArray($u_menu_id, &$menu) {
    	
    	$sql = "select 	m.menu_id as menu_id  					
				from menu m 
				where m.u_menu_id = $u_menu_id";
        
        $menu_row = $this->getDataObj($sql);
        $menu_obj = $menu_row->fetch_object();
   		$this->loadMenuTree($menu_obj->menu_id, $menu);

        $menu_row->free_result();
        return;
    }
    
    
    public function loadMenuTree($menu_id, &$menu) {
    	 
    	$sql = "select 	m.menu_id as menu_id,
    					m.parent_menu_id, parent_menu_id,
    					p.label as label,
    					p.url as url,
    					p.control as control,
    					p.parm as parm,
    					p.cust_parm as cust_parm
    				from menu m
    					left outer join programtransactions p
    						on m.programtransactions_id = p.programtransactions_id
    				where
    					m.parent_menu_id = $menu_id
    				order by m.`order`";
    
    	$menu_rows = $this->getDataObj($sql);
    	while (($menu_obj = $menu_rows->fetch_object()) != NULL) {
  			$menu[$menu_obj->menu_id] = $this->buildMenuArray($menu_obj);
			$this->loadMenuTree($menu_obj->menu_id, $menu[$menu_obj->menu_id]);
    	}
    
    	$menu_rows->free_result();
    	return;
    }
    
    
    protected function buildMenuArray($menu) {
    	$menu_item = array();
    	$menu_item['label'] = $menu->label;
		$menu_item['url'] = $menu->url;
		$menu_item['control'] = $menu->control;
		$menu_item['parm'] = $menu->parm;
		$menu_item['cust_parm'] = $menu->cust_parm;
		return ($menu_item);
    }
    
    
    public function getUid() {
    	return $this->uid;
    }
    
	public function getUserid() {
		return $this->Userid;
	}

	public function getFullName() {
		return $this->FullName;
	}

	public function getPassword() {
		return $this->Password;
	}

	public function getEmailAddress() {
		return $this->EmailAddress;
	}

	public function getJoinDate() {
		return $this->JoinDate;
	}

	public function getUserStatus() {
		return $this->UserStatus;
	}

	public function getAuthId() {
		return $this->AuthId;
	}

	public function getUserGroup() {
		return $this->UserGroup;
	}

	public function getDateFormat() {
		return $this->DateFormat;
	}

	public function getLanguage() {
		return $this->Language;
	}

	public function getMenuId() {
		return $this->MenuId;
	}
	

	public final function getDebugLevel() {
		return $this->debugLevel;
	}
	
	public final function getDbTraceLevel() {
		return $this->dbTraceLevel;
	}
	
	public final function getPreviewReceipt() {
		return $this->preview_receipt;
	}
	        

}


?>