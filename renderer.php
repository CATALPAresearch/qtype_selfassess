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
 * selfassess multiple choice question renderer class
 *
 * @package    qtype
 * @subpackage selfassess
 * @copyright  2020 FernUniversität Hagen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/type/multichoice/renderer.php');
require_once($CFG->dirroot . '/question/type/rendererbase.php');

/**
 * Represents an selfassess renderer class extending multichoice renderer
 */
class qtype_selfassess_renderer extends qtype_multichoice_multi_renderer {
    
    /**
     * Override formulation_and_controls of qtype_multichoice_multi_renderer.
     * 
     * this plugin does neither show the number of correct answers 
     * nor which answers are correct or false
     * 
     * @param array question_attempt $qa, 
     * @param array question_display_options $options
     *       
     * @return string $result representing the HTML block containing the proposed answers
     */
    public function formulation_and_controls(question_attempt $qa,
        question_display_options $options) {
        
            $quiz_uniqueid = $qa->get_usage_id();
            // If not in preview mode get all the parameter needed for the function trigger_log.
            if (! is_a($options, 'question_preview_options')) {  
                $attemptobj = quiz_attempt::create_from_usage_id($quiz_uniqueid);
                $cmid = $attemptobj->get_cmid();
                $retattempt = $attemptobj->get_attemptid();
            } else {   // Preview mode.
                $cmid = 0;
                $retattempt = 0;
            }
            
            $slot = $qa->get_slot();
            
            // Parameter needed to start the PHP script opening a new link showing the solution.
            $solutionurl = new \moodle_url('/question/type/selfassess/showsolution.php', 
                array('uniqueid' => $quiz_uniqueid, 'slot' => $slot, 'cmid' => $cmid, 'retattempt' => $retattempt));
            
            $question = $qa->get_question();
            $response = $question->get_response($qa);
            
            $qstate = $this->get_current_state($qa, $options);
            
            $files = '';
             
            if ((! is_a($options, 'question_preview_options')) && $qstate & 0x75) {    // Add up states: 0x01, 0x04, 0x10, 0x20, 0x40.
                $this->trigger_log($qstate, $cmid, $retattempt, $qa, $options);
             }
            
            $files = $this->files_input($qa, $question->attachments, $options);
            // If attachment has been uploaded it can't be edited any more.            
            if ($qstate & 0xBC) {    // Add up states: 0x04, 0x08, 0x10, 0x20, 0x80.           
                $files = $this->files_read_only($qa, $options);
            }
            
            // Makes sure to show the questions after uploading a required attachment.
            if ($qstate > 0x02) {                
                $inputname = $qa->get_qt_field_name('answer');
                $inputattributes = array(
                    'type' => $this->get_input_type(),
                    'name' => $inputname
                );                 
            
                // Chosen checkbox values have to be visible in the review. 
                if ($qstate >= 0x10 ) {    
                    $inputattributes['disabled'] = 'disabled';               
                    $inputattributes['onclick'] = 'return false';                    
                }
                
                $radiobuttons = array();
                $classes = array();
                foreach ($question->get_order($qa) as $value => $ansid) {
                    $ans = $question->answers[$ansid];
                    $inputattributes['name'] = $this->get_input_name($qa, $value);
                    $inputattributes['value'] = $this->get_input_value($value);
                    $inputattributes['id'] = $this->get_input_id($qa, $value);
                    $isselected = $question->is_choice_selected($response, $value);
                    if ($isselected && $qstate != 0x04) {
                        $inputattributes['checked'] = 'checked';
                    } else {
                        unset($inputattributes['checked']);
                    }
                    $hidden = '';
                    if (!$options->readonly && $this->get_input_type() == 'checkbox') {
                        $hidden = html_writer::empty_tag('input', array(
                            'type' => 'hidden',
                            'name' => $inputattributes['name'],
                            'value' => 0                           
                        ));
                    }
                    $radiobuttons[] = $hidden . html_writer::empty_tag('input', $inputattributes) .
                    html_writer::tag('label',
                        html_writer::span($this->number_in_style($value, $question->answernumbering), 'answernumber') .
                        $question->make_html_inline($question->format_text(
                            $ans->answer, $ans->answerformat, $qa, 'question', 'answer', $ansid)),
                        array('for' => $inputattributes['id'], 'class' => 'm-l-1'));
                    
                    $class = 'r' . ($value % 2);
                    
                    $classes[] = $class;
                }                
            }
            
            $result = '';        

            $result .= html_writer::tag('div', $question->format_questiontext($qa),
                array('class' => 'qtext'));       
            
            // Tip to uploaded file.
            $result .= html_writer::tag('div', $this->my_prompt2($question, $qstate), array('class' => 'my_prompt'));
 
            $result .= html_writer::tag('div', $files, array('class' => 'attachments'));
            
            $result .= html_writer::start_tag('div', array('class' => 'ablock'));
            
            $result .= html_writer::start_tag('div', array('class' => 'answer'));
            
            if ($qstate > 0x02) {
                $linktext = "";
                
                // Show a possible disposed solution.
                $result .= html_writer::tag('div', trim($question->instruction), array('class' => 'my_prompt'));
                if (!empty(trim($question->instructionlinkref))) {
                    if (!empty(trim($question->instructionlinktext))) {
                        $linktext = trim($question->instructionlinktext);
                    } else {
                        $linktext = get_string('samplesolution', 'qtype_selfassess');
                    }
                 } else if (!empty(trim($question->solution))) {
                    $linktext = get_string('samplesolution', 'qtype_selfassess');
                }
                if (!empty($linktext)) {
                    $result .= html_writer::start_tag('div', array('class' => 'solutionurl'));
                    $result .= html_writer::tag('a',  $linktext, array('href' => $solutionurl, 'target' => "_blank"));
                    $result .= html_writer::end_tag('div');
                }
             }
            
            // Insert the accentuated specific feedback at the appropriate position.
            if(!is_null($radiobuttons)){
                if ($qstate < 0x10) {    // Add up states: 0x10, 0x20, 0x40, 0x80.
                
                    foreach ($radiobuttons as $key => $radio) {                    
                        $result .= html_writer::tag('div', $radio,
                            array('class' => $classes[$key])) . "\n";
                    }
                } else {
                    $feedbackarray = $question->create_specific_feedback($response);
                    
                    foreach ($radiobuttons as $key => $radio) {
                        $feedbacktext = "";
                        
                        for ($i = 0; $i < count($feedbackarray); $i++) {
                            if ($feedbackarray[$i]['pos'] == $key) {
                                $feedbacktext = $feedbackarray[$i]['text'];
                            }
                        }
                        
                        $fbt = html_writer::nonempty_tag('div', $feedbacktext,
                            array('class' => 'specific_feedback'));
                        
                        $result .= html_writer::tag('div', $radio . ' ' . $fbt,
                            array('class' => $classes[$key])) . "\n";
                    }
                }
            }            
            
            $result .= html_writer::end_tag('div'); // Answer.
            
            $result .= html_writer::end_tag('div'); // Ablock.
            
            // Print my_prompt() if attachments are needed but not yet provided and no choices are made.
            $result .= html_writer::tag('div', $this->my_prompt($question, $qstate), array('class' => 'my_prompt'));
            
            $result .= html_writer::nonempty_tag('div',
                $this->error_prompt($qstate), array('class' => 'alert alert-warning'));

            // Creates the look of the buttons.
            if ($qstate & 0x5F) {    // Not state 0x20 or 0x80.

                if ($qstate & 0x43) {    // Add up states: 0x01,0x02, 0,x40.
                    $buttonname = get_string('uploadfile', 'qtype_selfassess');
                } else if ($qstate & 0x0C) {    // Add up states: 0x04, 0x08.
                    $buttonname = get_string('receivefeedback', 'qtype_selfassess');
                } else {
                    $buttonname = get_string('retry', 'qtype_selfassess');
                }
                $attributes = array(
                    'type' => 'submit',
                    'id' => $qa->get_behaviour_field_name('submit'),
                    'name' => $qa->get_behaviour_field_name('submit'),
                    'value' => $buttonname,
                    'class' => 'submit btn btn-secondary',
                );
 
                $result .= html_writer::empty_tag('input', $attributes);
            } 
            
          return $result;
     }
    
    /**
     * Retrieve current state of question processing.
     * State are as follows:
     *
     * State 0x01:  Initial State, display filepicker.
     * State 0x02:  Error handler, nothing was up loaded, behaviour as above.
     * State 0x04:  File(s) uploaded, display disabled filepicker, show checkboxes without pre-selection.
     * State 0x08:  Error handler, nothing selected, behaviour as above.
     * State 0x10:  At least one choice selected, but selection incorrect, display specific feedback and 'Retry' button.
     * State 0x20:  At least one choice selected, selection correct. Display specific feedback, but no button.
     * State 0x40:  Retry: Display filepicker again with uploaded file, show disabled checkboxes with last selection.
     * State 0x80:  Finally submitted. Show disabled filepicker, show disabled checkboxes with last selection.
     *
     * @param question_attempt $qa  question attempt to display
     * @param question_display_options $options controls what should and should
     *      not be displayed. Used to get the context
     *
     * @return integer current state of the question $qstate
     */
    public function get_current_state( question_attempt $qa, question_display_options $options) {
         if ($options->generalfeedback == 1) {
            return 0x80;
        }
        
        $question = $qa->get_question();
        $response = $question->get_response($qa);
        
        if ( (!array_key_exists('attachments', $response)) && !array_key_exists('choice0', $response)) {
            return 0x01;
        }
        
        if ($question->is_complete_response($response) && $this->repeated_response($qa)) {
            return 0x40;     // Jump back.
        }
        
        if ( array_key_exists('attachments', $response)) {
            // It has to be a reference to the content and not the object itself 
            // -> not "isset($response->attachments))"
            if (empty ($response['attachments'])) {
                return 0x02;
            } else {
                return 0x04;
            }
        } else {
            if (! $question->is_complete_response($response)) {
                return 0x08;
            } else {
                if (! $question->is_correct_response($response)) {
                    return 0x10;
                } else {
                   return 0x20;
                }
            }
        }
        
    }
    
    /**
     * Check whether a response has already been given
     * 
     * @param question_attempt $qa
     * 
     * @return boolean $same
     */
    public function repeated_response($qa) {
        $question = $qa->get_question();
        $cur_response = $question->get_response($qa);
        
        $cur_step_number = $qa->get_num_steps()-1;
        $prev_step = $qa->get_step($cur_step_number-1);
        $prev_response = $prev_step->get_qt_data();
        
        if (array_key_exists('attachments', $cur_response)) {
            return false;
        }

        $same = $question->is_retry_response($cur_response, $prev_response);

        return $same;
        
    }  
    
    /**
     * Count the number of complete responses
     * 
     * @param question_attempt $qa
     * 
     * @return int $counter
     */ 
    public function get_valid_choices_count(question_attempt $qa) {
        $question = $qa->get_question();
        
        $counter = 0;
        
        foreach ($qa->get_reverse_step_iterator() as $step) {
            $response = $step->get_qt_data();
            
            if ($question->is_complete_response($response)) {
                $counter++;
            }
        }
        return $counter;
    } 
    
   /**
     * Display any attached files when the question is in read-only mode.
     * 
     * @param question_attempt $qa  question attempt to display
     * @param question_display_options $options controls what should and should
     *      not be displayed. Used to get the context
     *      
     * @return string $output HTML fragment
     */
    public function files_read_only(question_attempt $qa, question_display_options $options) {
       
        $files = $qa->get_last_qt_files('attachments', $options->context->id);
        $output = array();        
        
        foreach ($files as $file) {
            $output[] = html_writer::tag('p', html_writer::link($qa->get_response_file_url($file),
                $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file),
                'moodle', array('class' => 'icon')) . ' ' . s($file->get_filename())));
        }
        return implode($output);
    }
    
    
    /**
     * Display the input control for when the student should upload a single file.
     * 
     * @param question_attempt $qa question attempt to display
     * @param int $numallowed the maximum number of attachments allowed
     * @param question_display_options $options controls what should and should
     *      not be displayed. Used to get the context.
     *      
     * @return $filemanager
     */
    public function files_input(question_attempt $qa, $numallowed,
        question_display_options $options) {            
            global $CFG;
            require_once($CFG->dirroot . '/lib/form/filemanager.php');
            
            $pickeroptions = new stdClass();
            $pickeroptions->mainfile = null;
            $pickeroptions->maxfiles = $numallowed;
            $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid(
                'attachments', $options->context->id);
            $pickeroptions->context = $options->context;
            $pickeroptions->return_types = FILE_INTERNAL | FILE_CONTROLLED_LINK;
            $pickeroptions->accepted_types = $qa->get_question()->filetypeslist;
            
            $fm = new form_filemanager($pickeroptions);
            $filesrenderer = $this->page->get_renderer('core', 'files');
            
            $text = '';
            if (!empty($qa->get_question()->filetypeslist)) {
                $text = html_writer::tag('p', get_string('acceptedfiletypes', 'qtype_selfassess'));
                $filetypesutil = new \core_form\filetypes_util();
                $filetypes = $qa->get_question()->filetypeslist;
                $filetypedescriptions = $filetypesutil->describe_file_types($filetypes);
                $text .= $this->render_from_template('core_form/filetypes-descriptions', $filetypedescriptions);
            }
            return $filesrenderer->render($fm). html_writer::empty_tag(
                'input', array('type' => 'hidden', 'name' => $qa->get_qt_field_name('attachments'),
                'value' => $pickeroptions->itemid)) . $text;
    }
    
    /**
     * Return HTML code to be included before the specific button when this question is used.
     * 
     * @param array question_attempt $qa
     * @param integer $qstate current state of the question
     * 
     * @return string $fb request what to do 
     */
     public function my_prompt($question, $qstate){
     $fb = "";

     if ($qstate & 0x43) {    // Add up states: 0x01, 0x02, 0x40.
         $fb = get_string('uploadatleast', 'qtype_selfassess') . $question->attachmentsrequired . " .\n ";
         $fb .= get_string('clickbuttonafteruploading', 'qtype_selfassess') . " '" . 
             get_string('uploadfile', 'qtype_selfassess') . "'.\n\n ";
         
     } else if ($qstate & 0x0C) {    // Add up states: 0x04, 0x08.
         $fb = get_string('selectchoice', 'qtype_selfassess')  . " '" . 
             get_string('receivefeedback', 'qtype_selfassess') . "'.\n\n ";

     } else if ($qstate & 0x10) {     // State: 0x10.        
         $fb = get_string('requestretry', 'qtype_selfassess')  . " '" .
             get_string('retry', 'qtype_selfassess') . "'.\n\n ";
     } 
     
     return format_text($fb);    
     }
     
     /**
      * Return HTML code to be included in the page's <head> when this question is used.
      *
      * @param array question_attempt $qa
      * @param integer $qstate current state of the question 
      *
      * @return string $tip text to the uploaded file
      */
      public function my_prompt2($question, $qstate){
          $tip = "";
          
          if ($qstate == 0x40) {
              $tip = get_string('tiptouploadedfile', 'qtype_selfassess') . "\n ";
              $tip .= get_string('leaveuploadedfile', 'qtype_selfassess') . " '" .
                  get_string('uploadfile', 'qtype_selfassess') . "'\n ";
          } else if ($qstate & 0x3C) {    // Add up states: 0x04, 0x08, 0x10, 0x20.
              $tip = get_string('yoursolution', 'qtype_selfassess') . "\n ";
          }
          
          return format_text($tip);
          
      }
     
     /**
      * Indicate whether an error occurred or not.
      *
      * @param int $qstate given response
      *
      * @return string empty or not
      */
     public function error_prompt(int $qstate) {
         if ($qstate == 0x02) {   // State: 0x02.
             return get_string('uploadyoursolutionfirst', 'qtype_selfassess');
         } else if ($qstate == 0x08) {    // State: 0x08.
             return get_string('selectatleastonechoice', 'qtype_selfassess');
         }
     }
          
    /**
     * 
     * 
     * Log the different events question_viewed, file_uploaded, attempt_performed and attempt_retried.
     * 
     * @param integer $qstate current state of the question 
     * @param integer $cmid course module id
     * @param integer $retattempt id of the quiz specific question attempt
     * @param array question_attempt $qa
     * @param array question_display_options $options
     *       
     * @return event->trigger()
     */
     public function trigger_log($qstate, $cmid, $retattempt, question_attempt $qa, question_display_options $options) {
         $question = $qa->get_question();
        $response = $question->get_response($qa);
        $retrycount = $this->get_valid_choices_count($qa);    // Retry.
        
        if ($qstate & 0x8A) {    // Add up states: 0x02, 0x08, 0x80.
            return;
        }
               
        if ($qstate == 0x01) {    // Event question_viewed.
            if (empty($cmid)) {
                $event = qtype_selfassess\event\question_viewed::create(array(
                    'objectid' => $question->id,
                    'context' => $options->context,
                    'other'    => array ('notused' => "notused"),                     
                ));
            } else {
                $event = qtype_selfassess\event\question_viewed::create(array(
                    'objectid' => $question->id,
                    'context' => context_module::instance($cmid),
                    'other'    => array ('retattempt' => $retattempt, 'cmid' => $cmid),                     
                ));                
            } 
        } else if ($qstate == 0x04) {    // Event solution_uploaded.
            $files = $qa->get_last_qt_files('attachments', $options->context->id);
            $filelist = array();
            
            foreach ($files as $file) {
                $filelist[] = html_writer::link($qa->get_response_file_url($file), $file->get_filename()) . "<br/>";
            }
            
            if (empty($cmid)) {
                $event = qtype_selfassess\event\solution_uploaded::create(array(
                    'objectid' => $question->id,
                    'context' => $options->context,
                    'other'    => array ('filelist' => $filelist),
                ));
            } else {
                $event = qtype_selfassess\event\solution_uploaded::create(array(
                    'objectid' => $question->id,
                    'context' => context_module::instance($cmid),
                    'other'    => array ('filelist' => $filelist, 'retattempt' => $retattempt, 'cmid' => $cmid),
                ));
            }
        } else if ($qstate & 0x30) {    // Add up: 0X010, 0x20; event attempt_retried.
            // Create log entry after the selection of the checkboxes.
            $retrycount = $this->get_valid_choices_count($qa);            
            $actualgrade = $question->grade_response($response);
            $result = $question->get_result($response);
            $graderounded = round($actualgrade[0],2);
        
            
            // Students have not the permission to see server logs so 'context' is different.
            if (empty($cmid)) {
                $event = qtype_selfassess\event\attempt_performed::create(array(
                    'objectid' => $question->id,
                    'context' => $options->context,
                    'other'    => array ('result' => $result, 'actgrade' => $graderounded, 'retry' => $retrycount),
                ));
            } else {
                $event = qtype_selfassess\event\attempt_performed::create(array(
                    'objectid' => $question->id,
                    'context' => context_module::instance($cmid),
                    'other'    => array ('result' => $result, 'actgrade' => $graderounded, 
                        'retry' => $retrycount, 'retattempt' => $retattempt, 'cmid' => $cmid),
                ));
            }
            } else if ($qstate == 0x40) {    // Event attempt_retried.
            $retrycount = $this->get_valid_choices_count($qa);
            if (empty($cmid)) {
                $event = qtype_selfassess\event\attempt_retried::create(array(
                    'objectid' => $question->id,
                    'context' => $options->context,
                    'other'    => array ('retry' => $retrycount),
                ));
            } else {
                $event = qtype_selfassess\event\attempt_retried::create(array(
                    'objectid' => $question->id,
                    'context' => context_module::instance($cmid),
                    'other'    => array ('retry' => $retrycount, 'retattempt' => $retattempt, 'cmid' => $cmid),
                ));
            }
        }
        $event->trigger();

    }
    
    /**
     * Override the feedback() of rendererbase.
     * Generates the answer specific feedback of the question
     *
     * @param question_attempt $qa question attempt to display
     * @param question_display_options $options controls what should be displayed or not
     * 
     * @return string $output HTML fragment
     * 
     */    
    public function feedback(question_attempt $qa, question_display_options $options) {
       $output = '';
        
       // Specific feedback should never be displayed, it's handled internally.
       
       if ($options->generalfeedback) {
            $output .= html_writer::nonempty_tag('div', $this->general_feedback($qa),
                array('class' => 'generalfeedback'));
        }

        return $output;
    }
    
   /*							      
     * Overrides function from multichoice: never display the correct answers
     * 
     * @param array $right
     * 
     * @return string empty
     */							      
     protected function correct_choices(array $right) {	      
        return "";					      
     }	
    

    /**
     * Override num_parts_correct of qtype_multichoice_multi_renderer.
     * it is not intended to show the number of correct answers
     *
     * @param array question_attempt $qa 
     *
     * @return string empty string
     */
    protected function num_parts_correct(question_attempt $qa) {
      return "";     
    }
}
