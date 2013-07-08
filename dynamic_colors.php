<?php

/**
 * *************************************************************************
 * *                                Chairman                              **
 * *************************************************************************
 * @package mod                                                          **
 * @subpackage chairman                                                  **
 * @name Chairman                                                        **
 * @copyright oohoo.biz                                                  **
 * @link http://oohoo.biz                                                **
 * @author Dustin Durand                                                 **
 * @license                                                              **
 * http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later              **
 * *************************************************************************
 * ************************************************************************ */

/**
 * This script that dynamically generates CSS for adding colour to course menu format
 * based on the course format settings for course menu format.
 */

//Set header to let the browser know its actually a css file, despite being a php extension
header("Content-type: text/css", true);

require_once('../../../config.php');
require_once('lib.php');

defined('MOODLE_INTERNAL') || die();
global $DB;

//course id is required to pull course menu instance specific settings
$courseid = required_param('id', PARAM_INT);

//load horizontal row background color based on instance settings
$option = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'cmf_row_horizontal_color'));

if ($option)
    echo <<<CSS

ul.course_menu_target_row, ul.course_menu_target_row_full {
    background-color: $option->value;
}

CSS;

//load background color based on instance settings
$option = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'cmf_backgroundcolor'));
if ($option)
    echo <<<CSS

ul.course_menu_target {
    background-color: $option->value;
}

CSS;

//load header background color (single) based on instance settings
$option = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'cmf_headerbackgroundcolor'));
if ($option)
    echo <<<CSS

ul.course_menu_target_row   li.course_menu_cell[data_type=course_menu_element_header] {
    background-color: $option->value;
}

CSS;


//load header background color (full) based on instance settings
$option = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'cmf_headerfullbackgroundcolor'));
if ($option)
    echo <<<CSS

ul.course_menu_target_row_full  li.course_menu_cell.course_menu_full_cell[data_type=course_menu_element_header] {
    background-color: $option->value;
}

CSS;


//load cm background color (single) based on instance settings
$option = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'cmf_cmbackgroundcolor'));
if ($option)
    echo <<<CSS

ul.course_menu_target_row   li.course_menu_cell[data_type=course_menu_element_cm] {
    background-color: $option->value;
}

CSS;

//load cm background color (full) based on instance settings
$option = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'cmf_cmfullbackgroundcolor'));
if ($option)
    echo <<<CSS

ul.course_menu_target_row_full  li.course_menu_cell.course_menu_full_cell[data_type=course_menu_element_cm] {
    background-color: $option->value;
}

CSS;
?>
