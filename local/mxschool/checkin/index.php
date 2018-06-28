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
 * Checkin sheets index page for Middlesex School's Dorm and Student functions plugin.
 *
 * @package    local_mxschool
 * @author     Jeremiah DeGreeff, Class of 2019 <jrdegreeff@mxschool.edu>
 * @author     Charles J McDonald, Academic Technology Specialist <cjmcdonald@mxschool.edu>
 * @copyright  2018, Middlesex School, 1400 Lowell Rd, Concord MA
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__.'/../classes/output/renderable.php');

admin_externalpage_setup('checkin_index');

$url = '/local/mxschool/checkin/index.php';
$title = get_string('checkin', 'local_mxschool');

$PAGE->set_url(new moodle_url($url));
$PAGE->set_context(context_system::instance());
$PAGE->set_title($title);
$PAGE->set_heading($title);

$output = $PAGE->get_renderer('local_mxschool');
$renderable = new \local_mxschool\output\index_page(array(
    get_string('checkin_preferences', 'local_mxschool') => '/local/mxschool/checkin/preferences.php',
    get_string('generic_report', 'local_mxschool') => '/local/mxschool/checkin/generic_report.php',
    get_string('weekday_report', 'local_mxschool') => '/local/mxschool/checkin/weekday_report.php',
    get_string('weekend_form', 'local_mxschool') => '/local/mxschool/checkin/weekend_enter.php',
    get_string('weekend_report', 'local_mxschool') => '/local/mxschool/checkin/weekend_report.php',
    get_string('weekend_calculator', 'local_mxschool') => '/local/mxschool/checkin/weekend_calculator.php'
));

echo $output->header();
echo $output->heading($title);
echo $output->render($renderable);
echo $output->footer();
