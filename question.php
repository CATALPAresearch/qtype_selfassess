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

// to be needed???
/*
use Box\Spout\Common\Type;
use mod_quiz\event\attempt_abandoned;
use core\event\note_created;
*/

/**
 * selfassess multiple choice question definition class
 *
 * @package    qtype_
 * @subpackage selfassess
 * @copyright  2020 FernUniversität Hagen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/type/multichoice/question.php');
require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/engine/questionattempt.php');

/**
 * Represents an selfassess question class extending multichoice
 */
class qtype_selfassess_question extends qtype_multichoice_multi_question {  //question_with_responses { -> grading
   
    /** @var int number of attachments allowed */
    public $attachments;
    /** @var int number of attachments required for a response to be complete */
    public $attachmentsrequired;
    /** @var string array of file types accepted upon file submission */
    public $filetypeslist;
    public $graderinfo;
    public $graderinfoformat;
    /** @var string decision tree upon which is decided what specialfeedbacktext is provided */
    public $specialfeedbackrule;
    /** @var string text provided according to the chosen choices */
    public $specialfeedbacktext;
    /** @var string answer tree containing the statements for the answers and their grading */
    public $answer;
    /**  @var string instruction text what the student is intended to do */
    public $instruction;
    /**  @var string instruction linktext text to the link */
    public $instructionlinktext;
    /**  @var string instruction linkref href to the link */
    public $instructionlinkref;
    /**  @var string solution text with a sample solution */
    public $solution;
 
    
    /**
     * Get the selfassess page renderer.
     * 
     * @param moodle_page $page
     * 
     * @return moodle_page $page renderer for selfassess 
     */
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_selfassess');
    }
    
    
    /**
      * Set the preferred behaviour for this question type.
      * 
      * @param question_attempt $qa question attempt
      * @param string $preferredbehaviour requested type of behaviour
      * 
      * @return question_behaviour
      */
     public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        return question_engine::make_behaviour('deferredfeedback', $qa, $preferredbehaviour);
    } 
    
        
    /**
     * Get the expected data for this question type.
     * 
     * @return array $expecteddata expected data
     * @see qtype_multichoice_multi_question::get_expected_data()
     */
    public function get_expected_data() {
        $expecteddata = array();
        // When activated only attachments are recognised and no checkboxes.
        $expecteddata = array('answer' => PARAM_RAW);
        $expecteddata['answerformat'] = PARAM_ALPHANUMEXT;
        if ($this->attachments != 0) {
            $expecteddata['attachments'] = question_attempt::PARAM_FILES;
        }
        // Checkbox and checkbox values are present.
        foreach ($this->order as $key => $notused) {            
            $expecteddata[$this->field($key)] = PARAM_BOOL;
        }
        
        return $expecteddata;
    }
    

    /**
     * Avoid the showing of the correct responses of the question.
     * 
     * @return void
     * @see qtype_multichoice_multi_question::get_correct_response()
     */
    public function get_correct_response() {
        return null;
    } 
    
    
    /**
     * Override is_same_response of qtype_multichoice_multi_question,
     * allways show answer-specific feedback when pressing the 'check' button 
     * also when nothing is selected.
     *
     * @param   array $prevresponse responses, as formerly chosen
     *          array $newresponse responses, as returned by
     *
     * @return false (this plugin does not allow zero attachments selected) 
     */
    public function is_same_response(array $prevresponse, array $newresponse) {
         return false;
    }
    
    
    /**
     * Check whether a retry of a response is done
     * 
     * @param array $prevresponse response given
     * @param array $newresponse new response is given
     * 
     * @return boolean
     */
    public function is_retry_response(array $prevresponse, array $newresponse) {
        if (! $this->is_complete_response($prevresponse) || ! $this->is_complete_response($newresponse) ) {
            return false;
        }
        foreach ($this->order as $key => $notused) {
            $fieldname = $this->field($key);
            if (!question_utils::arrays_same_at_key_integer($prevresponse, $newresponse, $fieldname)) {
                return false;
            }
        }
        return true;
    }
    
   
    /**
     * Override is_complete_response of qtype_multichoice_multi_question,
     * influences the appearance of a "Check"-button.
     *
     * @param array $response responses as returned by
     *
     * @return true (this plugin allows zero answers selected)
     */
    public function is_complete_response(array $response) {
        foreach ($this->order as $key => $notused) {
            if (!empty($response[$this->field($key)])) {
                return true;
            }
        }
        return false;
    }
    
    
    
    /** Override multichoice correct_response()
     * Check whether the question is answered completey correct.
     * 
     * @param array $response response given
     * 
     * @return boolean
     */
    public function is_correct_response(array $response) {
        $grade = 0.0;
        
        $choices = parent::classify_response($response);
        
        if (!array_key_exists('choice0', $response)) {            
             return false;
        }
        
        foreach ($choices as $choice) {
            $grade += $choice->fraction;
        }
        
        // Problem with older PHP versions: never compare floats directly.
        // See also: www.php.net/manual/en/function.bccomp.php
        /*if (bccomp($grade, 1.00, 2) == 0) {
            return true;
        }*/

        if($grade == 1.0) return true;      
        return false;
    }

 
    /**
     * Determine whether the response is gradable or not.
     * 
     * @param array $response given response 
     * @see qtype_multichoice_multi_question::is_gradable_response()
     * 
     * return bool false or true
     */
    public function is_gradable_response(array $response) {
        // Determine if the given response has attachments.
        if (array_key_exists('attachments', $response)
            && $response['attachments'] instanceof question_response_files) {
                 return true; 
            } else if (array_key_exists('choice0', $response)) {
                return true; 
            } else {
                return false;
            }
    }
    
    
    /**
     * Indicate whether an error occurred or not.
     *
     * @param array $response given response
     * @see qtype_multichoice_multi_question::get_validation_error()
     *
     * @return string empty or not
     */
    
    public function get_validation_error(array $response) {       
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('uploadyoursolutionfirst', 'qtype_selfassess');
    }
    
    
    /**
     * Check whether a file access happens. 
     * 
     * @param question_attempt $qa question attempt
     * @param question_display_options $options options that control display of the question
     * @param string $component name of the component we are serving files for
     * @param string $filearea name of the file area
     * @param array $args remaining bits of the file path
     * @param bool $forcedownload whether the user must be forced to download the file
     * 
     * @return bool true if the user can access this file
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'response_attachments') {
            // Response attachments visible if the question have them.
            return $this->attachments != 0;
            
        } else if ($component == 'qtype_selfassess' && $filearea == 'graderinfo') {
            return $options->manualcomment && $args[0] == $this->id;
            
        } else {
            return parent::check_file_access($qa, $options, $component,
                $filearea, $args, $forcedownload);
        }
    }
    
   
    /**
     * Create the answer for questiontype selfassess.
     * 
     * @param array $answer
     * 
     * @return array $answer
     */
    public function make_answer($answer) {
        // Overridden just so we can make it public for use by question.php.
        return parent::make_answer($answer);
    }
 
    /**
     * Create the result to be displayed in the log.
     * 
     * @param array $response given responses
     *      {@link question_attempt_step::get_qt_data()}
     *      
     * @return array $res
     */
    public function get_result(array $response) {
        $res = array();
        $cr_index = 0;
                
        foreach ($this->order as $key => $ans){
            $fieldname = $this->field($key);
             if (question_state::graded_state_for_fraction(
                $this->answers[$ans]->fraction)->is_incorrect()) {
                   $res[$cr_index] = array();
                    $res[$cr_index]['number'] = $ans;
                    $res[$cr_index]['correct'] = 0;
                    if (array_key_exists($fieldname, $response) && $response[$fieldname]) {
                        $res[$cr_index]['chosen'] = 1;
                    } else {
                        $res[$cr_index]['chosen'] = 0;
                    }
                    $cr_index++;
                } else {
                    $res[$cr_index] = array();
                    $res[$cr_index]['number'] = $ans;
                    $res[$cr_index]['correct'] = 1;
                    if (array_key_exists($fieldname, $response) && $response[$fieldname]) {
                        $res[$cr_index]['chosen'] = 1;
                    } else {
                        $res[$cr_index]['chosen'] = 0;
                    }
                    $cr_index++;
                }
        }
        return $res;
    }
    
   
    /**
     * Override create specific feedback of qtype_multichoice_multi_question.
     *
     * it is intended not to provide a static specific feedback
     * but a dynamic answer-specific feedback
     *
     * @param array $response responses, as returned by
     *      {@link question_attempt_step::get_qt_data()}
     *
     * @return array $specfeedback to be displayed as specific feedback
     *      for an attempt
     */
    public function create_specific_feedback(array $response) {
        $answerbitmap = "";
        
        // Choices selected by the student.
        foreach ($this->order as $key => $notused) {
            $fieldname = $this->field($key);
            if (array_key_exists($fieldname, $response) && $response[$fieldname]) {
                $answerbitmap .= "1";
            } else {
                $answerbitmap .= "0";
            }
        }
              
        return $this-> prepare_specific_feedback($answerbitmap);
    }
    
    /**
     * Prepare the final feedback text displayed to the student.
     * 
     * @param string $answerbitmap 
     * 
     * @return string finaltext feedback text displayed to the student
     */
    private function prepare_specific_feedback($answerbitmap) {
        $decisionxml = simplexml_load_string($this->specialfeedbackrule);
        $feedbackxml = simplexml_load_string($this->specialfeedbacktext);
        
        // Prepare array of rules.
        $match2text = array();
        for ($i = 0; $i < $decisionxml->rule->count(); $i++) {
            $match = "";
            $fbid = "";
            foreach($decisionxml->rule[$i]->attributes() as $att => $val) {
                if ($att == "clicked") {
                    $match = (string) $val;
                } else if ($att == "fbid") {
                    $fbid = (string) $val;
                }
            }           
                       
            // Calculate position of this rule.
            // Append an "x" to ensure PHP handles the variable as string.
            $lastZero = strrchr($match, "0") . "x";
            $lastOne = strrchr($match, "1") . "x";
            
            if ($lastZero == "x" && $lastOne == "x") {
                $pos = strlen($match) - 1;
            } else if ($lastZero == "x") {
                $pos = strlen($match) - strlen($lastOne) + 1;
            } else if ($lastOne == "x") {
                $pos = strlen($match) - strlen($lastZero) + 1;
            } else {
                $pos = max((strlen($match) - strlen($lastOne)), (strlen($match) - strlen($lastZero))) + 1;
            }
             
            $match2text[$i] = array('fbid' => $fbid, 'match' => $match, 'pos' => $pos);
        }
       
        // Prepare array of feedbacks for chosen answers.
        $feedbacklist = array();
        for ($i = 0; $i < $feedbackxml->feedback->count(); $i++) {
            $fbid = "";
            $text = "";
            $links = array();
            foreach($feedbackxml->feedback[$i]->attributes() as $att => $val) {
                if ($att == "fbid") {
                    $fbid = (string) $val;
                }
            }
            
            // Links with href and text have to be added.
            $text = trim((string) $feedbackxml->feedback[$i]->text);
            // Make sure text is correctly formatted.
            $text = html_to_text($text, 0, false);
            $linkcnt = count($feedbackxml->feedback[$i]->link);
            for ($j = 0; $j < $linkcnt; $j++) {
                $links[$j] = array();
                $links[$j]['href'] = trim((string) $feedbackxml->feedback[$i]->link[$j]->linkref);
                
                if(empty(trim((string) $feedbackxml->feedback[$i]->link[$j]->linktext))) {
                    $links[$j]['hlink'] = $links[$j]['href'];
                } else {
                    $links[$j]['hlink'] = trim((string) $feedbackxml->feedback[$i]->link[$j]->linktext);
                }
            }
            
            $feedbacklist[$i] = array('fbid' => $fbid, 'text' => $text, 'links' => $links);
        }
        
        $matching_fbids = array();
        $j = 0;
        
        // Summarize array of feedback references for the clicked answers.
        for ($i = 0; $i < count($match2text); $i++) {
            $pattern = $match2text[$i]['match'];
            
            if(preg_match("/" . $pattern . "/", $answerbitmap)) {
                $matching_fbids[$j]['pos'] = $match2text[$i]['pos'];
                for ($k = 0; $k < count($feedbacklist); $k++) {
                    $fbtext = "";
                    if ($match2text[$i]['fbid'] == $feedbacklist[$k]['fbid']) {
                        $tmpstr = str_replace("\n", '<br/>', $feedbacklist[$k]['text']);
                        $fbtext .= '<p>' . $tmpstr . '<br/>';

                        for ($l =0; $l < count($feedbacklist[$k]['links']); $l++) {
                            $hl = $feedbacklist[$k]['links'][$l]['hlink'];
                            $hr = $feedbacklist[$k]['links'][$l]['href'];
                            $fbtext .= '<a href="' . $hr . '" target="_blank">' . $hl . '</a><br/>';
                        }
                        $fbtext .= '</p>';
                        $matching_fbids[$j]['text'] = $fbtext;
                        break;
                    }
                }
                $j++;
            }
        }
        
        if (count($matching_fbids) == 0) {
            // No rule matches.
            foreach($decisionxml->rule[0]->attributes() as $att => $val) {
                if ($att == "clicked") {
                    $match = (string) $val;
                }
            }
            // No rule matches so print default feedback message at the end of the statements.
            $matching_fbids[0]['text'] = "<p>" . get_string('partiallycorrect', 'qtype_selfassess') . "</p>";
            $matching_fbids[0]['pos'] = strlen($match) - 1;
        }
        
        return $matching_fbids;
    }
}