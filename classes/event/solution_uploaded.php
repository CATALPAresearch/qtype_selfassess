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

namespace qtype_selfassess\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The qtype selfassess solution uploaded event class
 *
 * @property-read array $other {
 *      Extra information about event
 *      
 *      - array other['filelist'] list of uploaded file(s)
 *      - int other['retattempt'] id of the quiz specific question attempt
 *      - int other['cmid'] course module id
 * }
 *
 * @package    mod_quiz
 * @since      Moodle 3.5
 * @copyright  FernUniversität Hagen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class solution_uploaded extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['objecttable'] = 'quiz_attempts';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns description of what happened.
     * 
     * @return string
     */
    public function get_description() {
        $fileurls = "";
        foreach ($this->other['filelist'] as $files) {
            $fileurls .= $files;
        } 
        
        return "The user with id '$this->userid' has uploaded the following file(s) as solution for 
            question id '$this->objectid' . $fileurls";
    }
 
    /**
     * Returns localised general event name
     * 
     * @return string
     */
    public static function get_name() {
        return get_string('eventsolutionuploaded', 'qtype_selfassess');
    }
    
    /**
     * Returns relevant URL
     * 
     * @return \moodle_url
     */
    public function get_url() {
        // Students have not the permission to see server logs but only the own given solution.
        if (! empty($this->other['cmid'])) {
            return new \moodle_url('/mod/quiz/attempt.php', array('attempt' => $this->other['retattempt'], 'cmid' => $this->other['cmid']) );
        } else {
            return null;
        }
    } 
}
