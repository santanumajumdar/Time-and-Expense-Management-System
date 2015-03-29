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
$basedir = dirname(dirname(__FILE__));

$logFileName = "$basedir/log/upgrade.log";
require_once("$basedir/install/UpgradeDatabaseSQL.php");


class UpgradeDatabase
{
	public function CopyDatabase($oldDBConn, $newDBConn)
	{
		global $basedir, $logFileName;
		global $allTables, $straightCopy1, $straightCopy2, $straightUpdate1, $changedTables;
		global $preparation, $finalize;
		
		$logfile = fopen($logFileName, "a+"); 
		if ($logfile == NULL) {
			throw new iBLError('nocategory', 'er9999',  "Could not open file {$logFileName} for writing upgrade log. Check permission of the log directory.");
		}
		
		fwrite($logfile, PHP_EOL.PHP_EOL. date('c'). " *** Database upgrade begins. ***");
		
		//-----------------------------------------------------
		
		foreach ($preparation as $sqlStmt)
		{
			$newDBConn->query($sqlStmt);
			if (($newDBConn->errno != 0)
				and ($newDBConn->errno != 1025))
			{
				fwrite($logfile, PHP_EOL . date('c'). $sqlStmt. $newDBConn->error);
				throw new iBLError('nocategory', 'er9999',  "Error in executing: $sqlStmt.");
			}
		}
		fwrite($logfile, PHP_EOL. date('c'). " Preparation was completed Successfully.".PHP_EOL);
		$_SESSION['status'][] = "Step1: Preparation was completed.";
		
		//-----------------------------------------------------
		
		foreach ($allTables as $tablename)
		{
			$query = "Delete from $tablename";
			$newDBConn->query($query);
			if ($newDBConn->errno != 0)
			{
				fwrite($logfile, PHP_EOL . date('c'). " $tablename table could not be cleared. ". $newDBConn->error);
				throw new iBLError('nocategory', 'er9999',  "$tablename table could not be cleared.");
			}
			fwrite($logfile, PHP_EOL . date('c'). " $tablename table was cleared Successfully.");	
		}
		fwrite($logfile, PHP_EOL. date('c'). " All tables were cleared Successfully.".PHP_EOL);
		$_SESSION['status'][] = "Step2: All tables were cleared.";		
		
		//-----------------------------------------------------------------
		
		foreach ($changedTables as $tableSQLEntry)
		{
			$table = key($tableSQLEntry);
			$queryStmt = $tableSQLEntry[$table];
			$results = $oldDBConn->query($queryStmt);
			if ($oldDBConn->errno != 0)
			{
				fwrite($logfile, PHP_EOL . date('c'). " $table table could not be read.". $oldDBConn->error);
				throw new iBLError('nocategory', 'er9999',  "$table table could not be read.");
			}
			while (($dataset = $results->fetch_assoc()) != NULL)
			{
				$query = "INSERT INTO $table SET ";
				foreach ($dataset as $field=>$value) {
					$value = (($table == 'users') and ($field == 'createby') and  ($value=='')) ? 'admin' : $value;
					$value = (($table == 'users') and ($field == 'email') and  ($value=='')) ? 'not_specified@noemail.com' : $value;
					$value = (($table == 'times') and ($field == 'description') and  ($value=='')) ? 'Not Entered.' : $value;
					$query .= ($value == '') ? "$field=NULL, " : "$field='$value', ";
				}
				$query = rtrim($query, ', ');
				$newDBConn->query($query);
				if ($newDBConn->errno != 0)
				{
					fwrite($logfile, PHP_EOL . date('c'). " $table table could not be written.". PHP_EOL . $query . PHP_EOL . $newDBConn->error);
					throw new iBLError('nocategory', 'er9999',  "$table table could not be written.");
				}
			}
			fwrite($logfile, PHP_EOL . date('c'). " $table table was update Successfully.");
		}
		fwrite($logfile, PHP_EOL. date('c'). " All changed tables were copied Successfully.".PHP_EOL);
		$_SESSION['status'][] = "Step3: All tables were copied";
		
		//---------------------------------------------------------------------------
		
		foreach ($finalize as $sqlStmt)
		{
			$newDBConn->query($sqlStmt);
			if ($newDBConn->errno != 0)
			{
				fwrite($logfile, PHP_EOL . date('c'). $sqlStmt. $newDBConn->error);
				throw new iBLError('nocategory', 'er9999',  "Error in executing: $sqlStmt.");
			}
		}
		fwrite($logfile, PHP_EOL. date('c'). " Finalization was completed Successfully.".PHP_EOL);
		$_SESSION['status'][] = "Step4: Finalization was completed.";
		
		fwrite($logfile, PHP_EOL. date('c'). " *** Database upgrade completed Successfully. ***");
	}

}

?>