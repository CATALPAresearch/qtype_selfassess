SelfAssess Question Type
----------------------------

This is a question type plugin for Moodle self-assessments was created by Karin Steinkohl and Marc Burcharct, 2020 FernUniversität Hagen. It is a prototypical software that is still under development. The plugin is licensed under GNU GPL-3.

In an optional first step a self composed answer to the question is required; in a second step checkboxes with pre-formulated statements must be chosen. Based on the respective selection an appropriate feedback will be displayed. A retry and adaption of the given response is possible as long as the question is not completely correct answered.

This version can be used with Moodle 3.5 version and above.

## Installation

* Change into the directory `cd <my-moodle>/question/type`
* `git clone https://github.com/D2L2/qtype_selfassess`
* Rename the resulting folder into `selfassess` if necessary
* Open the administration panel of moodle in order to install the plugin.

## Description

### Editing Interface to the Teacher:
The menu shows the generic options of MOODLE for specifying questions, e.g. "Question name", "Question text", "General feedback", "Information for graders". However, the following menu fields are specific for this question type:

A selection menu to specify the number of attachments to be uploaded that contain a self composed solution. By allowing a self-composed answer the teacher may restrict the upload to a minimal and/or maximal number of documents and/or to specific file types; the default for uploaded files is 1, the file type default is PDF or JPG.

The other question specific attributes e.g. pre-formulated statements for the checkboxes as well as the associated positive or negative fraction percentage, specific feedback texts and a short solution text have to be included in the XML file (see also the section 'XML File').

A file picker area to upload the required XML file. See the example files in the folder `examples`. This file is used to generate a directive for the student, an answer specific feedback as well as a short sample solution. For details on this XML file see below in section 'XML File'.


### XML File
The uploaded XML file must comply with the following document type definition (DTD) that is hardcoded implemented but is nethertheless to be included in the XML file mandatorily:

`<!DOCTYPE selfassess [`  
`<!ELEMENT selfassess (instruction, solution?, answer_tree, decision_tree, feedbacktexts)>`  
`<!ELEMENT instruction (text, link?)>`    
`<!ELEMENT solution (#PCDATA)>`  
`<!ELEMENT answer_tree (answ)+>`  
`<!ELEMENT answ (answer, grade)+>`  
`<!ELEMENT answer (#PCDATA)>`  
`<!ELEMENT grade (#PCDATA)>`  
`<!ELEMENT decision_tree (rule)+>`  
`<!ELEMENT rule (#PCDATA)>`  
`<!ELEMENT feedbacktexts (feedback)+>`  
`<!ELEMENT feedback (text, link+)+>`  
`<!ELEMENT text (#PCDATA)>`  
`<!ELEMENT link (linkref, linktext?)>`
`<!ELEMENT linkref (#PCDATA)>`
`<!ELEMENT linktext (#PCDATA)>`  
`<!ATTLIST rule`  
`   ` `clicked CDATA #REQUIRED`  
`   ` `fbid CDATA #REQUIRED >`  
`<!ATTLIST feedback`  
`  ` `fbid CDATA #REQUIRED >`  
`]>`

An XML file with a divergent DTD throws an error.

So, this XML file contains the specific features instruction, an optional solution, an answer-tree, a decision-tree and feedbacktexts.

The required instruction contains a simple directive for the student as well as an optional link to an external source. If this link is not given there is the possibility via the solution to provide a short sample solution that the student may see by clicking the appropriate self-generated link.  If there are both - a link within the instruction and a solution - the link is preferred and overwrites the text within the solution.

For the second step of the question with checkboxes an answer-tree with pre-formulated answers have to be included here as well as the associated positive or negative fraction percentage for every answer.

In order to generate a specific feedback well adapted to the selected checkboxes the teacher may provide an arbitrary number of feedback texts. Each feedback text may be associated additionally with one or more links to where more information can be found. 

The teacher has to specify a decision-tree that is a set of matching rules defining which feedback text applies to the selected checkboxes. Every matching rule contains for every checkbox the character '1', '0' or '.' in the ordering of the checkboxes. '1' matches if the choice has been clicked, '0' matches if the choice has not be clicked and '.' means the choice is not valuated. 

For example:  
Assuming a multichoice checkbox with 4 statements where all answers are correct.

The instruction area may appear in two different ways:
1) Just a plain directive:  
`<instruction>`  
`   <text>Choose one of the possibilities:</text>`  
`</instruction>` 

2) A directive and a link:  
`<instruction>`    
`   <text>Choose one of the possibilities:</text>`  
`     <link>`  
`         <linkref>https://www.fernuni-hagen.de</linkref>`  
`         <linktext>FernUniversität Hagen</linktext>`  
`     </link>`  
`</instruction>`  


The answer_tree is formatted as follows:

`<answer_tree>`    
`   <answ>`     
`     <answer>Antwort 1</answer>`  
`     <grade>10</grade>`  
`   </answ>`  
`   <answ>`   
`     <answer>Antwort 2</answer>`  
`     <grade>20</grade>`  
`   </answ>`   
`   ...`  
`</answer_tree>`  

The decision_tree is formatted as follows:

`<decision_tree>`    
`<rule clicked="1111" fbid="0"/>`    
`   ` *Feedback text with reference '0' is displayed when all answers are selected.*    
`<rule clicked=".0.." fbid="1"/>`     
`   ` *Feedback text with reference '1' is displayed when checkbox 2 is not selected; the other selections are ignored.*    
`<rule clicked="1110" fbid="2"/>`    
`   ` *Feedback text with reference '2' is displayed when checkbox 1, 2 and 3 are selected, checkbox 4 is not selected.*    
`...`    
`</decision_tree>`    

The matching fields in the rules have not to be mutual exclusive. If more than one field match all reference texts are displayed.   
The referenced texts have also not to be mutual exclusive. Several matches may result in the same feedback text.

In the feedback text area additionally links to further information may be specified.

For example:

`<feedbacktexts>`  
`<feedback fbid="0">`  
`   ` `<text>`Completely correct response. Hurray!`</text>`  
`   ` `<link>`  
`   ` `</link>`  
`</feedback>`  
`<feedback fbid="1">`  
`   ` `<text>`The 2nd checkbox has been selected wrongly. See the following link for further information.`</text>`  
`   ` `<link>`  
`       ` `<linkref>https://www.wikipedia.org</linkref>`  
`       ` `<linktext>Cache</linktext>`  
`   ` `</link>`   
`</feedback>`  
`...`  
`</feedbacktexts>`  


### Grading
Every statement (that is equivalent with an answer field in the XML file) is combined with a positive or negative fraction percentage. The  positive fraction percentage must add up to 100% (see also the section 'XML File'). Grading is done by summarizing the fractions of every selected statement. However, the final grade can not be less than 0% even though the calculation may result in a negative sum.


### Interface to the Student:
The upload of a self composed answer is mandatory. Once the required number of files have been uploaded the multichoice part is shown.

As soon as the student has submitted the self composed answer as well as his or her choices the specific feedback is displayed based on the chosen checkboxes (see also the section 'XML File'). Now the student - when  he or she likes to do so - has the opportunity to adapt the uploaded file with his/her solution and retry the multichoice part of the question.

Furthermore the student may click on a link to a sample solution when provided by the teacher after uploading his or her solution.  




