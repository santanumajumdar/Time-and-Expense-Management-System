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

require_once("$basedir/core/view/PageElement.class.php");
require_once("$basedir/charges/model/DbObj.php");

class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		$tabArray = array ('general'=>'General', 'journal'=>'Journal');
		
		$_GET['uid'] = (!isset($_GET['uid'])) ? "" : $_GET['uid'];

		$ChargeData = new ChargeData();
		$charges_status_array = $ChargeData->getOptionArray('charges', 'status');
		$Charge = $ChargeData->getCharge($_GET['uid']);
		
		$str = "";
		$str .= $this->setHeading("Browse Charge");
		$str .= $this->setPageTabsBegin($tabArray);
		
		$str .= "\n<div id='general'>";
		
		$str .= $this->optionFieldFromTable("User", "users_id", $Charge->users_id, "Enable-On-Create", "users", "users_id", "fullname")."<br/>";
		$query = "SELECT projects_users.projects_id as value, projects.name as text FROM projects_users INNER JOIN projects ON projects_users.projects_id = projects.projects_id";
		$str .= $this->cascadeOptionFieldFromTable("Project", "projects_id", $Charge->projects_id, "Enable-On-Create Enable-On-Edit", "projects", "users_id", "users_id", "projects_users.users_id", $query, "projects.projects_id")."<br/>";
		$str .= $this->dateField("Weekend", "weekenddate", $Charge->weekenddate, "Hide-On-Create")."<br/>";
		$str .= $this->dateField("Charge Date", "chargedate", $Charge->chargedate, "Enable-On-Create Enable-On-Edit")."<br/>";
		$str .= $this->textField("Description", "description", $Charge->description, 100, 100, "Enable-On-Create Enable-On-Edit", "Description of the charge, this may be shown on the invoice. Your client will see this.")."<br/>";
		$str .= $this->textField("Comments", "comments", $Charge->comments, 100, 100, "Enable-On-Create Enable-On-Edit", "This is for your own reference, your client will not see this, people from your own company may see this.")."<br/>";
		$str .= $this->textField("Charges", "charges", $Charge->charges, 10, 10, "Enable-On-Create Enable-On-Edit", "Enter the amount of the charge")."<br/>";
		$str .= $this->radioField("Status", "status", $Charge->status, "Hide-On-Create Hide-On-Edit", $charges_status_array)."<br/>";

		$str .= "\n<div class='button'>
		<input id='Create-Button' type='button' value='$this->createButton' onclick=\"enableCreate('CreateCharge', 'Create Charge')\">";
		
		if($Charge->status<'30')
			$str .= "<input id='Edit-Button'   type='button' value='$this->editButton'   onclick=\"enableEdit('EditCharge', 'Edit Charge')\">";
		
		$str .= "<input id='Copy-Button'   type='button' value='$this->copyButton'   onclick=\"enableCopy('CreateCharge', 'Copy Charge')\">";
		
		if($Charge->status<'30')
			$str .= "<input id='Delete-Button' type='button' value='$this->deleteButton' onclick=\"enableDelete('DeleteCharge')\">";
		
		if($Charge->status=='10')
			$str .= "<input id='Submit-Button' type='button' value='$this->submitButton' onclick=\"enableAction('SubmitCharge')\">";
		
		if($Charge->status=='20')
			$str .= "<input id='Approve-Button' type='button' value='$this->approveButton' onclick=\"enableAction('ApproveCharge')\">
			<input id='Hold-Button' type='button' value='$this->holdButton' onclick=\"enableAction('HoldCharge')\">";
		
		if($Charge->status=='80')
			$str .= "<input id='Release-Button' type='button' value='$this->releaseButton' onclick=\"enableAction('ReleaseCharge')\">";
		
		$str .= "<span id='Save-Button'></span>
		<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>
		\n</div>";
		
		$str .= "\n</div>";
		
		$str .= "\n<div id='journal'>";
		$str .= $this->textField("Invoice Number", "invoices_id", $Charge->invoices_id, 25, 25)."<br/>";
		$str .= $this->textField("Submitted On", "submitdate", $Charge->submitdate, 25, 25)."<br/>";
		$str .= $this->textField("Approved On", "approvedate", $Charge->approvedate, 25, 25)."<br/>";
		$str .= $this->textField("Invoiced On", "invoicedate", $Charge->invoicedate, 25, 25)."<br/>";
		$str .= $this->textField("Created By", "createby", $Charge->createby, 25, 25)."<br/>";
		$str .= $this->textField("Created At", "createat", $Charge->createat, 25, 25)."<br/>";
		$str .= $this->textField("Modified By", "changeby", $Charge->changeby, 25, 25)."<br/>";
		$str .= $this->textField("Modified At", "changeat", $Charge->changeat, 25, 25)."<br/>";
		$str .= "\n</div>";
		
		$str .= $this->endOfFormMessage();
		
		$str .= $this->setPageTabsEnd();			// End of Page Tab Div.

		if (empty($_GET['uid'])) {
			$str .= "<script type='text/javascript' language='JavaScript'>
					enableCreate('CreateCharge', 'Create Charge')
					</script>";
		}
		
		return $str;
	}
	
}


?>