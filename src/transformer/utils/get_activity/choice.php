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
 * Transformer utility for retrieving (course discussion) activities.
 *
 * @package   logstore_xapi
 * @copyright Jerret Fowler <jerrett.fowler@gmail.com>
 *            Ryan Smith <https://www.linkedin.com/in/ryan-smith-uk/>
 *            David Pesce <david.pesce@exputo.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\utils\get_activity;

use src\transformer\utils as utils;

/**
 * Transformer utility for retrieving (course discussion) activities.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $course The course object.
 * @param $contextinstanceid The choice id.
 * @param $choice The choice object.
 * @return array
 */
function choice(array $config, \stdClass $course, $contextinstanceid, $choice) {
    $courselang = utils\get_course_lang($course);
    $choiceurl = $config['app_url'].'/mod/choice/view.php?id='.$contextinstanceid;
    $choicename = property_exists($choice, 'name') ? $choice->name : 'Choice';

    return [
        'id' => $choiceurl,
        'definition' => [
            'type' => 'http://activitystrea.ms/schema/1.0/question',
            'name' => [
                $courselang => $choicename,
            ],
        ],
    ];
}
