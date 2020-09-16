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
 * selfassess multiple choice question definition class
 *
 * @package    qtype
 * @subpackage selfassess
 * @copyright  2020 FernUniversität Hagen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Represent selfassess editing form definition class extending question_edit_form.
 */
class qtype_selfassess_edit_form extends question_edit_form {
  
    /**
     * Add question-type specific form fields.
     *
     * @param object $mform form being built
     * 
     * @return void
     */
    protected function definition_inner($mform) {
        global $COURSE;
               
        $qtype = question_bank::get_qtype('selfassess');
        
        $mform->addElement('select', 'attachments',
            get_string('allowattachments', 'qtype_selfassess'), $qtype->attachment_options());
        $mform->setDefault('attachments', 1);
        
        $mform->addElement('select', 'attachmentsrequired',
            get_string('attachmentsrequired', 'qtype_selfassess'), $qtype->attachments_required_options());
        $mform->setDefault('attachmentsrequired', 1);
        $mform->addHelpButton('attachmentsrequired', 'attachmentsrequired', 'qtype_selfassess');
        
        $mform->addElement('filetypes', 'filetypeslist', get_string('acceptedfiletypes', 'qtype_selfassess'));
        $mform->addHelpButton('filetypeslist', 'acceptedfiletypes', 'qtype_selfassess');
        $mform->setDefault('filetypeslist', '.pdf, .jpg');
        
        $mform->addElement('header', 'graderinfoheader', get_string('graderinfoheader', 'qtype_selfassess'));
        $mform->setExpanded('graderinfoheader');
        $mform->addElement('editor', 'graderinfo', get_string('graderinfo', 'qtype_selfassess'),
            array('rows' => 10), $this->editoroptions);
        

        $mform->addElement('advcheckbox', 'shuffleanswers',
            get_string('shuffleanswers', 'qtype_selfassess'), null, null, array(0, 1));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_selfassess');
        $mform->setDefault('shuffleanswers', 0);

        
        $mform->addElement('select', 'answernumbering',
            get_string('answernumbering', 'qtype_selfassess'),
            qtype_selfassess::get_numbering_styles());
        $mform->setDefault('answernumbering', get_config('qtype_selfassess', 'answernumbering'));
        
        // From lib/form/filemanager.php & 
        // URL: https://docs.moodle.org/dev/Using_the_File_API_in_Moodle_forms#filepicker.
        $filepickeroptions = array('subdirs'=>0,
            'maxbytes'=>$COURSE->maxbytes,
            'accepted_types'=>'.xml',
            'maxfiles'=>1,
            'itemid'=>file_get_unused_draft_itemid(),
            'return_types'=>FILE_INTERNAL
        );
        
        // From type/edit_question_form.php & 
        // https://docs.moodle.org/dev/Using_the_File_API_in_Moodle_forms#filepicker.
        $mform->addElement('filepicker', 'xmlfile',
            get_string('xmlfile', 'qtype_selfassess'), null, $filepickeroptions);
        $mform->addHelpButton('xmlfile', 'xmlfile', 'qtype_selfassess');
     }        

     
    /**
     * Override data_preprocessing of edit_question_form.
     * 
     * @param object $question data being passed to the form
     * 
     * @return object $question modified data
     */
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        
        if (empty($question->options)) {
            return $question;
        }
        
        $question->attachments = $question->options->attachments;
        $question->attachmentsrequired = $question->options->attachmentsrequired;
        $question->filetypeslist = $question->options->filetypeslist;

        // Important for grading.
        $draftid = file_get_submitted_draft_itemid('graderinfo');
        $question->graderinfo = array();
        $question->graderinfo['text'] = file_prepare_draft_area(
            $draftid,           // Draftid.
            $this->context->id, // Context.
            'qtype_selfassess', // Component.
            'graderinfo',       // Filarea.
            !empty($question->id) ? (int) $question->id : null, // Itemid.
            $this->fileoptions, // Options.
            $question->options->graderinfo // Text.
        );
        $question->graderinfo['format'] = $question->options->graderinfoformat;
	    $question->graderinfo['itemid'] = $draftid;
        
        $question = $this->data_preprocessing_answers($question, true);
        
        $question->shuffleanswers = $question->options->shuffleanswers;
        $question->answernumbering = $question->options->answernumbering;
        
        return $question;
    }
    
 
    /**
     * Validate the question provided.
     * 
     * @param object $data question data the question provides
     *        array $files array of uploaded files "element_name"=>tmp_file_path 
     *          
     * @return array $errors error messages 
     */
    public function validation($data, $files) {  
        $errors = parent::validation($data, $files);

        // Further validation of the XML file is done in validate_xml_file(String $draftid)).
        
        // Don't allow the teacher to require more attachments than they allow; as this would
        // create a condition that it's impossible for the student to meet.
        if ($data['attachments'] != -1 && $data['attachments'] < $data['attachmentsrequired'] ) {
            $errors['attachmentsrequired']  = get_string('mustrequirefewer', 'qtype_selfassess');
        }
        
        // Validate the uploaded xml file.
        $xmlreturn = $this->validate_xml_file($data['xmlfile']);
        if (!empty($xmlreturn)) {
            $errors['xmlfile'] = $xmlreturn;
        }
        
        return $errors;
     }
 
     
    /**
     * Retrieve file data before validation is finished.
     *
     * @param string $draftid string reading the complete XML file data.
     * @return string empty
     */
    protected function validate_xml_file(String $draftid) {        
        global $USER;
        // We can not use moodleform::get_file_content() method because 
        // we need the content before the form is validated.
        if (!$draftid) {
            return get_string('noxmlfile', 'qtype_selfassess');
        }
        $fs = get_file_storage();
        $context = context_user::instance($USER->id);
        if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
            return get_string('noxmlfile', 'qtype_selfassess');
        }
        $file = reset($files);
        
        if (!$file) {
            return get_string('noxmlfile', 'qtype_selfassess');
        } else {
            // Replaces any possible DTD with a hardcoded string
            // we do not allow changes in the DTD.
            $dtd = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE selfassess [
<!ELEMENT selfassess (title?, question?, instruction, solution?, max_score?, answer_tree, decision_tree, feedbacktexts)>
<!ELEMENT title (#PCDATA)>
<!ELEMENT question (#PCDATA)>
<!ELEMENT instruction (text, link?)>
<!ELEMENT solution (#PCDATA)>
<!ELEMENT max_score (#PCDATA)>
<!ELEMENT answer_tree (answ)+>
<!ELEMENT answ (answer, grade)+>
<!ELEMENT answer (#PCDATA)>
<!ELEMENT grade (#PCDATA)>
<!ELEMENT decision_tree (rule)+>
<!ELEMENT rule (#PCDATA)>
<!ELEMENT feedbacktexts (feedback)+>
<!ELEMENT feedback (text, link*)+>
<!ELEMENT text (#PCDATA)>
<!ELEMENT link (linkref, linktext?)>
<!ELEMENT linkref (#PCDATA)>
<!ELEMENT linktext (#PCDATA)>
<!ATTLIST rule
        clicked CDATA #REQUIRED
        fbid CDATA #REQUIRED>
<!ATTLIST feedback
        fbid CDATA #REQUIRED>
]>';
        
            $xmlstr = $file->get_content();
            $xmlcontentstart = stripos($xmlstr, '<selfassess>');
            $xmlcontentstop = stripos($xmlstr, '</selfassess>');
            $substring = substr($xmlstr, $xmlcontentstart, $xmlcontentstop-$xmlcontentstart+strlen('</selfassess>'));
            $finalstr = $dtd . "\n\n" . $substring;
            
            // Verify correct xml syntax.
            libxml_use_internal_errors(true);
            $dom = new DOMDocument;
            $dom->loadXML($finalstr);
            if (!$dom->validate()) {
                $xmlerror = "";
                foreach(libxml_get_errors() as $err) {
                    $xmlerror .= '<br/>' . $err->message;
                }
                return get_string('incorrectxmlfile', 'qtype_selfassess', $xmlerror);
            }
            
            $xml ="";
            $xml = simplexml_load_string($substring);
            
            if ($xml->decision_tree->rule->count() == 0 ) {
                return get_string('nodecisiontree', 'qtype_selfassess');
            }
            
            if ($xml->feedbacktexts->feedback->count() == 0 ) {
                return get_string('nofeedback', 'qtype_selfassess');
            }
            
            // There have to be at least 2 answers that may be chosen.
            if ($xml->answer_tree->answ->count() <= 1) {
                return get_string('notenoughanswers', 'qtype_selfassess', '2');
            }
            
            // Grades have to be added up.
            $totalgrades = 0.0;
            $answgrade = 0.0;
            for ($i = 0; $i < $xml->answer_tree->answ->count(); $i++) {
                $answgrade = ($xml->answer_tree->answ[$i]->grade)[0];
                if ($answgrade > 0) {
                    $totalgrades += floatval($answgrade);    // Add up floats.
                 }
            }

            // See also: https://www.php.net/manual/de/function.abs.php
            // https://www.php.net/manual/en/language.types.float.php.
            if (abs($totalgrades - 100.00) >= 0.01) {
                return get_string('errfractionsaddwrong', 'qtype_selfassess', $totalgrades);
            }
        }
        
        return "";    
    }
    
        
    /**
     * Provide the name of this question type.
     * 
     * @return string question type
     */
    public function qtype() {
        return 'Selfassess';
    }
}
