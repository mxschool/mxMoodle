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
 * Provider for the mxStudent Dashboard Block

 * @package    block_mxschool_dash_student
 * @author     mxMoodle Development Team
 * @copyright  2022 Middlesex School, 1400 Lowell Rd, Concord MA 01742 All Rights Reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mxschool_dash_student\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\null_provider;

class provider implements null_provider {

    /**
     * Returns a string identifier from the component's language file to explain why the plugin doesn't store any user data.
     *
     * @return string Unlocalized string which explains why the plugin doesn't store any user data.
     */
    public static function get_reason() {
        return 'privacy:metadata';
    }

}
