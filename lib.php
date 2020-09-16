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
 * Serve question type files
 *
 * @package    qtype_
 * @subpackage selfassess
 * @copyright  2020 FernUniversität Hagen 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Checks file access for selfassess questions
 *
 * @param stdClass $course course settings object
 * @param object $cm course module object representing the activity
 * @param stdClass $context context object
 * @param string $filearea name of the file area
 * @param array $args the remaining bits of the file path
 * @param bool $forcedownload whether the user must be forced to download the file
 * @param array $options additional options affecting the file serving
 * 
 * @return bool
 */
function qtype_selfassess_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    question_pluginfile($course, $context, 'qtype_selfassess', $filearea, $args, $forcedownload, $options);
}
