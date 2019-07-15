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
 * Weekend checkin sheet for Middlesex School's Dorm and Student Functions Plugin.
 *
 * @package    local_mxschool
 * @subpackage checkin
 * @author     Jeremiah DeGreeff, Class of 2019 <jrdegreeff@mxschool.edu>
 * @author     Charles J McDonald, Academic Technology Specialist <cjmcdonald@mxschool.edu>
 * @copyright  2019, Middlesex School, 1400 Lowell Rd, Concord MA
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../locallib.php');
require_once(__DIR__.'/../classes/output/renderable.php');
require_once(__DIR__.'/../classes/mx_dropdown.php');
require_once(__DIR__.'/weekend_table.php');
require_once(__DIR__.'/weekend_comment_form.php');

require_login();
require_capability('local/mxschool:manage_weekend', context_system::instance());

$filter = new stdClass();
$filter->dorm = get_param_faculty_dorm(false);
$filter->weekend = get_param_current_weekend();
$filter->start = optional_param('start', '', PARAM_RAW);
$filter->end = optional_param('end', '', PARAM_RAW);
$filter->submitted = optional_param('submitted', '', PARAM_RAW);
$filter->search = optional_param('search', '', PARAM_RAW);
$action = optional_param('action', '', PARAM_RAW);
$id = optional_param('id', 0, PARAM_INT);

setup_mxschool_page('weekend_report', 'checkin');

$queryfields = array('local_mxschool_comment' => array('abbreviation' => 'c', 'fields' => array(
    'id', 'weekendid' => 'weekend', 'dormid' => 'dorm', 'comment'
)));

if ($action === 'delete' && $id) {
    $record = $DB->get_record('local_mxschool_weekend_form', array('id' => $id));
    $redirect = new moodle_url($PAGE->url, array(
        'dorm' => $filter->dorm, 'weekend' => $filter->weekend, 'submitted' => $filter->submitted, 'search' => $filter->search
    ));
    if ($record) {
        $record->active = 0;
        $DB->update_record('local_mxschool_weekend_form', $record);
        logged_redirect($redirect, get_string('checkin_weekend_form_delete_success', 'local_mxschool'), 'delete');
    } else {
        logged_redirect($redirect, get_string('checkin_weekend_form_delete_failure', 'local_mxschool'), 'delete', false);
    }
}
$data = get_record($queryfields, "c.weekendid = ? AND c.dormid = ?", array($filter->weekend, $filter->dorm));
if (!$data) {
    $data = new stdClass();
    $data->weekend = $filter->weekend;
    $data->dorm = $filter->dorm;
}

$weekendrecord = $DB->get_record('local_mxschool_weekend', array('id' => $filter->weekend));
$dorms = get_boarding_dorm_list();
$weekends = get_weekend_list();
$startdays = get_weekend_start_day_list();
$enddays = get_weekend_end_day_list();
$submittedoptions = array(
    '1' => get_string('checkin_weekend_report_select_submitted_true', 'local_mxschool'),
    '0' => get_string('checkin_weekend_report_select_submitted_false', 'local_mxschool')
);
$start = array_key_exists($filter->start, $startdays) ? $filter->start : $weekendrecord->start_offset;
$end = array_key_exists($filter->end, $enddays) ? $filter->end : $weekendrecord->end_offset;

$table = new weekend_table($filter, $start, $end);

$dropdowns = array(
    local_mxschool_dropdown::dorm_dropdown($filter->dorm, false),
    new local_mxschool_dropdown('weekend', $weekends, $filter->weekend),
    new local_mxschool_dropdown(
        'start', $startdays, $filter->start, get_string('checkin_weekend_report_select_start_day_default', 'local_mxschool')
    ),
    new local_mxschool_dropdown(
        'end', $enddays, $filter->end, get_string('checkin_weekend_report_select_end_day_default', 'local_mxschool')
    ),
    new local_mxschool_dropdown(
        'submitted', $submittedoptions, $filter->submitted, get_string('report_select_default', 'local_mxschool')
    )
);
$addbutton = new stdClass();
$addbutton->text = get_string('checkin_weekend_report_add', 'local_mxschool');
$addbutton->url = new moodle_url('/local/mxschool/checkin/weekend_enter.php');
$headers = array(array('text' => '', 'length' => $filter->dorm ? 3 : 4));
$sunday = generate_datetime('Sunday this week');
for ($i = $start; $i <= $end; $i++) {
    $day = clone $sunday;
    $day->modify("{$i} days");
    $headers[] = array('text' => $day->format('l'), 'length' => 2);
}
$headers[] = array('text' => '', 'length' => 9);

$form = new weekend_comment_form(array('id' => $id));
$form->set_redirect(new moodle_url($PAGE->url, array(
    'dorm' => $filter->dorm, 'weekend' => $filter->weekend, 'start' => $filter->start, 'end' => $filter->end,
    'submitted' => $filter->submitted, 'search' => $filter->search
)), true);
$form->set_data($data);

if ($form->is_cancelled()) {
    redirect($form->get_redirect());
} else if ($data = $form->get_data()) {
    update_record($queryfields, $data);
    logged_redirect(
        $form->get_redirect(), get_string('checkin_weekend_comment_form_success', 'local_mxschool'), $data->id ? 'update' : 'create'
    );
}

$output = $PAGE->get_renderer('local_mxschool');
$reportrenderable = new \local_mxschool\output\report($table, $filter->search, $dropdowns, true, $addbutton, false, $headers);
$formrenderable = new \local_mxschool\output\form($form);

echo $output->header();
echo $output->heading(get_string('checkin_weekend_report_title', 'local_mxschool', array(
    'dorm' => $filter->dorm ? "{$dorms[$filter->dorm]} " : '', 'weekend' => $weekends[$filter->weekend],
    'type' => $weekendrecord->type
)));
echo $output->render($reportrenderable);
echo $output->render($formrenderable);
echo $output->footer();
