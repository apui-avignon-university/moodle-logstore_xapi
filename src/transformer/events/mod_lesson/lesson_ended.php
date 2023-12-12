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
 * Transform for assignment graded event.
 *
 * @package   logstore_xapi
 * @copyright Jerret Fowler <jerrett.fowler@gmail.com>
 *            Ryan Smith <https://www.linkedin.com/in/ryan-smith-uk/>
 *            David Pesce <david.pesce@exputo.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\mod_lesson;

use src\transformer\utils as utils;

/**
 * Transformer for the assignment graded event.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $event The event to be transformed.
 * @return array
 */
function lesson_ended(array $config, \stdClass $event) {
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->userid);
    $course = $repo->read_record_by_id('course', $event->courseid);

    $lesson = $repo->read_record_by_id('lesson', $event->objectid);

    $lang = utils\get_course_lang($course);

    $gradeitems = $repo->read_record('grade_items', [
        'itemmodule' => 'lesson',
        'iteminstance' => $lesson->id
    ]);

    $scoreraw = (float) ($lesson->grade ?: 0);
    $scoremin = (float) ($gradeitems->grademin ?: 0);
    $scoremax = (float) ($gradeitems->grademax ?: 0);
    $scorepass = (float) ($gradeitems->gradepass ?: null);

    $success = false;

    if ($scoreraw >= $scorepass) {
        $success = true;
    }

    $statement = [
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'http://activitystrea.ms/schema/1.0/complete',
            'display' => [
                $lang => 'completed'
            ],
        ],
        'object' => utils\get_activity\course_module(
            $config,
            $course,
            $event->contextinstanceid,
            'http://adlnet.gov/expapi/activities/lesson'
        ),
        'result' => [
            'score' => [
                'raw' => $scoreraw
            ],
            'completion' => true,
            'success' => $success
        ],
        'timestamp' => utils\get_event_timestamp($event),
        'context' => [
            'platform' => $config['source_name'],
            'language' => $lang,
            'extensions' => utils\extensions\base($config, $event, $course),
            'contextActivities' => [
                'grouping' => [
                    utils\get_activity\site($config),
                    utils\get_activity\course($config, $course)
                ],
                'category' => [
                    utils\get_activity\source($config),
                ],
            ],
        ]
    ];


    // Only include min score if raw score is valid for that min.
    if ($scoreraw >= $scoremin) {
        $statement['result']['score']['min'] = $scoremin;
    }
    // Only include max score if raw score is valid for that max.
    if ($scoreraw <= $scoremax) {
        $statement['result']['score']['max'] = $scoremax;
    }
    // Calculate scaled score as the distance from zero towards the max (or min for negative scores).
    if ($scoreraw >= 0) {
        $statement['result']['score']['scaled'] = $scoreraw / $scoremax;
    }

    return [$statement];
}
