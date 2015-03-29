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
require_once("$basedir/core/controller/UserActionAuthorization.php");


class CompanyData extends __DBCommonFunctions
{
	public $uid;
	public $name;
	public $address1;
	public $address2;
	public $city;
	public $state;
	public $postalcode;
	public $country;
	public $weekendday;
	public $language;
	public $logo;
	public $email;
	public $createat;
	public $createby;
	public $changeat;
	public $changeby;
	
	public function getCompany($uid) {
	
		$companyObj = $this->fetchRowObjforView("company", "uid", $uid);
		return ($companyObj == NULL) ? $this : $companyObj;
		
	}

	public function updateRow($uid, $aname, $ad1, $ad2, $acity, $astate, $apostal, $acountry, $alogo, $aweekendday ,$alanguage, $aemail) {
			
		if ($uid == ''
			or $aname == ''
			)
			throw new iInvalidArgumentException();
		
		if ($aweekendday == '') $weekendday = '6';
		$saname= $this->escapeString($aname);
		$sad1= $this->escapeString($ad1);
		$sad2 = $this->escapeString($ad2);
		$sacity = $this->escapeString($acity);
		$sastate = $this->escapeString($astate);
		$sapostal = $this->escapeString($apostal);
		$sacountry = $this->escapeString($acountry);
		$salogo = $this->escapeString($alogo);
		$saweekendday= $this->escapeString($aweekendday);
		$salanguage= $this->escapeString($alanguage);
		$semail= $this->escapeString($aemail);
		$loggedinUser = loggedUserID();

		$auth = new UserActionAuthorization();
		if (!$auth->chkAuthorityLevel('EditCompany', $this->getRecordCreator('company', $uid)))
			throw new iBLError('nocategory', 'er0041');

		try
		{
			$query = "UPDATE company
						 SET 
							 name = '$saname',
       						 address1 = '$sad1',
       						 address2='$sad2',
       						 city= '$sacity',
       						 state= '$sastate',
       						 postalcode = '$sapostal',
       						 country = '$sacountry',
						 	 weekendday = '$saweekendday',
                             language = '$salanguage',
       						 logo = '$salogo',
							 email = '$semail',
       						 changeby ='$loggedinUser',
       						 changeat= now()
       					WHERE uid = '$uid'";
				
			$conn = $this->getConnection();
			$conn->query($query);
			$this->chkQueryError($conn, $query);
		
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	public function setCompany() {
		
		if (isset($_SESSION['company']))
			return;
	
		$RowData = $this->fetchRowByWhereClause("company", "uid = 1");
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

	}

}



?>