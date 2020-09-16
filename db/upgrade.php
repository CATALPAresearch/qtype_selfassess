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
 * selfassess question type upgrade code.
 *
 * @package    qtype_selfassess
 * @copyright  2020 FernUniversität Hagen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Convert the selfassess info and write it into the question.xml
 *
 * @param int $oldversion the old (i.e. current) version of Moodle
 */
function xmldb_qtype_selfassess_upgrade($oldversion) {
    global $CFG;
    
    // Automatically generated Moodle v3.5.0 release upgrade line.
    // Put any upgrade step following this.
    
    return true;
}


