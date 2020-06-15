<?php
/**
 * Constant that is checked in included files to prevent direct access.
 * define() is used in the installation folder rather than "const" to not error for PHP 5.2 and lower
 */
define('_JEXEC', 1);

$joomla_directory = 'j3x'; // or j4x

if (file_exists(__DIR__ . '/' . $joomla_directory . '/defines.php'))
{
	include_once __DIR__ . '/' . $joomla_directory . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', __DIR__ . '/' . $joomla_directory);
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';
require_once __DIR__ . '/vendor/autoload.php';


