<?php
/**
* $Id: form_new.php 864 2011-03-21 06:02:52Z angek $
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

$now 	= strftime($date_format);
?>
<script type="text/javascript">
function closeTimeForm()
{
    document.getElementById('time_form_new').style.display = 'none';
}
</script>
<div id="time_form_new" class="pfl_search" style="display:none">
	<span>
    <?php 
        echo $form->SelectTask('time_task');
		if ($now == $date_format){
			echo JHTML::calendar(JHTML::_('date', $row->cdate, PFformat::JhtmlCalendarDateFormat()), 'cdate', 'cdate2');
		}
		else {
			echo JHTML::calendar($now, 'cdate', 'cdate2', $date_format);
		}
		echo $form->SelectHour('hours')
	    . $form->SelectMinute('minutes')
	    . $form->InputField('text', PFformat::Lang('QUICK_NOTE'), 'size="40"');
    ?>
	</span>
	<span class="btn">
        <?php echo $form->NavButton('SAVE', "javascript:task_save()");?>
	</span>
	<span class="btn">
	    <a href="javascript:closeTimeForm();" class="pf_button"><?php echo PFformat::Lang('CANCEL');?></a>
	</span>
</div>