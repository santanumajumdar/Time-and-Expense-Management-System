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


define("DEFAULT_ACTION", "ShowGetupPage");
define("DEFAULT_BO", "GetupPage");
define("DEFAULT_ACTION_AUTH", "Show");
define("DEFAULT_VIEW", "misc/view/GetupPage.php");
define("DEFAULT_MODEL", "core/model/GetList.php");

class ActionRegistry extends __DBCommonFunctions
{	

	public $action;
	public $businessObject;
	public $actionAuthority;
	public $viewFile;
	public $modelFile;
		
	public function __construct($action) {
		
		$actionObj = NULL;
		
		if (!empty($action)
			and $this->isValidAction($action)
			and (isUserRegistered()
				or $this->checkOpenAuthority($action))) {
			$actionObj = $this->fetchRowObj('actionregistry', 'action', $action);
		}

		if ($actionObj == NULL) {
			$this->action = DEFAULT_ACTION;
			$this->businessObject = DEFAULT_BO;
			$this->actionAuthority = DEFAULT_ACTION_AUTH;
			$this->viewFile = DEFAULT_VIEW;
			$this->modelFile = DEFAULT_MODEL;
		} else {
			$this->action = $action;
			$this->businessObject = $actionObj->businessobject;
			$this->actionAuthority = $actionObj->actionauthority;
			$this->viewFile = $actionObj->viewfile;
			$this->modelFile = (empty($actionObj->modelfile)) ?  DEFAULT_MODEL : $actionObj->modelfile;
		}
	}
	
	
	/**
	 * @return the $action
	 */
	public function getAction() {
		return trim($this->action);
	}

	/**
	 * @return the $businessObject
	 */
	public function getBusinessObject() {
		return trim($this->businessObject);
	}

	/**
	 * @return the $actionAuthority
	 */
	public function getActionAuthority() {
		return trim($this->actionAuthority);
	}

	/**
	 * @return the $viewFile
	 */
	public function getViewFile() {
		return trim($this->viewFile);
	}

	/**
	 * @return the $modelFile
	 */
	public function getModelFile() {
		return trim($this->modelFile);
	}	
	

}


?>