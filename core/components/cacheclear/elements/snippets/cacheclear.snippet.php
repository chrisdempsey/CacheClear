<?php
/**
 * CacheClear snippet for CacheClear extra
 *
 * Copyright 2012-2014 by Bob Ray <http://bobsguides.com>
 * Created on 12-14-2012
 *
 * CacheClear is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * CacheClear is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * CacheClear; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package cacheclear
 */

/**
 * Description
 * -----------
 * Delete all files in the core/cache directory
 *
 * Variables
 * ---------
 * @var $modx modX
 * @var $scriptProperties array
 *
 * @package cacheclear
 **/

/* addition by chris - allow managers and authorised IPs only */

// check for manager session
if (!$modx->user->hasSessionContext('mgr')) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[ClearCache] accessed by user without Manager Session' . "\n");
    die();
}

// create array of authorised IPs
$arrAuthorisedIPs = array();
$arrAuthorisedIPs[] = '217.155.46.234'; // cube office
$arrAuthorisedIPs[] = '188.39.8.200';   // aqua office

// die if current IP is not in $arrAuthorisedIPs
if (in_array(fn_real_ip_address(), $arrAuthorisedIPs)) {
    echo 'Authorised IP: ' . fn_real_ip_address() . "\n<br />";
} else {
    die();
}



if (!function_exists("rrmdir")) {
    function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}

$output = '';
$modx->lexicon->load('cacheclear:default');

$cm = $modx->getCacheManager();
$cacheDir = $cm->getCachePath();

$cacheDir = rtrim($cacheDir, '/\\');

$output .= '<p>' . $modx->lexicon('cc_cache_dir') . ': ' . $cacheDir;
$output .= '<br />';

$files = scandir($cacheDir);

$output .= "<ul>\n";
foreach ($files as $file) {
    if ($file == '.' || $file == '..') {
        continue;
    }
    if (is_dir($cacheDir . '/' . $file)) {
        if ($file == 'logs') {
            continue;
        }
        $output .= "\n<li>" . $modx->lexicon('cc_removing') . ': ' . $file . '</li>';
        rrmdir($cacheDir . '/' . $file);
        if (is_dir($cacheDir . '/' . $file)) {
            $output .= "\n<li>" . $modx->lexicon('cc_failed_to_remove') . ': ' . $file . '</li>';
        }
    } else {
        unlink($cacheDir . '/' . $file);
    }
}

/* addition to clear statcache directory */
$cacheDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'statcache';
$files = scandir($cacheDir);

$output .= "<li><strong>Statcache</strong></li>\n";

$output .= "<ul>\n";
foreach ($files as $file) {
    if ($file == '.' || $file == '..') {
        continue;
    }
    if (is_dir($cacheDir . '/' . $file)) {
        if ($file == 'logs') {
            continue;
        }
        $output .= "\n<li>" . $modx->lexicon('cc_removing') . ': ' . $file . '</li>';
        rrmdir($cacheDir . '/' . $file);
        if (is_dir($cacheDir . '/' . $file)) {
            $output .= "\n<li>" . $modx->lexicon('cc_failed_to_remove') . ': ' . $file . '</li>';
        }
    } else {
        unlink($cacheDir . '/' . $file);
    }
}
/* clear statcache directory ends */

$output .= "\n</p></ul><p>" . $modx->lexicon('cc_finished') . "</p>";

return $output;

/**
 * Function to find real IP address
 * http://www.cyberciti.biz/faq/php-howto-read-ip-address-of-remote-computerbrowser/
 */

function fn_real_ip_address() {
	if (getenv('HTTP_CLIENT_IP')) {
		$ip = getenv('HTTP_CLIENT_IP');
	}
	elseif (getenv('HTTP_X_FORWARDED_FOR')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	}
	elseif (getenv('HTTP_X_FORWARDED')) {
		$ip = getenv('HTTP_X_FORWARDED');
	}
	elseif (getenv('HTTP_FORWARDED_FOR')) {
		$ip = getenv('HTTP_FORWARDED_FOR');
	}
	elseif (getenv('HTTP_FORWARDED')) {
		$ip = getenv('HTTP_FORWARDED');
	}
	else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

