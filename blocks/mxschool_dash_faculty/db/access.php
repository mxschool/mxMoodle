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
 * Capabilites for mxFaculty Dashboard Block
 *
 * @package    block_mxschool_dash_faculty
 * @author     mxMoodle Development Team
 * @copyright  2022 Middlesex School, 1400 Lowell Rd, Concord MA 01742 All Rights Reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'block/mxschool_dash_faculty:addinstance' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM
    ), 'block/mxschool_dash_faculty:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM
    ), 'block/mxschool_dash_faculty:access' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM
    )
);
