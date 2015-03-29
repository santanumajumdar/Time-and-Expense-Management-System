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

class GetList extends __DBCommonFunctions {

	public $action;
	public $table;
	public $where;
	public $advCond;
	public $fieldList;
	public $hrArray;
	public $listData;

	
	public function __construct() {
		
		// Setting up the propetries those are used throughout this class.
		
		$this->action = $_POST["action"];
		$this->table = $_POST["table"];
		$this->where = stripslashes($_POST["where"]);
		$this->advCond = $_POST["advcond"];
		$this->fieldList = json_decode(stripslashes($_POST["columns"]), TRUE);
		$this->hrArray =json_decode(stripslashes($_POST["hrconversion"]), TRUE);
		
		// Storing the search data in $_SESSION for future iterations.

		if (!empty($this->advCond)) {
			$condArray = explode("&", $this->advCond);
			if (!empty($condArray)) {
				foreach ($condArray as $phrase) {
					$fields = explode("=", $phrase);
					if (empty($fields[1]))
						unset($_SESSION['advance-search'][$_SESSION['action']][$fields[0]]);
					else
						$_SESSION['advance-search'][$_SESSION['action']][$fields[0]] = $fields[1];
				}
			}
		}
		// Getting data to show in the view. 
		// Preparing the column lists
		
		$this->addUidToColumnList();
		$this->overrideFieldList();
		
		// Fetching the data
		
		// Make the where clause combining basic condition ($this->where) and advanced search
		
		$where = $this->where;
		if (isset($_SESSION['advance-search'][$_SESSION['action']])) {
			if (!empty($this->where))
				$where .= " and ";
			foreach ($_SESSION['advance-search'][$_SESSION['action']] as $field=>$value)
				$where .= $field . " like '%" . $value . "%'";
		}
		
		$this->listData = $this->listRowsSelectiveFields($this->table, $this->fieldList, $where);
		$this->overrideListData();
		$this->customUpdate();

		// Showing the data in the view.
		
		$this->addBrowseLink();
		
		if (!empty($this->hrArray)) {
			$this->listData = $this->modifyWithHR($this->listData, $this->hrArray);
		}
		
		// Echo JSON formatted data as it is expected by the JavaScript.
		
		$jsonData = convertDataRowToJSONaaDataFormat($this->listData);
		echo $jsonData;
		return;
	}
	
	
	protected function addUidToColumnList() {
		$this->fieldList = (array_merge(array("uid"=>"uid"), $this->fieldList));
	}
	
	
	protected function overrideListData() {}
	
	
	protected function customUpdate() {}
	
	
	protected function overrideFieldList() {}
	
	
	protected function addBrowseLink() {
		
		if (empty($this->listData))
			return;
	
		$image = "<img border=0 width=20 height=20 src='images/browse.png'>";
		$keys = array_keys($this->listData[0]);
		$linkField = ($keys[0] == 'uid')? $keys[1] : $keys[0];
	
		for ($i=0; $i<count($this->listData); $i++) {
			$this->listData[$i][$linkField] = "<a href=" . $this->browseLink($i) . ">$image</a>&nbsp;" . $this->listData[$i][$linkField];
			unset ($this->listData[$i]['uid']);
		}
	}
	
	
	protected function browseLink($index) {		
		return ("index.php?action=$this->action&uid={$this->listData[$index]['uid']}");
	}
	


}



?>