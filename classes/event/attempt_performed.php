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
 * The qtype selfassess attempt performed event class
 *
 * @property-read array $other {
 *      Extra information about event
 *      
 *      - array other['result'] answer id, correct answer, given answer
 *      - int other['retry'] number of current attempt
 *      - int other['actgrade'] amount of actual grade
 *      - int other['retattempt'] id of the quiz specific question attempt
 *      - int other['cmid'] course module id

 * }
 *
 * @package    mod_quiz
 * @since      Moodle 3.5
 * @copyright  FernUniversität Hagen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt_performed extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {       
        $this->data['objecttable'] = 'quiz_attempts';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns description of what happened
     * 
     * @return string
     */
    public function get_description() {
        $resultAsString = "";
        foreach ($this->other['result'] as $ans) {
            $resultAsString = $resultAsString . "<br>answer id: " . $ans['number'] . ", correct " . $ans['correct'] . ", selected " . $ans['chosen'] . "; ";
         } 
        
        return "The user with id '$this->userid' has performed attempt no. {$this->other['retry']} for 
            question id '$this->objectid' achieving grade: {$this->other['actgrade']}: " . $resultAsString;
    }
 
    /**
     * Returns localised general event name
     * 
     * @return string
     */
    public static function get_name() {
        return get_string('eventquestionattemptperformed', 'qtype_selfassess');
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
