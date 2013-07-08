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

$(function() {

    //initalize all the colour pickers for settings
    $(".color_picker").colorpicker({
					parts: 'full',
					showOn: 'both',
					buttonColorize: true,
					showNoneButton: true,
					alpha: true,
					colorFormat: '#HEX'
				});
});

