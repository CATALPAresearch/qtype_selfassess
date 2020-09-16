<?php

global $CFG;
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');


$uniqueid     = required_param('uniqueid',  PARAM_INT);
$cmid         = required_param('cmid', PARAM_INT);
$retattempt   = required_param('retattempt', PARAM_INT);
$slot         = required_param('slot', PARAM_INT);

$quba = question_engine::load_questions_usage_by_activity($uniqueid);

$question = $quba->get_question($slot);
$redirect = "";

if ($cmid != 0 ) {   // Not in preview mode.
    $event = qtype_selfassess\event\solution_viewed::create(array(
        'objectid' => $question->id,
        'context' => context_module::instance($cmid),
        'other'    => array ('retattempt' => $retattempt, 'cmid' => $cmid)
    ));
    $event->trigger();
}


if (!empty(($question->instructionlinkref).trim())) {
    $redirect = sprintf("%s%s%s\n", '<meta http-equiv="refresh" content="5; URL=', $question->instructionlinkref.trim(), '">');
}


printf("%s\n", '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
printf("%s\n\n", '<html xmlns="http://www.w3.org/1999/xhtml">');
printf("%s\n", '<head>');
printf("%s\n", '<meta content="text/html; charset=utf-8" http-equiv="content-type" />');
printf("%s\n", $redirect);
printf("%s%s%s\n", '<title>', get_string('samplesolution', 'qtype_selfassess'), '</title>');
printf("%s\n\n", '</head><body>');

if (!empty($redirect)) {
    printf("%s%s%s\n\n", '<h3>Sie werden weitergeleitet ... Sollten Sie nicht weitergeleitet werden, klicken Sie bitte <a href="',
        $question->instructionlinkref.trim(), '">hier</a></h3>');
} else {
    printf("%s%s%s\n\n", '<h1>', get_string('samplesolution', 'qtype_selfassess'), '</h1>');
    
    printf("%s%s%s\n\n", '<h3>', get_string('questiontextheader', 'qtype_selfassess'), '</h3>');
    
    printf("%s\n", "<div style='padding-left: 100px; padding-right: 100px'><div style='font-size: 18px; background: #f5f5f5'>");
    printf("%s\n", $question->questiontext);
    printf("%s\n", "</div></div>");
    
    printf("%s%s%s\n\n", '<h3>', get_string('solutionheader', 'qtype_selfassess'), '</h3>');
    
    printf("%s\n", "<div style='padding-left: 100px; padding-right: 100px'><div style='font-size: 18px; background: #f5f5f5'>");
    printf("%s\n", $question->solution);
    printf("%s\n", "</div></div>");
}


printf("\n\n%s\n", '</body></html>');

