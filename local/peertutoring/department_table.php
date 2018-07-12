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
 * Department table for Middlesex School's Peer Tutoring Subplugin.
 *
 * @package    local_peertutoring
 * @author     Jeremiah DeGreeff, Class of 2019 <jrdegreeff@mxschool.edu>
 * @author     Charles J McDonald, Academic Technology Specialist <cjmcdonald@mxschool.edu>
 * @copyright  2018, Middlesex School, 1400 Lowell Rd, Concord MA
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/../mxschool/classes/mx_table.php');

class department_table extends local_mxschool_table {

    /**
     * Creates a new department_table.
     */
    public function __construct() {
        $columns = array('name');
        $headers = array();
        foreach ($columns as $column) {
            $headers[] = get_string("department_report_header_{$column}", 'local_peertutoring');
        }
        $columns[] = 'actions';
        $headers[] = get_string('report_header_actions', 'local_mxschool');
        $fields = array('d.id', 'd.name');
        $from = array('{local_peertutoring_dept} d');
        $where = array('d.deleted = 0');
        $sortable = array('name');
        $urlparams = array();
        $centered = array('name');
        parent::__construct(
            'department_table', $columns, $headers, $sortable, 'name', $fields, $from, $where, $urlparams, $centered
        );
    }

    /**
     * Formats the actions column.
     */
    protected function col_actions($values) {
        return $this->edit_icon('/local/peertutoring/department_edit.php', $values->id)
               .$this->delete_icon($values->id, 'department');
    }

}