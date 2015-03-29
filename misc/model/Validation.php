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



class UserInput
{
	public function validate($action)
	{
		$ret = array();

		switch ($action)
		{
			case 'RegisterApps':
				$ret = array_merge($ret, (array)chkMandatory('key'));
		
				if ((strlen($_POST['key']) != 64)
					or (md5(substr($_POST['key'], 0, 32)) != substr($_POST['key'], 32, 32))) {
					$msg = getMessage('er0089');
					$ret = array_merge($ret, (array)array('key' => $msg));
				}
				break;
			
			case 'BackupDatabase':
				$ret = array_merge($ret, (array)chkMandatory('tofilename'));
				$ret = array_merge($ret, (array)chkMandatory('dbadmin'));
				$ret = array_merge($ret, (array)chkMandatory('dbadminpwd'));
				
				$directory_base_path = rtrim($_POST['tofilename'], "/");
				$pathend = strrpos($directory_base_path, "/");
				$directory_base_path = substr($directory_base_path, 0, $pathend);

				if (!is_dir($directory_base_path))
				{
					$msg = getMessage('er0067');
					$ret = array_merge($ret, array('tofilename' => $msg));
				}
				
				$pathend = strrpos($_POST['tofilename'], "/");
				$fileName = trim(substr($_POST['tofilename'], $pathend+1));
				if (empty($fileName)) {
					$msg = getMessage('er0068');
					$ret = array_merge($ret, array('tofilename' => $msg));
				}
				break;
				
			case 'RestoreDatabase':
				$ret = array_merge($ret, (array)chkMandatory('fromfilename'));
				$ret = array_merge($ret, (array)chkMandatory('dbadmin'));
				$ret = array_merge($ret, (array)chkMandatory('dbadminpwd'));
				
				if (!is_file($_POST['fromfilename']))
				{
					$msg = getMessage('er0068');
					$ret = array_merge($ret, array('fromfilename' => $msg));
				}
				break;
		}
		return $ret;
	}


}


?>