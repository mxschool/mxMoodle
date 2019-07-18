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
 * Middlesex's Student Deans Menu Block for the Dashboard.
 *
 * @package    block_mxschool_dash_student_deans
 * @author     Jeremiah DeGreeff, Class of 2019 <jrdegreeff@mxschool.edu>
 * @author     Charles J McDonald, Academic Technology Specialist <cjmcdonald@mxschool.edu>
 * @copyright  2019, Middlesex School, 1400 Lowell Rd, Concord MA
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/../../local/mxschool/classes/output/renderable.php');
require_once(__DIR__.'/../../local/mxschool/locallib.php');

class block_mxschool_dash_student_deans extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_mxschool_dash_student_deans');
    }

    public function get_content() {
        global $PAGE, $USER;
        if (isset($this->content)) {
            return $this->content;
        }

        $output = $PAGE->get_renderer('local_mxschool');
        $links = array();
        if (has_capability('moodle/site:config', context_system::instance())
            || (user_is_student() && student_may_access_advisor_selection($USER->id))) {
            $links[get_string('advisor_selection', 'block_mxschool_dash_student_deans')]
            = '/local/mxschool/advisor_selection/advisor_enter.php';
        }
        if (has_capability('moodle/site:config', context_system::instance())
            || (user_is_student() && student_may_access_rooming($USER->id))) {
            $links[get_string('rooming', 'block_mxschool_dash_student_deans')]
            = '/local/mxschool/rooming/rooming_enter.php';
        }
        if (has_capability('moodle/site:config', context_system::instance())
            || (user_is_student() && student_may_access_vacation_travel($USER->id))) {
            $links[get_string('vacation', 'block_mxschool_dash_student_deans')]
            = '/local/mxschool/vacation_travel/vacation_enter.php';
        }
        $renderable = new \local_mxschool\output\index($links);

        $this->content = new stdClass();
        if (count($links)) {
            $this->content->text = get_config('block_mxschool_dash_student_deans', 'description').$output->render($renderable);
        }
        return $this->content;
    }

    public function specialization() {
        $this->title = get_string('blockname', 'block_mxschool_dash_student_deans');
    }

    public function has_config() {
        return true;
    }
}
