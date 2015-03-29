<?php

/* *******************************************************************************
 * TEMS is a Time and Expense Management program developed by Initechs, LLC.
 * Copyright (C) 2009 - 2010 Initechs LLC. This program is free software; you
 * can redistribute it and/or modify it under the terms of the GNU General
 * Public License version 3 as published by the Free Software Foundation with
 * the addition of the following permission added to Section 15 as permitted in
 * Section 7(a): FOR ANY PART OF THE COVERED WORK IN WHICH THE COPYRIGHT IS
 * OWNED BY INITECHS, INITECHS DISCLAIMS THE WARRANTY OF NON INFRINGEMENT OF
 * THIRD PARTY RIGHTS. This program is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details. You should have received a copy of the GNU
 * General Public License along with this program; if not, see
 * http://www.gnu.org/licenses or write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA. You can contact
 * Initechs headquarters at 1841 Piedmont Road, Suite 301, Marietta, GA, USA. or
 * at email address contact@initechs.com. The interactive user interfaces in
 * modified source and object code versions of this program must display
 * Appropriate Legal Notices, as required under Section 5 of the GNU General
 * Public License version 3. In accordance with Section 7(b) of the GNU General
 * Public License version 3, these Appropriate Legal Notices must retain the
 * display od the "Initechs" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by Initechs".
 * ******************************************************************************
 */

$basedir = dirname(dirname(dirname(dirname(__FILE__))));

function chkMandatory($var) {
	if (!isset($_POST[$var]) or trim($_POST[$var]) == '') {
		$msg = getMessage('er0011');
		return array($var => $msg);
	}
	return NULL;
}

function chkDate($var) {
	if (chkMandatory($var) != NULL)
		return NULL;

	$dateFormat = getUserDateFormat();
	if (!isValidDate(trim($_POST[$var]), $dateFormat)) {
		$msg = getMessage('er0009');
		return array($var => $msg);
	}
	return NULL;
}

function chkValidEntry($var, $table, $whereclause, $errorid='er0013') {
	
	if (!class_exists('dbObj')) {
		class dbObj extends __DBCommonFunctions {}
	}
    
	$dbConn = new dbObj();
	$RowData = $dbConn->fetchRowbyWhereClause($table, $whereclause);
    If ($RowData == NULL) {
    	$msg = getMessage($errorid);
    	return array($var => $msg);
	}
	return NULL;
}

function chkNumeric($var, $decimal=0) {
	if ($_POST[$var] == '')
		return NULL;

	if (!is_numeric($_POST[$var])) {
		$msg = getMessage('er0083');
		return array($var => $msg);
	}

        //Validate for decimals

	$num1 = trim(($_POST[$var]));
	$decpos = strpos($num1, '.');
	if ($decpos == FALSE)
		return NULL;
	$decipart = substr($num1, $decpos + 1);
	$decilen = strlen($decipart);

	if ($decilen > $decimal) {
		$msg = getMessage('er0083');
		return array($var => $msg);
	}
	return NULL;
}

function chkWeekendDate($var, $weekendday) {
	if (($return = chkMandatory($var)) != NULL)
		return $return;

	if (!validWeekendDate($_POST[$var], $weekendday)) {
		$msg = getMessage('er0021');
		return array($var => $msg);
	}
	return NULL;
}

function chkValidEmail($var) {
	
	if (filter_var($_POST[$var], FILTER_VALIDATE_EMAIL) === FALSE) {
		$msg = getMessage('er0108');
		return array($var => $msg);
	}
	return NULL;
}



?>