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
 * Healthtest Audit Report
 *
 * @package     local_mxschool
 * @subpackage  healthtest
 * @author      mxMoodle Development Team
 * @copyright   2021 Middlesex School, 1400 Lowell Rd, Concord MA 01742 All Rights Reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mxschool\local\healthtest;

defined('MOODLE_INTERNAL') || die();

/**
 * Creates a new table.
 *
 * @param stdClass $filter Any filtering for the table.
 * @param string download, indicates if the table is downloading
 */

class audit_table extends \local_mxschool\table {
    public function __construct($filter, $download) {
        $this->is_downloading($download);
        // Define the names of the columns. Should match up with the $fields array.
        $columns = array('name', 'testing_cycle');
        // Get headers from language file.
        $headers = $this->generate_headers($columns, 'healthtest:audit_report');
        // Define sortable columns.
        $sortable = array('name', 'testing_cycle');
        // All columns are centered.
        $centered = array('name', 'testing_cycle');
        parent::__construct('healthtest_audit_table', $columns, $headers, $sortable, $centered, $filter, false);
        // The fields to query from the database.
        $fields = array('a.id', 'a.userid', 'u.firstname', 'u.alternatename', 'u.lastname', 'u.lastname AS name');
        // The tables which to query.
        $from = array('{local_mxschool_audit} a', '{user} u ON u.id = a.userid');
        // Filter active users.
        $where = array('u.deleted = 0');
        $searchable = array('u.firstname', 'u.lastname', 'u.alternatename');
        $this->define_sql($fields, $from, $where, $searchable, $filter->search);
    }

    // The following functions edit what is displayed in individual columns.
    protected function col_name($values) {
        if ($values->alternatename) {
            return "{$values->lastname}, {$values->firstname} ({$values->alternatename})";
        } else {
            return "{$values->lastname}, {$values->firstname}";
        }
    }

    protected function col_testing_cycle($values) {
        $testing_cycles = local_mx_get_testing_cycles($values->userid);
        return "{$testing_cycles}";
    }
}
