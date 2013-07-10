<?php

/**
 * 
 * <course_menus>
    * <cm_elements></cm_elements>
    * <header_elements></header_elements>
        * <course_menu id="1">
            * <course>2</course>
            * <header></header>
            * <elements></elements>
        * </course_menu>
 * </course_menus>
 * 
 * 
 */
class restore_format_course_menu_plugin extends restore_format_plugin {

    /**
     * Returns the paths to be handled by the plugin at course level
     */
    protected function define_course_plugin_structure() {
        $paths = array();

        $base = '/course'; //base mapping for format

        $cmenu_base = "$base/course_menus"; //base mapping for course_menu activity
        $cmenu = "$cmenu_base/course_menu";
        $element = "$cmenu/elements/element";
        $cm_element = "$cmenu_base/cm_elements/cm_element";
        $header_element = "$cmenu_base/header_elements/header_element";


        
        $paths[] = new restore_path_element('cm_element', $cm_element);
        $paths[] = new restore_path_element('header_element', $header_element);
        $paths[] = new restore_path_element('element', $element);
        $paths[] = new restore_path_element('course_menu', $cmenu);

        return $paths;
    }

    /**
     * Called after this runs for a course.
     */
    function after_execute_course() {
        // Need to restore file
        $this->add_related_files('format_course_menu', 'cmf_header', null);
       
    }
    
    /*
     * Call after course has been restored
     */
    function after_restore_course() {
         $this->update_cm_references();
    }
    
    
    private function update_cm_references() {
        global $DB;
        
        $course_menu_instance = $DB->get_record('course_menu', array('course'=> $this->task->get_courseid()));
        $elements = $DB->get_records('course_menu_element_position', array('course_menu_id'=> $course_menu_instance->id, 'element_table'=>'course_menu_element_cm'));
        
        foreach($elements as $element) {
           $cm_element = $DB->get_record('course_menu_element_cm', array('id'=> $element->element_table_id));
           
           $cm_element->course_module_id = $this->get_mappingid('course_module', $cm_element->course_module_id);

           $DB->update_record('course_menu_element_cm', $cm_element);
        }
        
    }

    public function process_course_menu($data) {
        global $DB;

        // Get data record ready to insert in database
        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $oldid = $data->id;

        // See if there is an existing record for this course
        $existingid = $DB->get_field('course_menu', 'id', array('course' => $data->course));


        if ($existingid) {
            $data->id = $existingid;
            $id = $DB->update_record('course_menu', $data);
        } else {
            $id = $DB->insert_record('course_menu', $data);
        }

        $this->set_mapping('course_menu', $oldid, $id);
    }

    public function process_element($data) {
        global $DB;

        // Get data record ready to insert in database
        $data = (object) $data;
        $oldid = $data->id;
        $data->course_menu_id = $this->get_mappingid("course_menu", $data->course_menu_id);

        if ($data->element_table === 'course_menu_element_cm')
            $data->element_table_id = $this->get_mappingid('course_menu_element_cm', $data->element_table_id);
        else
            $data->element_table_id = $this->get_mappingid('course_menu_element_header', $data->element_table_id);
        
        $new_id = $DB->insert_record('course_menu_element_position', $data);

        $this->set_mapping('course_menu_element_position', $oldid, $new_id);
    }

    public function process_cm_element($data) {
        global $DB;

        // Get data record ready to insert in database
        $data = (object) $data;
        $oldid = $data->id;
        $data->courseid = $this->task->get_courseid();

        $new_id = $DB->insert_record('course_menu_element_cm', $data);


        $this->set_mapping('course_menu_element_cm', $oldid, $new_id);
    }
    
    public function process_header_element($data) {
        global $DB;

        // Get data record ready to insert in database
        $data = (object) $data;
        $oldid = $data->id;
        $data->courseid = $this->task->get_courseid();


        $new_id = $DB->insert_record('course_menu_element_header', $data);


        $this->set_mapping('course_menu_element_header', $oldid, $new_id);
    }

}

?>
