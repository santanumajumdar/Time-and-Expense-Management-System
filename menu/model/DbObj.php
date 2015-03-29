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

class MenuData extends __DBCommonFunctions
{

	public function addMenuEntry($programtransactions_id, $order, $parent_menu_id=NULL, $menu_id=NULL) {

		if (($order == '')
			or ($programtransactions_id == ''))
			throw new iInvalidArgumentException();

		$sparent_menu_id = $this->escapeString($parent_menu_id);
		$sprogramtransactions_id = $this->escapeString($programtransactions_id);
		$sorder = $this->escapeString($order);
		$smenu_id = $this->escapeString($menu_id);
		
		$parentMenuString = empty($sparent_menu_id) ? "NULL" : $sparent_menu_id;
		$menuString = empty($smenu_id) ? "(SELECT max(m2.menu_id)+1 FROM menu m2)" : $smenu_id;
		
		$loggedinUser = loggedUserID();

		$conn = $this->getConnection();

		$query = "INSERT INTO menu (u_menu_id, parent_menu_id, menu_id, programtransactions_id, `order`, createat, createby, changeby)
			VALUES ((SELECT max(m1.u_menu_id)+1 FROM menu m1), $parentMenuString, $menuString, '$sprogramtransactions_id', '$sorder', now(), '$loggedinUser', '$loggedinUser')";

		$conn->query($query);
		$recid = $conn->insert_id;
		$this->chkQueryError($conn, $query);
	
		$query = "SELECT menu.menu_id
					FROM menu
					WHERE uid = '{$recid}'";

		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);

		$row = $results->fetch_assoc();
		$results->close();
		return $row['menu_id'];
	}

	

	public function updateRow($uid, $programtransactions_id, $order) {
		
		if ($uid == ''
			or $programtransactions_id == ''
			or $order == '')
			throw new iInvalidArgumentException();

		$sprogramtransactions_id = $this->escapeString($programtransactions_id);
		$sorder = $this->escapeString($order);
		$loggedinUser = loggedUserID();

		$query = "UPDATE menu
					 SET
						programtransactions_id = '$sprogramtransactions_id',
						order = '$order',
						changeby = '$loggedinUser'
					WHERE uid = '$uid'";
			
		$conn = $this->getConnection();
		$conn->query($query);
		$this->chkQueryError($conn, $query);
	}


	public function reorderMenu($parent_menu_id, $menu_id, $order) {
		
		if ($menu_id == ''
			or $order == '')
			throw new iInvalidArgumentException();

		$loggedinUser = loggedUserID();
		
		$where_append = (($parent_menu_id == NULL) or ($parent_menu_id == '')) ? 
				"`parent_menu_id` is NULL " : 
				"`parent_menu_id` = '$parent_menu_id'";

		$query = "UPDATE `menu`
					 SET
						`order` = '$order',
						`changeby` = '$loggedinUser'
					WHERE `menu_id` = '$menu_id'
					  and $where_append";
			
		$conn = $this->getConnection();
		$conn->query($query);
		$this->chkQueryError($conn, $query);
	}
	
	
	
	public function resequenceMenuOrder($parent_menu_id) {
	
		$loggedinUser = loggedUserID();
		$where_clause = (empty($parent_menu_id)) ? "`parent_menu_id` is NULL" : "`parent_menu_id` = " . trim($parent_menu_id);
		
		$query = "SELECT min(`order`) as lowest_order
					FROM `menu`
					WHERE $where_clause";
		
		$conn = $this->getConnection();
		$results = $conn->query($query);
		$this->chkQueryError($conn, $query);
		
		$row = $results->fetch_assoc();
		$results->close();
		$value = $row['lowest_order'] - 1;
		
		$query = "UPDATE `menu`
					SET `order` = (`order` - $value),
	   					`changeby` = '$loggedinUser'
 					WHERE $where_clause";

		$conn->query($query);
		$this->chkQueryError($conn, $query);
	
	}
	
	public function getMenuRecords() {
		 
		$where_append = (empty($_GET['menuid'])) ? "is NULL" : "= {$_GET['menuid']}";
	
		$query = "select p.label, p.url, p.programtransactions_id, m.menu_id, m.`order`
		from menu m join programtransactions p
		on m.programtransactions_id = p.programtransactions_id
		where m.parent_menu_id $where_append
		order by `order`";

		return ($this->getDatabyQuery($query));
	}
	
	
	public function getMenuBreadCrumbObj($menuid=NULL) {
		 
		$where_append = (empty($menuid)) ? " where m.menu_id is NULL" : " where m.menu_id = $menuid";
		$query = "select p.label, m.parent_menu_id, m.menu_id, m.order, m.programtransactions_id
						from menu m join programtransactions p
						on m.programtransactions_id = p.programtransactions_id
						$where_append";
	
		$breadCrumbs = $this->getDataObj($query)->fetch_object();

		return ($breadCrumbs);
	}
	
	
	public function getProgramTransactionRecords() {
		 
		$ret_array_menu = array();
		$ret_array_pgm = array();
		 
		if (empty($_GET['menuid'])) {		// For first time entry, i.e., for menu branches
			 
			$query = "select p.label, p.url, p.programtransactions_id
   						from programtransactions p
    					where `p`.`url` is NULL or `p`.`url` = ''
    					order by `p`.`label`";
			 
			$ret_array_pgm = $this->getDatabyQuery($query);
			return ($ret_array_pgm);
		}
		 
		// Following code will come to play only when any of the menu link is clicked and user enters into the menu system.
		 
		$query = "select p.label, p.url, p.programtransactions_id, m.menu_id
    				from menu m
    				join programtransactions p
    					on m.programtransactions_id = p.programtransactions_id
    				where
    					m.parent_menu_id is NULL
    				order by
    					`p`.`label`";
		 
		$ret_array_menu = $this->getDatabyQuery($query);
	
		$query = "select p.label, p.url, p.programtransactions_id
					from programtransactions p
   					where `p`.`url` != ''
					order by `p`.`label`";
		 
		$ret_array_pgm = $this->getDatabyQuery($query);
	
		return (array_merge($ret_array_menu, $ret_array_pgm));
	}
	
	
	
}


?>