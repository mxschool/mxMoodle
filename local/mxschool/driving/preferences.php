<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * eSignout preferences page for Middlesex School's Dorm and Student functions plugin.
 *
 * @package    local_mxschool
 * @author     Jeremiah DeGreeff, Class of 2019 <jrdegreeff@mxschool.edu>
 * @author     Charles J McDonald, Academic Technology Specialist <cjmcdonald@mxschool.edu>
 * @copyright  2018, Middlesex School, 1400 Lowell Rd, Concord MA
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../locallib.php');
require_once(__DIR__.'/../classes/output/renderable.php');
require_once('preferences_form.php');

require_login();
require_capability('local/mxschool:manage_esignout_preferences', context_system::instance());

$parents = array(
    get_string('pluginname', 'local_mxschool') => '/local/mxschool/index.php',
    get_string('driving', 'local_mxschool') => '/local/mxschool/driving/index.php'
);
$redirect = new moodle_url($parents[array_keys($parents)[count($parents) - 1]]);
$url = '/local/mxschool/driving/preferences.php';
$title = get_string('esignout_preferences', 'local_mxschool');

setup_mxschool_page($url, $title, $parents);

$data = new stdClass();
$data->editwindow = get_config('local_mxschool', 'esignout_edit_window');
$notification = $DB->get_record('local_mxschool_notification', array('class' => 'esignout_submitted'));
if ($notification) {
    $data->subject = $notification->subject;
    $data->body['text'] = $notification->body_html;
}
$data->nopassengers['text'] = get_config('local_mxschool', 'esignout_form_warning_nopassengers');
$data->needparent['text'] = get_config('local_mxschool', 'esignout_form_warning_needparent');
$data->onlyspecific['text'] = get_config('local_mxschool', 'esignout_form_warning_onlyspecific');
$data->confirmation['text'] = get_config('local_mxschool', 'esignout_form_confirmation');
$data->driver['text'] = get_config('local_mxschool', 'esignout_notification_warning_driver');
$data->any['text'] = get_config('local_mxschool', 'esignout_notification_warning_any');
$data->parent['text'] = get_config('local_mxschool', 'esignout_notification_warning_parent');
$data->specific['text'] = get_config('local_mxschool', 'esignout_notification_warning_specific');
$data->over21['text'] = get_config('local_mxschool', 'esignout_notification_warning_over21');

$form = new preferences_form();
$form->set_redirect($redirect);
$form->set_data($data);

if ($form->is_cancelled()) {
    redirect($form->get_redirect());
} else if ($data = $form->get_data()) {
    set_config('esignout_edit_window', $data->editwindow, 'local_mxschool');
    update_notification('esignout_submitted', $data->subject, $data->body);
    set_config('esignout_form_warning_nopassengers', $data->nopassengers['text'], 'local_mxschool');
    set_config('esignout_form_warning_needparent', $data->needparent['text'], 'local_mxschool');
    set_config('esignout_form_warning_onlyspecific', $data->onlyspecific['text'], 'local_mxschool');
    set_config('esignout_form_confirmation', $data->confirmation['text'], 'local_mxschool');
    set_config('esignout_notification_warning_driver', $data->driver['text'], 'local_mxschool');
    set_config('esignout_notification_warning_any', $data->any['text'], 'local_mxschool');
    set_config('esignout_notification_warning_parent', $data->parent['text'], 'local_mxschool');
    set_config('esignout_notification_warning_specific', $data->specific['text'], 'local_mxschool');
    set_config('esignout_notification_warning_over21', $data->over21['text'], 'local_mxschool');
    redirect(
        $form->get_redirect(), get_string('esignout_preferences_edit_success', 'local_mxschool'), null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$output = $PAGE->get_renderer('local_mxschool');
$renderable = new \local_mxschool\output\form($form);

echo $output->header();
echo $output->heading($title);
echo $output->render($renderable);
echo $output->footer();
