<?php
/**
* $Id: form_edit.php 864 2011-03-21 06:02:52Z angek $
* @package   Projectfork
* @copyright Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License,
* or any later version.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public License
* along with Projectfork.  If not, see <http://www.gnu.org/licenses/lgpl.html>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

$time = (int) $row->timelog;
$h    = floor($time / 60);
$m    = $time - floor(60 * $h);

?>
<table>
    <tr>
       <td><?php echo $form->SelectTask('time_task', $row->task_id);?></td>
       <td>
	   <?php 
		if ($now == $date_format) {
			echo JHTML::calendar(JHTML::_('date', $row->cdate, PFformat::JhtmlCalendarDateFormat()), 'cdate', 'cdate');
		}
		else {
			$now = strftime($date_format, PFformat::AddOffset($row->cdate));
			echo JHTML::calendar($now, 'cdate', 'cdate', $date_format);
		}
		?>
	   </td>
       <td><?php echo $form->SelectHour('hours', $h);?></td>
       <td><?php echo $form->SelectMinute('minutes', $m);?></td>
       <td><?php echo $form->InputField('text', htmlspecialchars($row->content), 'size="40"');?></td>
       <td><?php echo $form->NavButton('SAVE', "javascript:task_update()");?></td>
       <td><?php echo $form->NavButton('CANCEL', "section=time"); echo $form->HiddenField('id', $row->id);?></td>
    </tr>
</table>