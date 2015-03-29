<?php

/* ********************************************************************************
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
 * *******************************************************************************/

$basedir = dirname(dirname(dirname(__FILE__)));

require_once("$basedir/core/controller/UserActionAuthorization.php");
require_once("$basedir/core/model/History.php");


class URLRequestObj {
	
	public $action;
	public $error;
	public $headerData;
	public $responseData;

	public function __construct() {
		
		global $basedir;

		$this->setAction();
		$this->headerData = "";
		$this->responseData = "";
		$this->setError(FALSE);
		
		$this->addToHistory();

		// TODO: This section should under JS control, when necessary JS code will be ready to substitute the innerHtml.  --- Kallol.
		
		$this->setHeaderData();
		
		$ActionRegistry = new ActionRegistry($this->getAction());
		$this->setAction($ActionRegistry->getAction());				// This resetting action is very important, in case ActionRegistry overrides it for security reason.

    	if (!$this->checkViewFile($ActionRegistry->getViewFile()))
    		return;
    	
    	if (!$this->checkModelFile($ActionRegistry->getModelFile()))
    		return;
    	
    	if (!$this->checkActionAuthority($ActionRegistry))
    		return;
    	
    	$this->setResponseData($ActionRegistry->getViewFile(), $ActionRegistry->getModelFile());
   		return;
    }
   

    protected function checkViewFile($viewFile) {
    	
    	global $basedir;
    	
     	if (empty($viewFile)) {
    		$this->setError('er0104');
    		$this->responseData = getMessage($this->error, $this->action);
    		return FALSE;
    	}
    	
    	$viewFile = "$basedir/$viewFile";
		if (!file_exists($viewFile)) {
    		$this->setError('er0103');
    		$this->responseData = getMessage($this->error, $viewFile);
    		return FALSE;
    	}

    	return TRUE;
    }

    
    protected function checkModelFile($modelFile) {
    	
    	global $basedir;
    	 
    	if (empty($modelFile)) {
    		$this->setError('er0104');
    		$this->responseData = getMessage($this->error, $this->action);
    		return FALSE;
    	}

    	$modelFile = "$basedir/$modelFile";
    	if (!file_exists($modelFile)) {
    		$this->setError('er0103');
    		$this->responseData = getMessage($this->error, $modelFile);
    		return FALSE;
    	}
    
    	return TRUE;
    }
    
    
    protected function checkActionAuthority($ActionRegistry) {

		$auth = new UserActionAuthorization();
		$_SESSION['auth'] = $auth->chkUserActionAuthority($ActionRegistry);
		if ($_SESSION['auth'] == 'none') {
			$this->setError('er0078');
			$this->responseData = getMessage($this->getError(), $this->getAction());
			return FALSE;
		}
    	return TRUE;
    }
    
    /**
     * @return the $action
     */
    public function getAction() {
    	return trim($this->action);
    }
    
    /**
     * @param field_type $action
     */
    protected function setAction($action=NULL) {
    	if (($action==NULL)
    		and (isset($_GET['action'])))
    		$this->action = $_GET['action'];
    	else 
    		$this->action = $action;
    	$_SESSION['action'] = $this->action;
    }
    
    /**
     * @return the $error
     */
    public function getError() {
    	return $this->error;
    }
    
    /**
     * @param Ambigous <string, boolean> $error
     */
    protected function setError($error) {
    	$this->error = $error;
    }
    
    /**
     * @return the $headerData
     */
    public function getHeaderData() {
    	return $this->headerData;
    }
    
    /**
     * @param string $headerData
     */
    protected function setHeaderData() {
    	
    	global $basedir;
    	require_once("$basedir/login/view/Login.php");
    	$LoginEntry = new LoginEntry($this->getAction());
    	$this->headerData = $LoginEntry->setLogin();
    	
    }
    
    /**
     * @return the $responseData
     */
    public function getResponseData() {
    	return $this->responseData;
    }
    
    /**
     * @param Ambigous <string, unknown> $responseData
     */
    protected function setResponseData($viewFile, $modelFile) {
    	
    	global $basedir;
    	 
    	$viewFile = "$basedir/$viewFile";
    	require_once($viewFile);
    	try {
    		$Page = new PageMainContent();
    		$this->responseData = $Page->setUIContents($modelFile);
 
    	} catch (iBLError $e) {
			$this->responseData = getMessage("$e->messages_id", "$e->msgdta");
    		
    	}
    }
    
    protected function addToHistory() {
    
    	$history = new History();
    	$history->addToHistory();
    	return TRUE;
    }
    
    
}

?>