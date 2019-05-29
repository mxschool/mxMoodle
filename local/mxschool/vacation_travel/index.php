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
 * Vacation travel index page for Middlesex School's Dorm and Student functions plugin.
 *
 * @package    local_mxschool
 * @subpackage vacation_travel
 * @author     Jeremiah DeGreeff, Class of 2019 <jrdegreeff@mxschool.edu>
 * @author     Charles J McDonald, Academic Technology Specialist <cjmcdonald@mxschool.edu>
 * @copyright  2019, Middlesex School, 1400 Lowell Rd, Concord MA
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__.'/../locallib.php');
require_once(__DIR__.'/../classes/output/renderable.php');

if (!has_capability('moodle/site:config', context_system::instance())) {
    redirect(new moodle_url('/my'));
}

admin_externalpage_setup('vacation_travel_index');

$url = '/local/mxschool/vacation_travel/index.php';
$title = get_string('vacation_travel', 'local_mxschool');

setup_generic_page($url, $title);

$output = $PAGE->get_renderer('local_mxschool');
$renderable = new \local_mxschool\output\index(array(
    get_string('vacation_travel_preferences', 'local_mxschool') => '/local/mxschool/vacation_travel/preferences.php',
    get_string('vacation_travel_form', 'local_mxschool') => '/local/mxschool/vacation_travel/vacation_enter.php',
    get_string('vacation_travel_report', 'local_mxschool') => '/local/mxschool/vacation_travel/vacation_report.php',
    get_string('vacation_travel_transportation_report', 'local_mxschool') =>
    '/local/mxschool/vacation_travel/transportation_report.php'
));

echo $output->header();
echo $output->heading($title);
echo $output->render($renderable);
echo $output->footer();
