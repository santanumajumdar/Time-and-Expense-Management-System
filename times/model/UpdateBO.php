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

$basedir = dirname ( dirname ( dirname ( __FILE__ ) ) );

require_once("$basedir/core/controller/UpdateBORequest.class.php");
require_once("$basedir/times/model/DbObj.php");
require_once("$basedir/times/model/Validation.php");


class UpdateBO extends __UpdateBORequest {

	public $us;
	public $projid;
	public $taskid;
	public $wdate;
	public $tdesc;
	public $tcomment;
	public $nbhrs;
	public $billhrs;
	public $locn;
	
	public function setObj() {
		
		$dateFormat = getUserDateFormat ();
		
		$this->us = trim ( $_POST ['users_id'] );
		$this->projid = trim ( $_POST ['projects_id'] );
		$this->taskid = trim ( $_POST ['tasks_id'] );
		$this->wdate = convertdate ( trim ( $_POST ['workdate'] ), $dateFormat, 'ymd' );
		$this->tdesc = trim ( $_POST ['description'] );
		$this->tcomment = trim ( $_POST ['comments'] );
		$this->nbhrs = trim ( $_POST ['nonbillablehours'] );
		$this->billhrs = trim ( $_POST ['billablehours'] );
		$this->locn = trim ( $_POST ['location'] );
	}

	public function CreateTimeCard() {
		
		try {
			$TimeData = new TimeData ();
			$uid = $TimeData->createRow ($this->us, $this->projid, $this->taskid, $this->wdate, $this->tdesc, $this->tcomment,
								$this->billhrs, $this->nbhrs, $this->locn );
			return $uid;
		
		} catch ( iBLError $e ) {
			return (convertErrorToJSONFormat ( "$e->errField", "$e->messages_id", "$e->msgdta" ));
		
		}
	}
	
	
	public function EditTimeCard($uid) {

		try {			
			$TimeData = new TimeData ();
			$TimeData->updateRow ($uid, $this->us, $this->projid, $this->taskid, $this->wdate, $this->tdesc, $this->tcomment,
								$this->billhrs, $this->nbhrs, $this->locn);
			return TRUE;
		
		} catch ( iBLError $e ) {
			return (convertErrorToJSONFormat ( "$e->errField", "$e->messages_id", "$e->msgdta" ));
			
		} catch ( iIDInUseException $e ) {
			return (convertErrorToJSONFormat ( 'projects_id', 'er0004' ));
		}
	}
	
	
	public function DeleteTimeCard($uid) {
		
		try {			
			$TimeData = new TimeData ();
			$TimeData->deleteRow ( 'times', $uid );
			return TRUE;
		
		} catch ( iBLError $e ) {
			return (convertErrorToJSONFormat ( "$e->errField", "$e->messages_id", "$e->msgdta" ));
			
		} catch ( iPKInUseException $e ) {
			return (convertErrorToJSONFormat ( 'projects_id', "er0014" ));
			
		} catch ( iFKInUseException $e ) {
			return (convertErrorToJSONFormat ( 'projects_id', "er0013" ));
		}
	}
	
	
	Public function SubmitTimeCard($uid) {
		
		try {
			$TimeData = new TimeData ();
			$TimeData->submitTimeCard($uid);
			
			return TRUE;
			
		} catch ( iBLError $e ) {
			return (convertErrorToJSONFormat ( 'nocategory', $e->messages_id, $e->msgdta ));
		
		} catch ( iIDInUseException $e ) {
			return (convertErrorToJSONFormat ( 'projects_id', 'er0004' ));
		
		} catch ( iFKInUseException $e ) {
			return (convertErrorToJSONFormat ( 'accounts_id', 'er0013' ));
		
		}
	}
	
	
	public function ApproveTimeCard($uid) {
	
		try {
			$TimeData = new TimeData ();
			$TimeData->approveTimeCard($uid);
				
			return TRUE;
		
		} catch ( iBLError $e ) {
			return (convertErrorToJSONFormat ( 'nocategory', $e->messages_id, $e->msgdta ));
				
		} catch ( iIDInUseException $e ) {
			return (convertErrorToJSONFormat ( 'projects_id', 'er0004' ));
	
		} catch ( iFKInUseException $e ) {
			return (convertErrorToJSONFormat ( 'accounts_id', 'er0013' ));
	
		}
	}
		
	
	public function HoldTimeCard($uid) {
		
		try {
					
			$TimeData = new TimeData();
			$TimeData->HoldTimeCard($uid);
		
			return TRUE;
			
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		}		
	}
	
	
	public function ReleaseTimeCard($uid) {
		
		try {
				
			$TimeData = new TimeData();
			$TimeData->ReleaseTimeCard($uid);
		
			return TRUE;
				
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		}
		
	}
	
	
	public function SubmitWeeklyTime($userid, $weekenddate) {
	
		try {
			$TimeData = new TimeData ();
			$TimeData->submitWeeklyTime($userid, $weekenddate);
			return TRUE;
		
		} catch ( iBLError $e ) {
			return (convertErrorToJSONFormat ( 'nocategory', $e->messages_id, $e->msgdta ));
					
		} catch ( iIDInUseException $e ) {
			return (convertErrorToJSONFormat ( 'projects_id', 'er0004' ));
	
		} catch ( iFKInUseException $e ) {
			return (convertErrorToJSONFormat ( 'accounts_id', 'er0013' ));
	
		}
	}
	
	
	public function ApproveWeeklyTime($userid, $weekenddate) {
	
		try {
			$TimeData = new TimeData ();
			$TimeData->approveWeeklyTime($userid, $weekenddate);
			return TRUE;
	
		} catch ( iBLError $e ) {
			return (convertErrorToJSONFormat ( 'nocategory', $e->messages_id, $e->msgdta ));
					
		} catch ( iIDInUseException $e ) {
			return (convertErrorToJSONFormat ( 'projects_id', 'er0004' ));
	
		} catch ( iFKInUseException $e ) {
			return (convertErrorToJSONFormat ( 'accounts_id', 'er0013' ));
	
		}
	}

	
	
}


?>