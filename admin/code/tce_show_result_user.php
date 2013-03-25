<?php
//============================================================+
// File name   : tce_show_result_user.php
// Begin       : 2004-06-10
// Last Update : 2012-04-15
//
// Description : Display test results for specified user.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               Manor Coach House, Church Hill
//               Aldershot, Hants, GU12 4RQ
//               UK
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//    Additionally, you can't remove, move or hide the original TCExam logo,
//    copyrights statements and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display test results for specified user.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-06-10
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_result_user'];
require_once('tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('tce_functions_auth_sql.php');
require_once('tce_functions_user_select.php');

if (isset($_REQUEST['test_id']) AND ($_REQUEST['test_id'] > 0)) {
	$test_id = intval($_REQUEST['test_id']);
	// check user's authorization
	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		exit;
	}
} else {
	$test_id = 0;
}
if (isset($_REQUEST['user_id'])) {
	$user_id = intval($_REQUEST['user_id']);
	if (!F_isAuthorizedEditorForUser($user_id)) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		exit;
	}
} else {
	$user_id = 0;
}
if (isset($_REQUEST['testuser_id'])) {
	$testuser_id = intval($_REQUEST['testuser_id']);
} else {
	$testuser_id = 0;
}
if (isset($_REQUEST['selectcategory'])) {
	$changecategory = 1;
}

if(isset($_POST['lock'])) {
	$menu_mode = 'lock';
} elseif(isset($_POST['unlock'])) {
	$menu_mode = 'unlock';
} elseif(isset($_POST['extendtime'])) {
	$menu_mode = 'extendtime';
}

switch($menu_mode) {

	case 'delete':{
		F_stripslashes_formfields();
		// ask confirmation
		F_print_error('WARNING', $l['m_delete_confirm']);
		echo '<div class="confirmbox">'.K_NEWLINE;
		echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
		echo '<div>'.K_NEWLINE;
		echo '<input type="hidden" name="testuser_id" id="testuser_id" value="'.$testuser_id.'" />'.K_NEWLINE;
		F_submit_button('forcedelete', $l['w_delete'], $l['h_delete']);
		F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
		echo '</div>'.K_NEWLINE;
		echo '</form>'.K_NEWLINE;
		echo '</div>'.K_NEWLINE;
		break;
	}

	case 'forcedelete':{
		F_stripslashes_formfields(); // Delete
		if($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
				$sql = 'DELETE FROM '.K_TABLE_TEST_USER.'
					WHERE testuser_id='.$testuser_id.'';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error();
				} else {
					$testuser_id = false;
					F_print_error('MESSAGE', $l['m_deleted']);
				}
		}
		break;
	}

	case 'extendtime':{
		// extend the test time by 5 minutes
		// this time extension is obtained moving forward the test starting time
		$sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
			SET testuser_creation_time=\''.date(K_TIMESTAMP_FORMAT, F_getTestStartTime($testuser_id) + (K_EXTEND_TIME_MINUTES * K_SECONDS_IN_MINUTE)).'\'
			WHERE testuser_id='.$testuser_id.'';
		if(!$ru = F_db_query($sqlu, $db)) {
			F_display_db_error();
		} else {
			F_print_error('MESSAGE', $l['m_updated']);
		}
		break;
	}

	case 'lock':{
		// update test mode to 4 = test locked
		$sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
			SET testuser_status=4
			WHERE testuser_id='.$testuser_id.'';
		if(!$ru = F_db_query($sqlu, $db)) {
			F_display_db_error();
		} else {
			F_print_error('MESSAGE', $l['m_updated']);
		}
		break;
	}

	case 'unlock':{
		// update test mode to 1 = test unlocked
		$sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
			SET testuser_status=1
			WHERE testuser_id='.$testuser_id.'';
		if(!$ru = F_db_query($sqlu, $db)) {
			F_display_db_error();
		} else {
			F_print_error('MESSAGE', $l['m_updated']);
		}
		break;
	}

	default: {
		break;
	}

} //end of switch

// --- Initialize variables

if(!isset($test_id) OR empty($test_id)) {
	$test_id = 0;
	// select default test ID
	$sql = F_select_executed_tests_sql().' LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$test_id = $m['test_id'];
		}
	} else {
		F_display_db_error();
	}
}

if($formstatus) {
	if ((isset($changecategory) AND ($changecategory > 0)) OR (!isset($user_id)) OR empty($user_id)) {
			$sql = 'SELECT testuser_id, testuser_test_id, testuser_user_id, testuser_creation_time, testuser_status, user_lastname, user_firstname, user_name, SUM(testlog_score) AS test_score, MAX(testlog_change_time) AS test_end_time
				FROM '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_USERS.'
				WHERE testlog_testuser_id=testuser_id
					AND testuser_user_id=user_id
					AND testuser_test_id='.$test_id.'
					AND testuser_status>0
				GROUP BY testuser_id, testuser_test_id, testuser_user_id, testuser_creation_time, testuser_status, user_lastname, user_firstname, user_name
				ORDER BY testuser_test_id, user_lastname, user_firstname
				LIMIT 1';
	} else {
		$sql = 'SELECT testuser_id, testuser_test_id, testuser_user_id, testuser_creation_time, testuser_status, user_lastname, user_firstname, user_name, SUM(testlog_score) AS test_score, MAX(testlog_change_time) AS test_end_time
			FROM '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_USERS.'
			WHERE testlog_testuser_id=testuser_id
				AND testuser_user_id=user_id
				AND testuser_test_id='.$test_id.'
				AND testuser_user_id='.$user_id.'
				AND testuser_status>0
			GROUP BY testuser_id, testuser_test_id, testuser_user_id, testuser_creation_time, testuser_status, user_lastname, user_firstname, user_name
			LIMIT 1';
	}
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$testuser_id = $m['testuser_id'];
			$test_id = $m['testuser_test_id'];
			$user_id = $m['testuser_user_id'];
			$user_lastname = $m['user_lastname'];
			$user_firstname = $m['user_firstname'];
			$user_name = $m['user_name'];
			$test_start_time = $m['testuser_creation_time'];
			$test_score = $m['test_score'];
			$testuser_status = $m['testuser_status'];
			$usrtestdata = F_getUserTestStat($test_id, $user_id);
			$test_end_time = $m['test_end_time'];
		} else {
			$testuser_id = '';
			$test_id = '';
			$user_id = '';
			$user_lastname = '';
			$user_firstname = '';
			$user_name = '';
			$test_start_time = '';
			$test_end_time = '';
			$test_score = '';
			$testuser_status = 0;
		}
	} else {
		F_display_db_error();
	}
}

// get test basic score
$test_basic_score = 1;
$sql = 'SELECT test_score_right, test_duration_time	FROM '.K_TABLE_TESTS.' WHERE test_id='.intval($test_id).'';
if($r = F_db_query($sql, $db)) {
	if($m = F_db_fetch_array($r)) {
		$test_basic_score = $m['test_score_right'];
		$test_duration_time = $m['test_duration_time'];
	}
} else {
	F_display_db_error();
}


echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_resultuser">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="test_id">'.$l['w_test'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="hidden" name="testuser_id" id="testuser_id" value="'.$testuser_id.'" />'.K_NEWLINE;
echo '<input type="hidden" name="changecategory" id="changecategory" value="" />'.K_NEWLINE;
echo '<select name="test_id" id="test_id" size="0" onchange="document.getElementById(\'form_resultuser\').changecategory.value=1;document.getElementById(\'form_resultuser\').submit()" title="'.$l['h_test'].'">'.K_NEWLINE;
$sql = F_select_executed_tests_sql();
if($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['test_id'].'"';
		if($m['test_id'] == $test_id) {
			echo ' selected="selected"';
		}
		echo '>'.substr($m['test_begin_time'], 0, 10).' '.htmlspecialchars($m['test_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
}
else {
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectcategory');

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="user_id">'.$l['w_user'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
$userids = array();
echo '<select name="user_id" id="user_id" size="0" onchange="document.getElementById(\'form_resultuser\').submit()" title="'.$l['h_select_user'].'">'.K_NEWLINE;
$sql = 'SELECT user_id, user_lastname, user_firstname, user_name FROM '.K_TABLE_TEST_USER.', '.K_TABLE_USERS.' WHERE testuser_user_id=user_id AND testuser_test_id='.intval($test_id).'';
if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
	// filter for level
	$sql .= ' AND ((user_level<'.$_SESSION['session_user_level'].') OR (user_id='.$_SESSION['session_user_id'].'))';
	// filter for groups
	$sql .= ' AND user_id IN (SELECT tb.usrgrp_user_id
		FROM '.K_TABLE_USERGROUP.' AS ta, '.K_TABLE_USERGROUP.' AS tb
		WHERE ta.usrgrp_group_id=tb.usrgrp_group_id
			AND ta.usrgrp_user_id='.intval($_SESSION['session_user_id']).'
			AND tb.usrgrp_user_id=user_id)';
}
$sql .= ' ORDER BY user_lastname, user_firstname, user_name';
if($r = F_db_query($sql, $db)) {
	$usrcount = 1;
	while($m = F_db_fetch_array($r)) {
		$userids[] = $m['user_id'];
		echo '<option value="'.$m['user_id'].'"';
		if(isset($user_id) AND ($m['user_id'] == $user_id)) {
			echo ' selected="selected"';
		}
		echo '>';
		echo ''.$usrcount.'. ';
		echo ''.htmlspecialchars($m['user_lastname'].' '.$m['user_firstname'].' - '.$m['user_name'].'', ENT_NOQUOTES, $l['a_meta_charset']).'';
		echo '</option>'.K_NEWLINE;
		$usrcount++;
	}
}
else {
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;

// link for user selection popup
$jslink = 'tce_select_users_popup.php?cid=user_id';
if (!empty($userids)) {
	$uids = implode('x', $userids);
	if (strlen(K_PATH_PUBLIC_CODE.$jslink.$uids) < 512) {
		// add this filter only if the URL is short
		$jslink .= '&amp;uids='.$uids;
	}
}
$jsaction = 'selectWindow=window.open(\''.$jslink.'\', \'selectWindow\', \'dependent, height=600, width=800, menubar=no, resizable=yes, scrollbars=yes, status=no, toolbar=no\');return false;';
echo '<a href="#" onclick="'.$jsaction.'" class="xmlbutton" title="'.$l['w_select'].'">...</a>';

echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

if (isset($usrtestdata)) {

	echo '<div class="row">'.K_NEWLINE;
	echo '<span class="label">'.K_NEWLINE;
	echo '<span title="'.$l['h_time_begin'].'">'.$l['w_time_begin'].':</span>'.K_NEWLINE;
	echo '</span>'.K_NEWLINE;
	echo '<span class="formw">'.K_NEWLINE;
	echo $test_start_time.' ';
	if (isset($test_id) AND ($test_id > 0) AND isset($user_id) AND ($user_id > 0)) {
		F_submit_button('extendtime', '+'.K_EXTEND_TIME_MINUTES.' min', $l['h_add_five_minutes']);
	}
	echo '&nbsp;'.K_NEWLINE;
	echo '</span>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;

	echo getFormDescriptionLine($l['w_time_end'].':', $l['h_time_end'], $test_end_time);

	if (!isset($test_end_time) OR ($test_end_time <= 0)) {
		$time_diff = $test_duration_time * 60;
	} else {
		$time_diff = strtotime($test_end_time) - strtotime($test_start_time); //sec
	}
	$time_diff = gmdate('H:i:s', $time_diff);
	echo getFormDescriptionLine($l['w_test_time'].':', $l['w_test_time'], $time_diff);

	$passmsg = '';
	if ($usrtestdata['score_threshold'] > 0) {
		if ($usrtestdata['score'] >= $usrtestdata['score_threshold']) {
			$passmsg = ' - '.$l['w_passed'];
		} else {
			$passmsg = ' - '.$l['w_not_passed'];
		}
	}
	$score_all = $usrtestdata['score'].' / '.$usrtestdata['max_score'].' ('.round(100 * $usrtestdata['score'] / $usrtestdata['max_score']).'%)'.$passmsg;
	echo getFormDescriptionLine($l['w_score'].':', $l['h_score_total'], $score_all);

	$score_right_all = $usrtestdata['right'].' / '.$usrtestdata['all'].' ('.round(100 * $usrtestdata['right'] / $usrtestdata['all']).'%)';
	echo getFormDescriptionLine($l['w_answers_right'].':', $l['h_answers_right'], $score_right_all);

	echo getFormDescriptionLine($l['w_comment'].':', $l['h_testcomment'], F_decode_tcecode($usrtestdata['comment']));

	echo '<div class="rowl">'.K_NEWLINE;
	$topicresults = array(); // per-topic results
	if (isset($testuser_id) AND (!empty($testuser_id))) {
		// display user questions
		$sql = 'SELECT *
			FROM '.K_TABLE_QUESTIONS.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_SUBJECTS.', '.K_TABLE_MODULES.'
			WHERE question_id=testlog_question_id
				AND testlog_testuser_id='.$testuser_id.'
				AND question_subject_id=subject_id
				AND subject_module_id=module_id
			ORDER BY testlog_id';
		if($r = F_db_query($sql, $db)) {
			echo '<ol class="question">'.K_NEWLINE;
			while($m = F_db_fetch_array($r)) {

				// create per-topic results array
				if (!array_key_exists($m['module_id'], $topicresults)) {
					$topicresults[$m['module_id']] = array();
					$topicresults[$m['module_id']]['name'] = $m['module_name'];
					$topicresults[$m['module_id']]['num'] = 0;
					$topicresults[$m['module_id']]['right'] = 0;
					$topicresults[$m['module_id']]['wrong'] = 0;
					$topicresults[$m['module_id']]['unanswered'] = 0;
					$topicresults[$m['module_id']]['undisplayed'] = 0;
					$topicresults[$m['module_id']]['unrated'] = 0;
					$topicresults[$m['module_id']]['score'] = 0;
					$topicresults[$m['module_id']]['maxscore'] = 0;
					$topicresults[$m['module_id']]['subjects'] = array();
				}
				if (!array_key_exists($m['subject_id'], $topicresults[$m['module_id']]['subjects'])) {
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']] = array();
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['name'] = $m['subject_name'];
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['num'] = 0;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['right'] = 0;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['wrong'] = 0;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['unanswered'] = 0;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['undisplayed'] = 0;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['unrated'] = 0;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['score'] = 0;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['maxscore'] = 0;
				}
				$question_max_score = ($m['question_difficulty'] * $test_basic_score);
				// total number of questions
				$topicresults[$m['module_id']]['num'] += 1;
				$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['num'] += 1;
				// number of right answers
				if ($m['testlog_score'] > ($question_max_score / 2)) {
					$topicresults[$m['module_id']]['right'] += 1;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['right'] += 1;
				} else {
					// number of wrong answers
					$topicresults[$m['module_id']]['wrong'] += 1;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['wrong'] += 1;
				}
				// total number of unanswered questions
				if (strlen($m['testlog_change_time']) <= 0) {
					$topicresults[$m['module_id']]['unanswered'] += 1;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['unanswered'] += 1;
				}
				// total number of undisplayed questions
				if (strlen($m['testlog_display_time']) <= 0) {
					$topicresults[$m['module_id']]['undisplayed'] += 1;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['undisplayed'] += 1;
				}
				// number of free-text unrated questions
				if (strlen($m['testlog_score']) <= 0) {
					$topicresults[$m['module_id']]['unrated'] += 1;
					$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['unrated'] += 1;
				}
				// score
				$topicresults[$m['module_id']]['score'] += $m['testlog_score'];
				$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['score'] += $m['testlog_score'];
				// max score
				$topicresults[$m['module_id']]['maxscore'] += $question_max_score;
				$topicresults[$m['module_id']]['subjects'][$m['subject_id']]['maxscore'] += $question_max_score;

				echo '<li>'.K_NEWLINE;
				// display question stats
				echo '<strong>['.$m['testlog_score'].']'.K_NEWLINE;
				echo ' (';
				echo 'IP:'.getIpAsString($m['testlog_user_ip']).K_NEWLINE;
				if (isset($m['testlog_display_time']) AND (strlen($m['testlog_display_time']) > 0)) {
					echo ' | '.substr($m['testlog_display_time'], 11, 8).K_NEWLINE;
				} else {
					echo ' | --:--:--'.K_NEWLINE;
				}
				if (isset($m['testlog_change_time']) AND (strlen($m['testlog_change_time']) > 0)) {
					echo ' | '.substr($m['testlog_change_time'], 11, 8).K_NEWLINE;
				} else {
					echo ' | --:--:--'.K_NEWLINE;
				}
				if (isset($m['testlog_display_time']) AND isset($m['testlog_change_time'])) {
					echo ' | '.date('i:s', (strtotime($m['testlog_change_time']) - strtotime($m['testlog_display_time']))).'';
				} else {
					echo ' | --:--'.K_NEWLINE;
				}
				if (isset($m['testlog_reaction_time']) AND ($m['testlog_reaction_time'] > 0)) {
					echo ' | '.($m['testlog_reaction_time']/1000).'';
				} else {
					echo ' | ------'.K_NEWLINE;
				}
				echo ')</strong>'.K_NEWLINE;
				echo '<br />'.K_NEWLINE;
				// display question description
				echo F_decode_tcecode($m['question_description']).K_NEWLINE;
				if (K_ENABLE_QUESTION_EXPLANATION AND !empty($m['question_explanation'])) {
					echo '<br /><span class="explanation">'.$l['w_explanation'].':</span><br />'.F_decode_tcecode($m['question_explanation']).''.K_NEWLINE;
				}
				if ($m['question_type'] == 3) {
					// TEXT
					echo '<ul class="answer"><li>'.K_NEWLINE;
					echo F_decode_tcecode($m['testlog_answer_text']);
					echo '&nbsp;</li></ul>'.K_NEWLINE;
				} else {
					echo '<ol class="answer">'.K_NEWLINE;
					// display each answer option
					$sqla = 'SELECT *
						FROM '.K_TABLE_LOG_ANSWER.', '.K_TABLE_ANSWERS.'
						WHERE logansw_answer_id=answer_id
							AND logansw_testlog_id=\''.$m['testlog_id'].'\'
						ORDER BY logansw_order';
					if($ra = F_db_query($sqla, $db)) {
						while($ma = F_db_fetch_array($ra)) {
							echo '<li>';
							if ($m['question_type'] == 4) {
								// ORDER
								if ($ma['logansw_position'] > 0) {
									if ($ma['logansw_position'] == $ma['answer_position']) {
										echo '<acronym title="'.$l['h_answer_right'].'" class="okbox">'.$ma['logansw_position'].'</acronym>';
									} else {
										echo '<acronym title="'.$l['h_answer_wrong'].'" class="nobox">'.$ma['logansw_position'].'</acronym>';
									}
								} else {
									echo '<acronym title="'.$l['m_unanswered'].'" class="offbox">&nbsp;</acronym>';
								}
							} elseif ($ma['logansw_selected'] > 0) {
								if (F_getBoolean($ma['answer_isright'])) {
									echo '<acronym title="'.$l['h_answer_right'].'" class="okbox">x</acronym>';
								} else {
									echo '<acronym title="'.$l['h_answer_wrong'].'" class="nobox">x</acronym>';
								}
							} elseif ($m['question_type'] == 1) {
								// MCSA
								echo '<acronym title="-" class="offbox">&nbsp;</acronym>';
							} else {
								if ($ma['logansw_selected'] == 0) {
									if (F_getBoolean($ma['answer_isright'])) {
										echo '<acronym title="'.$l['h_answer_wrong'].'" class="nobox">&nbsp;</acronym>';
									} else {
										echo '<acronym title="'.$l['h_answer_right'].'" class="okbox">&nbsp;</acronym>';
									}
								} else {
									echo '<acronym title="'.$l['m_unanswered'].'" class="offbox">&nbsp;</acronym>';
								}
							}
							echo '&nbsp;';
							if ($m['question_type'] == 4) {
								echo '<acronym title="'.$l['w_position'].'" class="onbox">'.$ma['answer_position'].'</acronym>';
							} elseif (F_getBoolean($ma['answer_isright'])) {
								echo '<acronym title="'.$l['w_answers_right'].'" class="onbox">&reg;</acronym>';
							} else {
								echo '<acronym title="'.$l['w_answers_wrong'].'" class="offbox">&nbsp;</acronym>';
							}
							echo ' ';
							echo F_decode_tcecode($ma['answer_description']);
							if (K_ENABLE_ANSWER_EXPLANATION AND !empty($ma['answer_explanation'])) {
								echo '<br /><span class="explanation">'.$l['w_explanation'].':</span><br />'.F_decode_tcecode($ma['answer_explanation']).''.K_NEWLINE;
							}
							echo '</li>'.K_NEWLINE;
						}
					} else {
						F_display_db_error();
					}
					echo '</ol>'.K_NEWLINE;
				} // end multiple answers
				// display teacher/supervisor comment to the question
				if (isset($m['testlog_comment']) AND (!empty($m['testlog_comment']))) {
					echo '<ul class="answer"><li class="comment">'.K_NEWLINE;
					echo F_decode_tcecode($m['testlog_comment']);
					echo '&nbsp;</li></ul>'.K_NEWLINE;
				}
				echo '<br /><br />'.K_NEWLINE;
				echo '</li>'.K_NEWLINE;
			}
			echo '</ol>'.K_NEWLINE;
		} else {
			F_display_db_error();
		}
	}
	echo '</div>'.K_NEWLINE;

	// print per-topic results
	echo '<div class="rowl">'.K_NEWLINE;
	echo '<hr />'.K_NEWLINE;
	echo '<h2>'.$l['w_subjects'].'</h2>';
	echo '<ul>';
	foreach ($topicresults as $res_module) {
		echo '<li>';
		$score_percent = round(100 * $res_module['score'] / $res_module['maxscore']);
		echo '<acronym title="'.$l['w_score'].'" class="';
		if ($score_percent > 50) {echo 'okbox';} else {echo 'nobox';}
		echo '">'.$res_module['score'].' / '.$res_module['maxscore'].' ('.$score_percent.'%)</acronym>';
		$score_percent = round(100 * $res_module['right'] / $res_module['num']);
		echo ' <acronym title="'.$l['w_answers_right'].'" class="';
		if ($score_percent > 50) {echo 'okbox';} else {echo 'nobox';}
		echo '">'.$res_module['right'].' / '.$res_module['num'].' ('.$score_percent.'%)</acronym>';
		echo ' <strong>'.$res_module['name'].'</strong>';
		echo '<ul>';
		foreach ($res_module['subjects'] as $res_subject) {
			echo '<li>';
			$score_percent = round(100 * $res_subject['score'] / $res_subject['maxscore']);
			echo '<acronym title="'.$l['w_score'].'" class="';
			if ($score_percent > 50) {echo 'okbox';} else {echo 'nobox';}
			echo '">'.$res_subject['score'].' / '.$res_subject['maxscore'].' ('.$score_percent.'%)</acronym>';
			$score_percent = round(100 * $res_subject['right'] / $res_subject['num']);
			echo ' <acronym title="'.$l['w_answers_right'].'" class="';
			if ($score_percent > 50) {echo 'okbox';} else {echo 'nobox';}
			echo '">'.$res_subject['right'].' / '.$res_subject['num'].' ('.$score_percent.'%)</acronym>';
			echo ' '.$res_subject['name'];
			echo '</li>'.K_NEWLINE;
		}
		echo '</ul>';
		echo '</li>'.K_NEWLINE;
	}
	echo '</ul>';
	echo '<hr />'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;

	echo '<div class="row">'.K_NEWLINE;

	// show buttons by case
	if (isset($test_id) AND ($test_id > 0) AND isset($user_id) AND ($user_id > 0)) {
		F_submit_button('delete', $l['w_delete'], $l['h_delete']);

		if($testuser_status < 4) {
			// lock test button
			F_submit_button('lock', $l['w_lock'], $l['w_lock']);
		} else {
			// unlock test button
			F_submit_button('unlock', $l['w_unlock'], $l['w_unlock']);
		}

		echo '<br /><br />';
		echo '<a href="'.pdfLink(3, $test_id, 0, $user_id, '', 0).'" class="xmlbutton" title="'.$l['h_pdf'].'">'.$l['w_pdf'].'</a> ';
		echo '<a href="tce_email_results.php?testid='.$test_id.'&amp;userid='.$user_id.'&amp;mode=1" class="xmlbutton" title="'.$l['h_email_result'].'">'.$l['w_email_result'].'</a> ';
		echo '<a href="tce_email_results.php?testid='.$test_id.'&amp;userid='.$user_id.'&amp;mode=0" class="xmlbutton" title="'.$l['h_email_result'].' + PDF">'.$l['w_email_result'].' + PDF</a> ';
	}

	// comma separated list of required fields
	echo '<input type="hidden" name="ff_required" id="ff_required" value="" />'.K_NEWLINE;
	echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;

} // end "if (isset($usrtestdata))"

echo '</form>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_result_user'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
