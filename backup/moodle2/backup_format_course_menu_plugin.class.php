<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of backup_theme_ou_plugin
 *
 * @author dddurand
 */
class backup_format_course_menu_plugin extends backup_format_plugin  {

    /**
     * Returns the theme information to attach to course element
     */
    protected function define_course_plugin_structure() {
        // Define virtual plugin element
        $plugin = $this->get_plugin_element(null, $this->get_format_condition(), 'course_menu');
        
        // Create plugin container element with standard name
        $course_menus = new backup_nested_element('course_menus');

        // Add wrapper to plugin
        $plugin->add_child($course_menus);

        //plugin --> course_menus ->course_menu ---> 1. cm_element, header_element, element
        $course_menu = new backup_nested_element('course_menu', array('id'), array(
            'course', 'header'));
        
        
        /**
         * CM Elements Table
         */
        $cm_elements= new backup_nested_element('cm_elements');
        $cm_element= new backup_nested_element('cm_element', array('id'), array(
            'course_module_id'));
        
        $cm_elements->add_child($cm_element);
        
        $course_menus->add_child($cm_elements);
        
        /**
         * Header Elements Table
         */
        $header_elements= new backup_nested_element('header_elements');
        $header_element= new backup_nested_element('header_element', array('id'), array(
            'text'));
        
        $header_elements->add_child($header_element);
        
        $course_menus->add_child($header_elements);
        
        /**
         * Generics Elements Table
         */
        $elements= new backup_nested_element('elements');
        $element= new backup_nested_element('element', array('id'), array(
            'course_menu_id', 'element_table', 'element_table_id', 'position_row', 'position_order', 'is_full'));
        
        $elements->add_child($element);
        
        $course_menu->add_child($elements);

        $course_menus->add_child($course_menu);
        
         // Use database to get source
        $course_menu->set_source_table('course_menu',
                array('course' => backup::VAR_COURSEID));
        
        
        
       $header_sql = "SELECT header.* FROM {course_menu_element_header} as header " .
        " LEFT JOIN {course_menu_element_position} as element ON element.element_table_id=header.id " .
        " LEFT JOIN {course_menu} as menu ON menu.id=element.course_menu_id " . 
        "WHERE element.element_table='course_menu_element_header' AND menu.course=?";

       // Use database to get source
       $header_element->set_source_sql($header_sql, array(backup::VAR_COURSEID));
        
       
        $cm_sql = "SELECT cm.* FROM {course_menu_element_cm} as cm " .
        " LEFT JOIN {course_menu_element_position} as element ON element.element_table_id=cm.id " .
        " LEFT JOIN {course_menu} as menu ON menu.id=element.course_menu_id " . 
        "WHERE element.element_table='course_menu_element_cm' AND menu.course=?";
        
        // Use database to get source
        $cm_element->set_source_sql($cm_sql, array(backup::VAR_COURSEID));

        
        
        
        // Use database to get source
        $element->set_source_table('course_menu_element_position',
                array('course_menu_id' => backup::VAR_PARENTID));

        // Include files which have theme_ou and area image and no itemid
        $course_menu->annotate_files('format_course_menu', 'cmf_header', null);

        return $plugin;
    }

}

?>
