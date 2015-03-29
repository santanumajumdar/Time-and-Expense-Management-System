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

class PageStaticSection {

    public function getReportHtmlHead () {

    	$str = '';
    	
    	$str .= '<head>
    				<meta charset="utf-8">
    				<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    				<meta name="description" content="TEMS - Time and Expense Management System, powered by Initechs">
					<link rel="stylesheet" type="text/css" media="all" href="../../core/css/temsbase.css">
        			<link rel="stylesheet" type="text/css" media="all" href="../../core/css/temsreport.css">
        			<link rel="shortcut icon" type="image/x-icon" href="../../images/favicon.ico">
    			
					<script type="text/javascript" language="JavaScript" src="../../core/controller/ControllerFunctions.js"></script>
					<script type="text/javascript" language="JavaScript" src="../../core/lib/js/jquery-1.9.1.js"></script>
    			
					<title>'. changeLiteral("Time and Expense Management System").'</title>
				</head>';
    	
    	return $str;
    }
    
    
    
    public function getScreenHtmlHead () {
    	
    	global $basedir;
    	
    	$str = '';
    	
    	$str .= '<head>';
		$str .= '<meta http-equiv="Content-Type" content="text/html" charset="utf-8">

        		<link rel="stylesheet" type="text/css" media="all" href="core/css/temsbase.css">
        		<link rel="stylesheet" type="text/css" media="all" href="core/css/temsmain.css">
    			<link rel="stylesheet" type="text/css" media="all" href="core/css/jquery-ui-1.9.2.custom.css">
    			<link rel="stylesheet" type="text/css" media="all" href="core/lib/js/datatables/css/demo_page.css">
				<link rel="stylesheet" type="text/css" media="all" href="core/lib/js/datatables/css/demo_table.css">

        		<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">

        		<script type="text/javascript" language="JavaScript" src="core/lib/js/jquery-1.9.1.js"></script>
        		<script type="text/javascript" language="JavaScript" src="core/lib/js/jquery-ui-1.9.2.custom.min.js"></script>
    			<script type="text/javascript" language="JavaScript" src="core/lib/js/datatables/js/jquery.dataTables.js"></script>
        		<script type="text/javascript" language="JavaScript" src="core/controller/ControllerFunctions.js"></script>
    			<script type="text/javascript" language="JavaScript" src="core/view/ViewFunctions.js"></script>
				<script type="text/javascript" language="JavaScript" src="core/model/ModelFunctions.js"></script>';
		
		if (file_exists("$basedir/premium/expensedetails"))
			$str .= '<script type="text/javascript" language="JavaScript" src="premium/expensedetails/ControllerFunctions.js"></script>';

		$str .= '<title>'. changeLiteral("Time and Expense Management System"). '</title>
    			</head>';
        	
    	return $str;
    }
    
    
    public function getPageFooter() {
    
    	global $GLOBAL_copyRightText;
    
    	$str = "";
    
    	$str .= $GLOBAL_copyRightText;
    	$str .= "<a href='http://www.initechs.com/products'><img src='images/Initechs_Logo.png' alt='Initechs' width='60' height='20' style='float:right'/></a>";
    
    	return $str;
    }
    
    
    public function getPageHeader() {
    
  	
    	$logo = ((!empty($_SESSION['company']['logo'])) && file_exists($_SESSION['company']['logo'])) ? 
    						$_SESSION['company']['logo'] : 'images/Initechs_Logo.png';
    	$applicationName = changeLiteral($_SESSION['ini']['literal']['system_description']);
    
    	$str = "";
    
    	$str .= "<a href='index.php'> <img class='logo' src='$logo' alt='Company Logo'/></a>"; 
		$str .= "<span class='app-name'>$applicationName</span>";    	
    
    	return $str;
    }
    

}

?>