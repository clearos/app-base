<?php

/**
 * Software install ajax helper.
 *
 * @category   apps
 * @package    base
 * @subpackage javascript
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2013 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/base/
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

clearos_load_language('marketplace');
clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type: application/x-javascript');
?>

///////////////////////////////////////////////////////////////////////////
// M A I N
///////////////////////////////////////////////////////////////////////////

$(document).ready(function() {
    lang_installation_complete = '<?php echo lang("base_installation_complete"); ?>';

    get_progress();
});

function get_progress() {
    $.ajax({
        url: '/app/base/install/progress',
        method: 'GET',
        dataType: 'json',
        success : function(json) {
            if (json.busy && !json.wc_busy) {
                $("#software_install_busy").show();
                $("#software_install_running").hide();
            } else {
                $("#software_install_busy").hide();
                $("#software_install_running").show();
            }

            $('#progress').animate_progressbar(parseInt(json.progress));
            $('#overall').animate_progressbar(parseInt(json.overall));

            if (json.code === 0) {
                $('#details').html(json.details);
            } else if (json.code === -999) {
                // Do nothing...no data yet
            } else {
                // Uh oh...something bad happened
                $('#progress').progressbar({value: 0});
                $('#overall').progressbar({value: 0});
                $('#details').html(json.errmsg);
            }

            if (json.overall == 100) {
                if ($('#theme_wizard_nav_next').length == 0) {
                    $('#reload_button').show();
                    $('#progress').progressbar({value: 100});
                    $('#overall').progressbar({value: 100});
                    $('#details').html(lang_installation_complete);
                }
                window.setTimeout(get_progress, 2000);
                // FIXME return;
            } else {
                window.setTimeout(get_progress, 2000);
            }

        },
        error: function(xhr, text, err) {
            window.setTimeout(get_progress, 1000);
        }
    });
}

// vim: syntax=javascript ts=4
