<?php

$app['basename'] = 'base';
$app['version'] = '5.9.9.0';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['summary'] = 'Base system and settings.'; // FIXME: translate

$app['description'] = 'Base system and settings ... blah blah.'; // FIXME: translate

$app['name'] = lang('base_dashboard');
$app['category'] = lang('base_category_system');
$app['subcategory'] = lang('base_subcategory_settings');

// Packaging
$app['core_dependencies'] = array(
    'clearos-base',
    'clearos-framework',
    'clearos-theme',
    'chkconfig',
    'coreutils',
    'file',
    'initscripts',
    'passwd',
    'rpm',
    'shadow-utils',
    'sudo',
    'sysvinit-tools',
    'webconfig-php',
    'webconfig-utils'
);
