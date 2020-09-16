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
// use Phpml\FeatureExtraction\StopWords;

/**
 * selfassess multiple choice question type class
 *
 * @package    qtype_
 * @subpackage selfassess
 * @copyright  2020 FernUniversität Hagen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/questionlib.php');

/**
 * Represent the selfassess question type class extending question_type.
 */
class qtype_selfassess extends question_type {

    /**
     * Determine whether this questiontype is graded manual.
     * 
     * {@inheritDoc}
     * @see question_type::is_manual_graded()
     * 
     * @return bool false
     */
    public function is_manual_graded() {
        return false; // Originally true.
    }
    
    
    /**
     * Return array of attachments and answers.
     * 
     * @return array of attachments and answers
     * @see question_type::response_file_areas()
     */
    public function response_file_areas() {
        return array('attachments', 'answer');
    }
    
    
    /**
     * Return choices that are offered for the number of attachments.
     * 
     * @return array choices that are offered for the number of attachments
     */
    public function attachment_options() {
        return array( 
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
        );
    }
    
    
    /**
     * Return choices that are offered for the number of required attachments.
     * 
     * @return array choices that are offered for the number of required attachments
     */
    public function attachments_required_options() {
        return array(
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
        );
    }
 
    
    /**
     * Override has_html_answers of question_type.
     * 
     * @return bool true
     */
    public function has_html_answers() {
        return true;
    }
    
    
    /**
     * Get the question options.
     *
     * @param object $question question
     * 
     * @return parent::get_question_options($question)
     */
    public function get_question_options($question) {
        global $DB;
        
        $question->options = $DB->get_record('qtype_selfassess_options',
            array('questionid' => $question->id), '*', MUST_EXIST);
        if ($question->options === false) {
            // For the user to be able to edit or delete this question we need options.
            debugging("Question ID {$question->id} was missing an options record. Using default.", DEBUG_DEVELOPER);

            $question->options = $this->create_default_options($question);
        }

        parent::get_question_options($question);
    }
    

    /**
     * Create a default options object for the provided question.
     *
     * @param object $question question we are working with
     * 
     * @return stdClass $options question default obtions
     */
    protected function create_default_options($question) {
        // Create a default question options record.
        $options = new stdClass();
        $options->questionid = $question->id;

        $config = get_config('qtype_selfassess'); 
        
        $options->answernumbering = $config->answernumbering;
        $options->shuffleanswers = $config->shuffleanswers;
               
        $options->specialfeedbackrule = $config->specialfeedbackrule;
        $options->specialfeedbacktext = $config->specialfeedbacktext;
        $options->instruction = $config->instruction;        
        $options->instructionlinktext = $config->instructionlinktext;
        $options->instructionlinkref = $config->instructionlinkref;
        $options->solution = $config->solution;
        
        return $options;
    }
    

     /**
     * Save the question options.
     *
     * @param object $question question
     * 
     * @return object error if there aren't at least 2 answers
     */
    public function save_question_options($question) {
        global $DB, $USER;
        
        $context = $question->context;                         
        $result = new stdClass();
        
        $xmlanswer = "";
        $specialfeedbackrule = "";
        $specialfeedbacktext = "";
        $instruction = "";
        $insttext = "";
        $instlinktext = "";
        $instlinkref = "";
        $solution = "";
        $title = "";
        $questiontext = "";
        $maxscore = "";
        
        $fs = get_file_storage();
        
        $xmlcontext = context_user::instance($USER->id);
        if ($files = $fs->get_area_files($xmlcontext->id, 'user', 'draft', $question->xmlfile, 'id DESC', false)) {
            $file = reset($files);
            
            
            $xmlanswer = $this->xmlfile_splitter($file->get_content(), 0);
            $specialfeedbackrule = $this->xmlfile_splitter($file->get_content(), 1);
            $specialfeedbacktext = $this->xmlfile_splitter($file->get_content(), 2);
            $instruction = $this->xmlfile_splitter($file->get_content(), 3);
            $solution = $this->xmlfile_splitter($file->get_content(), 4);
            $title = $this->xmlfile_splitter($file->get_content(), 5);
            $questiontext = $this->xmlfile_splitter($file->get_content(), 6);
            $maxscore = $this->xmlfile_splitter($file->get_content(), 7);
            
            // Split up <instruction>.
            $insttext = $this->xmlfile_splitter($instruction, 10);
            $instlinktext = $this->xmlfile_splitter($instruction, 11);
            $instlinkref = $this->xmlfile_splitter($instruction, 12);
        } else {
            $result->error = "internal error: uploaded file not found";
            return result;
        }
               
        $arrayanswer = $this->xmlanswer_to_answerarray($xmlanswer);
        $arrayfraction = $this->xmlanswer_to_fractionarray($xmlanswer);
                
        $oldanswers = $DB->get_records('question_answers',
            array('question' => $question->id), 'id ASC');

        // Insert all the new answers.
        foreach ($arrayanswer as $key => $answerdata) {
            if (trim($answerdata['text']) == '') {
                continue;
            }

            // Update an existing answer if possible.
            $answer = array_shift($oldanswers);
            // Otherwise create a new answer.
            if (!$answer) {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = '';
                $answer->feedback = '';
                $answer->id = $DB->insert_record('question_answers', $answer);
            }

            if (is_array($answerdata)) {
                // Do an import.
                $answer->answer = $this->import_or_save_files($answerdata,
                        $context, 'question', 'answer', $answer->id);
                $answer->answerformat = $answerdata['format'];
            } else {
                // Save the form.
                $answer->answer = $answerdata;
                $answer->answerformat = FORMAT_HTML;
             }

            // For grading the answer.
	        $answer->fraction = $arrayfraction[$key];

            $DB->update_record('question_answers', $answer);
        }
        
 
        // Delete any left over old answer records.
        foreach ($oldanswers as $oldanswer) {
            $fs->delete_area_files($context->id, 'question', 'answerfeedback', $oldanswer->id);
            $DB->delete_records('question_answers', array('id' => $oldanswer->id));
        }
        
        $options = $DB->get_record('qtype_selfassess_options', array('questionid' => $question->id) );
        
        if (!$options) {            
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->id = $DB->insert_record('qtype_selfassess_options', $options);
        }
        
        
        $options->graderinfo = $question->graderinfo['text'];
        $options->graderinfoformat = $question->graderinfo['format'];
               
        $options->answernumbering = $question->answernumbering;
        $options->shuffleanswers = $question->shuffleanswers;          
        
        $options->attachments = $question->attachments;
        $options->attachmentsrequired = $question->attachmentsrequired;
        if (!isset($question->filetypeslist)) {
            $options->filetypeslist = "";
        } else {
            $options->filetypeslist = $question->filetypeslist;
        }
        
        $options->specialfeedbackrule = $specialfeedbackrule;
        $options->specialfeedbacktext = $specialfeedbacktext;
        $options->instruction = $insttext;
        $options->instructionlinktext = $instlinktext;
        $options->instructionlinkref = $instlinkref;
        $options->solution = $solution;

        $DB->update_record('qtype_selfassess_options', $options);
        
        // Title, questiontext and maxscore can also be inserted via the edit form.
        if (!empty($title) || !empty($questiontext) ||  !empty($maxscore)) {
            $options = $DB->get_record('question', array('id' => $question->id) );
            if (!empty($title)) {
                $options->name = $title;
            }
            if (!empty($questiontext)) {
                $options->questiontext = $questiontext;
            }
            if (!empty($maxscore)) {
                $options->defaultmark = $maxscore;
            }
            $DB->update_record('question', $options);
        }
        
   }
   
   
   /**
    * Split the xml-file txt in an 'answer-tree'-part, a 'decision tree'-part, a 'feedbacktexts'-part,
    *     an 'instruction'-part and a 'solution'-part.
    *
    * @param string $xmlstr complete text of the xml file
    * @param int $task differs between the different parts of the string
    *
    * @return string $ret
    */
   public function xmlfile_splitter(String $xmlstr, int $task) {
       $ret = "";
       
       if ($task == 0 ) {   // Select answer text part of XML.
           $answstart = stripos($xmlstr, '<answer_tree>');
           $answstop = stripos($xmlstr, '</answer_tree>');
           $ret = substr($xmlstr, $answstart, $answstop-$answstart+strlen('</answer_tree>'));
       } else if ($task == 1 ) {    // Select rule text part of XML.
           $rulestart = stripos($xmlstr, '<decision_tree>');
           $rulestop = stripos($xmlstr, '</decision_tree>');
           $ret = substr($xmlstr, $rulestart, $rulestop-$rulestart+strlen('</decision_tree>'));
       } else if ($task == 2 ) {    // Select feedback text part of XML.
           $fbtextstart = stripos($xmlstr, '<feedbacktexts>');
           $fbtextstop = stripos($xmlstr, '</feedbacktexts>');
           $ret = substr($xmlstr, $fbtextstart, $fbtextstop-$fbtextstart+strlen('</feedbacktexts>'));
       } else if ($task == 3 ) {    // Select instruction text part of XML.
           $instextstart = stripos($xmlstr, '<instruction>');
           $instextstop = stripos($xmlstr, '</instruction>');
           $ret = substr($xmlstr, $instextstart, $instextstop-$instextstart+strlen('<instruction>'));           
        } else if ($task == 4 ) {    // Select solution text part of XML.
           if (stripos($xmlstr, '<solution>') != false ) {
               $soltextstart = stripos($xmlstr, '<solution>');
               $soltextstop = stripos($xmlstr, '</solution>');
               $ret = substr($xmlstr, $soltextstart+strlen('<solution>'), $soltextstop-$soltextstart+strlen('<solution>'));
               $ret = html_to_text($ret, 0, false);
           } else {
               $ret = "";
           }
       } else if ($task == 5 ) {    // Select title text part of XML.
           if (stripos($xmlstr, '<title>') != false ) {
               $tittextstart = stripos($xmlstr, '<title>');
               $tittextstop = stripos($xmlstr, '</title>');
               $ret = substr($xmlstr, $tittextstart+strlen('<title>'), $tittextstop-$tittextstart+strlen('<title>'));
           } else {
               $ret = "";
           }
       } else if ($task == 6 ) {    // Select question text part of XML.
           if (stripos($xmlstr, '<question>') != false ) {
               $qutextstart = stripos($xmlstr, '<question>');
               $qutextstop = stripos($xmlstr, '</question>');
               $ret = substr($xmlstr, $qutextstart+strlen('<question>'), $qutextstop-($qutextstart+strlen('<question>')));
               $ret = html_to_text($ret, 0, false);
               
           } else {
               $ret = "";
           }
       } else if ($task == 7 ) {    // Select max_score text part of XML.
           if (stripos($xmlstr, '<max_score>') != false ) {
               $mstextstart = stripos($xmlstr, '<max_score>');
               $mstextstop = stripos($xmlstr, '</max_score>');
               $ret = substr($xmlstr, $mstextstart+strlen('<max_score>'), $mstextstop-($mstextstart+strlen('<max_score>')));
           } else {
               $ret = "";
           }
       } else if ($task == 10 ) {    // Select text part of instruction
           $ittextstart = stripos($xmlstr, '<text>');
           $ittextstop = stripos($xmlstr, '</text>');
           $ret = substr($xmlstr, $ittextstart+strlen('<text>'), $ittextstop-($ittextstart+strlen('<text>')));
               $ret = html_to_text($ret, 0, false);
               
       } else if ($task == 11 ) {    // Select linktext part of instruction
           if (stripos($xmlstr, '<linktext>') != false ) {
               $ilttextstart = stripos($xmlstr, '<linktext>');
               $ilttextstop = stripos($xmlstr, '</linktext>');
               $ret = substr($xmlstr, $ilttextstart+strlen('<linktext>'), $ilttextstop-($ilttextstart+strlen('<linktext>')));
           } else {
               $ret = "";
           }
       } else if ($task == 12 ) {    // Select linkref part of instruction
           if (stripos($xmlstr, '<linkref>') != false ) {
               $ilrtextstart = stripos($xmlstr, '<linkref>');
               $ilrtextstop = stripos($xmlstr, '</linkref>');
               $ret = substr($xmlstr, $ilrtextstart+strlen('<linkref>'), $ilrtextstop-($ilrtextstart+strlen('<linkref>')));
           } else {
               $ret = "";
           }
       }
 
       return $ret;
    }
    
    
    /**
     * Transform the XML string answer in an array answer.
     * 
     * @param string $xmlanswer complete texts and grades of the answers
     * 
     * @return string $ret
     */
    protected function xmlanswer_to_answerarray($xmlanswer) {
        $xml = simplexml_load_string($xmlanswer);
        
        $ret = array();
        
        for ($i = 0; $i < $xml->answ->count(); $i++) {
            $atext = trim((string) $xml->answ[$i]->answer);
            $tmpstr = str_replace("\n", '<br/>', $atext);
            $answtext = '<p>' . $tmpstr . '</p>';
            
            $ret[$i]['text'] = trim((string) $answtext);
            $ret[$i]['format'] = 1;
        }
        
        return $ret;
    }
    
    
    /**
     * Transform the XML string answer in an array fraction.
     * 
     * @param string $xmlanswer complete texts and grades of the answers
     * 
     * @return string $ret
     */
    protected function xmlanswer_to_fractionarray($xmlanswer) {
        $xml = simplexml_load_string($xmlanswer);
        
        $ret = array();
        
        for ($i = 0; $i < $xml->answ->count(); $i++) {
            $atext = trim((string) $xml->answ[$i]->grade);
           
            $ret[$i] = $atext / 100;
        }
        
        return $ret;
    }
   

    /**
     * Make a question instance.
     *
     * @param object $questiondata question data
     * 
     * @return object $class question instance
     */
    protected function make_question_instance($questiondata) {
        question_bank::load_question_definition_classes($this->name());
        $class = 'qtype_selfassess_question';
        return new $class();
    }
    

    /**
     * Initialise the question instance.
     *
     * @param question_definition $question question_definition we are creating
     * @param object $questiondata question data
     * 
     * @return void
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);

        $question->attachments = $questiondata->options->attachments;
        $question->attachmentsrequired = $questiondata->options->attachmentsrequired;
        $question->graderinfo = $questiondata->options->graderinfo;
        $question->graderinfoformat = $questiondata->options->graderinfoformat;
        
        $filetypesutil = new \core_form\filetypes_util();
        $question->filetypeslist = $filetypesutil->normalize_file_types($questiondata->options->filetypeslist);
        
        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $question->answernumbering = $questiondata->options->answernumbering; 
        
        $question->specialfeedbackrule = $questiondata->options->specialfeedbackrule;
        $question->specialfeedbacktext = $questiondata->options->specialfeedbacktext;
        $question->instruction = $questiondata->options->instruction;
        $question->instructionlinktext = $questiondata->options->instructionlinktext;
        $question->instructionlinkref = $questiondata->options->instructionlinkref;
        $question->solution = $questiondata->options->solution;
                
        $this->initialise_question_answers($question, $questiondata, false);
    }

    
    /**
     * Make an answer.
     *
     * @param object $answer answer
     * 
     * @return parent::make_answer($answer)
     */
    public function make_answer($answer) {
        return parent::make_answer($answer);
    }
    

    /**
     * Delete the question.
     *
     * @param int $questionid question ID
     * @param object $contextid context ID
     * 
     * @return parent::delete_question($questionid, $contextid)
     */
    public function delete_question($questionid, $contextid) {
         $DB->delete_records('qtype_selfassess_options', array('questionid' => $questionid));
        
        parent::delete_question($questionid, $contextid);
        
        return parent::delete_question($questionid, $contextid);
    }
    
    
    /**
     * Get the number of correct response choices.
     *
     * @param object $questiondata question data
     * 
     * @return int $numright the number of correct choices
     */

    protected function get_num_correct_choices($questiondata) {
        $numright = 0;
        foreach ($questiondata->options->answers as $answer) {
            if (!question_state::graded_state_for_fraction($answer->fraction)->is_incorrect()) {
                $numright += 1;
            }
        }
        return $numright;
    }
    

    /**
     * Get the score if random response chosen - but not computed for this question type.
     *
     * @param object $questiondata question data
     * 
     * @return null
     */
    public function get_random_guess_score($questiondata) {
        return null;
    }
    

    /**
     * Get the possible responses to the question.
     *
     * @param object $questiondata question data
     * 
     * @return array $parts question parts
     */

    public function get_possible_responses($questiondata) {
        $parts = array();

        foreach ($questiondata->options->answers as $aid => $answer) {
            $parts[$aid] = array($aid => new question_possible_response(
                            html_to_text(format_text(
                            $answer->answer, $answer->answerformat, array('noclean' => true)),
                            0, false), $answer->fraction));
        }

        return $parts;
    }
    

    /**
     * Get the available question numbering styles.
     *
     * @return array $styles the numbering styles supported. For each one, there
     *      should be a lang string answernumberingxxx in the qtype_selfassess
     *      language file, and a case in the switch statement in number_in_style,
     *      and it should be listed in the definition of this column in install.xml.
     */
    public static function get_numbering_styles() {
        $styles = array();
        foreach (array('abc', 'ABCD', '123', 'iii', 'IIII', 'none') as $numberingoption) {
            $styles[$numberingoption]
                    = get_string('answernumbering' . $numberingoption, 'qtype_selfassess');
        }
        return $styles;
    }


    /**
     * Move files from old to new context.
     *
     * @param int $questionid question ID
     * @param object $oldcontextid source context ID
     * @param object $newcontextid destination context ID
     * 
     * @return void
     */
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        $fs = get_file_storage();

        parent::move_files($questionid, $oldcontextid, $newcontextid);
               
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid, true);
     }
     

    /**
     * Delete any files in the context.
     *
     * @param int $questionid question ID
     * @param object $contextid context ID
     * 
     * @return void
     */
    protected function delete_files($questionid, $contextid) {
        $fs = get_file_storage();

        parent::delete_files($questionid, $contextid);
        
        $this->delete_files_in_answers($questionid, $contextid, true);
        $fs->delete_area_files($contextid, 'qtype_selfassess', 'graderinfo', $questionid);
    }    
}