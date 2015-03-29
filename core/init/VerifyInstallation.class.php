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
 *
 **********************************************************************************/

$basedir = dirname(dirname(dirname(__FILE__)));

require_once("$basedir/core/config/GlobalVariables.php");

class VerifyInstallation {

    public function verifySetup() {
    	
    	if ($this->isTEMSInstallationVerified())
    		return TRUE;

        $gotoInstall = "install/index.php";
        
        if(!$this->registerDataBaseInfo()) {
            redirectToPage($gotoInstall);
            exit();
        }
               
        if (!isset($_SESSION['ini']['database']['db_server'])) {
            redirectToPage($gotoInstall);
            exit();
        }
        
        $conn = mysql_connect($_SESSION['ini']['database']['db_server'], 
        					  $_SESSION['ini']['database']['db_user'], 
        					  $_SESSION['ini']['database']['db_password']);
        if (!$conn) {
            redirectToPage($gotoInstall);
            exit();
        }

        $sql = "show tables from {$_SESSION['ini']['database']['db_database']}";
        $result = mysql_query($sql, $conn);
        if (!$result) {
            redirectToPage($gotoInstall);
            mysql_close($conn);
            unset($conn);
            exit();
        }

        $notProperDB = TRUE;
        while (($tableName = mysql_fetch_row($result)) == TRUE) {
              if (strtolower($tableName[0]) == 'users') {
                  $notProperDB = FALSE;
                  break;
              }
        }

        if ($notProperDB) {
            redirectToPage($gotoInstall);
            mysql_close($conn);
            unset($conn);
            exit();
        }

        $db_selected = mysql_select_db($_SESSION['ini']['database']['db_database'], $conn);
        if (!$db_selected) {
            redirectToPage($gotoInstall);
            mysql_close($conn);
            unset($conn);
            exit();
        }
        
        // Everything is verified, we are ready to set the SESSION variables for future access.
        
		$this->setTEMSInstallationVerified();
		$this->recordAccessLog();

        return TRUE;
        
    }
    
    private function isTEMSInstallationVerified() {
    	
    	if (!isset($_SESSION['tems_installation_verified'])) {
    		return FALSE;
    	}
    	else {
    		return TRUE;
    	}
    }
    
    // This is ideal place for setting session variables. This will be used only once.
    
    private function setTEMSInstallationVerified() {
    	
    	global $basedir;
    
    	$_SESSION['tems_installation_verified'] = TRUE;
    	$_SESSION['history'] = "";
    	$_SESSION['db_trace_log'] = "$basedir/log/db_trace_log_".date("Ymd_His").".txt";
    }
    
    
    private function registerDataBaseInfo() {
    
    	global $configFile, $DBconfigFile;
    	
    	$_SESSION['ini'] = parse_ini_file($configFile, TRUE);
    
    	if (is_file($DBconfigFile)) {
    		$db_ini_array = parse_ini_file($DBconfigFile, TRUE);
    		$_SESSION['ini']['database'] = $db_ini_array['database'];
    		
    		return TRUE;
    	}
    	else {
    		return FALSE;
    	}
    
    }
    

    private function recordAccessLog() {
    
    	global $accessLogFile;
    
   		$accessFile = fopen($accessLogFile, 'a');
   		if ($accessFile != NULL) {
   			$timeNow = date("Y-m-d h:m:s");
   			$RemoteIP = clientIP();
   			$hostName = gethostbyaddr($RemoteIP);
   			$referredHost = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'No reference';
   			fwrite($accessFile, "At $timeNow Accessed from IP: $RemoteIP, Referred from: $referredHost, Host Name: $hostName".PHP_EOL);
   			fclose($accessFile);
    		return TRUE;
   		}
   		else {
   			return FALSE;
   		}
    
    }  

}



?>