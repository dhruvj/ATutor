<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2005 by Greg Gay & Joel Kronenberg        */
/* Adaptive Technology Resource Centre / University of Toronto  */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/
// $Id$

define('AT_INCLUDE_PATH', '../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');

require (AT_INCLUDE_PATH.'header.inc.php');

$sql	= "SELECT word_id, related_word_id FROM ".TABLE_PREFIX."glossary WHERE related_word_id>0 AND course_id=$_SESSION[course_id] ORDER BY related_word_id";
$result = mysql_query($sql, $db);
while ($row = mysql_fetch_array($result)) {
	$glossary_related[$row['related_word_id']][] = $row['word_id'];			
}

if ($_GET['w']) {
	$sql = "SELECT * FROM ".TABLE_PREFIX."glossary WHERE course_id=$_SESSION[course_id] AND word='".addslashes(urldecode($_GET['w']))."'";		
} else {
	$sql = "SELECT * FROM ".TABLE_PREFIX."glossary WHERE course_id=$_SESSION[course_id] ORDER BY word";			
}

$result= mysql_query($sql, $db);

if(mysql_num_rows($result) > 0){		

	$gloss_results = array();
	while ($row = mysql_fetch_assoc($result)) {
		$gloss_results[] = $row;
	}
	$num_results = count($gloss_results);
	$results_per_page = 3;
	$num_pages = ceil($num_results / $results_per_page);
	$page = intval($_GET['p']);
	if (!$page) {
		$page = 1;
	}
	
	$count = (($page-1) * $results_per_page) + 1;
	$gloss_results = array_slice($gloss_results, ($page-1)*$results_per_page, $results_per_page);
	
	if($num_pages > 1):
		echo _AT('page').': ';
		for ($i=1; $i<=$num_pages; $i++) {
			if ($i != 1) {
				echo ' | ';
			}
			
			if ($i == $page) { 
				echo '<strong>'.$i.'</strong>';
			} else {
				echo '<a href="'.$_SERVER['PHP_SELF'].'?p='.$i.'#list">'.$i.'</a>';
			}
		}
?>
	<br /><br />
<?php endif; ?>

<a name="list"></a>

<?php
	$current_letter = '';
	foreach ($gloss_results as $item):
		$item['word'] = AT_print($item['word'], 'glossary.word');

		if ($current_letter != strtoupper(substr($item['word'], 0, 1))):
			if ($current_letter != '') {				
				echo '</dl>';
			} 
			$current_letter = strtoupper(substr($item['word'], 0, 1)); ?>
			<h3 style="padding-bottom:5px;"><a name="<?php echo $current_letter; ?>"></a><?php echo $current_letter; ?></h3>
			<dl style="margin:0px;">
		<?php endif; ?>

			<dt><a name="<?php echo urlencode($item['word']); ?>"></a><strong><?php echo stripslashes($item['word']); ?>

			<?php if (($item['related_word_id'] != 0) || (is_array($glossary_related[urlencode($item['word_id'])]) )):
				echo ' ('._AT('see').': ';

				$output = false;

				if ($item['related_word_id'] != 0) {
					echo '<a href="'.$_SERVER['PHP_SELF'].'?w='.urlencode($glossary_ids[$item['related_word_id']]).'">'.urldecode($glossary_ids[$item['related_word_id']]).'</a>';
					$output = true;
				}

				if (is_array($glossary_related[urlencode($item['word_id'])]) ) {
					$my_related = $glossary_related[$item['word_id']];

					$num_related = count($my_related);
					for ($i=0; $i<$num_related; $i++) {
						if ($glossary_ids[$my_related[$i]] == $glossary_ids[$item['related_word_id']]) {
							continue;
						}
						if ($output) {
							echo ', ';
						}

						echo '<a href="'.$_SERVER['PHP_SELF'].'?w='.urlencode($glossary_ids[$my_related[$i]]).'">'.urldecode($glossary_ids[$my_related[$i]]).'</a>';

						$output = true;
					}
				}
				echo ')';
		endif; ?>
		</strong></dt>

		<dd><?php echo AT_print($item['definition'], 'glossary.definition'); ?><br /><br /></dd>


	<?php endforeach; ?>

	</dl>

<?php
	if ($_GET['w']) {
		echo '<br /><br /><a href="glossary/index.php">'._AT('view_all').'</a>';

		if ($_GET['g_cid']) {
			$path	= $contentManager->getContentPath(intval($_GET['g_cid']));
			echo ' | '._AT('back_to').' <a href="content.php?cid='.intval($_GET['g_cid']).'">'.$path[0]['title'].'</a>';
		}
	}
	
} else {
	echo '<p>'._AT('no_glossary_items').'</p>';
}

require(AT_INCLUDE_PATH.'footer.inc.php');
?>