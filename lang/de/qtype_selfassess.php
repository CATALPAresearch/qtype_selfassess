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
 * Strings for component 'qtype_selfassess', language 'de', branch 'MOODLE_35_STABLE'
 *
 * @package    qtype
 * @subpackage selfassess
 * @copyright  2020 FernUniversität Hagen 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['acceptedfile'] = 'Laden Sie eine XML-Datei<br /> hoch, die das Feedback enthält.';
$string['acceptedfiletypes'] = 'Akzeptierte Dateitypen';
$string['acceptedfiletypes_help'] = 'Akzeptierte Dateitypen sind vom Format: PDF, JPG';
$string['allowattachments'] = 'Anhänge sind erlaubt';
$string['answernumbering'] = 'Welches Aufzählungszeichen wünschen Sie?';
$string['answernumbering123'] = '1., 2., 3., ...';
$string['answernumberingabc'] = 'a., b., c., ...';
$string['answernumberingABCD'] = 'A., B., C., ...';
$string['answernumberingiii'] = 'i., ii., iii., ...';
$string['answernumberingIIII'] = 'I., II., III., ...';
$string['answernumberingnone'] = 'Kein Aufzählungszeichen';
$string['answernumbering_desc'] = 'Das voreingestellte Aufzählungszeichen';
$string['attachmentsoptional'] = 'Anhänge sind optional';
$string['attachmentsrequired'] = 'Anhänge sind zwingend erforderlich';
$string['attachmentsrequired_help'] = 'Diese Option spezifiziert die Anzahl der Anhänge, die mindestens für eine Antwort nötig sind, um eine Bewertung zu erhalten.';
$string['clickbuttonafterselection'] = 'Um nach dem Hochladen und/oder nach Ihrer Auswahl fortzufahren, klicken Sie den Button ';
$string['clickbuttonafteruploading'] = 'Um nach Ihrer Auswahl fortzufahren, klicken Sie den Button ';
$string['checkxmlfile'] = 'Zwingend erforderliche Paramenter fehlen in der XML-Datei. Überprüfen Sie die Document Type Definition.';
$string['choiceno'] = 'Auswahlmöglichkeit(en) {$a}';
$string['correctanswer'] = 'Korrekt';

$string['deletedchoice'] = 'Die Auswahl steht nicht mehr zur Verfügung';
$string['distractor'] = 'Inkorrekt';
$string['errfractionsaddwrong'] = 'Die positiven Anteile der Bewertung, die Sie gewählt haben, addieren sich nicht zu 100%.<br />Sie addieren sich zu {$a}%.';
$string['errgradesetanswerblank'] = 'Die Bewertung ist gesetzt, aber die zugehörige Antwort ist leer.';
$string['eventquestionattemptperformed'] = 'Fragenbeantwortung ist durchgeführt';
$string['eventquestionattemptretried'] = 'Fragenbeantwortung erneut durchgeführt';
$string['eventquestionviewed'] = 'Frage angeschaut';
$string['eventsolutionuploaded'] = 'Lösung hochgeladen';
$string['eventsolutionviewed'] = 'Lösung angeschaut';
$string['graderinfo'] = 'Information für die Bewerter';
$string['graderinfoheader'] = 'Bewertungsinformation';
$string['included'] = 'Korrekt';
$string['incorrectxmlfile'] = 'Der XML-Code in der hochgeladenen Datei ist inkorrekt: {$a}';
$string['leaveuploadedfile'] = 'Wollen Sie Ihre Lösung behalten wollen, klicken Sie auf den Button: ';
$string['mayupload'] = 'Anzahl der maximal möglichen Lösungsvorschläge: ';
$string['mustattach'] = 'Mindestens 1 Anhang ist erforderlich.';
$string['mustrequire'] = 'Mindestens 1 Anhang ist erforderlich.';
$string['mustrequirefewer'] = 'Sie dürfen nicht mehr Anhänge hinzufügen als erlaubt sind.';
$string['nodecisiontree'] = 'Kein Entscheidungsbaum in der übergebenden XML-Datei vorhanden';
$string['nofeedback'] = 'Keine Rückmeldung in der übergebenden XML-Datei vorhanden';
$string['nlines'] = '{$a} Zeilen';
$string['nonexistentfiletypes'] = 'Die folgenden Dateitypen werden nicht erkannt: {$a}';
$string['notenoughfeedback'] = 'Dieser Fragentyp benötigt ein Feedback pro Antwort.';
$string['notenoughanswers'] = 'Dieser Fragentyp benötigt mindestens {$a} Auswahlmöglichkeit(en).';
$string['noxmlfile'] = 'Das Hochladen einer XML-Datei, die das Feedback enthält, ist erforderlich.';
$string['partiallycorrect'] = 'Ihre Lösung ist teilweise korrekt. Leider gibt es dazu keine spezifische Rückmeldung. Überdenken Sie Ihre Lösung und versuchen Sie es erneut.';
$string['pluginname'] = 'Selfassess'; // Erscheint in den Logs und in der Liste der Fragentypen.
$string['pluginnameadding'] = 'Hinzufügen eines Multiple Choice Selfassess Fragen-Plugins';
$string['pluginnameediting'] = 'Editieren eines Multiple Choice SelfAssess Fragen-Plugins';
$string['pluginnamesummary'] = 'Erlaubt zuerst das Hochladen einer oder mehrerer Dateien, bevor aus einer vordefinierten Liste eine oder mehrere Optionen ausgewählt werden können. Diese Auswahlmöglichkeit(en) werden selbständig beurteilt, indem sie ein automatisch erstelltes Feedback geben.';
$string['pluginname_help'] = 'Hochladen keiner, einer oder mehrerer Dateien, bevor Optionen gewählt werden, um ein automatisch erstelltes Feedback zu erhalten.';
$string['pluginname_link'] = 'question/type/selfassess';
$string['privacy:metadata'] = 'Das SelfAssess Multiple Choice Self Assess Plugin speichert keine persönlichen Daten.';
$string['questiontextheader'] = 'Fragentext: ';
$string['receivefeedback'] = 'Feedback erhalten';
$string['requestretry'] = 'Ist die Lösung noch nicht komplett richtig und vollständig, können Sie einen erneuten Versuch anfordern. Wenn Sie einen neuen Versuch wollen, klicken Sie auf den Button: ';
$string['retry'] = 'Erneut versuchen';
$string['samplesolution'] = 'Musterlösung';
$string['selectatleastonechoice'] = 'Wählen Sie mindestens eine Möglichkeit.';
$string['selectchoice'] = 'Wählen Sie mindestens eine Möglichkeit. Zum Senden klicken Sie den Button ';
$string['shuffleanswers'] = 'Auswahlmöglichkeit(en) mischen?';
$string['shuffleanswers_desc'] = 'Standardmäßig werden bei jedem Versuch die Optionen zufällig gemischt.';
$string['shuffleanswers_help'] = 'Wenn diese Option aktiviert ist, wird bei jedem Versuch die Anordnung der Antworten zufällig gemischt. Voraussetzung ist, dass "Shuffle within questions" in den Aktivitäteneinstellungen ebenfalls angeschaltet ist.';
$string['solutionheader'] = 'Antwortvorschlag: ';
$string['tiptouploadedfile'] = 'Wenn Sie Ihre hochgeladene Lösung überarbeiten wollen, klicken Sie die hochgeladene Datei an und entfernen Sie diese anschließend. Nun können Sie eine neue Datei mit Ihrer Lösung hochladen.';
$string['uploadfile'] = 'Hochladen Ihrer Dateiauswahl';
$string['uploadatleast'] = 'Mindestanzahl der Dateien zum Hochladen:  ';
$string['uploadyoursolutionfirst'] = 'Bitte laden Sie zuerst eine Lösung hoch, bevor Sie Optionen auswählen.';
$string['yoursolution'] = 'Im Folgenden finden Sie die hochgeladene Datei mit Ihrer Lösung.';
$string['xmlfile'] = 'Hochladen einer XML-Datei';
$string['xmlfile_help'] = 'Hochladen einer XML-Datei, die Feedbackinformation enthält.';
