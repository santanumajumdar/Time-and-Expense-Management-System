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
$basedir = dirname(dirname(dirname(dirname(__FILE__))));
require_once("$basedir/core/model/DBCommonFunctions.class.php");

function getCompany() {
    if (isset($_SESSION['company']))
        return;
        
    if (!class_exists('dbComObj')) {
        class dbComObj extends __DBCommonFunctions { }
    }

    $DbObj = new dbComObj();
    $RowData = $DbObj->fetchRowByWhereClause("company", "uid = 1");
    if ($RowData != NULL) {
        $_SESSION['company']['company_id'] = $RowData['company_id'];
        $_SESSION['company']['name'] = $RowData['name'];
        $_SESSION['company']['address1'] = $RowData['address1'];
        $_SESSION['company']['address2'] = $RowData['address2'];
        $_SESSION['company']['city'] = $RowData['city'];
        $_SESSION['company']['state'] = $RowData['state'];
        $_SESSION['company']['postalcode'] = $RowData['postalcode'];
        $_SESSION['company']['country'] = $RowData['country'];
        $_SESSION['company']['logo'] = $RowData['logo'];
        $_SESSION['company']['weekendday'] = $RowData['weekendday'];
        $_SESSION['company']['language'] = $RowData['language'];
    }
    unset($DbObj);
}

class ReportFunctions {

    public function ProjectCompanyAddress($projects_id) {
        $CA = '';
        $DbObj = new dbObj();

        $query = "select accounts.name as name,
			 accounts.address1 as address1,
			 accounts.address2 as address2,
			 accounts.city as city,
			 accounts.state as state,
			 accounts.postalcode as postalcode
		from projects, accounts
		where projects.projects_id = '$projects_id'
		    and projects.billtoaccounts_id = accounts.accounts_id
		    and projects.billtoaccounts_id <> projects.accounts_id";
        $dataset = $DbObj->getDatabyQuery($query);
        if ((!isset($dataset[0])) or ($dataset[0] == NULL)) {
            $companyRec = $DbObj->getCompanyRec();
            $CA['name'] = $companyRec['name'];
            $CA['address1'] = $companyRec['address1'];
            $CA['address2'] = $companyRec['address2'];
            $CA['city'] = $companyRec['city'];
            $CA['state'] = $companyRec['state'];
            $CA['postalcode'] = $companyRec['postalcode'];
        } else {
            $CA['name'] = $dataset[0]['name'];
            $CA['address1'] = $dataset[0]['address1'];
            $CA['address2'] = $dataset[0]['address2'];
            $CA['city'] = $dataset[0]['city'];
            $CA['state'] = $dataset[0]['state'];
            $CA['postalcode'] = $dataset[0]['postalcode'];
        }
        return $CA;
    }

}


?>