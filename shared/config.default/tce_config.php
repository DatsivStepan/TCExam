<?php
//============================================================+
// File name   : tce_config.php
// Begin       : 2002-02-24
// Last Update : 2013-04-02
//
// Description : Shared configuration file.
//
// Author: Nicola Asuni
//
// (c) Copyright 2004-2013:
//               Nicola Asuni
//               Tecnick.com LTD
//               UK
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com LTD
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
 * Shared configuration file.
 * @package com.tecnick.tcexam.shared.cfg
 * @brief TCExam Main Configuration
 * @author Nicola Asuni
 * @since 2002-02-24
 */

/**
 * TCExam version (do not change).
 */
define ('K_TCEXAM_VERSION', '12.0.007');

/**
 * 2-letters code for default language.
 */
define ('K_LANGUAGE', 'cn');

/**
 * If true, display a language selector.
 */
define ('K_LANGUAGE_SELECTOR', true);

/**
 * Defines a serialized array of available languages.
 * Each language is indexed using a 2-letters code (ISO 639).
 */
define ('K_AVAILABLE_LANGUAGES', serialize(array(
	'ar' => 'Arabian',
	'az' => 'Azerbaijani',
	'bg' => 'Bulgarian',
	'br' => 'Brazilian Portuguese',
	'cn' => 'Chinese',
	'de' => 'German',
	'el' => 'Greek',
	'en' => 'English',
	'es' => 'Spanish',
	'fr' => 'French',
	'hi' => 'Hindi',
	'he' => 'Hebrew',
	'hu' => 'Hungarian',
	'id' => 'Indonesian',
	'it' => 'Italian',
	'jp' => 'Japanese',
	'mr' => 'Marathi',
	'ms' => 'Malay (Bahasa Melayu)',
	'nl' => 'Dutch',
	'pl' => 'Polish',
	'ro' => 'Romanian',
	'ru' => 'Russian',
	'tr' => 'Turkish',
	'vn' => 'Vietnamese'
)));

ini_set('zend.ze1_compatibility_mode', false); // disable PHP4 compatibility mode

// -- INCLUDE files --
require_once('../../shared/config/tce_paths.php');
require_once('../../shared/config/tce_general_constants.php');

/**
 * If true enable One-Time-Password authentication on login.
 */
define ('K_OTP_LOGIN', false);

/**
 * Ratio at which the delay will be increased after every failed login attempt.
 */
define ('K_BRUTE_FORCE_DELAY_RATIO', 2);

/**
 * Number of difficulty levels for questions.
 */
define ('K_QUESTION_DIFFICULTY_LEVELS', 10);

/**
 * If true enable virtual keyboard on some textarea fields.
 */
define('K_ENABLE_VIRTUAL_KEYBOARD', true);

/**
 * Popup window height in pixels for test info.
 */
define ('K_TEST_INFO_HEIGHT', 400);

/**
 * Popup window width in pixels for test info.
 */
define ('K_TEST_INFO_WIDTH', 700);

/**
 * Number of columns for answer textarea.
 */
define ('K_ANSWER_TEXTAREA_COLS', 70);

/**
 * Number of rows for answer textarea.
 */
define ('K_ANSWER_TEXTAREA_ROWS', 15);

/**
 * If true enable explanation field for questions.
 */
define ('K_ENABLE_QUESTION_EXPLANATION', true);

/**
 * If true enable explanation field for answers.
 */
define ('K_ENABLE_ANSWER_EXPLANATION', true);

/**
 * If true display test description before executing the test.
 */
define ('K_DISPLAY_TEST_DESCRIPTION', true);

/**
 * If true compare short answers in binary mode.
 */
define ('K_SHORT_ANSWERS_BINARY', false);

/**
 * User's session life time in seconds.
 */
define ('K_SESSION_LIFE', K_SECONDS_IN_HOUR);

/**
 * Define timestamp format using PHP notation (do not change).
 */
define ('K_TIMESTAMP_FORMAT', 'Y-m-d H:i:s');

/**
 * Define max line length in chars for question navigator on test execution interface.
 */
define ('K_QUESTION_LINE_MAX_LENGTH', 70);

/**
 * If true, check for possible session hijacking (set to false if you have login problems).
 */
define ('K_CHECK_SESSION_FINGERPRINT', false);

/**
 * If true uses a strong encryption algorithm for passwords.
 */
define ('K_STRONG_PASSWORD_ENCRYPTION', true);

// Client Cookie settings

/**
 * Cookie domain.
 */
define ('K_COOKIE_DOMAIN', '');

/**
 * Cookie path.
 */
define ('K_COOKIE_PATH', '/');

/**
 * If true use secure cookies.
 */
define ('K_COOKIE_SECURE', false);

/**
 * Expiration time for cookies.
 */
define ('K_COOKIE_EXPIRE', K_SECONDS_IN_DAY);

/**
 * Various pages redirection modes after login (valid values are 1, 2, 3 and 4).
 * 1 = relative redirect.
 * 2 = absolute redirect.
 * 3 = html redirect.
 * 4 = full redirect.
 */
define ('K_REDIRECT_LOGIN_MODE', 4);

/**
 * If true enable password reset feature.
 */
define ('K_PASSWORD_RESET', TRUE);

/**
 * URL to be redirected at logout (leave empty for default).
 */
define ('K_LOGOUT_URL', '');


// Error settings

/**
 * Define error reporting types for debug.
 */
define ('K_ERROR_TYPES', E_ALL | E_STRICT);
//define ('K_ERROR_TYPES', E_ERROR | E_WARNING | E_PARSE);

/**
 * Enable error logs (../log/tce_errors.log).
 */
define ('K_USE_ERROR_LOG', false);

/**
 * If true display messages and errors on Javascript popup window.
 */
define ('K_ENABLE_JSERRORS', false);

/**
 * Set your own timezone here.
 * Possible values are listed on:
 * http://php.net/manual/en/timezones.php
 */
define ('K_TIMEZONE', 'UTC');

/**
 * Default minutes used to extend test duration.
 */
define('K_EXTEND_TIME_MINUTES', 5);


// ---------- * ---------- * ---------- * ---------- * ----------


/**
 * Error handlers.
 */
require_once('../../shared/code/tce_functions_errmsg.php');

// load language resources

// set user's selected language or default language
if(isset($_REQUEST['lang'])
	AND (strlen($_REQUEST['lang']) == 2)
	AND (array_key_exists($_REQUEST['lang'],unserialize(K_AVAILABLE_LANGUAGES)))) {
	/**
	 * Use requested language.
	 * @ignore
	 */
	define ('K_USER_LANG', $_REQUEST['lang']);
	// set client cookie
	setcookie('SessionUserLang', K_USER_LANG, time() + K_COOKIE_EXPIRE, K_COOKIE_PATH, K_COOKIE_DOMAIN, K_COOKIE_SECURE);
} elseif (isset($_COOKIE['SessionUserLang'])
	AND (strlen($_COOKIE['SessionUserLang']) == 2)
	AND (array_key_exists($_COOKIE['SessionUserLang'],unserialize(K_AVAILABLE_LANGUAGES)))) {
	/**
	 * Use session language.
	 * @ignore
	 */
	define ('K_USER_LANG', $_COOKIE['SessionUserLang']);
} else {
	/**
	 * Use default language.
	 * @ignore
	 */
	define ('K_USER_LANG', K_LANGUAGE);
}

// TMX class
require_once('../../shared/code/tce_tmx.php');
// istantiate new TMXResourceBundle object
$lang_resources = new TMXResourceBundle(K_PATH_TMX_FILE, K_USER_LANG, K_PATH_LANG_CACHE.basename(K_PATH_TMX_FILE, '.xml').'_'.K_USER_LANG.'.php');
$l = $lang_resources->getResource(); // language array

ini_set('arg_separator.output', '&amp;');
//date_default_timezone_set(K_TIMEZONE);

if(!defined('PHP_VERSION_ID')) {
	$version = PHP_VERSION;
	define('PHP_VERSION_ID', (($version{0} * 10000) + ($version{2} * 100) + $version{4}));
}
if (PHP_VERSION_ID < 50300) {
	@set_magic_quotes_runtime(false); //disable magic quotes
	ini_set('magic_quotes_gpc', 'On');
	ini_set('magic_quotes_runtime', 'Off');
	ini_set('register_long_arrays', 'On');
	//ini_set('register_globals', 'On');
}

// --- get 'post', 'get' and 'cookie' variables
foreach ($_REQUEST as $postkey => $postvalue) {
	if (($postkey{0} != '_') AND (!preg_match('/[A-Z]/', $postkey{0}))) {
		if (!get_magic_quotes_gpc() AND !is_array($postvalue)) {
			// emulate magic_quotes_gpc
			$postvalue = addslashes($postvalue);
			$_REQUEST[$postkey] = $postvalue;
			if (isset($_GET[$postkey])) {
				$_GET[$postkey] = $postvalue;
			} elseif (isset($_POST[$postkey])) {
				$_POST[$postkey] = $postvalue;
			} elseif (isset($_COOKIE[$postkey])) {
				$_COOKIE[$postkey] = $postvalue;
			}
		}
		$$postkey = $postvalue;
	}
}

//============================================================+
// END OF FILE
//============================================================+
