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

require_once("$basedir/core/init/initialize.php");
require_once("$basedir/menu/model/DbObj.php");

class ProcessRequest extends MenuData
{

	public function processIncomingFormData()
	{
		$__INITIAL_ORDER__ = 100000;
		$newOrder = $__INITIAL_ORDER__;
		
		try {
			$MenuData = new MenuData();
			$MenuData->beginTransaction();
			
			$parent_menu_id = (empty($_POST['menuid'])) ? NULL : $_POST['menuid'];
			$content = explode('``', $_POST['contents']);
			foreach ($content as $key=>$value) {
				if (trim($value) == '')
					continue;
				list($label, $programtransactions_id, $menu_id, $tag) = explode('`', $value);
				$tag = trim($tag);
				if ($tag == 'add_here')
					continue;
				$label = trim($label);
				$programtransactions_id = trim($programtransactions_id);
				$menu_id = trim($menu_id);
				$newOrder +=1;

				// $tag string indicates that this entry is pulled from the right column
				if (($tag == 'its_a_menu') or ($tag == 'its_a_program'))
					$menu_id = $MenuData->addMenuEntry($programtransactions_id, $newOrder, $parent_menu_id, $menu_id);
				else
					$MenuData->reorderMenu($parent_menu_id, $menu_id, $newOrder);
				
				$content[$key] = $label . '`' . $programtransactions_id . '`' . $menu_id . '`' . $tag;
			}
	
			$where = empty($_POST['menuid']) ? "`parent_menu_id` is NULL" : "`parent_menu_id` = {$_POST['menuid']}";
			$where .= " and `order` < $__INITIAL_ORDER__";
			
			$MenuData->deleteRows('menu', $where);

			$MenuData->resequenceMenuOrder($parent_menu_id);
		
			$MenuData->commitTransaction();
			echo "Successful";
			return TRUE;
		}
		catch (Exception $e) {
			$MenuData->rollbackTransaction();
			echo $label;
			return FALSE;
		}
	}
}

$page = new ProcessRequest();
return $page->processIncomingFormData();


?>