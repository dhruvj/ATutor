<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2008                                      */
/* Written by Greg Gay, Joel Kronenberg & Chris Ridpath         */
/* Adaptive Technology Resource Centre / University of Toronto  */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/
// $Id: add_resource_av.php 7482 2008-05-06 17:44:49Z greg $
define('AT_INCLUDE_PATH', '../../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
authenticate(AT_PRIV_READING_LIST);

// initial values for form
$id = intval($_REQUEST['id']);
$title = "";
$author = "";
$publisher = ""; 
$date = ""; 
$comments = "";
$page_return = $_GET['page_return'];

// check if user has submitted form
if (isset($_POST['cancel'])) {
	$msg->addFeedback('CANCELLED');

	header('Location: index_instructor.php');
	exit;
} else if (isset($_POST['submit'])) {
	$missing_fields = array();

	if (trim($_POST['title']) == '') {
		$missing_fields[] = _AT('title');
	}
	if (trim($_POST['author']) == '') {
		$missing_fields[] = _AT('author');
	}

	if ($missing_fields) {
		$missing_fields = implode(', ', $missing_fields);
		$msg->addError(array('EMPTY_FIELDS', $missing_fields));
	}


	if (!$msg->containsErrors()) {
		$_POST['title'] = $addslashes(validate_length($_POST['title'], 255));
		$_POST['author'] = $addslashes(validate_length($_POST['author'], 150));
		$_POST['publisher'] = $addslashes(validate_length($_POST['publisher'], 150));
		$_POST['date'] = $addslashes($_POST['date']);
		$_POST['comments'] = $addslashes(validate_length($_POST['comments'], 255));
		
		if ($id == '0'){ // creating a new URL resource
			$sql = "INSERT INTO ".TABLE_PREFIX."external_resources VALUES (NULL, $_SESSION[course_id],
			".RL_TYPE_AV.", 
			'$_POST[title]', 
			'$_POST[author]', 
			'$_POST[publisher]', 
			'$_POST[date]', 
			'$_POST[comments]',
			'',
			'')";
			$result = mysql_query($sql,$db);

			// index to new URL resource
			$id_new = mysql_insert_id($db);

			$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
		} else { // modifying an existing URL resource

			$sql = "UPDATE ".TABLE_PREFIX."external_resources SET title='$_POST[title]', author='$_POST[author]', publisher='$_POST[publisher]', date='$_POST[date]', comments='$_POST[comments]' WHERE resource_id='$id' AND course_id=$_SESSION[course_id]";

			$result = mysql_query($sql,$db);

			// index to URL resource
			$id_new = $id;

			$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
		}

		if (trim($_POST['page_return']) != ''){
			header('Location: '. $_POST['page_return']. '?existingbook='. $id_new);
		} else {
			header('Location: index_instructor.php');
		}
		exit;
	} else { // submission contained an error, update form values for redisplay
		$title       = $stripslashes($_POST['title']);
		$author      = $stripslashes($_POST['author']);
		$publisher   = $stripslashes($_POST['publisher']);
		$date        = $stripslashes($_POST['date']);
		$comments    = $stripslashes($_POST['comments']);
		$page_return = $stripslashes($_POST['page_return']);
	}
}

// is user modifying an existing AV resource?
if ($id && !isset($_POST['submit'])){
	// yes, get resource from database
	$id = intval ($_GET['id']);

	$sql = "SELECT * FROM ".TABLE_PREFIX."external_resources WHERE course_id=$_SESSION[course_id] AND resource_id=$id";
	$result = mysql_query($sql, $db);
	if ($row = mysql_fetch_assoc($result)){
		$title     = $row['title'];
		$author    = $row['author'];
		$publisher = $row['publisher']; 
		$date      = $row['date']; 
		$comments  = $row['comments'];
	}
	// change title of page to 'edit AV resource' (default is 'add AV resource')
	$_pages['mods/_standard/reading_list/add_resource_av.php'][title_var] = 'rl_edit_resource_av';
} else if ($id) {
	$_pages['mods/_standard/reading_list/add_resource_av.php'][title_var] = 'rl_edit_resource_av';
}

$onload = 'document.form.name.focus();';

require(AT_INCLUDE_PATH.'header.inc.php');
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="form">
<input type="hidden" name="id" value="<?php echo $id ?>" />
<input type="hidden" name="page_return" value="<?php echo $page_return ?>" />

<div class="input-form">	
	<fieldset class="group_form"><legend class="group_form"><?php echo _AT('rl_add_resource_av'); ?></legend>
	<div class="row">
		<div class="required" title="<?php echo _AT('required_field'); ?>">*</div><label for="title"><?php  echo _AT('title'); ?></label><br />
		<input type="text" name="title" size="35" id="title" value="<?php echo htmlspecialchars($title); ?>" />
	</div>

	<div class="row">
		<div class="required" title="<?php echo _AT('required_field'); ?>">*</div><label for="author"><?php  echo _AT('author'); ?></label><br />
		<input type="text" name="author" size="25" id="author" value="<?php echo htmlspecialchars($author); ?>" />
	</div>

	<div class="row">
		<label for="date"><?php  echo _AT('rl_year_written'); ?></label><br />
		<input type="text" name="date" size="6" id="date" value="<?php echo htmlspecialchars($date); ?>" />
	</div>

	<div class="row">
		<label for="publisher"><?php  echo _AT('rl_publisher'); ?></label><br />
		<input type="text" name="publisher" size="20" id="publisher" value="<?php echo htmlspecialchars($publisher); ?>" />
	</div>

	<div class="row">
		<label for="comments"><?php  echo _AT('comment'); ?></label><br />
		<textarea name="comments" cols="30" rows="2" id="comments"><?php echo htmlspecialchars($comments); ?></textarea>
	</div>

	<div class="row buttons">
		<input type="submit" name="submit" value="<?php echo _AT('save'); ?>" accesskey="s" />
		<input type="submit" name="cancel" value="<?php echo _AT('cancel'); ?>" />
	</div>
	</fieldset>
</div>
</form>

<?php require(AT_INCLUDE_PATH.'footer.inc.php'); ?>