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


class ExpenseCategoryData extends __DBCommonFunctions
{
	public $uid;
	public $expensecategories_id;
	public $description;
	public $seq;
	public $ismileage;
	public $mileagerate;
	public $status;
	public $createat;
	public $createby;
	public $changeat;
	public $changeby;

	public function getExpenseCategory($uid) {
			
		$expCatObj = $this->fetchRowObjforView("expensecategories", "uid", $uid);
		return ($expCatObj == NULL) ? $this : $expCatObj;
	}
	
	
	public function createRow($ecid, $description, $seq, $stat, $ismileage, $mileagerate) {

		$expensecategories_status_array = $this->getOptionArray('expensecategories', 'status');
			
		if ($ecid == ''
			or $description == ''
			or $seq == ''
			or $stat == ''
			or $ismileage == ''
			)
			throw new iInvalidArgumentException();


		$secid = $this->escapeString($ecid);
		$sdescription= $this->escapeString($description);
		$sseq= $this->escapeString($seq);
		$sstat = $this->escapeString($stat);
		$sismileage = $this->escapeString($ismileage);
		$smileagerate = $this->escapeString($mileagerate);
		if ($smileagerate == '')
			$smileagerate = 0;
		$loggedinUser = loggedUserID();
		
		if (($sismileage == '1')
			and ($smileagerate == ''))
			throw new iBLError('mileagerate', 'er0084');

		if (($sismileage == '0')
			and ($smileagerate <> ''))
			throw new iBLError('mileagerate', 'er0085');

		try
		{
			$query = "INSERT INTO expensecategories
						(expensecategories_id, description, seq, ismileage, mileagerate, status, 
						createby, changeby, createat, changeat)
						VALUES('$secid', '$sdescription', '$sseq', $sismileage, $smileagerate, '$sstat',
						'$loggedinUser', '$loggedinUser', now(), now())";

			$conn = $this->getConnection();
			$conn->query($query);
			$recid = $conn->insert_id;
			$this->chkQueryError($conn, $query);

			return $recid;
		}
		catch (Exception $e)
		{
			throw $e;
		}

	}



	public function updateRow($uid, $description, $seq, $stat, $ismileage, $mileagerate) {
			
		$expensecategories_status_array = $this->getOptionArray('expensecategories', 'status');
		
		if ($uid == ''
			or $description == ''
			or $seq == ''
			or $stat == ''
			or $ismileage == ''
			)
			throw new iInvalidArgumentException();

		if (!isset($stat))
			throw new iInvalidDataException();

		$sdescription= $this->escapeString($description);
		$sseq= $this->escapeString($seq);
		$sstat = $this->escapeString($stat);
		$sismileage = $this->escapeString($ismileage);
		$smileagerate = $this->escapeString($mileagerate);
		$loggedinUser = loggedUserID();

		$auth = new UserActionAuthorization();
		if (!$auth->chkAuthorityLevel('EditExpenseCategory'))
			throw new iBLError('nocategory', 'er0041');

		if ($smileagerate == '')
			$smileagerate = 0;
		
		if (($sismileage == '1')
			and ($smileagerate == ''))
			throw new iBLError('mileagerate', 'er0084');

		if (($sismileage == '0')
			and ($smileagerate <> ''))
			throw new iBLError('mileagerate', 'er0085');

		try
		{
			$query = "UPDATE expensecategories
						SET 
       						description = '$sdescription',
       						seq = '$sseq',
       						ismileage = '$sismileage',
       						mileagerate = '$smileagerate',
       						status='$sstat',
       						changeby ='$loggedinUser',
       						changeat= now()
       				WHERE uid = '$uid'";
			$conn = $this->getConnection();
			$conn->query($query);
			$this->chkQueryError($conn, $query);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}
	


}


?>