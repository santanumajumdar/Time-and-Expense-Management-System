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

$basedir = dirname(dirname(dirname ( __FILE__ )));
require_once("$basedir/core/view/PageElement.class.php");
require_once("$basedir/menu/model/DbObj.php");

class PageMainContent extends __PageElement {
	
    public function setUIContents($modelFile=NULL) {
    	
        $menuid = (empty($_GET['menuid'])) ? NULL : $_GET['menuid'];
        $breadCrumbs = "";
        $menuBreadCrumbs = $this->buildMenuBreadCrumbs($breadCrumbs, $menuid);
        
        $MenuData = new MenuData();        
    	$MenuDataSet = $MenuData->getMenuRecords();    	
    	$ProgramDataSet = $MenuData->getProgramTransactionRecords();
    	 
        
		$str = '';
		$str .= $this->setHeading(changeLiteral('Menu') . $menuBreadCrumbs);
		$str .= "<div class=\"menu-list-div\">";
        $str .= "<div class=\"menu-heading-ui\">". changeLiteral('Menu List') . "</div>";
        $str .= "<div class=\"menu-list-ui\">";
        $str .= "<table><tbody id=\"menu-list\">";				// Begining of the menu list
        foreach ($MenuDataSet as $key=>$row){

        	$label = changeLiteral($row['label']);
        	$url = (!empty($row['url'])) ? $row['url'] : "index.php?action=ListMenu&menuid={$row['menu_id']}";
        	$label = (!empty($row['url'])) ? $label : "+ ".$label;
        		
        	$str .= "<tr>
					<td><img src=\"images/arrow-updown.png\" width=16px width=16px><a href=\"$url\">$label</a></td>
					<td class=\"hideme\">`{$row['programtransactions_id']}</td>
					<td class=\"hideme\">`{$row['menu_id']}</td>
					<td class=\"hideme\">`{$row['order']}``</td>
					</tr>";
        }
        if (empty($MenuDataSet))
        	$str .= "<tr><td>". changeLiteral("Add here") . "</td>
        			<td class=\"hideme\">`0</td>
        			<td class=\"hideme\">`0</td>
					<td class=\"hideme\">`add_here``</td>
        			</tr>";
        $str .= "</tbody></table>";								// End of Menu List
        
        $str .= "</div>";

        $str .= "<button onclick=\"reorderMenu($menuid)\">" . changeLiteral('Confirm') . "</button>";
        $str .= "<button onclick=\"onclick=window.history.go(-1)\">" . changeLiteral('Go Back') . "</button>";
        $str .= "<div class=\"delete-box-ui\" id=\"remove-menu\">".changeLiteral("Drop here to remove"). "</div>";
        
        $str .= "</div>";
        
        $str .= "<div  class=\"program-list-div\">";
        $str .= "<div class=\"program-heading-ui\">". changeLiteral('Program Transactions') . "</div>";
        
        $str .= "<div class =\"program-list-ui\">";		// Begining of program list as a draggle elements

        $str .= "<table><tbody>";
        foreach ($ProgramDataSet as $key=>$row){

        	$label = changeLiteral($row['label']);
        	$label = (!empty($row['url'])) ? "<a href=\"{$row['url']}\">$label</a>" : $label;
        	$menu_id = (!empty($row['menu_id'])) ? $row['menu_id'] : NULL;
        	$tag = (empty($_GET['menuid']) or (!empty($row['menu_id']))) ? "its_a_menu" : "its_a_program";

        	$str .= "
        		<tr class=\"pgm-list\">
					<td><img src=\"images/left_arrow.png\" width=16px width=16px>$label</td>
					<td class=\"hideme\">`{$row['programtransactions_id']}</td>
					<td class=\"hideme\">`$menu_id</td>
					<td class=\"hideme\">`$tag``</td>
				</tr>";
        }
        $str .= "</tbody></table>";
        $str .= "</div>";
        $str .= "</div>";									// End of Program list as draggable elements
        
        unset($_SESSION['error']);
        return $str;
    }
    
    protected function buildMenuBreadCrumbs($breadCrumbs, $menuid=NULL) {
    	
    	$MenuData = new MenuData(); 
    	
    	$menuBreadCrumbsObj = $MenuData->getMenuBreadCrumbObj($menuid);
    
    	if (!$menuBreadCrumbsObj)
    		return;
    	$breadCrumbs =  " :: " . changeLiteral($menuBreadCrumbsObj->label) . $breadCrumbs;
    	
    	if (empty($menuBreadCrumbsObj->parent_menu_id))    		
    		return ($breadCrumbs);
		
    	$breadCrumbs = $this->buildMenuBreadCrumbs($breadCrumbs, $menuBreadCrumbsObj->parent_menu_id);
    	return ($breadCrumbs);

    }

    
}

?>