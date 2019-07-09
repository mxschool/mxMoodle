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
 * Faculty edit page for Middlesex School's Dorm and Student Functions Plugin.
 *
 * @package    local_mxschool
 * @subpackage user_management
 * @author     Jeremiah DeGreeff, Class of 2019 <jrdegreeff@mxschool.edu>
 * @author     Charles J McDonald, Academic Technology Specialist <cjmcdonald@mxschool.edu>
 * @copyright  2019, Middlesex School, 1400 Lowell Rd, Concord MA
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../locallib.php');
require_once(__DIR__.'/../classes/output/renderable.php');
require_once(__DIR__.'/faculty_edit_form.php');

require_login();
require_capability('local/mxschool:manage_faculty', context_system::instance());

$id = optional_param('id', 0, PARAM_INT);

setup_edit_page('faculty_edit', 'faculty_report', 'user_management');
$redirect = get_redirect();

$queryfields = array('local_mxschool_faculty' => array('abbreviation' => 'f', 'fields' => array(
    'id', 'dormid' => 'dorm', 'faculty_code' => 'facultycode', 'may_approve_signout' => 'approvesignout',
    'advisory_available' => 'advisoryavailable', 'advisory_closing' => 'advisoryclosing'
)), 'user' => array('abbreviation' => 'u', 'join' => 'f.userid = u.id', 'fields' => array(
    'id' => 'userid', 'firstname', 'middlename', 'lastname', 'alternatename', 'email'
)));

if (!$DB->record_exists('local_mxschool_faculty', array('id' => $id))) {
    redirect($redirect);
}

$data = get_record($queryfields, "f.id = ?", array($id));
$dorms = array(null => '') + get_dorm_list();

$form = new faculty_edit_form(array('id' => $id, 'dorms' => $dorms));
$form->set_redirect($redirect);
$form->set_data($data);

if ($form->is_cancelled()) {
    redirect($form->get_redirect());
} else if ($data = $form->get_data()) {
    update_record($queryfields, $data);
    logged_redirect($form->get_redirect(), get_string('user_management_faculty_edit_success', 'local_mxschool'), 'update');
}

$output = $PAGE->get_renderer('local_mxschool');
$renderable = new \local_mxschool\output\form($form);

echo $output->header();
echo $output->heading($PAGE->title);
echo $output->render($renderable);
echo $output->footer();
