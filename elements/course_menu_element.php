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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/course_menu/elements/course_menu_header.php');
require_once($CFG->dirroot . '/course/format/course_menu/elements/course_menu_cm.php');

/**
 * An element that represents a text based header in the course menu header
 *
 * @author dddurand
 */
class course_menu_element {

    private $delegate; //A specialized object that provides core functionality for each type of element
    private $element; //The course_menu_element_position record that represents the element
    //Valid specialized types for each element ex: cm, or header
    //these identifiers also are the table names thats contained the specialized information for each type of element
    public static $data_types = array('course_menu_element_cm', 'course_menu_element_header');

    /**
     * General Constructor
     * 
     * @param object $element course_menu_element_position db record
     */
    public function __construct($element) {
        //determine the delegate object type that will handle the core functionality of this element
        //the delegate type is determined by the table name present in the course_menu_element_position record provided
        $delegate = course_menu_element::get_delegate($element->element_table);

        //create delegate
        $this->delegate = new $delegate($element);

        //save element just incase we need it later
        $this->element = $element;
    }

    /**
     * Returns the html output for the provided element
     * 
     * Parameters:
     *      dataid - The id from the course_menu_element_* specialized element table ex: course_menu_element_cm
     *      row_cell_count - The number of elements present in the current row
     * 
     * @param array $parameters A set of parameters that can effect the output of the HTML
     * @return string html representation of the current element
     */
    public function get_html_output(array $parameters) {

        //delegate generation of html to delegate
        return $this->delegate->get_html_output($parameters);
    }

    /**
     * Returns whether the current element is full or a single cell element
     * @return bool true on full cell, false if a single cell(multiple cells per row)
     */
    public function is_full() {
        return $this->delegate->is_full();
    }

    /**
     * Returns the row number for the current element
     * 
     * @return int The row number the current element is in
     */
    public function get_row() {
        return $this->element->position_row;
    }

    /**
     * Returns the position of this element in the current row
     * 
     * @return int Position of the element in row
     * 
     */
    public function get_position() {
        return $this->element->position_order;
    }

    /**
     * Updates/saves an element.
     * 
     * Saves general data to the course_menu_position_element, and specialized data
     * to its specific table
     * 
     * $submit_element_data MUST contain: data_type (the table that contains the specialized data)
     *                                    data_id: id for the record in the general table (course_menu_position_element)
     * 
     * @global moodle_database $DB
     * @param object $submit_element_data
     * @param int $layout_id id of the record for the general element table (course_menu_position_element)
     */
    public static function update_element($submit_element_data, $layout_id) {
        global $DB;

        //get the table for the specialized data of the element
        $data_type = $submit_element_data->data_type;

        //get the record id for the general element table
        $data_id = $submit_element_data->data_id;

        //if table name is not in acceptable array list, then bail
        //add a little security
        if (!in_array($data_type, course_menu_element::$data_types))
            return;

        //get general record for element
        $dbelement = $DB->get_record('course_menu_element_position', array('id' => $data_id));

        //if the element hasn't been saved yet, set the specialized table id to 0 (signifies doesn't exist yet)
        $sub_element_id = 0;
        if ($dbelement)
            $sub_element_id = $dbelement->element_table_id;

        //get the correct specialized delegate
        $delegate_type = course_menu_element::get_delegate($submit_element_data->data_type);

        //run update and get updated/inserted id from specialized table
        $element_sub = $delegate_type::update($submit_element_data, $sub_element_id);

        //record to be updated in general table
        $record = new stdClass();
        $record->position_row = $submit_element_data->position_row; //update row position
        $record->position_order = $submit_element_data->position_order; //update position in the row

        if ($dbelement) {//if element already exists
            $record->id = $data_id;
            $DB->update_record('course_menu_element_position', $record);
        } else {//element is new
            $record->course_menu_id = $layout_id; //id of the course menu instance
            $record->element_table = $data_type; //table for the specialized data
            $record->element_table_id = $element_sub; //record id in specialized table
            $record->is_full = $submit_element_data->is_full; //whether the element is full or single sized
            $data_id = $DB->insert_record('course_menu_element_position', $record);
        }

        //return id from general table
        return $data_id;
    }

    /**
     * Determines the name of the correct delegate to be used based on a given table name
     * 
     * @param string $table_identifier Name of the table - aka the identifier for the type of element
     * @return string The name of the delegate class to be used
     * @throws Exception If invalid identifier provided
     */
    private static function get_delegate($table_identifier) {

        switch ($table_identifier) {
            case "course_menu_element_cm"://if identifier is the CM element table
                return 'course_menu_cm';
                break;

            case "course_menu_element_header"://if identifier is the header element table
                return 'course_menu_header';

            //no matches... throw exception
            default:
                throw new Exception();
                break;
        }
    }

}

?>
