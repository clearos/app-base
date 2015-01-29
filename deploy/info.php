<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'base';
$app['version'] = '1.6.11';
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
// Controller info
/////////////////////////////////////////////////////////////////////////////

// Wizard extras
$app['controllers']['session']['wizard_name'] = lang('base_change_password');
$app['controllers']['session']['wizard_description'] = lang('base_change_password_description');
$app['controllers']['session']['inline_help'] = array(
    lang('base_change_password') => lang('base_change_password_help'),
);

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'acpid',
    'clearos-base',
    'clearos-framework >= 6.5.8',
    'clearos-release',
    'cpupowerutils',
    'csplugin-filewatch',
    'theme-default >= 6.5.8',
    'chkconfig',
    'coreutils',
    'file',
    'initscripts',
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
    'webconfig-php',
    'webconfig-php-gd',
    'webconfig-php-ldap',
    'webconfig-php-mbstring',
    'webconfig-php-mysql',
    'webconfig-php-process',
    'webconfig-php-xml',
    'webconfig-utils',
    'wget',
    'yum-utils'
);


$app['core_file_manifest'] = array(
    'authenticated.acl' => array('target' => '/var/clearos/base/access_control/authenticated/base'),
    'public.acl' => array('target' => '/var/clearos/base/access_control/public/base'),
    'rest.acl' => array('target' => '/var/clearos/base/access_control/rest/base'),
    'filewatch-base-install.conf'=> array('target' => '/etc/clearsync.d/filewatch-base-install.conf'),
    'filewatch-base-ulimit.conf'=> array('target' => '/etc/clearsync.d/filewatch-base-ulimit.conf'),
    'filewatch-base-webconfig.conf'=> array('target' => '/etc/clearsync.d/filewatch-base-webconfig.conf'),
    'filewatch-base-clearsync.conf'=> array('target' => '/etc/clearsync.d/filewatch-base-clearsync.conf'),
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
    'yum-install' => array(
        'target' => '/usr/sbin/yum-install',
        'mode' => '0755',
    ),
    'webconfig-service' => array(
        'target' => '/usr/sbin/webconfig-service',
        'mode' => '0755',
    ),
    'RPM-GPG-KEY-CentOS-6' => array( 'target' => '/etc/pki/rpm-gpg/CLEAROS-RPM-GPG-KEY-CentOS-6' ),
    'RPM-GPG-KEY-EPEL-6' => array( 'target' => '/etc/pki/rpm-gpg/CLEAROS-RPM-GPG-KEY-EPEL-6' ),
    'RPM-GPG-KEY-EPEL-7' => array( 'target' => '/etc/pki/rpm-gpg/CLEAROS-RPM-GPG-KEY-EPEL-7' ),
    'RPM-GPG-KEY-atrpms' => array( 'target' => '/etc/pki/rpm-gpg/CLEAROS-RPM-GPG-KEY-atrpms' ),
    'centos-scl.repo' => array( 'target' => '/etc/yum.repos.d/centos-scl.repo' ),
    'clearos-epel.repo' => array( 'target' => '/etc/yum.repos.d/clearos-epel.repo' ),
    'clearos-developer.repo' => array( 'target' => '/etc/yum.repos.d/clearos-developer.repo' ),
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
    '/var/clearos/base/lock' => array(
        'mode' => '0775',
        'owner' => 'root',
        'group' => 'webconfig',
    ),
);
