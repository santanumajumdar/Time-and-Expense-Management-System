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

require_once("$basedir/core/view/PageElement.class.php");

class LoginEntry extends __PageElement {
	
	public $action;
	
	public function __construct($action=NULL) {
		
		$this->action = $action;
	}
	

	/**
	 * @return the $action
	 */
	public function getAction() {
		return $this->action;
	}
	

	public function setLogin () {
		
		global $basedir;
		
		if ($this->getAction() == 'CompleteRegistration')
			return;

        $str = '';
		
        if (!$this->isAppsRegistered()) {
        	$str .= $this->registerAppString();
        }
        
		if (!isUserRegistered()) {
			$str .= $this->loginString();
			$str .= $this->loginForm();
			if (file_exists("$basedir/selfregistration"))
				$str .= $this->userRegistrationForm();
		
		} else {
			$str .= $this->logoutString();
		}        

        return $str;
    }
    
    
    private function isAppsRegistered () {
    	 
    	global $registerFile;
    
    	if (isset($_SESSION['registered']))
    		return TRUE;
    
    	if (!file_exists($registerFile))
    		return FALSE;
    
    	$content = file($registerFile, FILE_IGNORE_NEW_LINES);
    	if (strlen($content[0]) != 64)	
    		return FALSE;
    	
    	if (md5(substr($content[0], 0, 32)) != substr($content[0], 32, 32))
    		return FALSE;
    		
    	$_SESSION['registered'] = TRUE;
    	return TRUE;
    }
    
    
    private function registerAppString() {
    	
    	$str = "";
    	$str .= "<span class='register'><a href='index.php?action=RegisterApps'>" 
        			. changeLiteral("Register your application at no-cost.") 
        			. "</a></span>";
    	return $str;
    }
    
    
    private function loginString() {
    	
    	global $basedir;
    	 
    	$str = "";
    	$str .= "<span class='app-logo'><a href='http://www.temsonline.com'><img class='logo' src='images/TEMS_Logo.png' alt='TEMS Logo'/></a></span>";
    	$str .= "\n<span class='right-link'>";
    	$str .= "\n<button type='button' id='login-button' class='text ui-state-default ui-corner-all'>Login</button>";
    	if (file_exists("$basedir/selfregistration"))
    		 $str .= "\n<button type='button' id='register-button' class='text ui-state-default ui-corner-all'>Register</button>";
    	$str .= "\n</span>";
    	return $str;
    }
    
    
    private function logoutString() {
    	
    	$str = "";
    	$str .= "<span class='app-logo'><a href='http://www.temsonline.com'><img class='logo' src='images/TEMS_Logo.png' alt='TEMS Logo'/></a></span>";
    	$str .= "\n<span class='right-link'>";
    	$str .= ChangeLiteral('Welcome')
			    	. " <a href='index.php?action=BrowseUser&uid=".loggedInUserUid()."'><b>".loggedInUserName()."</b></a> [<a href='login/controller/Logout.php'>"
    				. ChangeLiteral('Logout') ."</a>]";
    	$str .= "\n</span>";
    	return $str;
    }
    
    
    private function loginForm() {
    	
    	$str = "";

   		$str .= "\n<div id='login-form' title='". changeLiteral("Login to TEMS")."'>";
    	$str .= "\n<form>";
		$str .= "\n<fieldset>";
		$str .= "\n\t<p><label for='login-id'>" . changeLiteral("Login") . "</label>";
		$str .= "\n\t<input type='text' name='login-id' id='login-id' class='text ui-widget-content ui-corner-all' /></p>";
		$str .= "\n\t<p><label for='password'>" . changeLiteral("Password") . "</label>";
		$str .= "\n\t<input type='password' name='password' id='password' value='' class='text ui-widget-content ui-corner-all' /></p>";
		$str .= "\n</fieldset>";
		$str .= "\n</form>";
		$str .= "\n<p id='login-error'></p>";
		$str .= "\n</div>";
		
		return $str;
    }
    
  
    private function userRegistrationForm() {
    	 
    	$str = "";
    	 
    	$str .= "\n<div id='user-registration-form' title='". changeLiteral("Register to get Access to TEMS")."'>";
    	$str .= "\n<form>";
    	$str .= "\n<fieldset>";
    	$str .= "\n\t<p><label for='registration-id'>" . changeLiteral("Email Address") . "</label>";
    	$str .= "\n\t<input type='text' name='registration-id' id='registration-id' class='text ui-widget-content ui-corner-all' /></p>";
    	$str .= "\n</fieldset>";
    	$str .= "\n</form>";
    	$str .= "\nPlease enter a <b>valid email address</b>. Login instruction and credentials will be send to the email address you provide here.";
    	$str .= "\n<p id='registration-error'></p>";
    	$str .= "\n</div>";
    
    	return $str;
    }

}


?>