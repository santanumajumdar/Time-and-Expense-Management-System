<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

$basedir = dirname(dirname(dirname(__FILE__)));
require_once("$basedir/core/model/DBCommonFunctions.class.php");


class dbObjforPage extends __DBCommonFunctions {}


abstract class __PageElement {
	
	public $createButton;
	public $editButton;
	public $copyButton;
	public $deleteButton;
	public $goBackButton;
	public $submitButton;
	public $approveButton;
	public $verifyButton;
	public $releaseButton;
	public $holdButton;
	public $printButton;
	public $printWithAssociatedDocumentButton;
	public $changePasswordButton;
	public $addProjectButton;
	public $addUserToProjectButton;
	public $addTaskToProjectButton;
	public $addExpenseDetailButton;
	public $createInvoiceButton;
	public $undoInvoiceButton;
	public $enterTimeCardButton;
	public $createPDFButton;
	public $proceedButton;
	
	

	
	public function __construct() {
		
		$this->createButton = changeLiteral('Create');
		$this->editButton = changeLiteral('Edit');
		$this->copyButton = changeLiteral('Copy');
		$this->deleteButton = changeLiteral('Delete');
		$this->goBackButton = changeLiteral('Go Back');
		$this->submitButton = changeLiteral('Submit');
		$this->approveButton = changeLiteral('Approve');
		$this->verifyButton = changeLiteral('Verify');
		$this->releaseButton = changeLiteral('Release');
		$this->holdButton = changeLiteral('Hold');
		$this->printButton = changeLiteral('Print');
		$this->printWithAssociatedDocumentButton = changeLiteral('Print With Associated Documents');
		$this->changePasswordButton = changeLiteral('Change Password');
		$this->addProjectButton = changeLiteral('Add Project');
		$this->addUserToProjectButton = changeLiteral('Add User to Project');
		$this->addTaskToProjectButton = changeLiteral('Add Task to Project');
		$this->addExpenseDetailButton = changeLiteral('Add Expense Detail');
		$this->createInvoiceButton = changeLiteral('Create Invoice');
		$this->undoInvoiceButton = changeLiteral('Undo Invoice');
		$this->enterTimeCardButton = changeLiteral("Enter New Time Card");
		$this->createPDFButton = changeLiteral('Create PDF');
		$this->proceedButton = changeLiteral('Proceed');
		
		
	}
	

	protected function setHeading($heading=NULL, $fieldArray=NULL, $createAction=NULL, $class=NULL) {
		
		$str = '';
		if (!empty($heading)) {
			$heading = changeLiteral($heading);
			$str .= "\n<div class='option-heading $class'>";
			$str .= "\n$heading";
			if (!empty($fieldArray))
				$str .= "\n<button id='Advance-Search-Button' class='Advance-Search-Button-Label $class'>" . changeLiteral("Advanced Search") . "</button>";
			if (!empty($createAction))
				$str .= "\n<input id='Create-Button' class='create-button-on-heading-band $class' type='button' value='$this->createButton' onclick=\"actionURL('action=$createAction')\">";
			$str .= "\n</div>";
			
			if (!empty($fieldArray))
				$str .= $this->setAdvancedSearch($fieldArray, $class);
		}
		return $str;
	}
	
	
	protected function setAdvancedSearch($fieldArray, $class=NULL) {
		
		$str = "";

		$str .= "\n<div id='Advanced-Search-Form' title='".changeLiteral("Advance Search")."'>";
		$str .= "\n<form>";
		$str .= "\n<fieldset>";
		foreach ($fieldArray as $fieldName=>$fieldLabel) {
			$fieldValue = (isset($_SESSION['advance-search'][$_SESSION['action']][$fieldName])) ?
				 $_SESSION['advance-search'][$_SESSION['action']][$fieldName] : "";
			$str .= "\n<label class='search-field-label' for='$fieldName'>$fieldLabel:</label>";
			$str .= "\n<input type='text' name='$fieldName' value='$fieldValue' id='$fieldName-fld' class='$class advance-search text ui-widget-content ui-corner-all' /></br>";
		}
		$str .= "\n</fieldset>";
		$str .= "\n</form>";
		$str .= "\n</div>";
		
		if ((isset($_SESSION['advance-search'][$_SESSION['action']]))
			and (count($_SESSION['advance-search'][$_SESSION['action']]) > 0))
			$str .= "<script>glowAdvSearch(true);</script>";
		
		
		return $str;
	}
	
	
	protected function endOfFormMessage() {
		
		$str = "";
		$str .= "\n<div id='Error-Msg'></div>";
		$str .= "\n<div id='Successful-Msg'></div>";
		return $str;
	}
	
	
	protected function setDatatableLayout($tableID, $colArray) {

		$str = "";
		$str .= "\n<table id='$tableID' class='list'>";
		$str .= "\n<thead><tr>\n";
		foreach ($colArray as $fld=>$desc) {
			$desc = changeLiteral($desc);
			$str .= "<th>$desc</th>";
		}
		$str .= "\n</tr></thead>";
	
		$str .= "\n<tbody></tbody>";
		$str .= "\n</table>";
		
		return $str;
	}
	
	
	protected function setPageTabsBegin($tabArray) {
	
		if (empty($tabArray))
			return;
		
		$str = '';
		$str .= "\n<div id='page-tabs'>";
		$str .= "\n<ul>";
		foreach ($tabArray as $tabId=>$tabLabel)
			$str .= "\n<li><a href='#$tabId'><span id=$tabId-tab>$tabLabel</span></a></li>";
		$str .= "\n</ul>";
		
		return $str;
	}
	
	protected function setPageTabsEnd() {

		$str = '';
		$str .= "\n</div>";
	
		return $str;
	}
	
	protected function hiddenField($fieldName, $fieldValue) {
	
		$str = '';
		$str .= "\n<input
					id='{$fieldName}-fld'
					name='$fieldName'	
					type='hidden'
					value='$fieldValue'>";
		
		return $str;
	}
	
	protected function displayField($fieldLabel, $fieldName, $fieldValue, $class=NULL) {
	
		$str = "";
		$str .= $this->setFieldLabel($fieldLabel, $class);
		$str .= "\n<div id='{$fieldName}-fld' class='fieldEntry $class'>$fieldValue</div>";
	
		return $str;
	}
	
	protected function textField($fieldLabel, $fieldName, $fieldValue, $size, $fieldlen, $class=NULL, $tooltip=NULL, $lookupTable=NULL, $lookupField=NULL, $lookupTableFilter=NULL) {
		
		if (!empty($fieldValue) 
				and (strtotime($fieldValue))) {
			$dateFormat = getUserDateFormat();
			$dateField = $fieldValue;
			$fieldValue = cvtDateIso2Dsp($dateField, $dateFormat);
			$fieldValue .= cvtTime2Dsp($dateField);
		}

		if (!empty($tooltip))
			$tooltip = changeLiteral($tooltip);
		$str = "";

		$str .= $this->setFieldLabel($fieldLabel, $class);
		
		$str .= "\n<div id='{$fieldName}-field-and-error' class='fieldEntry'>
					<input
						id='{$fieldName}-fld'
						class='$class'
						name='$fieldName'
						type='text'
						value='$fieldValue'
						size='$size'
						maxlength='$fieldlen'
						disabled='disabled'
						title='$tooltip'";
		
		if (($lookupTable != NULL) && ($lookupField != NULL))
			$str .= " onkeyup=\"showTips(this.value, '$lookupTable', '$lookupTableFilter', '$lookupField', '{$fieldName}-fld')\"";
		
		$str .= ">";

		$str .= $this->fieldLevelError($fieldName);
		$str .= "\n</div>";

		return $str;
	}

	
	protected function textArea($fieldLabel, $fieldName, $fieldValue, $size, $fieldlen, $class=NULL, $tooltip=NULL) {
		
		if (!empty($tooltip))
			$tooltip = changeLiteral($tooltip);
		
		$str = "";
		
		$str .= $this->setFieldLabel($fieldLabel, $class);
		
		$row = 1 + ($size - ($size % 20)) / 20;
		
		$str .= "\n<div id='{$fieldName}-field-and-error' class='fieldEntry'>
				<input 
					id='{$fieldName}-fld'
					class='$class'
					name='$fieldName'
					type='textarea'
					value='$fieldValue'
					size='$size'
					maxlength='$fieldlen'
					row='$row'
					col='20'
					disabled='disabled'
					title='$tooltip'>";
		
		$str .= $this->fieldLevelError($fieldName);
		$str .= "\n</div>";
		
		return $str;
	}

	
	protected function passwordField($fieldLabel, $fieldName, $size, $fieldlen, $class=NULL, $tooltip=NULL) {
		
		if (!empty($tooltip))
			$tooltip = changeLiteral($tooltip);
	
		$str = "";
		$str .= $this->setFieldLabel($fieldLabel, $class);
	
		$str .= "\n<div id='{$fieldName}-field-and-error' class='fieldEntry'>
					<input
						id='{$fieldName}-fld'
						class='$class'
						name='$fieldName'
						type='password'
						size='$size'
						maxlength='$fieldlen'
						title='$tooltip'>";
			
		$str .= $this->fieldLevelError($fieldName);
		$str .= "</div>";
		return $str;
	}
	
	protected function cascadeOptionFieldFromTable($fieldLabel, $fieldName, $fieldValue, $class, $table, $fldOnChange, $refDspFldIds, $refDbFlds, $query, $distinctFields=NULL) {

	// NOTE: $refDspFldIds and $refDbFlds MUST have one to one match.
	
		$optionArray = array();
		
		if (!empty($fieldValue)) {
			$optionArray = array();
			$dbObjforPage = new dbObjforPage();
			$sql = $query . " and $table.$fieldName='$fieldValue'";
			$optionArrayObj = $dbObjforPage->getDataObj($sql);
			while (($row = $optionArrayObj->fetch_assoc()) !== NULL)
				$optionArray[$row['value']] = $row['text'];
		}
		
		$str = "";
		$str .= $this->optionField($fieldLabel, $fieldName, $fieldValue, $class, $optionArray);
		$str .= "\n<script type='text/javascript' language='JavaScript'>";
		$str .= "\n$('#$fldOnChange-fld').change(function(){";
		$str .= "\ncascadeOption('$refDspFldIds', '$refDbFlds', '{$fieldName}-fld', '$query', '$distinctFields');";
		$str .= "\n});";
		$str .= "\n</script>";
		
		return $str;
	}
	
	protected function optionFieldFromTable($fieldLabel, $fieldName, $fieldValue, $class, $table, $dbfield, $dspField) {
		
		$select = changeLiteral("--Select--");
		$optionArray = array('' => $select);
		$dbObjforPage = new dbObjforPage();
		$columns = "$dbfield, $dspField";
		$optionArrayObj = $dbObjforPage->getDataSetObj($table, $columns, '', $dspField);
		while (($row = $optionArrayObj->fetch_assoc()) !== NULL)
				$optionArray[$row[$dbfield]] = $row[$dspField];
		$str = '';
		$str .= $this->optionField($fieldLabel, $fieldName, $fieldValue, $class, $optionArray);
		return $str;
	}
	
	protected function optionFieldByQuery($fieldLabel, $fieldName, $fieldValue, $class, $query, $dbfield, $dspField) {
	
		$select = changeLiteral("--Select--");
		$optionArray = array('' => $select);
		$dbObjforPage = new dbObjforPage();
		$optionArrayObj = $dbObjforPage->getDataObj($query);
		while (($row = $optionArrayObj->fetch_assoc()) !== NULL)
			$optionArray[$row[$dbfield]] = $row[$dspField];
		$str = '';
		$str .= $this->optionField($fieldLabel, $fieldName, $fieldValue, $class, $optionArray);
		return $str;
	}
	
	protected function optionField($fieldLabel, $fieldName, $fieldValue, $class=NULL, $optionArray=NULL) {
		
		$str = '';
		$str .= $this->setFieldLabel($fieldLabel, $class);
		$str .= $this->optionField_w_o_label($fieldName, $fieldValue, $class, $optionArray);
		return $str;
	}
	
	
	protected function optionField_w_o_label($fieldName, $fieldValue, $class=NULL, $optionArray=NULL) {
		
		$str = '';
	
		$str .= "\n<div id='{$fieldName}-field-and-error' class='fieldEntry' >";
		$str .= "\n<select id='{$fieldName}-fld' class='$class' name='$fieldName' disabled='disabled'>";
	
		if ($optionArray == NULL) {
			$select = changeLiteral("--Select--");
			$optionArray = array('' => $select);
		}
		
		foreach ($optionArray as $key => $value) {
			$str .= "\n<option";
			if ($key == $fieldValue)
				$str .= " selected='selected'";
			
			$value = changeLiteral($value);
			$str .= " value='$key'>$value</option>";
		}
	
		$str .= "\n</select>";
	
		$str .= $this->fieldLevelError($fieldName);
		$str .= "</div>";
	
		return $str;
	}
	
	protected function radioField($fieldLabel, $fieldName, $fieldValue,  $class=NULL, $optionArray=NULL) {
		
		$str = '';
		$str .= $this->setFieldLabel($fieldLabel, $class);
	
		$str .= "\n<div id='{$fieldName}-field-and-error' class='fieldEntry $class'>";
		foreach ($optionArray as $key => $value) {
			$str .= "<input
						id='{$fieldName}-fld'
						class='$class'
						name='$fieldName'
						type='radio'
						value='$key'
						disabled='disabled'";
			if ($key == $fieldValue)
				$str .= " checked='checked'";
	
			$value = changeLiteral($value);
			$str .= ">$value";
		}

		$str .= $this->fieldLevelError($fieldName);
		$str .= "</div>";

		return $str;
	}
	
	protected function dateField($fieldLabel, $fieldName, $fieldValue, $class=NULL, $tooltip=NULL) {

		if (!empty($tooltip))
			$tooltip = changeLiteral($tooltip);
		
		$dateFormat = getUserDateFormat();
	
		$str = '';
		$str .= $this->setFieldLabel($fieldLabel, $class);
		
		if (!empty($fieldValue)
				and (strtotime($fieldValue))) {
			$dateField = $fieldValue;
			$fieldValue = cvtDateIso2Dsp($dateField, $dateFormat);
			$fieldValue .= cvtTime2Dsp($dateField);
		}
		
		
		$CalDateFmt = 'mm/dd/yy';
		$LblDateFmt = 'MM/DD/YYYY';
		$dateFormat = strtolower($dateFormat);
	
		if ($dateFormat == 'mdy') {
			$CalDateFmt = 'mm/dd/yy';
			$LblDateFmt = 'MM/DD/YYYY';
		} elseif ($dateFormat == 'dmy') {
			$CalDateFmt = 'dd/mm/yy';
			$LblDateFmt = 'DD/MM/YYYY';
		} elseif ($dateFormat == 'ymd') {
			$CalDateFmt = 'yy/mm/dd';
			$LblDateFmt = 'YYYY/MM/DD';
		}
		
		$str .= "\n<div id='{$fieldName}-field-and-error' class='fieldEntry'>
					<input						
						id='{$fieldName}-fld'
						class='$class'
						name='$fieldName'
						type='text'
						value='$fieldValue'
						size='10'
						maxlength='10'
						title='$tooltip'
						disabled='disabled'
						onfocus=\"calendar(this, '$CalDateFmt')\">";
	
		$str .= "<span class='$class'>".$LblDateFmt."</span>";

		$str .= $this->fieldLevelError($fieldName);
		$str .= "</div>";
		
		return $str;
	}
	
	
	
	protected function fileField($fieldLabel, $fieldName, $fieldValue,  $class=NULL, $type='image', $imageLocation=NULL, $loader='uploadImages', $preview='yes') {
	
		$str = "";
		$str .= $this->setFieldLabel($fieldLabel, $class);
					
		$str .= "\n<div id='{$fieldName}-field-and-error' class='fieldEntry'>
				<input
					id='{$fieldName}-fld'
					class='$class'
					name='$fieldName'
					type='file'
					accept='$type'
					disabled='disabled'
					onchange=\"$loader('{$fieldName}-fld', '$imageLocation', '$preview')\">";
		
		$str .= $this->fieldLevelError($fieldName);
		$str .= "</div><br/>";

		return $str;
	}

	
	
	protected function embedImage ($fieldValue) {
		
		$str = "";
		
		if (empty($fieldValue))
			return NULL;

		if (strtolower(substr($fieldValue, strrpos($fieldValue, '.'))) == ".pdf") {
			$str .= "\n<object class='receipt-image' data='$fieldValue' width='100%' height='700'></object>";
		} else {
			$str .= "\n<object class='receipt-image' data='$fieldValue'></object>";
		}
		
		return $str;
	}

	
	
	protected function setFieldLabel($fieldLabel, $class=NULL) {
	
		$labelClass = "class='fieldLabel $class'";
		$str = "";
		$str .= "\n<div $labelClass>".changeLiteral($fieldLabel).":</div>";
		
		return $str;
	}
	
	
	
	protected function setMartixElementLabel($fieldLabel, $class=NULL) {
	
		$labelClass = "class='matrixLabel $class'";
		$str = "";
		$str .= "\n<div $labelClass>".changeLiteral($fieldLabel).":</div>";
	
		return $str;
	}
	
	protected function fieldLevelError($fieldName) {
		$str = '';
		$str .= "\n<div id='{$fieldName}-error' class='in-line-error-message'></div>";
		return $str;
	}


	protected function onClickFillupList($tab, $tableID, $columnsJSON, $program, $table, $action, $where=NULL, $statusJSON=NULL) {
	
		$str = "";
		$str .= "\n<script type='text/javascript' language='JavaScript'>";
		$str .= "\n$(\"#$tab-tab\").click(function() {";
		$str .= "\nvar columns = '$columnsJSON';";
		$str .= "\nvar hrConversion = '$statusJSON';";
		$str .= "\nfillupList('#$tableID', columns, '$program', '$table', '$action', '$where', hrConversion);";
		$str .= "\n$(\"#$tab-tab\").unbind('click');";
		$str .= "\n});";
		$str .= "\n</script>";
	
		return $str;
	}
	
	protected function fillupList($tableID, $columnsJSON, $program, $table, $action, $where=NULL, $statusJSON=NULL) {
		
		$str = "";
		$str .= "\n<script type='text/javascript' language='JavaScript'>";
		$str .= "\nvar columns = '$columnsJSON';";
		$str .= "\nvar hrConversion = '$statusJSON';";
		$str .= "\nfillupList('#$tableID', columns, '$program', '$table', '$action', '$where', hrConversion);";
		$str .= "\nfunction fillupListWrapper(advCond) {";
		$str .= "\n\tfillupList('#$tableID', columns, '$program', '$table', '$action', '$where', hrConversion, advCond);";
		$str .= "\n}";
		$str .= "\n</script>";
		
		return $str;
	}
	

    protected function printField($fieldValue, $style='', $fieldType='textField', $validValues='', $fieldSize=0) {
        $dateFormat = getUserDateFormat();

        if (strtolower(trim($fieldType)) == 'datefield')
            $fieldValue = cvtDateIso2Dsp($fieldValue, $dateFormat);

        if ((strtolower(trim($fieldType)) == 'optionfield')
                or (strtolower(trim($fieldType)) == 'radiofield'))
            $fieldValue = $validValues[$fieldValue];

        if ($fieldSize > 0)
            $fieldValue = substr($fieldValue, 0, $fieldSize - 1);

        if ($fieldValue == '')
            $fieldValue = '&nbsp;';

        if ($style <> '')
            $style = "style='{$style}'";

        $str = "<td $style>$fieldValue</td>";

        return $str;
    }
    


}

?>