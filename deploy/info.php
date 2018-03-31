<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'base';
$app['version'] = '2.4.20';
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

$app['controllers']['base']['title'] = $app['name'];
$app['controllers']['theme']['title'] = lang('base_theme');
$app['controllers']['language']['title'] = lang('base_language');
$app['controllers']['shutdown']['title'] = lang('base_shutdown_restart');

/////////////////////////////////////////////////////////////////////////////
// Controller info
/////////////////////////////////////////////////////////////////////////////

// Wizard extras
$app['controllers']['session']['wizard_name'] = lang('base_change_password');
$app['controllers']['session']['wizard_description'] = lang('base_change_password_description');
$app['controllers']['session']['inline_help'] = array(
    lang('base_change_password') => lang('base_change_password_help'),
);

/////////////////////////////////////////////////////////////////////////////
// Dashboard Widgets
/////////////////////////////////////////////////////////////////////////////

$app['dashboard_widgets'] = array(
    $app['category'] => array(
        'base/base_dashboard/shutdown' => array(
            'title' => lang('base_shutdown_restart'),
            'restricted' => TRUE,
        )
    )
);

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['requires'] = array(
    'theme-clearos-admin >= 7.4.3'
);

$app['core_requires'] = array(
    'acpid',
    'system-base',
    'clearos-framework >= 7.4.3',
    'clearos-release >= 7-4.4',
    'cpupowerutils',
    'csplugin-filewatch',
    'chkconfig',
    'coreutils',
    'file',
    'firewalld',
    'grub2-tools',
    'initscripts',
    'logrotate',
    'passwd',
    'pciutils',
    'rpm',
    'shadow-utils',
    'sudo',
    'sysvinit-tools',
    'tmpwatch',
    'util-linux-ng',
    'usbutils',
    'virt-what',
    'webconfig-mod_ssl',
    'webconfig-httpd >= 2.4.6-67',
    'webconfig-php',
    'webconfig-php-mbstring',
    'webconfig-php-process',
    'webconfig-php-xml',
    'webconfig-utils >= 7.4.0',
    'wget',
    'yum-utils'
);

$app['core_file_manifest'] = array(
    'authenticated.acl' => array('target' => '/var/clearos/base/access_control/authenticated/base'),
    'public.acl' => array('target' => '/var/clearos/base/access_control/public/base'),
    'rest.acl' => array('target' => '/var/clearos/base/access_control/rest/base'),
    'filewatch-base-webconfig.conf'=> array('target' => '/etc/clearsync.d/filewatch-base-webconfig.conf'),
    'filewatch-base-clearsync.conf'=> array('target' => '/etc/clearsync.d/filewatch-base-clearsync.conf'),
    'filewatch-system-database-event.conf'=> array('target' => '/etc/clearsync.d/filewatch-system-database-event.conf'),
    'centos-sclo-scl-rh-unverified.repo' => array(
        'target' => '/etc/yum.repos.d/centos-sclo-scl-rh-unverified.repo',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'centos-sclo-scl-unverified.repo' => array(
        'target' => '/etc/yum.repos.d/centos-sclo-scl-unverified.repo',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'centos-unverified.repo' => array(
        'target' => '/etc/yum.repos.d/centos-unverified.repo',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'epel-unverified.repo' => array(
        'target' => '/etc/yum.repos.d/epel-unverified.repo',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
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
    'access_control.conf' => array( 'target' => '/etc/clearos/base.d/access_control.conf' ),
    'rsyslog.php'=> array('target' => '/var/clearos/base/daemon/rsyslog.php'),
    'wizard.conf' => array(
        'target' => '/etc/clearos/base.d/wizard.conf',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'wc-yum' => array(
        'target' => '/usr/sbin/wc-yum',
        'mode' => '0755',
    ),
    'app-manager' => array(
        'target' => '/usr/sbin/app-manager',
        'mode' => '0755',
    ),
    'yum-install' => array(
        'target' => '/usr/sbin/yum-install',
        'mode' => '0755',
    ),
    'webconfig-service' => array(
        'target' => '/usr/sbin/webconfig-service',
        'mode' => '0755',
    ),
    'software-updates-event'=> array(
        'target' => '/var/clearos/events/software_updates/base',
        'mode' => '0755'
    ),
);

$app['core_directory_manifest'] = array(
    '/etc/clearos/base.d' => array(),
    '/var/clearos/base' => array(),
    '/var/clearos/base/access_control' => array(),
    '/var/clearos/base/access_control/authenticated' => array(),
    '/var/clearos/base/access_control/custom' => array(),
    '/var/clearos/base/access_control/public' => array(),
    '/var/clearos/base/access_control/rest' => array(),
    '/var/clearos/base/daemon' => array(),
    '/var/clearos/base/translations' => array(),
    '/var/clearos/base/backup' => array(),
    '/var/clearos/base/lock' => array(
        'mode' => '0775',
        'owner' => 'root',
        'group' => 'webconfig',
    ),
);
