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

require_once("$basedir/core/init/initialize.php");
require_once("$basedir/core/controller/UserActionAuthorization.php");
require_once("$basedir/core/model/History.php");


abstract class __UpdateBORequest
{
	
	private $action;
	
	/**
	 * @return the $action
	 */
	public function getAction() {
		return $this->action;
	}
	
	/**
	 * @param field_type $action
	 */
	public function setAction($action) {
		$this->action = $action;
	}
	

	public function setObj() {}							// This function is needed due to the framework requirement
	
	
	
	public function processRequest($action)
	{
		$return = FALSE;
		$this->setAction($action);
		
		$ActionRegistry = new ActionRegistry($this->getAction());
		$return = $this->checkAuthority($ActionRegistry);

		if ($return === TRUE) {
			$return = $this->loadModelFile($ActionRegistry->getModelFile());
		}
		
		if ($return === TRUE)
			$return = $this->validateUserInput($this->getAction());
		
		if ($return === TRUE)
			$return = $this->processAction($this->getAction(), $ActionRegistry->getModelFile());
		
		if ($return === FALSE) {
			echo "Failed";
			return FALSE;
			
		} else if ($return === TRUE) {
			echo "Successful";
			return TRUE;
					
		} else if (!is_numeric($return)) {
			echo $return;
			return FALSE;
		
		} else {
			echo "Successful : uid=$return";
			return TRUE;
		}
		
	}
	
	private function checkAuthority($ActionRegistry) {
	
		$auth = new UserActionAuthorization();
		$_SESSION['auth'] = $auth->chkUserActionAuthority($ActionRegistry);
		if ($_SESSION['auth'] == 'none') {
			$return = json_encode(array("nocategory" => getMessage('er0078', $this->getAction())));
			return $return;
		}
		return TRUE;
	}

	
	private function processAction($action, $modelFile)
	{
		$this->addToHistory();
		
		if (substr($action, 0, 5) == 'Print')
			return TRUE;
		
		$return = FALSE;
		
		switch ($action) {
				
			case 'CreateAccount':
			case 'CreateAuthorizationList':
			case 'CreateCharge':
			case 'CreateCompany':
			case 'BackupDatabase':
			case 'RestoreDatabase':
			case 'CreateExpenseCategory':
			case 'CreateExpenseDetail':
			case 'CreateExpenseHeader':
			case 'CreateLiteral':
			case 'CreateMessage':
			case 'CreateProgramTransaction':
			case 'CreateProject':
			case 'CreateProjectUser':
			case 'CreateProjectUserTask':
			case 'RegisterApps':
			case 'CompleteRegistration':
			case 'CreateTask':
			case 'CreateTimeCard':
			case 'CreateUser':
				$UpdateBO = new UpdateBO();
				$UpdateBO->setObj();
				$return = $UpdateBO->$action();
				unset ($UpdateBO);
				break;
	
			case 'EditAccount':
			case 'EditAuthorizationList':
			case 'EditCharge':
			case 'EditExpenseCategory':
			case 'EditExpenseDetail':
			case 'EditExpenseHeader':
			case 'EditLiteral':
			case 'EditMessage':
			case 'EditProgramTransaction':
			case 'EditProject':
			case 'EditProjectUser':
			case 'EditProjectUserTask':
			case 'EditTask':
			case 'EditTimeCard':
			case 'EditUser':
			case 'ChangeUserPassword':
				$postFieldsArray = array('uid');
				$return = $this->validateMandatoryPostField($postFieldsArray);
				if ($return !== TRUE)
					break;
				$UpdateBO = new UpdateBO();
				$UpdateBO->setObj();
				$return = $UpdateBO->$action($_POST['uid']);
				unset ($UpdateBO);
				break;
	
			case 'EditCompany':
			case 'DeleteAccount':
			case 'DeleteAuthorizationList':
			case 'DeleteCharge':
			case 'SubmitCharge':
			case 'ApproveCharge':
			case 'HoldCharge':
			case 'ReleaseCharge':
			case 'DeleteCompany':
			case 'DeleteExpenseCategory':
			case 'DeleteExpenseDetail':
			case 'DeleteExpenseHeader':
			case 'CreateInvoice':
			case 'UndoInvoice':
			case 'DeleteLiteral':
			case 'DeleteMessage':
			case 'DeleteProgramTransaction':
			case 'DeleteProject':
			case 'DeleteProjectUser':
			case 'DeleteProjectUserTask':
			case 'DeleteTask':
			case 'DeleteTimeCard':
			case 'SubmitTimeCard':
			case 'ApproveTimeCard':
			case 'HoldTimeCard':
			case 'ReleaseTimeCard':
			case 'DeleteUser':
				$postFieldsArray = array('uid');
				$return = $this->validateMandatoryPostField($postFieldsArray);
				if ($return !== TRUE)
					break;
				$UpdateBO = new UpdateBO();
				$return = $UpdateBO->$action($_POST['uid']);
				unset ($UpdateBO);
				break;
					
			case 'SubmitExpenseHeader':
			case 'VerifyExpenseHeader':
			case 'HoldExpenseHeader':
			case 'ReleaseExpenseHeader':
				$postFieldsArray = array('expenseuid');
				$return = $this->validateMandatoryPostField($postFieldsArray);
				if ($return !== TRUE)
					break;
				$UpdateBO = new UpdateBO();
				$return = $UpdateBO->$action($_POST['expenseuid']);
				unset ($UpdateBO);
				break;
	
			case 'SubmitWeeklyTime':
			case 'ApproveWeeklyTime':
				$postFieldsArray = array('users_id', 'weekenddate');
				$return = $this->validateMandatoryPostField($postFieldsArray);
				if ($return !== TRUE)
					break;
				$UpdateBO = new UpdateBO();
				$return = $UpdateBO->$action($_POST['users_id'], $_POST['weekenddate']);
				unset ($UpdateBO);
				break;
				
			default:
				$return = json_encode(array("nocategory" => "Technical Error: <b>" . $action ."</b> --- is not configured in processRequest method of /core/controller/UpdateBORequest.class.php"));
				break;
		}
		
		return $return;
	
	}
	
	
	protected function loadModelFile($modelFile) {
		
		global $basedir;
		
		if (empty($modelFile)) 
			return (json_encode(array("nocategory" => "Technical Error: \"<b>modelFile</b>\" is not configured in ActionRegistry table for action = <b>" . $this->action . "</b>")));
		
		$modelFile = "$basedir/$modelFile";
		
		if (!file_exists($modelFile))
			return (json_encode(array("nocategory" => "Technical Error: The model file <b>" . $modelFile."</b> does not exist.")));
		
		require_once($modelFile);
		return TRUE;
	}
	
	
	protected function validateMandatoryPostField($postFieldsArray) {

		foreach ($postFieldsArray as $field) {
			if (empty($_POST[$field])) {
				$msg = getMessage('er0107', $field);
				return (json_encode(array("nocategory" => $msg)));
			}
		}
		return TRUE;
	}


	protected function validateUserInput($action) {
		
		$gerenal_error = changeLiteral("There may be more errors in the other tabs.");
		$gerenal_error = array('nocategory' => $gerenal_error);
		
		if (!class_exists("UserInput"))
			return TRUE;
		
		$UserInput = new UserInput();
		$ret =  $UserInput->validate($action);
		
		if (!empty($ret)) {
			$ret = array_merge($ret, $gerenal_error);
			return (json_encode($ret));
		}
		return TRUE;
	}


	protected function addToHistory() {
	
		$history = new History();
		$history->addToHistory();
		return TRUE;
	}


	
}



?>