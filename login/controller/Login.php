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
require_once("$basedir/users/model/User.php");

class UserLogin 
{

	public function processLoginData() {
		
		global $accessLogFile;
		
		try {

			$id = trim($_POST['loginId']);
			$pw = trim($_POST['password']);

			if (empty($id)
				or empty($pw)) {
				$msg = getMessage('er0098');
				echo $msg;
				return FALSE;
			}
				
			$User = new User($id);
			$id = $User->escapeString($id);
			if (!$User->registerUser($id, $pw)) {
				$msg = getMessage('er0012');
				echo $msg;
				return FALSE;
			}
			
			$accessFile = fopen($accessLogFile, 'a');
			if ($accessFile != NULL) {
				
				$timeNow = date("Y-m-d h:m:s");
				$RemoteIP = clientIP();
				$hostName = gethostbyaddr($RemoteIP);
				fwrite($accessFile, "At $timeNow Logged in from IP: $RemoteIP, Host Name: $hostName".PHP_EOL);
				fclose($accessFile);
			}
							
			echo "Successful";
			return TRUE;
		
		} catch (iBLError $e) {

			$msg = getMessage($e->messages_id);
			echo $msg;
			return FALSE;
		
		} catch (Exception $e) {

			$msg = getMessage('er0012');
			echo $msg;
			return FALSE;
		}
		
		

		return FALSE;


	}


}

$UserLogin = new UserLogin();
return $UserLogin->processLoginData();


?>