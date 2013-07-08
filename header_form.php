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

/**
 * Form for editing the main header in course menu format
 */

require_once("$CFG->libdir/formslib.php");
 
class course_menu_header_form extends moodleform {
 
    private $context;//course context
    private $course;//course id
    
    /**
     * General Constructor
     * 
     * @param object $context
     * @param int $courseid 
     */
    function __construct($context, $courseid) {
        $this->context = $context;
        $this->course = $courseid;
        
        parent::__construct();
    }
    
    /**
     * Form definition
     */
    function definition() {
        $mform =& $this->_form; // Don't forget the underscore! 
        
        //add editor
        $mform->addElement('editor', 'cmf_header', get_string('header_label', 'format_course_menu'), null, $this->get_editor_options());
        $mform->setType('cmf_header', PARAM_RAW);
        
        //add submit button
        $mform->addElement('submit', 'save', get_string("save", "format_course_menu"));
        
        //course id hidden
        $mform->addElement('hidden', 'courseid', $this->course);
        $mform->setType('courseid', PARAM_INT);
        
    }
    
    /**
     * Returns the options for an editor that allows uploading files
     * 
     * @global object $CFG
     * @return array options for an editor 
     */
    public function get_editor_options() {
        global $CFG;
        return array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 0, 'context' => $this->context, 'noclean' => 1, 'trusttext' => true);
    }
    
} 
?>
