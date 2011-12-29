<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'base';
$app['version'] = '6.2.0.beta3';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('base_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('base_app_name');
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

// FIXME: beta only - remove for final
$app['obsoletes'] = array(
    'app-shutdown',
);
$app['core_obsoletes'] = array(
    'app-shutdown-core',
);

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
    'webconfig-mod_ssl',
    'webconfig-php',
    'webconfig-php-process',
    'webconfig-utils'
);


$app['core_file_manifest'] = array(
    'syncaction' => array(
        'target' => '/usr/sbin/syncaction',
        'mode' => '0755',
        'owner' => 'root',
        'group' => 'root',
    ),
    'webconfig-restart' => array(
        'target' => '/usr/sbin/webconfig-restart',
        'mode' => '0755',
        'owner' => 'root',
        'group' => 'root',
    ),
    'app-base.cron' => array( 'target' => '/etc/cron.d/app-base' ),
    'base' => array( 'target' => '/var/clearos/base/access_control/public' ),
    'access_control.conf' => array( 'target' => '/etc/clearos/base.d/access_control.conf' ),
    'clearos-beta.repo' => array( 'target' => '/etc/yum.repos.d/clearos-beta.repo' ),
);

$app['core_directory_manifest'] = array(
    '/etc/clearos/base.d' => array(),
    '/var/clearos/base' => array(),
    '/var/clearos/base/access_control' => array(),
    '/var/clearos/base/access_control/authenticated' => array(),
    '/var/clearos/base/access_control/custom' => array(),
    '/var/clearos/base/access_control/public' => array(),
    '/var/clearos/base/daemon' => array(),
);
