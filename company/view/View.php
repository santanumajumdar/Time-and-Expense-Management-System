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
require_once("$basedir/company/model/DbObj.php");


class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		$tabArray = array ('general'=>'General', 'logo'=>'Logo', 'journal'=>'Journal');
		
		$_GET['uid'] = (!isset($_GET['uid'])) ? "" : $_GET['uid'];
		
		$CompanyData = new CompanyData();
		$language_array = $CompanyData->getOptionArray('users', 'language');
		$weekdays_array = $CompanyData->getOptionArray('', 'weekday');		
		$company = $CompanyData->getCompany($_GET['uid']);

		$str = "";
		$str .= $this->setHeading("Brwose Company Record");
		$str .= $this->setPageTabsBegin($tabArray);
		
		$str .= "\n<div id='general'>";
		$str .= $this->textField("Name", "name", $company->name, 50, 50, "Enable-On-Edit")."<br/>";
		$str .= $this->textField("Address", "address1", $company->address1, 50, 50, "Enable-On-Edit")."<br/>";
		$str .= $this->textField("Address", "address2", $company->address2, 50, 50, "Enable-On-Edit")."<br/>";
		$str .= $this->textField("City", "city", $company->city, 50, 50, "Enable-On-Edit")."<br/>";
		$str .= $this->textField("State", "state", $company->state, 50, 50, "Enable-On-Edit")."<br/>";
		$str .= $this->textField("Postal Code", "postalcode", $company->postalcode, 50, 50, "Enable-On-Edit")."<br/>";
		$str .= $this->textField("Country", "country", $company->country, 50, 50, "Enable-On-Edit")."<br/>";
		$str .= $this->textField("Email", "email", $company->email, 50, 50, "Enable-On-Edit")."<br/>";
		$str .= $this->optionField("Weekend Day", "weekendday", $company->weekendday, "Enable-On-Edit", $weekdays_array)."<br/>";
		$str .= $this->optionField("Default Language", "language", $company->language, "Enable-On-Edit", $language_array)."<br/>";		

		$str .= "\n<div class='button'>
				<input id='Edit-Button' type='button' value='$this->editButton' onclick=\"enableEdit('EditCompany', 'Edit Company Record')\">
				<span id='Save-Button'></span>
				<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>
				\n</div>";
		
		$str .= "\n</div>";
		
		$str .= "\n<div id='logo'>";		
		$str .= $this->fileField("Logo", "logo", $company->logo, "Enable-On-Edit Hide-On-Browse", "image", "uploaded-image-list")."<br/>";
		$str .= "<ul id='uploaded-image-list'></ul>";
		$str .= $this->embedImage($company->logo);
		$str .= "\n</div>";
		
		$str .= "\n<div id='journal'>";
		$str .= $this->textField("Created By", "createby", $company->createby, 25, 25)."<br/>";
		$str .= $this->textField("Created At", "createat", $company->createat, 25, 25)."<br/>";
		$str .= $this->textField("Modified By", "changeby", $company->changeby, 25, 25)."<br/>";
		$str .= $this->textField("Modified At", "changeat", $company->changeat, 25, 25)."<br/>";
		$str .= "\n</div>";

		$str .= $this->endOfFormMessage();
		
		$str .= $this->setPageTabsEnd();			// End of Page Tab Div.
		
		$str .= "<script type='text/javascript' language='JavaScript'>";
		$str .= "enableBrowse();";
		$str .= "</script>";

		return $str;
		
	}

}

?>