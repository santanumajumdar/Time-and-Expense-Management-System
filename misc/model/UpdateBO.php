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

require_once("$basedir/core/config/GlobalVariables.php");
require_once("$basedir/core/controller/UpdateBORequest.class.php");
require_once("$basedir/misc/model/Validation.php");


class UpdateBO extends __UpdateBORequest
{

	public function RegisterApps()
	{
		global $registerFile;

		try {
			if (($fhandle = @fopen($registerFile, "w")) == NULL) {
				throw new iBLError('nocategory', 'er0090');
			}
			
			$key = trim($_POST['key']);

			fwrite($fhandle, $key);
			fclose($fhandle);
			$_SESSION['registered'] = true;
			return TRUE;
		
		} catch (iBLError $e) {
			return (convertErrorToJSONFormat("$e->errField", "$e->messages_id", "$e->msgdta"));
		
		}

	}
	
	
	public function BackupDatabase()
	{		
		$dump = '';
		$status = '';
		$db_server = $_SESSION['ini']['database']['db_server'];
		$db_database = $_SESSION['ini']['database']['db_database'];

		$toFile = trim($_POST['tofilename']);
		$adminId = trim($_POST['dbadmin']);
		$adminPwd = trim($_POST['dbadminpwd']);

		$command = "mysqldump -h $db_server -u $adminId --password=$adminPwd $db_database > $toFile ";

		exec($command, $dump, $status);
		if($status != 0)		// checking the status of the command after runing
		{
			return (convertErrorToJSONFormat('nocategory', "er9999", "Backup database operation failed. Check user id, password and file directory/filename."));
		}
		
// TODO: Show a message as a successful message.
//		$this->issueMessage('info0001', $toFile);
		
		return TRUE;
	}
	
	
	public function RestoreDatabase()
	{
		$dump = '';
		$status = '';
		$db_server = $_SESSION['ini']['database']['db_server'];
		$db_database = $_SESSION['ini']['database']['db_database'];
	
		$filename = trim($_POST['fromfilename']);
		$adminId = trim($_POST['dbadmin']);
		$adminPwd = trim($_POST['dbadminpwd']);
	
		$command= "mysql -u $adminId --password=$adminPwd $db_database < $filename ";
	
		exec($command, $dump, $status);		/*checking the status of the command after runing */
		if($status!=0)
		{
			return (convertErrorToJSONFormat('nocategory', "er0081"));
		}
		
// TODO: Show a message as a successful message.
//		$this->issueMessage('info0002', $filename);
		return TRUE;
	}

}



?>