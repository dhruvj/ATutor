<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2003 by Greg Gay & Joel Kronenberg        */
/* Adaptive Technology Resource Centre / University of Toronto  */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/

define('AT_INCLUDE_PATH', '../../include/');

$CACHE_DEBUG=0;
require(AT_INCLUDE_PATH.'vitals.inc.php');

require('include/functions.inc.php');
$admin = getAdminSettings();

require(AT_INCLUDE_PATH.'header.inc.php');

?>
<p align="center"><a href="discussions/achat/chat.php?firstLoginFlag=1<?php echo SEP; ?>g=31"><b> <?php echo _AC('enter_chat');  ?></b></a></p>

<h4><?php echo _AC('transcripts');  ?></h4>
<?php

if ($_GET['col']) {
	$col = addslashes($_GET['col']);
} else {
	$col = 'date';
}

if ($_GET['order']) {
	$order = addslashes($_GET['order']);
} else {
	$order = 'desc';
}

${'highlight_'.$col} = ' u';
	$tran_files = array();
	if (!@opendir(AT_CONTENT_DIR . 'chat/')){
		mkdir(AT_CONTENT_DIR . 'chat/', 0777);
	}
	if(!file_exists(AT_CONTENT_DIR . 'chat/'.$_SESSION['course_id'].'/admin.settings')){
		@mkdir(AT_CONTENT_DIR . 'chat/'.$_SESSION['course_id'], 0777);
		@mkdir(AT_CONTENT_DIR . 'chat/'.$_SESSION['course_id'].'/tran/', 0776);
		@mkdir(AT_CONTENT_DIR . 'chat/'.$_SESSION['course_id'].'/msgs/', 0776);
		@mkdir(AT_CONTENT_DIR . 'chat/'.$_SESSION['course_id'].'/users/', 0776);
		@copy('admin.settings.default', AT_CONTENT_DIR . 'chat/'.$_SESSION['course_id'].'/admin.settings');
		@chmod (AT_CONTENT_DIR . 'chat/'.$_SESSION['course_id'].'/admin.settings', 0777);
	
	}
		
	
	if ($dir = @opendir(AT_CONTENT_DIR . 'chat/'.$_SESSION['course_id'].'/tran/')) {
		while (($file = readdir($dir)) !== false) {
			if (substr($file, -strlen('.html')) == '.html') {
				$la	= stat(AT_CONTENT_DIR . 'chat/'.$_SESSION['course_id'].'/tran/'.$file);

				$file = str_replace('.html', '', $file);
				$tran_files[$file] = $la['ctime'];
			}
		}
	}else{
		echo "still nothing";

	}

	

	if (count($tran_files) == 0) {
		echo '<p>'._AC('chat_none_found').'</p>';
	} else {?>
		
		<table class="data" rules="cols" summary="">
		<thead>
		<tr>
		<th scope="col" > <?php
			echo '<a href="'.$_SERVER['PHP_SELF'].'?col=name'.SEP.'order=asc" title="'._AC('chat_sort_by_name').'">'. _AC('chat_name').'</a>';
		if (($col == 'name') && ($order == 'asc')) {
			echo '<a href="'.$_SERVER['PHP_SELF'].'?col=name'.SEP.'order=desc" title="'._AC('chat_name_descending').'"><img src="images/desc.gif" height="7" width="11" alt="'._AC('chat_name_descending').'" border="0" /></a>';
		} else if (($col == 'name') && ($order == 'desc')) {
			echo '<a href="'.$_SERVER['PHP_SELF'].'?col=name'.SEP.'order=asc" title="'._AC('chat_name_ascending').'"><img src="images/asc.gif" height="7" width="11" alt="'._AC('chat_name_ascending').'" border="0" /></a>';
		} else {
			echo '<img src="images/clr.gif" height="7" width="11" alt="" />';
		} ?>
		</th>
		<th scope="col">&nbsp;</th>
		<th scope="col"><?php
			echo '<a href="'.$_SERVER['PHP_SELF'].'?col=date'.SEP.'order=desc" title="'._AC('chat_sort_by_date').'">'._AC('chat_date').'</a> ';
			if (($col == 'date') && ($order == 'asc')) {
				echo '<a href="'.$_SERVER['PHP_SELF'].'?col=date'.SEP.'order=desc" title="'._AC('chat_date_descending').'"><img src="images/desc.gif" height="7" width="11" alt="'._AC('chat_date_descending').'" border="0" /></a>';
			} else if (($col == 'date') && ($order == 'desc')) {
				echo '<a href="'.$_SERVER['PHP_SELF'].'?col=date'.SEP.'order=asc" title="'._AC('chat_date_ascending').'"><img src="images/asc.gif" height="7" width="11" alt="'._AC('chat_date_ascending').'" border="0" /></a>';
			} else {
				echo '<img src="images/clr.gif" height="7" width="11" alt="" />';
			} ?>
		</th> <?php

		if (authenticate(AT_PRIV_ADMIN, AT_PRIV_RETURN) && $_SESSION['prefs'][PREF_EDIT]) {
			echo '<th scope="col">&nbsp;</th>';
		}

		echo '</tr>';
		echo '<thead>';

		if (($col == 'date') && ($order == 'asc')) {
			asort($tran_files);
		} else if (($col == 'date') && ($order == 'desc')) {
			arsort($tran_files);
		} else if (($col == 'name') && ($order == 'asc')) {
			ksort($tran_files);
		} else if (($col == 'name') && ($order == 'desc')) {
			krsort($tran_files);
		}
		reset ($tran_files);


		echo '<tbody>';
		foreach ($tran_files as $file => $date) {
			echo '<tr>';
			echo '<td><a href="discussions/achat/tran.php?t='.$file.'">'.$file.'</a>';
			echo '</td>';
			echo '<td>';

			if (($file.'.html' == $admin['tranFile']) && ($admin['produceTran'])) {

				echo '<strong>'._AC('chat_currently_active').'</strong>';
			}
			echo '&nbsp;</td>';
				
			echo '<td>'.date('Y-m-d h:i:s', $date).'</td>';
			
			if (authenticate(AT_PRIV_ADMIN, AT_PRIV_RETURN) && $_SESSION['prefs'][PREF_EDIT]) {
				echo '<td>';
				if (($file.'.html' == $admin['tranFile']) && ($admin['produceTran'])) {

					echo '&nbsp;';
				} else {
					echo '<a href="discussions/achat/tran_delete.php?m='.$file.'">'._AC('chat_delete').'</a>';
				}
				
				echo '</td>';
			}

			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}
	require(AT_INCLUDE_PATH.'footer.inc.php');
?>
