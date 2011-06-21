<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'base';
$app['version'] = '5.9.9.2';
$app['release'] = '2.2';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['summary'] = 'Base system and settings.'; // FIXME: translate
$app['description'] = 'Base system and settings ... blah blah.'; // FIXME: translate

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('base_dashboard');
$app['category'] = lang('base_category_system');
$app['subcategory'] = lang('base_subcategory_settings');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

// Special case -- hide this from controller list
// $app['controllers']['base']['title'] = lang('base_dashboard');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'clearos-base',
    'clearos-framework',
    'system-theme',
    'chkconfig',
    'coreutils',
    'file',
    'initscripts',
    'passwd',
    'rpm',
    'shadow-utils',
    'sudo',
    'sysvinit-tools',
    'syswatch',
    'util-linux-ng',
    'webconfig-php',
    'webconfig-utils'
);


$app['core_file_manifest'] = array(
   'webconfig-restart' => array(
        'target' => '/usr/sbin/webconfig-restart',
        'mode' => '0755',
        'owner' => 'root',
        'group' => 'root',
    ),
);
