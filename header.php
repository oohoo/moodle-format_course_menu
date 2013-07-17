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

require_once('../../../config.php');
require_once('header_form.php');

$close_dialog = optional_param('close_dialog','0', PARAM_INT);
inline_close_dialog_message($close_dialog);


//get course id
$courseid = required_param('courseid', PARAM_INT);

//get content for course
$context = CONTEXT_COURSE::instance($courseid); 

//require login
require_course_login($courseid);

//setup page layout
$PAGE->set_url("/header.php");
$PAGE->set_pagelayout("embedded");

//create our header form
$mform = new course_menu_header_form($context, $courseid);//name of the form you defined in file above.

//default 'action' for form is strip_querystring(qualified_me())
$draftid_editor = file_get_submitted_draft_itemid('cmf_header'); 

//get instance of course menu format
$course_menu = $DB->get_record('course_menu', array('course'=>$courseid));

//if it doesn't exist - something went wrong
if(!$course_menu)
    print_error (get_string("no_course_menu",'format_course_menu'));

//on submit - save files, and save to course menu
if ($fromform = $mform->get_data()){
    
    //save uploaded images, and replace absolute links with relative links
    $headertext = file_save_draft_area_files($draftid_editor, $context->id, 'format_course_menu', 'cmf_header',
                                          0, $mform->get_editor_options(), $fromform->cmf_header['text']);
    //update header 
    $course_menu->header = $headertext;
    
    //update
    $DB->update_record('course_menu', $course_menu);
    
    
    //redirect back to current page
    redirect("$CFG->wwwroot/course/format/course_menu/header.php?courseid=$courseid&close_dialog=1");
}
 
/**
 * No Submit, Load Form
 */

//Setup object to load data
if (empty($data->id)) {
  $data = new object();
  $data->id = null;
  $data->format = FORMAT_HTML;
  $data->header = $course_menu->header;
}

//load text with absolute links from relative links
$currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'format_course_menu', 'cmf_header',
                                       0, $mform->get_editor_options(), $data->header);

//load defaults into editor
$data->cmf_header = array('text'=>$currenttext, 'format'=>$data->format, 'itemid'=>$draftid_editor);
 
// Set the initial values, for example the existing data loaded from the database.
$mform->set_data($data);

//output form
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();


/**
 * Causes a signal flare to be sent to the parent of the iframe that contains this form
 * This should be fired after the form has been updated - it sends the message
 * that signals for the dialog that iframe is in, to be closed.
 * 
 * @param int $close_dialog
 */
function inline_close_dialog_message($close_dialog) {
    if ($close_dialog == 1) {
        echo <<< JS
    <script> 

         //send signal to parent
         parent.postMessage(
             "1",//send anything
              "*"//no restrictions for domain
          );

</script> 
   
JS;
    }
}

?>
