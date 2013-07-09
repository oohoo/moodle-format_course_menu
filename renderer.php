<?php
/**
 * *************************************************************************
 * *                                Chairman                              **
 * *************************************************************************
 * @package mod                                                          **
 * @subpackage chairman                                                  **
 * @name Chairman                                                        **
 * @copyright oohoo.biz                                                  **
 * @author Dustin Durand                                                 **
 * @license                                                              **
  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later                **
 * *************************************************************************
 * ************************************************************************ 
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');
require_once($CFG->dirroot.'/course/format/course_menu/elements/course_menu_element.php');


class format_course_menu_renderer extends format_section_renderer_base {

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'topics'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the edit controls of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of links with edit controls
     */
    protected function section_edit_controls($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();
        if (has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $controls[] = html_writer::link($url,
                                    html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marked'),
                                        'class' => 'icon ', 'alt' => get_string('markedthistopic'))),
                                    array('title' => get_string('markedthistopic'), 'class' => 'editing_highlight'));
            } else {
                $url->param('marker', $section->section);
                $controls[] = html_writer::link($url,
                                html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marker'),
                                    'class' => 'icon', 'alt' => get_string('markthistopic'))),
                                array('title' => get_string('markthistopic'), 'class' => 'editing_highlight'));
            }
        }

        return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
    }
    
    
        /**
         * Outputs the course menu 
         * 
         * @global type $PAGE
         * @param type $course
         */
        public function output_course_menu($course) {
        global $PAGE;
        //get context for course
        $context = context_course::instance($course->id);
        
        //page in edit mode
        $is_edit = $PAGE->user_is_editing() and has_capability('moodle/course:update', $context);
        
        //main content div
        echo html_writer::start_tag('div', array('class' => 'course_menu_content','id' => 'course_menu_content'));
        
        //if in edit mode - add menu
        if($is_edit)
           $this->course_menu_display_edit_elements($course); 
        
        //main header section
        $this->course_menu_main_header();
        
        //actual course menu layout elements
        $this->course_menu_display_layout_elements($course);
        
        //if in edit mode - add garbage
        if($is_edit)         
            $this->course_menu_display_garbage(); 
        
        
        //end course menu content
        echo html_writer::end_tag('div');
     
 }
    
 
 /**
  * Outputs the main header for course menu layout
  * 
  * @global object $CFG
  * @global moodle_page $PAGE
  * @global object $COURSE
  */
    private function course_menu_main_header() {
        global $CFG, $PAGE, $COURSE;

        //output the actual main header
        echo html_writer::start_tag('div', array('class' => "cmf_main_header"));
        echo $this->course_menu_main_header_elements();
        echo html_writer::end_tag('div');

        //if in edit mode - add edit dialog and iframe
        if ($PAGE->user_is_editing()) {
            echo html_writer::start_tag('div', array('class' => "cmf_header_dialog"));
            echo html_writer::start_tag('iframe', array('frameBorder' => "0", 'scrolling' => 'no', 'style' => 'overflow:hidden;min-width:100%;min-height:100%', 'src' => "$CFG->wwwroot/course/format/course_menu/header.php?courseid=$COURSE->id"));
            echo html_writer::end_tag('iframe');
            echo html_writer::end_tag('div');
        }
    }
    
    /**
     * Outputs the main header html
     * 
     * @global moodle_database $DB
     * @global object $COURSE
     * @global moodle_page $PAGE 
     * @return type
     */
    public function course_menu_main_header_elements() {
         global $DB, $COURSE, $PAGE;
         
         //get course menu instance
         $course_menu = $DB->get_record('course_menu', array('course'=>$COURSE->id));
         
         //get course context
         $context = CONTEXT_COURSE::instance($COURSE->id);
         
         //get draft id
         $draftid_editor = file_get_submitted_draft_itemid('cmf_header'); 
         
         //replace relative links to abs. links
         $currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'format_course_menu', 'cmf_header',
                                       0, array(), $course_menu->header);
         
         //if its empty, don't output anything
         if(empty($currenttext)) {
             
             if($PAGE->user_is_editing()) {
                 $currenttext = "<h1>" . get_string('main_header_editing_default', 'format_course_menu') . "</h1>";
             } else {
                 return;
             }
         }
         
         //output html
         echo $currenttext;
         
    }
    
 
    /**
     * Adds all the edit elements.
     *  
     * The main content output by this function is the top editing menu
     * 
     * @global object $CFG
     * @param object $course
     */
    private function course_menu_display_edit_elements($course) {
        global $CFG;
        
        //menu ul
         echo html_writer::start_tag('ul', array('class' => 'format_course_menu_list','id' => 'format_course_menu_list'));
                
                //non-menu li containing cell type
                echo html_writer::start_tag('li', array('class'=>'ignore'));
                     echo html_writer::start_tag('div', array('class'=>"cmf_pre_section_header"));
                        echo html_writer::empty_tag("img", array('src'=>"$CFG->wwwroot/course/format/course_menu/pix/single_cell.png", "class"=>"cmf_cell_selection_button", "type"=>"single", "id"=>"cmf_cell_selection_button_single", "active"=>"1"));
                        echo html_writer::empty_tag("img", array('src'=>"$CFG->wwwroot/course/format/course_menu/pix/full_cell.png", "style"=>"display:none;", "class"=>"cmf_cell_selection_button", "type"=>"full", "id"=>"cmf_cell_selection_button_full", "active"=>"0"));
                     
                        //vert seperator
                        echo html_writer::empty_tag('div', array('style'=>"display:inline-block;margin-left:10px;border-left:1px solid #000;height:25px;width:10px;"));
                        echo html_writer::end_tag('div');
                        
                        //list for header icon
                        echo html_writer::start_tag('ol', array('class'=>"course_menu_option_list", "id"=>"cmf_header_menu_option"));
                            echo html_writer::start_tag('li');
                            
                                echo html_writer::start_tag('div', array('class'=>"cmf_content_wrapper", "style"=>"display:inline"));
                                    echo html_writer::empty_tag("img", array('src'=>"$CFG->wwwroot/course/format/course_menu/pix/header.png", "class"=>"cmf_cell_selection_button"));
                                echo html_writer::end_tag('div');
                             echo html_writer::end_tag('li');
                        echo html_writer::end_tag('ol');
                        
                        echo html_writer::end_tag('div');
                echo html_writer::end_tag('li');
                
                //output section menus containing activities and resources
                $this->course_menu_display_section_edit_menu($course);
                
                
             echo html_writer::end_tag('ul');
             
         
         

        
    
    }
    
    /**
     * Outputs the garbage section
     */
    private function course_menu_display_garbage() {
         echo html_writer::start_tag('div', array('class'=>'cmf_garbage_main'));//garbage wrapper
            echo html_writer::start_tag('span', array('class'=>'cmf_garbage_title'));//label span
                echo get_string('garbage', 'format_course_menu');
            echo html_writer::end_tag('span');
            echo html_writer::start_tag('ul', array('class'=>'cmf_garbage ui-corner-all'));//garbage list
            echo html_writer::end_tag('ul');
         echo html_writer::end_tag('div');
    }
    
    /**
     * Output the section menus, with the currently created activies & resources
     * 
     * @global type $PAGE
     * @param type $course
     */
    private function course_menu_display_section_edit_menu($course) {
        global $PAGE;
        
        //get core renderer
        $course_renderer = $PAGE->get_renderer('core', 'course');
        
        //course mod info
        $modinfo = get_fast_modinfo($course);

        //go through each section and create a sub-menu of all course modules
        foreach ($modinfo->get_section_info_all() as $sectionnum => $thissection) {
            $section_html = '';
            $mod_count = 0;
            
            //get section
            $section = $modinfo->get_section_info($thissection->section);
            if (empty($modinfo->sections[$section->section]))
                continue;

            //start sub-menu
            $section_html .= html_writer::start_tag('li');
            $section_html .= '<a href="#">' . get_section_name($course, $thissection) . '</a>';

            $section_html .= html_writer::start_tag('ul', array('class' => 'course_menu_option_list_content'));
            $section_html .= html_writer::start_tag('li');
            $section_html .= html_writer::start_tag('div');

            $section_html .= html_writer::start_tag('ol', array('class' => 'course_menu_option_list'));

            //each course module - create dragable element
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];
                $url = $mod->get_url();
                if(empty($url)) continue;
                
                $section_html .=  course_menu_cm::get_cm_element_display($course, $mod->id, false, array());
                
                $mod_count++;
            }

            $section_html .= html_writer::end_tag('ol');

            $section_html .= html_writer::end_tag('div');
            $section_html .= html_writer::end_tag('li');
            $section_html .= html_writer::end_tag('ul');
            $section_html .= html_writer::end_tag('li');
            
            //only output section if it contains more than one course module
            if($mod_count > 0)
               echo $section_html;
            
        }
    }
    
    /**
     * Outputs the layout elements for the course menu format
     * 
     * @global moodle_database $DB
     * @param type $course
     */
    public function course_menu_display_layout_elements($course) {
        global $DB;
        $html = '';
        $layout = $DB->get_record('course_menu', array('course' => $course->id));

        //get all elements in this course menu instance
        $layout_elements = $DB->get_records('course_menu_element_position', array('course_menu_id' => $layout->id), 'position_row ASC, position_order ASC');

        //layout content wrapper
        $html .= html_writer::start_tag('div', array('class' => 'course_menu_content_main', 'id' => 'course_menu_content_main'));
        $html .= html_writer::start_tag('ul', array('class' => 'course_menu_target', 'id' => 'course_menu_target'));

      
        $row = -1;//start at non-existant row
        $row_siblings_count = 0;//sibling count
        foreach ($layout_elements as $index => $element) {
            $el = new course_menu_element($element);//create element

            $el_row = $el->get_row();//get element row
            if ($row != $el_row) {//of row is different than previous one - we are on a new row
                $horiz_row_class = 'course_menu_target_row';//assume its a normal element
                if ($el->is_full()) {//if its actually a full row - change class
                    $horiz_row_class = 'course_menu_target_row_full';
                }

                //unless its the first row - end last row and open a new one - since on a new row
                if ($row > -1) {
                    $html .= html_writer::end_tag('ul');
                    $html .= html_writer::end_tag('li');
                }

                //open a new row
                $html .= html_writer::start_tag('li', array('class' => 'course_menu_target_row_li'));
                $html .= html_writer::start_tag('ul', array('class' => $horiz_row_class, 'id' => 'course_menu_target_row'));

                //update row #
                $row = $el_row;

                //get sibling count
                $row_siblings_count = $this->count_row_siblings($layout_elements, $row);
            }

            //output element html
            $html .= $el->get_html_output(array('row_cell_count' => $row_siblings_count));
        }

        //if not the first row - end last row
        if ($row > -1) {
            $html .= html_writer::end_tag('ul');
            $html .= html_writer::end_tag('li');
        }

        //always add an empty row
        $html .= html_writer::start_tag('li', array('class' => 'course_menu_target_row_li'));
        $html .= html_writer::start_tag('ul', array('class' => 'course_menu_target_row cmf_empty_target_row', 'id' => 'course_menu_target_row'));
        $html .= html_writer::end_tag('ul');
        $html .= html_writer::end_tag('li');

        $html .= html_writer::end_tag('ul');
        $html .= html_writer::end_tag('div');

        
        //output layout html
        echo $html;
    }
    
    /**
     * Looks ahead in the current layout elements, and determines the number of elements
     * in the current row.
     * 
     * @param array $layout_elements
     * @param type $current_row
     * @return int
     */
    private function count_row_siblings(array $layout_elements, $current_row) {
        
        $count = 1;
        while(true) {
            if(!current($layout_elements)) break;//if past end = break (done)
            
            //if row changes, break
            if(current($layout_elements)->position_row != $current_row)
                break;
           
            //otherwise look at next element
            next($layout_elements);
            $count++;
        }
        
        //move array internal pointer back to original position
        for($i = $count; $i > 0; $i--)
            prev($layout_elements);
        
        //return # of element in this row
        return $count;
        
    }
    
      /**
     * Output the html for a single section page .
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     * @param int $displaysection The section number in the course which is being displayed
     */
    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
	
	echo $this->output_course_menu($course);

	$context = context_course::instance($course->id);
        
        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {

        // Can we view the section in question?
        if (!($sectioninfo = $modinfo->get_section_info($displaysection))) {
            // This section doesn't exist
            print_error('unknowncoursesection', 'error', null, $course->fullname);
            return;
        }

        if (!$sectioninfo->uservisible) {
            if (!$course->hiddensections) {
                echo $this->start_section_list();
                echo $this->section_hidden($displaysection);
                echo $this->end_section_list();
            }
            // Can't view this section.
            return;
        }

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, $displaysection);
        $thissection = $modinfo->get_section_info(0);
        if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
            echo $this->start_section_list();
            echo $this->section_header($thissection, $course, true, $displaysection);
            echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
            echo $this->courserenderer->course_section_add_cm_control($course, 0, $displaysection);
            echo $this->section_footer();
            echo $this->end_section_list();
        }
        
        // Start single-section div
        echo html_writer::start_tag('div', array('class' => 'single-section'));

        // The requested section page.
        $thissection = $modinfo->get_section_info($displaysection);

        // Title with section navigation links.
        $sectionnavlinks = $this->get_nav_links($course, $modinfo->get_section_info_all(), $displaysection);
        $sectiontitle = '';
        $sectiontitle .= html_writer::start_tag('div', array('class' => 'section-navigation header headingblock'));
        $sectiontitle .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
        $sectiontitle .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));
        // Title attributes
        $titleattr = 'mdl-align title';
        if (!$thissection->visible) {
            $titleattr .= ' dimmed_text';
        }
        $sectiontitle .= html_writer::tag('div', get_section_name($course, $displaysection), array('class' => $titleattr));
        $sectiontitle .= html_writer::end_tag('div');
        echo $sectiontitle;

        // Now the list of sections..
        echo $this->start_section_list();
        
        echo $this->section_header($thissection, $course, true, $displaysection);
        // Show completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();

        echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
        echo $this->courserenderer->course_section_add_cm_control($course, $displaysection, $displaysection);
        echo $this->section_footer();
        echo $this->end_section_list();

        // Display section bottom navigation.
        $sectionbottomnav = '';
        $sectionbottomnav .= html_writer::start_tag('div', array('class' => 'section-navigation mdl-bottom'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));
        $sectionbottomnav .= html_writer::tag('div', $this->section_nav_selection($course, $sections, $displaysection),
            array('class' => 'mdl-align'));
        $sectionbottomnav .= html_writer::end_tag('div');
        echo $sectionbottomnav;

        // Close single-section div.
        echo html_writer::end_tag('div');
	}
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');
        
        echo $this->output_course_menu($course);
        
	if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        // Now the list of sections..
        echo $this->start_section_list();

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                // 0-section is displayed a little different then the others
                if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                    echo $this->section_header($thissection, $course, false, 0);
                    echo $this->courserenderer->course_section_cm_list($course, $thissection);
                    echo $this->courserenderer->course_section_add_cm_control($course, 0);
                    echo $this->section_footer();
                }
                continue;
            }
            if ($section > $course->numsections) {
                // activities inside this section are 'orphaned', this section will be printed as 'stealth' below
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but showavailability is turned on (and there is some available info text).
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available && $thissection->showavailability
                    && !empty($thissection->availableinfo));
            if (!$showsection) {
                // Hidden section message is overridden by 'unavailable' control
                // (showavailability option).
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section);
                }

                continue;
            }

            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $course, null);
            } else {
                echo $this->section_header($thissection, $course, false, 0);
                if ($thissection->uservisible) {
                    echo $this->courserenderer->course_section_cm_list($course, $thissection);
                    echo $this->courserenderer->course_section_add_cm_control($course, $section);
                }
                echo $this->section_footer();
            }
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
                    // this is not stealth section or it is empty
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));

            // Increase number of sections.
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php',
                array('courseid' => $course->id,
                      'increase' => true,
                      'sesskey' => sesskey()));
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon.get_accesshide($straddsection), array('class' => 'increase-sections'));

            if ($course->numsections > 0) {
                // Reduce number of sections sections.
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php',
                    array('courseid' => $course->id,
                          'increase' => false,
                          'sesskey' => sesskey()));
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link($url, $icon.get_accesshide($strremovesection), array('class' => 'reduce-sections'));
            }

            echo html_writer::end_tag('div');
        } else {
            echo $this->end_section_list();
        }
	}

    }
    
    
}
    

