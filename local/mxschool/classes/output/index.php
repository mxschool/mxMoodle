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
 * Renderable class for indexes for Middlesex's Dorm and Student Functions Plugin.
 *
 * @package     local_mxschool
 * @author      Jeremiah DeGreeff, Class of 2019 <jrdegreeff@mxschool.edu>
 * @author      Charles J McDonald, Academic Technology Specialist <cjmcdonald@mxschool.edu>
 * @copyright   2019 Middlesex School, 1400 Lowell Rd, Concord MA 01742 All Rights Reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mxschool\output;

defined('MOODLE_INTERNAL') || die();

class index implements \renderable, \templatable {

    /** @var array Associative array of links [displaytext => url] to be rendered.*/
    private $links;
    /** @var string|bool String to display as a subheading or false.*/
    private $heading;

    /**
     * @param array $links Associative array of links [displaytext => url] to be rendered.
     * @param string|bool $heading String to display as a subheading or false.
     */
    public function __construct($links, $heading = false) {
        $this->links = $links;
        $this->heading = $heading;
    }

    /**
     * Exports this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output The renderer which is rendering this renderable.
     * @return stdClass Object with property links which is an array of stdClass with properties text and url.
     */
    public function export_for_template(renderer_base $output) {
        $data = new \stdClass();
        $data->heading = $this->heading;
        $data->links = array_map(function($text, $url) {
            return array('text' => $text, 'url' => (new \moodle_url($url))->out());
        }, array_keys($this->links), $this->links);
        return $data;
    }

}
