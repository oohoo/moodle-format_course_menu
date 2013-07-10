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
/*
 * An object that represents an activity or resource placeholder in the course menu layout
 */

class course_menu_cm {

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
        $layout_cm = $DB->get_record('course_menu_element_cm', array('id' => $this->element->element_table_id));

        //the id for the general element table (course_menu_position_element)
        $parameters['dataid'] = $this->element->id;

        //get the html representation of this element
        $html = $this->get_cm_element_display($COURSE, $layout_cm->course_module_id, $this->is_full(), $parameters);

        //return html
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
    public static function get_cm_element_display($course, $cmid, $is_full, array $parameters) {
        global $PAGE;

        $html = ''; //html to be output
        //get core render for outputting the elements
        $course_renderer = $PAGE->get_renderer('core', 'course');

        //get info for all the mobs for this course
        $modinfos = get_fast_modinfo($course->id);

        //get the specific information for the current CM that corresponds to this element
        $mod = $modinfos->get_cm($cmid);



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
        if (!$is_full && isset($parameters['row_cell_count']) && $parameters['row_cell_count'] > 0) {//cell count avaliable & not full
            $cells = $parameters['row_cell_count'];
            $percent = 60 / $cells; //divide cell by an equal portion of 60% of total size
            $style = "width: $percent%"; //set css style
        }

        //create horiz li wrapper
        $html .= html_writer::start_tag('li', array('style' => $style, 'class' => $class, 'cmid' => $cmid, 'data_id' => $dataid, 'data_type' => 'course_menu_element_cm'));

        //content div
        $html .= html_writer::start_tag('div', array('class' => 'cmf_content_wrapper'));

       
        //Not Pretty... but required to support moodle 2.4
        if(moodle_major_version() >= '2.5')
            $html .= $course_renderer->course_section_cm_name($mod);
        else
           $html .= course_menu_cm::moodle_2_4_cm_display($mod, $course);
        
        //end content div
        $html .= html_writer::end_tag('div');

        //end horiz li wrapper
        $html .= html_writer::end_tag('li');


        //return html representation of element
        return $html;
    }
    
    /**
     * Provides legacy support for moodle 2.4
     * Prints the logo and link for a given module in a given course!
     * 
     * 
     * @param cm_info $mod
     * @param object $course
     * @return string html of logo and link for a module
     */
    private static function moodle_2_4_cm_display($mod, $course) {

        // Get data about this course-module
        list($content, $instancename) =
                get_print_section_cm_text($mod, $course);

        $html = '';
        $onclick = htmlspecialchars_decode($mod->get_on_click(), ENT_QUOTES);

        if ($url = $mod->get_url()) {
            // Display link itself.
            $activitylink = html_writer::empty_tag('img', array('src' => $mod->get_icon_url(),
                        'class' => 'iconlarge activityicon', 'alt' => $mod->modfullname)) .
                    html_writer::tag('span', $instancename, array('class' => 'instancename'));
            $html .= html_writer::link($url, $activitylink, array('class' => '', 'onclick' => $onclick));

            // If specified, display extra content after link.
            if ($content) {
                $html .= html_writer::tag('div', $content, array('class' =>
                            trim('contentafterlink ' . $textclasses)));
            }
        }

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
     * Updates/Saves an CM element based on the provided submitted data, and the id
     * of specialized table to save to.
     * 
     * The $submit_data must contain:
     *      cmid: course module id
     *      data_type: specialized table to save the element data to
     *      
     * 
     * @global moodle_database $DB
     * @param object $submit_data An object containing the specialized information for saving the element
     * @param int $sub_element_id id of the element in specialized element, 0 if doesn't exist yet
     * @return int id of the save/updated record from the specialized table
     */
    public static function update($submit_data, $sub_element_id = 0) {
        global $DB;

        //create record to be saved
        $record = new stdClass();

        //the id of the CM that is associated with this element
        $record->course_module_id = $submit_data->cmid;

        if ($sub_element_id == 0) {//cm element doesn't exist
            $sub_element_id = $DB->insert_record($submit_data->data_type, $record);
        } else {//update - cm element already exists
            $record->id = $sub_element_id;
            $DB->update_record($submit_data->data_type, $record);
        }

        //return record id of cm element (from specialized table - cm_element
        return $sub_element_id;
    }

}

?>
