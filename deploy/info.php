<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'base';
$app['version'] = '1.1.1';
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
$app['menu_enabled'] = FALSE;

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
    'webconfig-mod_ssl',
    'webconfig-php',
    'webconfig-php-ldap',
    'webconfig-php-process',
    'webconfig-php-xml',
    'webconfig-utils',
    'webconfig-zend-guard-loader'
);


$app['core_file_manifest'] = array(
    'base.acl' => array( 'target' => '/var/clearos/base/access_control/authenticated/base' ),
    'filewatch-base-install.conf'=> array('target' => '/etc/clearsync.d/filewatch-base-install.conf'),
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
    'wc-yum' => array(
        'target' => '/usr/sbin/wc-yum',
        'mode' => '0755',
    ),
    'yum-install' => array(
        'target' => '/usr/sbin/yum-install',
        'mode' => '0755',
    ),
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
