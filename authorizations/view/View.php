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
require_once("$basedir/authorizations/model/DbObj.php");


class PageMainContent extends __PageElement {

	public function setUIContents($modelFile=NULL) {
		
		$tabArray = array ('general'=>'General', 'journal'=>'Journal');

		$_GET['uid'] = (!isset($_GET['uid'])) ? "" : $_GET['uid'];
		
		$AuthorizationListData = new authData();
		$authorization = $AuthorizationListData->getAuthorizationList($_GET['uid']);
		
		$authMatrix = $AuthorizationListData->getAuthorizationMatrix($authorization->authorizations_id);
		
		$str = "";
		$str .= $this->setHeading("Browse Authorization List");
		$str .= $this->setPageTabsBegin($tabArray);
		
		$str .= "\n<div id='general'>";
		$str .= $this->textField("Authorization List", "authorizations_id", $authorization->authorizations_id, 25, 25, "Enable-On-Create", "Enter a tag/ID for this authorization list")."<br/>";
		$str .= $this->textField("Description", "description", $authorization->description, 100, 100, "Enable-On-Create Enable-On-Edit", "Description of this authorization list for document purpose")."<br/>";
		$str .= $this->showDetail($authMatrix);
		
		$str .= "\n<div class='button'>
		<input id='Create-Button' type='button' value='$this->createButton' onclick=\"enableCreate('CreateAuthorizationList', 'Create Authorization List')\">
		<input id='Edit-Button'   type='button' value='$this->editButton'   onclick=\"enableEdit('EditAuthorizationList', 'Edit Authorizatio nList')\">
		<input id='Copy-Button'   type='button' value='$this->copyButton'   onclick=\"enableCopy('CreateAuthorizationList', 'Copy Authorization List')\">
		<input id='Delete-Button' type='button' value='$this->deleteButton' onclick=\"enableDelete('DeleteAuthorizationList')\">
		<span id='Save-Button'></span>
		<input id='Go-Back-Button' type='button' value='$this->goBackButton' onclick='window.history.go(-1)'>
		\n</div>";
		
		$str .= "\n</div>";
		
		$str .= "\n<div id='journal'>";
		$str .= $this->textField("Created By", "createby", $authorization->createby, 25, 25)."<br/>";
		$str .= $this->textField("Created At", "createat", $authorization->createat, 25, 25)."<br/>";
		$str .= $this->textField("Modified By", "changeby", $authorization->changeby, 25, 25)."<br/>";
		$str .= $this->textField("Modified At", "changeat", $authorization->changeat, 25, 25)."<br/>";
		$str .= "\n</div>";
		
		
		$str .= $this->endOfFormMessage();
		
		$str .= $this->setPageTabsEnd();				// End of Page Tab Div.

		if (empty($_GET['uid'])) {
			$str .= "<script type='text/javascript' language='JavaScript'>
					enableCreate('CreateAuthorizationList', 'Create Authorization List')
					</script>";
		}
		
		return $str;
	}
	
	
	protected function showDetail($authMatrix) {
		
		global $GLOBAL_authLevelArray;
		
		$authLevelArrayDisplay = $GLOBAL_authLevelArray;
		unset($authLevelArrayDisplay[' ']);

		$str = "";
		$old_bo = NULL;
		
		foreach ($authMatrix as $authRow) {
			if ($authRow['businessobject'] != $old_bo) {
				$str .= "<hr>" . $this->setFieldLabel($authRow['businessobject'], 'Enable-On-Create Enable-On-Edit');
				$old_bo = $authRow['businessobject'];
			}
			
			$fieldName = $authRow['businessobject'] . '___' . $authRow['actionauthority'];
			
			$str .= $this->setMartixElementLabel($authRow['actionauthority'], 'Enable-On-Create Enable-On-Edit');
			$str .= $this->optionField_w_o_label($fieldName, $authRow['authlevel'], 'Enable-On-Create Enable-On-Edit', $authLevelArrayDisplay);
		}
		
		return $str;
	}
	
}


?>