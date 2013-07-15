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
 * http://www.gnu.org/copyleft/gpl.haftml GNU GPL v3 or later              **
 * *************************************************************************
 * ************************************************************************ */

/**
 * JS that is used in EDIT MODE and NORMAL MODE
 */

       /**
        * Hides the CM icons if set to hide in course menu settings
        * 
        */
        $(function() {
            update_icon_visibility();
        });
        
        function update_icon_visibility() {
            if(course_menu_strings['include_icons'] !== 1) 
                $("li[data_type=course_menu_element_cm] img").hide();
        }


