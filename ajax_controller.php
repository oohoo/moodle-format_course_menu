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
 * This script handles the various ajax functionality used by this course format
 */

//ERRORS ARE SUPRESSED - Change if development required or issues arrise
error_reporting(0);

require_once('../../../config.php');
require_once($CFG->dirroot . '/course/format/course_menu/elements/course_menu_element.php');
require_once('lib.php');

//Always returning json
//header('Content-type: application/json');
$json_string = required_param('course_menu_json', PARAM_RAW);//json data used in the ajax calls
$operation = required_param('operation', PARAM_TEXT);//the ajax operation to be completed
$json = json_decode($json_string);//convert json data to an object

//set page url
$PAGE->set_url("/ajax_controller.php");

// On failure we will always return an empty json element
try {
    require_course_login($json->course, false, NULL, false, true);
} catch (Exception $e) {
    $empty = array();
    return json_encode($empty);
}

//get course menu renderer for outputting html for ajax return
$renderer = $PAGE->get_renderer('format_course_menu');

//determine what to do during this ajax call
switch($operation) {
    case "update"://update/save current layout of the course menu
      cmf_update($json);
      $renderer->course_menu_display_layout_elements($COURSE);//output HTML of saved layout
        break;
    
    case "get_main_header"://output header
        $renderer->course_menu_main_header_elements();
        break;
    
    //operation provided was invalid... do nothing!
    default:
        break;
}

/**
 * Given a layout id (course menu instance) and a list of valid element ids, it will
 * remove all elements that do not contain an ID in the given id.
 * 
 * @global moodle_database $DB
 * @param int $layout_id Course menu instance id
 * @param type $element_ids IDs of elements that will remain in the course menu instance after this function has run
 */
function remove_extraneous_layouts($layout_id, $element_ids) {
    global $DB;

    $ids = implode(",", $element_ids); //make a list of ids to be kept

    if ($ids == null || $ids == "") {
        $ids = "0";
    }

    //custom sql to get lists of elements currently tied to the course menu instance, but not in our array of valid elements
    $sql = "SELECT * FROM {course_menu_element_position} WHERE id NOT IN ($ids) AND COURSE_MENU_ID=? ";



    $extr_recs = $DB->get_records_sql($sql, array($layout_id));

    //for all the elements not in valid ids array, but in database - remove them!
    foreach ($extr_recs as $record) {
        $DB->delete_records($record->element_table, array('id' => $record->element_table_id));
        $DB->delete_records('course_menu_element_position', array('id'=>$record->id));
    }
    
}

/**
 * A function that itterates through all the elements of the course menu and proceeds to
 * update/save them.
 * 
 * @param type $json
 */
function cmf_update($json) {

    $layout_id = get_layout_id($json);//get course menu instance id
    
    $elements = $json->elements;//get all elements in layout
    
    //itterate through the elements and update them
    //for each one save the id updated/inserted
    $element_ids = [];
    foreach($elements as $element) {
        $id = course_menu_element::update_element($element, $layout_id);//update/insert element
        array_push($element_ids, $id);//save id of saved id
    }
    
    //remove all elements currently in the database, but were not saved - which
    //means they have been removed! Therefore they are deleted!.
    remove_extraneous_layouts($layout_id, $element_ids);
    
}

/**
 * Retrieves the ID of the course menu instance from the submitted json data
 * 
 * @global moodle_database $DB
 * @param object $json
 */
function get_layout_id($json) {
    global $DB;
    
    //attempt to get course menu instance from course
   $layout = $DB->get_record('course_menu', array('course'=> $json->course));
   
   
   if(!$layout)//if layout object doesn't exist, create the instance and return id
   {
       $record = new stdClass();
       $record->course = $json->course;
       $layout_id = $DB->insert_record('course_menu', $record);
   } else//return the instance id
       $layout_id = $layout->id;
   
   
   return $layout_id;
   
}

/**
 * Generates an empty json string
 * @return string
 */
function empty_json() {
    $empty = array();
    return json_encode($empty);
}

?>
