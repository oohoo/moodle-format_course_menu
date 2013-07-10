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
require_once($CFG->dirroot . '/course/format/lib.php');

class format_course_menu extends format_base {

    /**
     * Returns true if this course format uses sections
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns the display name of the given section that the course prefers.
     *
     * Use section name is specified by user. Otherwise use default ("Topic #")
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string) $section->name !== '') {
            return format_string($section->name, true, array('context' => context_course::instance($this->courseid)));
        } else if ($section->section == 0) {
            return get_string('section0name', 'format_course_menu');
        } else {
            return get_string('topic') . ' ' . $section->section;
        }
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                $usercoursedisplay = $course->coursedisplay;
            }
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else {
                if (!empty($options['navigation'])) {
                    return null;
                }
                $url->set_anchor('section-' . $sectionno);
            }
        }
        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     * The property (array)testedbrowsers can be used as a parameter for {@link ajaxenabled()}.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        $ajaxsupport->testedbrowsers = array('MSIE' => 6.0, 'Gecko' => 20061111, 'Safari' => 531, 'Chrome' => 6.0);
        return $ajaxsupport;
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        global $PAGE;
        // if section is specified in course/view.php, make sure it is expanded in navigation
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('section', null, PARAM_INT);
            if ($selectedsection !== null && (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                    $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                $navigation->includesectionnum = $selectedsection;
            }
        }

        // check if there are callbacks to extend course navigation
        parent::extend_course_navigation($navigation, $node);
    }

    /**
     * Custom action after section has been moved in AJAX mode
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    function ajax_section_move() {
        global $PAGE;
        $titles = array();
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return array('sectiontitles' => $titles, 'action' => 'move');
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => array('search_forums', 'news_items', 'calendar_upcoming', 'recent_activity')
        );
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * Topics format uses the following options:
     * - coursedisplay
     * - numsections
     * - hiddensections
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = array(
                'numsections' => array(
                    'default' => $courseconfig->numsections,
                    'type' => PARAM_INT,
                ),
                'hiddensections' => array(
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT,
                ),
                'coursedisplay' => array(
                    'default' => $courseconfig->coursedisplay,
                    'type' => PARAM_INT,
                ), 'cmf_backgroundcolor' => array(
                    'default' => '#aaaaaa',
                    'type' => PARAM_TEXT,
                ),
                'cmf_row_horizontal_color' => array(
                    'default' => '#aaaaaa',
                    'type' => PARAM_TEXT,
                ),
                'cmf_headerfullbackgroundcolor' => array(
                    'default' => '#aaaaaa',
                    'type' => PARAM_TEXT,
                ),
                'cmf_headerbackgroundcolor' => array(
                    'default' => '#aaaaaa',
                    'type' => PARAM_TEXT,
                ),
                'cmf_cmfullbackgroundcolor' => array(
                    'default' => '#aaaaaa',
                    'type' => PARAM_TEXT,
                ),
                'cmf_cmbackgroundcolor' => array(
                    'default' => '#aaaaaa',
                    'type' => PARAM_TEXT,
                ),
                'cmf_allowicons' => array(
                    'default' => 1,
                    'type' => PARAM_INT,
                )
                
                
            );
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseconfig = get_config('moodlecourse');
            $max = $courseconfig->maxsections;
            if (!isset($max) || !is_numeric($max)) {
                $max = 52;
            }
            $sectionmenu = array();
            for ($i = 0; $i <= $max; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseformatoptionsedit = array(
                'numsections' => array(
                    'label' => new lang_string('numberweeks'),
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                ),
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('hiddensectionscollapsed'),
                            1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                ),
                'coursedisplay' => array(
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
                        )
                    ),
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                ), 'cmf_backgroundcolor' => array(
                    'label' => new lang_string('backgroundcolorpicker', 'format_course_menu'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        array('style' => 'display:none', 'class' => 'color_picker')
                    ),
                    'help' => 'backgroundcolorpicker',
                    'help_component' => 'format_course_menu',
                ),
                'cmf_row_horizontal_color' => array(
                    'label' => new lang_string('rowcolorpicker', 'format_course_menu'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        array('style' => 'display:none', 'class' => 'color_picker')
                    ),
                    'help' => 'rowcolorpicker',
                    'help_component' => 'format_course_menu',
                ),
                'cmf_headerfullbackgroundcolor' => array(
                    'label' => new lang_string('headerfullbackgroundcolor', 'format_course_menu'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        array('style' => 'display:none', 'class' => 'color_picker')
                    ),
                    'help' => 'headerfullbackgroundcolor',
                    'help_component' => 'format_course_menu',
                ),
                'cmf_headerbackgroundcolor' => array(
                    'label' => new lang_string('headerbackgroundcolor', 'format_course_menu'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        array('style' => 'display:none', 'class' => 'color_picker')
                    ),
                    'help' => 'headerbackgroundcolor',
                    'help_component' => 'format_course_menu',
                ),
                'cmf_cmfullbackgroundcolor' => array(
                    'label' => new lang_string('cmfullbackgroundcolor', 'format_course_menu'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        array('style' => 'display:none', 'class' => 'color_picker')
                    ),
                    'help' => 'cmfullbackgroundcolor',
                    'help_component' => 'format_course_menu',
                ),
                'cmf_cmbackgroundcolor' => array(
                    'label' => new lang_string('cmbackgroundcolor', 'format_course_menu'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        array('style' => 'display:none', 'class' => 'color_picker')
                    ),
                    'help' => 'cmbackgroundcolor',
                    'help_component' => 'format_course_menu',
                ),
                'cmf_allowicons' => array(
                    'label' => new lang_string('allowicons', 'format_course_menu'),
                    'element_type' => 'advcheckbox',
                    'element_attributes' => array(
                       '', array("group"=> 1), array(0, 1)
                    ),
                    'help' => 'allowicons',
                    'help_component' => 'format_course_menu',
                )
            );
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Adds form elements 
     * 
     * @global moodle_page $PAGE
     * @global type $CFG
     * @param type $mform
     * @param type $forsection
     * @return type
     */
    function create_edit_form_elements(&$mform, $forsection = false) {
        global $CFG, $PAGE;

        $script = "<script>var course_menu_strings = new Array();</script>";
        $script .= "<script>course_menu_strings['wwwroot'] ='$CFG->wwwroot';</script>";

        if(!$PAGE->headerprinted)
            $this->page_load_main_course_js();
        
        $mform->addElement('html', $script);
        return parent::create_edit_form_elements($mform, $forsection);
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'course_menu', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * If previous course format did not have 'numsections' option, we populate it with the
     * current number of sections
     *
     * @global moodle_database $DB 
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB, $COURSE;

        $layout = $DB->get_record('course_menu', array('course' => $COURSE->id));

        if (!$layout) {
            $layout = new stdClass();
            $layout->course = $COURSE->id;
            $DB->insert_record('course_menu', $layout);
        }
        

        if ($oldcourse !== null) {
            $data = (array) $data;
            $oldcourse = (array) $oldcourse;
            $options = $this->course_format_options();
            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        $data[$key] = $oldcourse[$key];
                    } else if ($key === 'numsections') {
                        // If previous format does not have the field 'numsections'
                        // and $data['numsections'] is not set,
                        // we fill it with the maximum section number from the DB
                        $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                            WHERE course = ?', array($this->courseid));
                        if ($maxsection) {
                            // If there are no sections, or just default 0-section, 'numsections' will be set to default
                            $data['numsections'] = $maxsection;
                        }
                    }
                }
            }
        }
        return $this->update_format_options($data);
    }

    /**
     * This function is called after the course has been set on the page. We use this to include various JS,
     * depending on the course page.
     * 
     * 
     * @global moodle_page $PAGE
     * @global type $COURSE
     * @param moodle_page $page
     */
    public function page_set_course(moodle_page $page) {
       parent::page_set_cm($page);
       $this->page_load_main_course_js();
    }
    
    /**
     * Loads the required jquery & JS needed for course menu
     */
    private function page_load_main_course_js() {
        global $PAGE, $COURSE, $CFG;
        
        //get page url - if not set then ignore it
        $url = $PAGE->has_set_url() ? $PAGE->url : "";

        //check if its the main course view page
        $regex_url = "/" . preg_quote($CFG->wwwroot . "/course/view.php?id=", "/") . "[\d]*" . "[\d]*/";
        $is_course_main = preg_match($regex_url, $url);

        //check if course mod_edit page
        $regex_editing_url = "/" . preg_quote($CFG->wwwroot . "/course/edit.php?id=", "/") . "[\d]*/";
        $is_course_editing_main = preg_match($regex_editing_url, $url);
        
        //if either if main view or main edit
        if($is_course_main === 1 || $is_course_editing_main === 1) {
            
            //load jquery
            $this->load_jQuery();
            
            //dynamic colors
            $PAGE->requires->css('/course/format/course_menu/dynamic_colors.php?id=' . $COURSE->id);
            
            //if main page
            if($is_course_main === 1) {
            $context = context_course::instance($COURSE->id);//get context
            $is_editting = $PAGE->user_is_editing() and has_capability('moodle/course:update', $context);//is editing mode

            //if were not editing, then override display css (display only css)
            if (!$is_editting)
                $PAGE->requires->css('/course/format/course_menu/display_override.css');
            }
           
         //if editing
         if ($is_course_editing_main === 1) {
            $PAGE->requires->css('/course/format/course_menu/jquery/plugin/colorpicker/jquery.colorpicker.css');
            $PAGE->requires->js('/course/format/course_menu/jquery/plugin/colorpicker/jquery.colorpicker.js');
            $PAGE->requires->js('/course/format/course_menu/init.js');
          }   
            
        }
        
        
    }

    /**
     * An function to interface PHP (server) based information to JS (browser)
     * 
     * Loads a set of language strings, and other server based info to be used in the JS.
     * 
     * @global moodle_database $DB
     * @global type $CFG
     * @global type $COURSE
     */
    public function load_php_strings() {
        global $DB, $CFG, $COURSE;
        
        $include_icons_config = $DB->get_record('course_format_options', array('courseid' => $COURSE->id, 'name' => 'cmf_allowicons'));
        
        if(!$include_icons_config)
            $include_icons = 0;
        else
            $include_icons = $include_icons_config->value;
        
        echo "<script>";
        echo "var course_menu_strings = new Array();";
        echo "course_menu_strings['wwwroot'] ='$CFG->wwwroot';";
        echo "course_menu_strings['confirm_delete_message'] ='" . get_string('confirm_delete_message', 'format_course_menu') . "';";
        echo "course_menu_strings['yes'] ='" . get_string('yes') . "';";
        echo "course_menu_strings['no'] ='" . get_string('no') . "';";
        echo "course_menu_strings['courseid'] = " . $COURSE->id . ";";
        echo "course_menu_strings['ajax'] = '" . $CFG->wwwroot . "/course/format/course_menu/ajax_controller.php';";
        echo "course_menu_strings['include_icons'] = ".$include_icons.";";
        echo "course_menu_strings['new_header'] = '".get_string('new_header', 'format_course_menu')."';";
        
        echo "</script>";
    }
    
    /**
     * Loads jquery using the built in (if moodle 2.5+) or uses an included version if < moodle 2.4
     * 
     * @global moodle_page $PAGE
     */
    public function load_jQuery() {
        global $PAGE;

        if (moodle_major_version() >= '2.5') {
            $PAGE->requires->jquery();
            $PAGE->requires->jquery_plugin('migrate');
            $PAGE->requires->jquery_plugin('ui');
            $PAGE->requires->jquery_plugin('ui-css');
        } else {
            $PAGE->requires->js("/course/format/course_menu/jquery/jquery-1.9.1.js");
            $PAGE->requires->js("/course/format/course_menu/jquery/jquery-ui.min.js");
            $PAGE->requires->css("/course/format/course_menu/jquery/themes/base/jquery.ui.all.css");
        }
        
        $PAGE->requires->js("/course/format/course_menu/display.js");
        
    }

}
