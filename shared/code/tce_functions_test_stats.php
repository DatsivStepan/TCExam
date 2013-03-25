<?php
//============================================================+
// File name   : tce_functions_test_stats.php
// Begin       : 2004-06-10
// Last Update : 2011-05-24
//
// Description : Statistical functions for test results.
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
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com LTD
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
 * Statistical functions for test results.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2004-06-10
 */

/**
 * Returns an URL to open the PDF generator page.
 * @param $mode (string) PDF mode (1=all users results, 2=questions stats, 3=detailed report for single user 4=all users details)
 * @param $test_id (int) test ID
 * @param $groupid (int) group ID
 * @param $user_id (int) user ID
 * @param $order_field (string) table order field name
 * @param $orderdir (int) order direction (0 = ASC, 1 = DESC)
 * @return string
 */
function pdfLink($mode, $test_id, $groupid=0, $user_id=0, $order_field='', $orderdir=0) {
	$pdflink = 'tce_pdf_results.php?';
	$pdflink .= 'mode='.$mode;
	$pdflink .= '&amp;testid='.$test_id;
	$pdflink .= '&amp;groupid='.$groupid;
	if ($user_id > 0) {
		$pdflink .= '&amp;userid='.$user_id;
	}
	if (!empty($order_field)) {
		$pdflink .= '&amp;order_field='.urlencode($order_field);
	}
	$pdflink .= '&amp;orderdir='.$orderdir;
	return $pdflink;
}

/**
 * Lock the user's test.<br>
 * @param $test_id (int) test ID
 * @param $user_id (int) user ID
 */
function F_lockUserTest($test_id, $user_id) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$test_id = intval($test_id);
	$user_id = intval($user_id);
	$sql = 'UPDATE '.K_TABLE_TEST_USER.'
			SET testuser_status=4
			WHERE testuser_test_id='.$test_id.'
				AND testuser_user_id='.$user_id.'';
	if(!$r = F_db_query($sql, $db)) {
		F_display_db_error();
	}
}

/**
 * Returns test data structure for selected question:
 * <ul>
 * <li>$data['right'] = number of right answers</li>
 * <li>$data['wrong'] = number of wrong answers</li>
 * <li>$data['unanswered'] = number of unanswered questions</li>
 * <li>$data['undisplayed'] = number of undisplayed questions</li>
 * </ul>
 * @param $test_id (int) test ID
 * @param $question_id (int) question ID
 * return $data
 */
function F_getQuestionTestStat($test_id, $question_id) {
	require_once('../config/tce_config.php');
	global $db, $l;

	$test_id = intval($test_id);
	$question_id = intval($question_id);

	$test_score_right = 0;
	$question_difficulty = 0;

	// get test default scores
	$sql = 'SELECT test_score_right
		FROM '.K_TABLE_TESTS.'
		WHERE test_id='.$test_id.'';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$test_score_right = $m['test_score_right'];
		}
	} else {
		F_display_db_error();
	}

	// get question difficulty
	$sql = 'SELECT question_difficulty
		FROM '.K_TABLE_QUESTIONS.'
		WHERE question_id='.$question_id.'';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$question_difficulty = $m['question_difficulty'];
		}
	} else {
		F_display_db_error();
	}

	$question_half_score = $test_score_right * $question_difficulty / 2;

	$data = array();
	// number of questions
	$data['num'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$question_id.'');
	// number of questions with right answers
	$data['right'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$question_id.' AND testlog_score>'.$question_half_score.'');
	// number of questions with wrong answers
	$data['wrong'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$question_id.' AND testlog_score<='.$question_half_score.'');
	// number of unanswered questions
	$data['unanswered'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$question_id.' AND testlog_change_time IS NULL');
	// number of undisplayed questions
	$data['undisplayed'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$question_id.' AND testlog_display_time IS NULL');
	// number of free-text unrated questions
	$data['unrated'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$question_id.' AND testlog_score IS NULL');

	return $data;
}

/**
 * Returns test data structure for selected user:
 * <ul>
 * <li>$data['all'] = total number of questions</li>
 * <li>$data['right'] = number of right answers for multiple-choice questions (score &gt; 50% max points)</li>
 * <li>$data['wrong'] = number of wrong answers for multiple-choice questions (score &lt;= 50% max points)</li>
 * <li>$data['textright'] = number of right answers for free-text questions (score &gt; 50% max points)</li>
 * <li>$data['textwrong'] = number of wrong answers for free-text questions (score &lt;= 50% max points)</li>
 * <li>$data['unanswered'] = total number of unanswered questions</li>
 * <li>$data['undisplayed'] = total number of undisplayed questions</li>
 * <li>$data['basic_score'] = basic points for each difficulty level of questions</li>
 * <li>$data['max_score'] = maximum test score</li>
 * <li>$data['score'] = user's score</li>
 * <li>$data['comment'] = user's test comment</li>
 * <li>$data['time'] = user's test start time</li>
 * </ul>
 * @param $test_id (int) test ID
 * @param $user_id (int) user's test ID
 * return array $data
 */
function F_getUserTestStat($test_id, $user_id) {
	require_once('../config/tce_config.php');
	global $db, $l;

	$test_id = intval($test_id);
	$user_id = intval($user_id);

	$data = array();

	// get test default scores
	$sql = 'SELECT test_score_right, test_max_score, test_score_threshold
		FROM '.K_TABLE_TESTS.'
		WHERE test_id='.$test_id.'';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$data['basic_score'] = $m['test_score_right'];
			$data['max_score'] = $m['test_max_score'];
			$data['score_threshold'] = $m['test_score_threshold'];
		}
	} else {
		F_display_db_error();
	}

	// total number of questions
	$data['all'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_QUESTIONS, 'WHERE testlog_testuser_id=testuser_id AND testlog_question_id=question_id AND testuser_test_id='.$test_id.' AND testuser_user_id='.$user_id.'');
	// number of right answers
	$data['right'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_QUESTIONS, 'WHERE testlog_testuser_id=testuser_id AND testlog_question_id=question_id AND testuser_test_id='.$test_id.' AND testuser_user_id='.$user_id.' AND testlog_score>((question_difficulty*'.$data['basic_score'].')/2)');
	// number of wrong answers
	$data['wrong'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_QUESTIONS, 'WHERE testlog_testuser_id=testuser_id AND testlog_question_id=question_id AND testuser_test_id='.$test_id.' AND testuser_user_id='.$user_id.' AND testlog_score<=((question_difficulty*'.$data['basic_score'].')/2)');
	// total number of unanswered questions
	$data['unanswered'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testuser_user_id='.$user_id.' AND testlog_change_time IS NULL');
	// total number of undisplayed questions
	$data['undisplayed'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testuser_user_id='.$user_id.' AND testlog_display_time IS NULL');
	// number of free-text unrated questions
	$data['unrated'] = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND  testuser_test_id='.$test_id.' AND testuser_user_id='.$user_id.' AND testlog_score IS NULL');
	// get user's score
	$sql = 'SELECT SUM(testlog_score) AS total_score
		FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.'
		WHERE testlog_testuser_id=testuser_id
			AND testuser_user_id='.$user_id.'
			AND testuser_test_id='.$test_id.'
		GROUP BY testuser_id';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$data['score'] = $m['total_score'];
		}
	} else {
		F_display_db_error();
	}

	// get user's test comment
	$data['comment'] = '';
	$sql = 'SELECT testuser_comment, testuser_creation_time
	FROM '.K_TABLE_TEST_USER.'
	WHERE testuser_user_id='.$user_id.'
		AND testuser_test_id='.$test_id.'
	LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$data['comment'] = $m['testuser_comment'];
			$data['time'] = $m['testuser_creation_time'];
		}
	} else {
		F_display_db_error();
	}
	
	$sql = 'SELECT testuser_id, testuser_creation_time, testuser_status, MAX(testlog_change_time) AS test_end_time
		FROM '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.'
		WHERE testlog_testuser_id=testuser_id
			AND testuser_test_id='.$test_id.'
			AND testuser_user_id='.$user_id.'
			AND testuser_status>0
		GROUP BY testuser_id, testuser_creation_time, testuser_status
		LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$data['test_start_time'] = $m['testuser_creation_time'];
			$data['test_end_time'] = $m['test_end_time'];
			$data['testuser_id'] = $m['testuser_id'];
			$data['testuser_status'] = $m['testuser_status'];
		}
	} else {
		F_display_db_error();
	}
	
	return $data;
}

//============================================================+
// END OF FILE
//============================================================+
