<?php

/**
 * Base javascript helper.
 *
 * @category   apps
 * @package    base
 * @subpackage javascript
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////
// FIXME_THEME: review clearos_infobox calls.

header('Content-Type:application/x-javascript');
?>

var lang_go = '<?php echo lang('base_go'); ?>';
var lang_warning = '<?php echo lang('base_warning'); ?>';
var lang_no_results = '<?php echo lang('base_no_results'); ?>';

$(document).ready(function() {

    if ($("#clearos_username").length) 
        document.getElementsByName("clearos_username")[0].select();

    // Wizard next button handling
    //----------------------------

    $("#wizard_nav_next").click(function(){
        if ($(location).attr('href').match('.*\/change_password') != null) {
            if ($('#password_changed').length != 0)
                window.location = '/app/base/wizard/next_step';
            else
                $('form#change_password_form').submit();
        } else {
            window.location = '/app/base/wizard/next_step';
        }
    });

    if ($(location).attr('href').match('.*base\/search$') != null) {
        get_installed_apps();
        get_marketplace_apps();
        get_files();
    }
    if ($(location).attr('href').match('.*app\/base\\/\\?reloading$') != null) {
        $('#reload_bar').removeClass('theme-hidden');
        get_webconfig_reload(0);
    }
});

function get_webconfig_reload(counter) {
    counter += 1;
    clearos_set_progress_bar('webconfig_reload', counter, null);
    if (counter < 100)
        window.setTimeout(function() {get_webconfig_reload(counter)}, 250);
    else
        window.location = '/app/base';
}

function get_installed_apps() {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/base/search/get_installed_apps',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&search=' + $('#g_search').val(),
        success: function(data) {
            $('div#clearos-installed-apps div.clearos-loading-overlay').remove();
            $('div#content-installed-apps').removeClass('theme-search-empty');
            if (data.code != 0) {
                $('#content-installed-apps').html(clearos_infobox_warning(lang_warning, data.errmsg));
                return;
            }
            var t = get_table_clearos_installed_apps();
            t.fnClearTable();
            var options = new Object();
            options.buttons = 'extra-small';
            for (var key in data.list) {
                t.fnAddData([
                    data.list[key].name,
                    clearos_anchor('/app/' + data.list[key].basename, lang_go, options)
                ]);
            }
            if (data.list.length == 0)
                $('#clearos_installed_apps').find('td.dataTables_empty').html(lang_no_results);
            else
                t.fnDraw();
        },
        error: function(xhr, text, err) {
            $('div#clearos-installed-apps div.clearos-loading-overlay').remove();
            $('#content-installed-apps').html(clearos_infobox_warning(lang_warning, xhr.responseText.toString()));
        }
    });
}

// FIXME: fix marketplace / base mixing here (notably, clearos_marketplace_app_list should not be global)
function get_marketplace_apps() {
    search_string = $('#g_search').val();
    if (search_string == '')
        search_string = 'RESET_SEARCH';
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/marketplace/ajax/get_apps',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&max=0&realtime=0&search=' + search_string,
        success: function(data) {
            $('div#clearos-marketplace-apps div.clearos-loading-overlay').remove();
            $('div#content-marketplace-apps').removeClass('theme-search-empty');
            if (data.code != undefined && data.code != 0) {
                $('#content-installed-apps').html(clearos_infobox_warning(lang_warning, data.errmsg));
                return;
            }
            var options = new Object();
            options.container = 'content-marketplace-apps';
            options.search_only = true;
            options.columns = 2;
            clearos_marketplace_app_list('tile', data.list, 0, data.total, options);
            logo_list = [];
            $('.theme-placeholder').each(function( index ) {
                logo_list.push($(this).data('basename'));
            });
            get_app_logos(logo_list);
        },
        error: function(xhr, text, err) {
            $('div#clearos-marketplace-apps div.clearos-loading-overlay').remove();
            $('#content-marketplace-apps').html(clearos_infobox_warning(lang_warning, xhr.responseText.toString()));
        }
    });
}

function get_files() {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/base/search/get_files',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&search=' + $('#g_search').val(),
        success: function(data) {
            $('div#clearos-files div.clearos-loading-overlay').remove();
            $('div#content-files').removeClass('theme-search-empty');
            var t = get_table_clearos_files();
            t.fnClearTable();
            if (data.code != undefined && data.code != 0) {
                $('#content-files').html(clearos_infobox_warning(lang_warning, data.errmsg));
                t.fnAddData([
                    data.errmsg
                ]);
                t.fnDraw();
                return;
            }
            var options = new Object();
            for (index = 0; index < data.list.length; index++) {
                t.fnAddData([
                    data.list[index]
                ]);
            }
            if (data.list.length == 0)
                $('#clearos_files').find('td.dataTables_empty').html(lang_no_results);
            else
                t.fnDraw();
        },
        error: function(xhr, text, err) {
            $('div#clearos-marketplace-apps div.clearos-loading-overlay').remove();
            $('#content-marketplace-apps').html(clearos_infobox_warning(lang_warning, xhr.responseText.toString()));
        }
    });
}

// vim: ts=4 syntax=javascript
