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
 * Description of course_menu_cm
 *
 * @author dddurand
 */
class course_menu_header {

    //The element record (course_menu_element_position) for a course menu element
    private $element;

    /**
     * General Constructor
     * 
     * @param object $element element record (course_menu_element_position) for a course menu element
     */
    public function __construct($element) {
        $this->element = $element;
    }

    /**
     * Returns the specific html output for the current element
     * 
     * @global moodle_database $DB
     * @global object $COURSE
     * @param array $parameters Additional paramters that used during displaying - ex: dataid
     * @return string the html of the current element
     */
    public function get_html_output(array $parameters) {
        global $DB, $COURSE;

        //get the specific information for this type of element
        $layout_header = $DB->get_record('course_menu_element_header', array('id' => $this->element->element_table_id));

        //the id for the general element table (course_menu_position_element)
        $parameters['dataid'] = $this->element->id;

        //get the html representation of this element
        $html = $this->get_cm_element_display($COURSE, $layout_header->text, $this->is_full(), $parameters);

        //return html representation of element
        return $html;
    }

    /**
     * Generates the specific html output for the current element
     * 
     * 
     * @global moodle_page $PAGE
     * @param object $course
     * @param int $cmid
     * @param bool $is_full
     * @param array $parameters
     * @return type
     */
    public static function get_cm_element_display($course, $text, $is_full, array $parameters) {

        $html = ''; //html to be output
        //class for the horizontal li
        $class = 'course_menu_cell';
        if ($is_full) {//if the cell needs to be a full, the class needs to be different
            $class .= ' course_menu_full_cell';
        }


        $dataid = "-1"; //if this element doesn't have an dataid provided (for the element table) use -1
        if (isset($parameters['dataid'])) {
            $dataid = $parameters['dataid']; //update dataid with provided dataid
        }


        /*
         * If the user has provided a count of the number of elements in the current row, and the cell/row isn't a full row - 
         * the cell output for this element will be limited to an equal portion of 60% of avaliable space
         * 
         */
        $style = '';
        if (!$is_full && isset($parameters['row_cell_count']) && $parameters['row_cell_count'] > 0) {
            $cells = $parameters['row_cell_count'];
            $percent = 60 / $cells;
            $style = "width: $percent%";
        }

        //create horiz li wrapper
        $html .= html_writer::start_tag('li', array('style' => $style, 'class' => $class, 'data_id' => $dataid, 'data_type' => 'course_menu_element_header'));

        //content div
        $html .= html_writer::start_tag('div', array('class' => 'cmf_content_wrapper'));

        //html for displaying the header as a header
        $html .= html_writer::start_tag('h4', array('class' => 'cmf_header_display'));
        $html .= $text;
        $html .= html_writer::end_tag('h4');

        //html for a textbox that allows editing of header
        $html .= html_writer::empty_tag('input', array('class' => 'cmf_header_textfield', 'type' => "text", 'value' => $text, 'style' => "display:none"));

        //end content
        $html .= html_writer::end_tag('div');

        //end horiz li
        $html .= html_writer::end_tag('li');


        //return html
        return $html;
    }

    /**
     * Returns whether the current element is considered full or a single cell in the row.
     * @return boolean
     */
    public function is_full() {
        if ($this->element->is_full == 1)//if the db element has is_full set to 1, its a full cell
            return true;
        else
            return false;
    }

    /**
     * Updates/Saves an header element based on the provided submitted data, and the id
     * of specialized table to save to.
     * 
     * The $submit_data must contain:
     *      text: text for the header
     *      data_type: specialized table to save the element data to
     *      
     * 
     * @global moodle_database $DB
     * @param object $submit_data An object containing the specialized information for saving the element
     * @param int $sub_element_id id of the element in specialized element, 0 if doesn't exist yet
     * @return int id of the save/updated record from the specialized table
     */
    public static function update($element, $sub_element_id = 0) {
        global $DB;

        //setup record to be saved/updated
        $record = new stdClass();
        $record->text = $element->text;

        if ($sub_element_id == 0) {//element doesn't exist yet
            $sub_element_id = $DB->insert_record($element->data_type, $record);
        } else {//element exists, update!
            $record->id = $sub_element_id;
            $DB->update_record($element->data_type, $record);
        }

        //return id from updating/saving specialized if from element_header
        return $sub_element_id;
    }

}

?>
