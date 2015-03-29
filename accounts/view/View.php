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
 * The interactive account interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display od the "Initechs" logo.
 * If the display of the logo is not reasonably feasible for technical reasons,
 * the Appropriate Legal Notices must display the words "Powered by Initechs".

 ********************************************************************************/

$basedir = dirname(dirname(dirname(__FILE__)));

require_once("$basedir/core/view/PageElement.class.php");
require_once("$basedir/accounts/model/DbObj.php");
require_once("$basedir/core/controller/ActionRegistry.php");


class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		$_GET['uid'] = (!isset($_GET['uid'])) ? "" : $_GET['uid'];
		
		$tabArray = array ('general'=>'General', 'journal'=>'Journal', 'projects'=>'Projects');
		
		$AccountData = new AccountData();
		$accounts_status_array = $AccountData->getOptionArray('accounts', 'status');
		$account = $AccountData->getAccount($_GET['uid']);
		
		$projectListView = $this->projectList($account->accounts_id, "projects");
		
		$str = "";
		$str .= $this->setHeading("Browse Account");
		$str .= $this->setPageTabsBegin($tabArray);
		
		$str .= "\n<div id='general'>";
		$str .= $this->textField("Account", "accounts_id", $account->accounts_id, 25, 25, "Enable-On-Create Show-On-Print", "Enter account code that will be used to identify the account")."<br/>";
		$str .= $this->textField("Account Name", "name", $account->name, 50, 50, "Enable-On-Create Enable-On-Edit", "Descriptive name of the account")."<br/>";
		$str .= $this->textField("Address Line1", "address1", $account->address1, 50, 50, "Enable-On-Create Enable-On-Edit Hide-On-Print", "Postal address")."<br/>";
		$str .= $this->textField("Address Line2", "address2", $account->address2, 50, 50, "Enable-On-Create Enable-On-Edit Hide-On-Print", "Postal address continued from the first line")."<br/>";
		$str .= $this->textField("City", "city", $account->city, 50, 50, "Enable-On-Create Enable-On-Edit Hide-On-Print", "City")."<br/>";
		$str .= $this->textField("State", "state", $account->state, 20, 20, "Enable-On-Create Enable-On-Edit Hide-On-Print", "State/Province")."<br/>";
		$str .= $this->textField("Postal Code", "postalcode", $account->postalcode, 20, 20, "Enable-On-Create Enable-On-Edit Hide-On-Print", "PIN/Zip Code")."<br/>";
		$str .= $this->textField("Country", "country", $account->country, 20, 20, "Enable-On-Create Enable-On-Edit Hide-On-Print", "Country")."<br/>";
		$str .= $this->textField("Contact", "contact", $account->contact, 20, 20, "Enable-On-Create Enable-On-Edit Hide-On-Print", "Name of the contact person where all time sheet, expenses, charges, and invoices will be send.")."<br/>";
		$str .= $this->textField("Email", "email", $account->email, 50, 50, "Enable-On-Create Enable-On-Edit Hide-On-Print", "Email of the account contact for sending the time cards, expenses, chrages, and invoices.")."<br/>";
		$str .= $this->dateField("Last Bill Date", "lastbilldate", $account->lastbilldate, 'Hide-On-Create Hide-On-Edit Hide-On-Print')."<br/>";
		$str .= $this->radioField("Status", "status", $account->status, "Enable-On-Create Enable-On-Edit Hide-On-Print", $accounts_status_array)."<br/>";
		$str .= $this->dateField("Weekend Date", "weekenddate", "", "Hide-On-All Enable-On-Print Enable-On-Print")."<br/>";
		
		$str .= "\n<div class='button'>
		<input id='Create-Button' type='button' value='$this->createButton' onclick=\"enableCreate('CreateAccount', 'Create Account')\">
		<input id='Edit-Button'   type='button' value='$this->editButton'   onclick=\"enableEdit('EditAccount', 'Edit Account')\">
		<input id='Copy-Button'   type='button' value='$this->copyButton'   onclick=\"enableCopy('CreateAccount', 'Copy Account')\">
		<input id='Delete-Button' type='button' value='$this->deleteButton' onclick=\"enableDelete('DeleteAccount')\">
		<input id='Print-Button'  type='button' value='$this->printButton'  onclick=\"enablePrint('PrintAccountWeeklyTimeSheet', 'Print Weekly Time Sheet')\">
		<span id='Save-Button'></span>
		<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>
		\n</div>";
		
		$str .= "\n</div>";
		
		$str .= "\n<div id='journal'>";
		$str .= $this->textField("Created By", "createby", $account->createby, 25, 25)."<br/>";
		$str .= $this->textField("Created At", "createat", $account->createat, 25, 25)."<br/>";
		$str .= $this->textField("Modified By", "changeby", $account->changeby, 25, 25)."<br/>";
		$str .= $this->textField("Modified At", "changeat", $account->changeat, 25, 25)."<br/>";
		$str .= "\n</div>";
		
		$str .= "\n<div id='projects'>";
		$str .= $projectListView;
		$str .= "\n</div>";
		
		$str .= $this->endOfFormMessage();
		$str .= $this->setPageTabsEnd();				// End of Page Tab Div.;

		$str .= "<script type='text/javascript' language='JavaScript'>";
		if (empty($_GET['uid'])) {
			$str .= "enableCreate('CreateAccount', 'Create Account')";
		} else {
			$str .= "enableBrowse()";
		}
		
		$str .= "</script>";
		
		return $str;
	}
	
	
	protected function projectList($accountID, $tab) {
		
		$ActionRegistry = new ActionRegistry("ListProject");
		$modelFile = $ActionRegistry->getModelFile();
		
		$columnsArray = array(	"projects_id" => "Project",
								"name" => "Project Name",
								"billtoaccounts_id" => "Bill To Account",
								"status" => "Status");

		$statusArray = array(	"table" => "projects",
								"column" => "status");
		
		$columnsJSON = json_encode($columnsArray);
		$statusJSON = json_encode($statusArray);
		
		$where = "accounts_id=\"$accountID\" or billtoaccounts_id=\"$accountID\" ";
		
		$str = "";
		$str .= $this->setHeading("Account :: $accountID");
		$str .= $this->setDatatableLayout('list-table-project', $columnsArray);
		$str .= "<input id='Create-Button' class='Add-Button-Footer' type='button' value='$this->addProjectButton' onclick=\"actionURL('action=CreateProject&accounts_id=$accountID')\">";
		
		$str .= $this->onClickFillupList($tab, 'list-table-project', $columnsJSON, $modelFile, 'projects', 'BrowseProject', $where, $statusJSON);

		return $str;
	}
		
}


?>