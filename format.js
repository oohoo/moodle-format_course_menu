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
 * This JS is only applied to a course menu in EDIT MODE
 */

//Garbage UL Drop Point
var CMF_MAIN_CONTENT = "course_menu_content";
var CMF_MAIN_CONTENT_C = "."+CMF_MAIN_CONTENT;

//Garbage UL Drop Point
var CMF_GARBAGE_UL_SELECTOR = "cmf_garbage";
var CMF_GARBAGE_UL_SELECTOR_C = "."+CMF_GARBAGE_UL_SELECTOR;

//The top UL that allows the vertical sorting of rows
var CMF_MAIN_UL_SELECTOR = "course_menu_target";
var CMF_MAIN_UL_SELECTOR_C = "."+CMF_MAIN_UL_SELECTOR;

//The UL(s) that allow horizontal sorting within a single row
var CMF_MAIN_ROW_ULS_SELECTOR = "course_menu_target_row";
var CMF_MAIN_ROW_ULS_SELECTOR_C = "."+CMF_MAIN_ROW_ULS_SELECTOR;

//The UL(s) that contain header elements
var CMF_MAIN_ROW_ULS_FULL_SELECTOR = "course_menu_target_row_full";
var CMF_MAIN_ROW_ULS_FULL_SELECTOR_C = "."+CMF_MAIN_ROW_ULS_FULL_SELECTOR;

//The UL(s) that contain contain no elements - can have headers dropped in them
var CMF_MAIN_ROW_ULS_EMPTY_SELECTOR = "cmf_empty_target_row";
var CMF_MAIN_ROW_ULS_EMPTY_SELECTOR_C = "."+CMF_MAIN_ROW_ULS_EMPTY_SELECTOR;

//The LIs wrapping the UL(s) that allow horizontal sorting within a single row
var CMF_MAIN_UL_LI_SELECTOR = "course_menu_target_row_li";
var CMF_MAIN_UL_LI_SELECTOR_C = "."+CMF_MAIN_UL_LI_SELECTOR;

//The header LIs within "full" header UL 
var CMF_ROW_UL_LI_FULL_SELECTOR = "course_menu_full_cell";
var CMF_ROW_UL_LI_FULL_SELECTOR_C = "."+CMF_ROW_UL_LI_FULL_SELECTOR;

//The horiz LIs
var CMF_ROW_UL_LI_SELECTOR = "course_menu_cell";
var CMF_ROW_UL_LI_SELECTOR_C = "."+CMF_ROW_UL_LI_SELECTOR;

//the selector for the menu UL
var CMF_MENU_SELECTOR = "format_course_menu_list";
var CMF_MENU_SELECTOR_I = "#"+CMF_MENU_SELECTOR;

//the selector for the UL lists within each sub-menu dropdown (containing section resources)
var CMF_MENU_ULS_SELECTOR = "course_menu_option_list";
var CMF_MENU_ULS_SELECTOR_C = "."+CMF_MENU_ULS_SELECTOR;


var active_menu = null;

/**
 * Hiearchy
 * 
 * (div)CMF_MAIN_CONTENT
 *      
 *      (ul)CMF_MENU_SELECTOR - menu
 *          (ul)CMF_MENU_ULS_SELECTOR - section lists
 *      
 *      (ul)CMF_GARBAGE_UL_SELECTOR - garbage list
 *      
 *      (ul)CMF_MAIN_UL_SELECTOR - main list for vertical reordering
 *          (li)CMF_MAIN_UL_LI_SELECTOR
 *             1.(ul)  CMF_MAIN_ROW_ULS_FULL_SELECTOR.
 *                  (li)CMF_ROW_UL_LI_FULL_SELECTOR
 *             or
 *             2.(ul)CMF_MAIN_ROW_ULS_SELECTOR
 *                  (li)
 *          
 * @returns {undefined}
 */

//Initialization
$(function() {

    //create editing menu
    $(CMF_MENU_SELECTOR_I).menu({
        position: {at: "left bottom"},
                 
         focus: function( event, ui ) {
            active_menu = ui.item;
         },
    });

    //Upon hovering over the non-dropdown section of the menu (containing header / type)
    //other dropdowns are collapsed
    $(CMF_MENU_SELECTOR_I + " .cmf_pre_section_header").hover(
           //handler in
            function() {
              $(CMF_MENU_SELECTOR_I).menu( "collapseAll", null, true );  
            },
        function() {});

        

    //init the various lists on the page
    sortables_init();
    
    //initalize the button that allows selection between single-cell elements &
    //elements that are full sized and take a whole row (ex. header)
    initalize_cell_type_button_init();
    
    //Whenever a scroll or resize of the window occurs - check if menus should be
    //converted to floating based menus
    $(window).scroll(function() {
        conditional_floating_menus();
    }).resize(function() {
        conditional_floating_menus();
    });

    iframe_listener();

});


/**
 * A function that conditionally converts our static menu & garbage to become floating
 * at the top and bottom of the screen.
 * 
 * The menus are converted to floating when you have scrolled beyond the top of the course menu content, and the
 * width of the screen is greater than 840px.
 * 
 */
function conditional_floating_menus() {
    
    var offset = $(".course_menu_target").offset();//get offset from top where the course menu content section starts
    var scrollOffset = $(window).scrollTop();//gets the current offset
    var window_width = $(window).width();//gets the current width of the page
    
    var top_menu = $("#format_course_menu_list");
    var garbage = $(".cmf_garbage_main");
    
    //if we scroll past the start of the content section and width is more than 840
    //convert the menus to be floating based
    if(scrollOffset > offset.top && window_width > 840) {
        
        if(!top_menu.hasClass("fixed_header"))//if header isn't already fixed to top of screen
            top_menu.hide(500, function() {//hide menu
                top_menu.addClass("fixed_header");//add class to make fixed to top (aka floating at top)
                top_menu.show(500);//show menu
        });
        
        if(!garbage.hasClass("fixed_footer"))//if footer isn't already fixed to bottom of screen
            garbage.hide(500, function() {//hide garbage
                garbage.addClass("fixed_footer");//add class to make fixed to bottom
                garbage.show(500);//show garbage
        });
    } else {//too close to top of screen, or window too thin
        
        if(top_menu.hasClass("fixed_header"))//if menu is already fixed to top of screen
            top_menu.hide(500, function() {//hide
                top_menu.removeClass("fixed_header");//removed class that fixed it to top of screen
                top_menu.show(500);//show menu
        });
        
        if(garbage.hasClass("fixed_footer"))//if garbage is already fixed to bottom of screen
            garbage.hide(500, function() {//hide
                garbage.removeClass("fixed_footer");//removed class that fixed it to bottom of screen
                garbage.show(500);//show garbage
                
        });
        
        }
    
}

/**
 * Initalizes the cell type button with appropriate callbacks
 */
function initalize_cell_type_button_init() {
    
    //grab the two cell type selection images
    $(".cmf_cell_selection_button").each(function(index, element) {

        //add click listener
        $(this).click(function(event) {
            //type is either full cell (one element per row) or single cell(multiple elements per row)
            var type = $(this).attr("type");
            
            //get single type image
            var single = $("#cmf_cell_selection_button_single");
            
            //get full type image
            var full = $("#cmf_cell_selection_button_full");

            //if set to full when the click occured, change to single cell mode
            if (type === 'full') {
                $(full).hide().attr("active", "0");//set full image to inactive
                $(single).show().attr("active", "1");//set single image to active
                //change the menu to allow drops to all non-header rows & the main ul itself (& garbage)
                $(CMF_MENU_ULS_SELECTOR_C).sortable( "option", "connectWith", CMF_MAIN_ROW_ULS_SELECTOR_C + ", " + CMF_MAIN_UL_SELECTOR_C );
            } else {//if set to single mode, change to full cell mode
                $(single).hide().attr("active", "0");//set single image to inactive
                $(full).show().attr("active", "1");//set full image to active
                //change the menu to allow drops to all empty rows & the main ul itself (& garbage)
                $(CMF_MENU_ULS_SELECTOR_C).sortable( "option", "connectWith", CMF_MAIN_ROW_ULS_EMPTY_SELECTOR_C + ", " + CMF_MAIN_UL_SELECTOR_C );
            }
        });

    });


}

/*
 * Initalizes all the sortable lists that are present on the page
 */
function sortables_init() {
    
    //generate the vertical sorting list
    $(CMF_MAIN_UL_SELECTOR_C).sortable({
        connectWith: CMF_GARBAGE_UL_SELECTOR_C,//only allow drops to the garbage or sorting within itself
        placeholder: "ui-state-highlight",//placeholder class
        tolerance: "pointer",//use the mouse to determine when should sorting change
        
        //handler for when the main UL recieves a new LI item
        receive: function(event, ui) {
            //clones a copy back into the menu & makes menu visible again
            onRecievedFromMenu(event, ui);
            
            //check if this is a full celled item from the menu
            var isFullCell = isFullCellFromMenu(ui.sender);
            
            //what the UL row's class will be
            var row_class = '';
            
            //if its a full cell from the menu, we need to add a specific class
            //to the given LI for specific CSS
            if(isFullCell) {
                row_class = CMF_MAIN_ROW_ULS_FULL_SELECTOR;//mark row UL as full
                $(ui.item).addClass(CMF_ROW_UL_LI_FULL_SELECTOR);//mark LI for specific full css
                $(ui.item).css("width", "");//remove any width params from menu
            } else {
                row_class = CMF_MAIN_ROW_ULS_SELECTOR;//standard row class
            }
            
            //create the UL for the horizontal sorting
            var ul = $("<ul/>", {class:row_class});
            
            //create another LI to wrap the horizontal UL to add to vertical UL
            var li = $("<li/>", {class:CMF_MAIN_UL_LI_SELECTOR});
            
            
            /*
             * Attempting to place our new DOM subtree in the position that the
             * user dropped the menu item to begin with
             */
            //grab vertical UL list
            var parent = $(ui.item).parent();
            
            //grab previous sibling if there is one
            var previous = $(ui.item).prev();
            
            //add menu li item to our horizontal UL
            $(ul).append(ui.item);
            
            //if its a single cell - need to initalize to a sortable so we can add more single cells
            if(!isFullCell)
                {
                    create_sortable_row(ul);//sortable init
                     update_list_size(ul);
                }
            
            //add horizontal UL to an LI to be placed in veritcal UL
            $(li).append(ul);
            
            //IF there is a sibling element in the position just before where the menu
            //li was placed - the new subtree is placed after it
            if($(previous).length > 0) 
                $(previous).after(li);
            else//menu li was dropped at top of vertical UL, so we prepend the subtree
                $(parent).prepend(li);
            
            //save layout
            cmf_ajax_update();
        },
        
        stop: function(event, ui) {
            if(!ui.item.parent().hasClass("cmf_garbage"))
               cmf_ajax_update();//save layout when NOT a garbage placement
        }
    });

    /**
     * Initialize our "GARBAGE" list
     * This acts as the trash can, accepting any element and deleting it if confirmed
     */
    $(CMF_GARBAGE_UL_SELECTOR_C).sortable({
        containment: CMF_GARBAGE_UL_SELECTOR_C,//nothing can leave trash
        placeholder: "ui-state-highlight",//placeholder
        tolerance: "pointer",//pointer based tolerance
        
        //when something is placed in the trash listener
        receive: function(event, ui) {
            
            //generate a confirm dialof
            confirm_dialog(
                    function() {//on accepted
                        ui.item.remove();//remove element
                        target_row_empty_check(ui.sender);//update if the horizontal UL is now empty
                        
                        cmf_ajax_update();//save layout
                        
                    },
                    function() {//on cancel/no
                        var sender = $(ui.sender);//get original sender
                        sender.prepend(ui.item);//add it to the start (order is lost at this point)
                        sender.sortable("refresh");//refresh that list
                        update_list_size(sender);//update its size
                        target_row_empty_check(sender);//update that its not empty
                        
                        cmf_ajax_update();//save layout
                    });


            //make menu visible again
            set_menu_dimmed(false)
            
            //update list size of original sender
            update_list_size($(ui.sender));
            
            //remove all the targeting assist images
            $(".cmf_target_image").remove();
            
        }
    });

    /*
     * Creates the menu sortables for drag and dropping sections
     */
    $(CMF_MENU_ULS_SELECTOR_C).sortable({
        connectWith: CMF_MAIN_ROW_ULS_SELECTOR_C +", "+CMF_MAIN_UL_SELECTOR_C,//on page load it's always assumed to be single cell

        placeholder: "ui-state-highlight course_menu_placeholder",//placeholder class
        tolerance: "pointer",
        
        //when drag and drop starts starts
        start: function(e, ui) {
            //add content to placeholder to allow correct vertical alignment
            ui.placeholder.html(" &nbsp; ");
            set_menu_dimmed(true);
            //show target images to help assist users to sort
            load_sortable_targets();
            
        },
                
        //when drag and drop ends
        stop: function() {
            //remove all of the target assist images
            $(".cmf_target_image").remove();
            set_menu_dimmed(false);
        },
                
    });
    
    $(CMF_MENU_ULS_SELECTOR_C + " li div a").click(function(e) {
        e.preventDefault();
    });
    
    
    //abstract event for when a menu drop has completed
    $(CMF_MENU_ULS_SELECTOR_C).each(function(index, element) {
        element.menu_post_drag = post_menu_drag_callback;
    });
    
    //override menu dropped event for header element
    $("#cmf_header_menu_option").each(function(index, element) {
        $(element)[0].menu_post_drag = function(item) {
            var content_div = item.find(":first-child");//get first child  content div
            content_div.children().remove();//remove contents children
            content_div.css("display","block");//set css as block based
            
            //add textfield for editing header
            var text_field = $("<input>", {type:"text", class:"cmf_header_textfield", value: course_menu_strings['new_header']});
            
            //add header tag for displaying header
            var text_display = $("<h4>", {class:"cmf_header_display", style:"display:none"});
            
            //add attributes for an unsaved header element
            $(content_div).parent().attr('data_id', '-1').attr('data_type','course_menu_element_header');
            
            content_div.append(text_display);//attach display header tag to content
            content_div.append(text_field);//attach text field header tag to content
            
            //initalize events to allow double clicking of title display to allow editing field to appear
            initalize_header(content_div);
            
            
        };
    });
    
    
    //Initialize all of the horizontal sortable rows
    create_sortable_row(CMF_MAIN_ROW_ULS_SELECTOR_C);
    
    //initalize headers
    $(".cmf_header_textfield").each(function(index, header_textfield) {
       var horiz_li = $(header_textfield).parent().parent(); 
       initalize_header(horiz_li)
    });
    
    //initalize the dialog for editing the main header
    initialize_main_header_dialog();
}

/**
 * Initalizes the dialog that allows editing of the course menu main header
 */
function initialize_main_header_dialog() {
    
    //initalize header
    $(".cmf_header_dialog").dialog({
        modal: true,
        width: 930,
        height: 620,
        autoOpen: false,
        
        //on close event, pull new header html via ajax
        close: function(event, ui) {

            var json_string = JSON.stringify({course: course_menu_strings['courseid']});
            $.ajax({
                url: course_menu_strings['ajax'],
                data: {course_menu_json: json_string, operation: "get_main_header"},
                beforeSend: function(data) {
                    $(".cmf_main_header").hide(500);//hide animation
                    $(".cmf_header_dialog").dialog('destroy');//destory dialog (since all content in that div will be removed)
                    $(".cmf_header_dialog").hide();//hide dialog
                }
            
            //when ajax completed, load returned html into header
            }). done(function(data) {
                $(".cmf_main_header").children().remove();//remove all old main header content
                $(".cmf_main_header").append(data);//append new html
                initialize_main_header_dialog();//re-initalize the dialog
                $(".cmf_main_header").show(500);//show header

            });

        }
    });

    $(".cmf_main_header").click(function() {
        set_dialog_position(".cmf_header_dialog");
        $(".cmf_header_dialog").dialog('open');
    });

}

/**
 * Adds a variety of events for header elements.
 * When header displays are clicked, the edit textfield is loaded
 * When the edit textfield loses focus, the display header is shown
 * 
 * @param {object} horiz_li
 */
function initalize_header(horiz_li) {

    //on textfield losing focus, convert to header html element
    horiz_li.find(".cmf_header_textfield").blur(function(event) {
        var parent = $(this).parent();//get parent, which contains h4 & textfield
        var display = $(parent).find(".cmf_header_display");//find h4 for display

        display.text($(this).val());//load text from textfield into h4
        $(this).hide();//hide textfield
        display.show();//show display

        cmf_ajax_update();//save layout
    });

    //on h4 being clicked, load edit textfield
    horiz_li.find(".cmf_header_display").click(function(event) {
        var parent = $(this).parent();//get parent, which contains h4 & textfield
        var text = $(parent).find(".cmf_header_textfield");//find h4 for display

        $(text).val($(this).text());//load h4 data into textfield
        $(this).hide();//hide h4
        $(text).show();//show textfield
    });
}

/**
 * Default function called after an menu drag is completed - returns false
 * @param {li} item
 */
function post_menu_drag_callback(item) {
    //allow link capabilities again - firefox workaround
    $(item).find("div li a").unbind('click');
    return false;
}

/**
 * A function that sets the top menu to be dimmed or undimmed depending on the given boolean
 * 
 * @param {bool} isDimmed On true dims the menu, on false removes any dimming applied.
 */
function set_menu_dimmed(isDimmed) {
    if(isDimmed) {
        $(CMF_MENU_SELECTOR_I).css({'opacity': 0.1});
        $(CMF_MENU_SELECTOR_I).css({'z-index': -9999999999}); 
        $(CMF_MENU_ULS_SELECTOR_C).parent().css({'max-height': 0, 'overflow': 'hidden'}); 

    } else {
        $(CMF_MENU_SELECTOR_I).css({'opacity': 1});
        $(CMF_MENU_SELECTOR_I).css({'z-index': 9999999999});
        $(CMF_MENU_ULS_SELECTOR_C).parent().css({'max-height': '', 'overflow': ''}); 
    }
    
    
}

/**
 * Updates whether the given horizontal UL (CMF_MAIN_ROW_ULS_SELECTOR) is empty or not.
 * 
 * If empty CMF_MAIN_ROW_ULS_EMPTY_SELECTOR class is added, otherwise
 * it is removed.
 * 
 * @param target_row A DOM based object
 */
function target_row_empty_check(target_row) {
    
    //check if the given object has the class for a horizontal ul
    if($(target_row).hasClass(CMF_MAIN_ROW_ULS_SELECTOR))
        
        
        if($(target_row).children().length === 0)//if it has no children it is empty
            $(target_row).addClass(CMF_MAIN_ROW_ULS_EMPTY_SELECTOR);//add empty class
        else//has children - so not considered empty
            $(target_row).removeClass(CMF_MAIN_ROW_ULS_EMPTY_SELECTOR);//remove empty class
}

/**
 * Determines if the given element is the menu UL, and whether the cell type
 * is current set to 
 * 
 * @param {object} sender A DOM object
 * @returns {Boolean} true if the sender has class CMF_MENU_ULS_SELECTOR & full cell type is currently active
 */
function isFullCellFromMenu(sender) {
    
    //if the full cell type image is currently active & sender has the class for menu section ULs
    if($("#cmf_cell_selection_button_full").attr("active") == 1 &&
            $(sender).hasClass(CMF_MENU_ULS_SELECTOR))
        return true;//is a full element from the menu
    else
        return false;
}

/**
 * Converts an li, which is located in a horizontal UL into an horizontal UL header
 * Effectively, this converts an existing horizontal UL to not be sortable, and adds specific header
 * classes for the li and its parent UL.
 * 
 * Note: The horizontal UL should only have the on li_element as a child.
 * 
 * @param {object} li_element An li dom element that is contained in a horizontal UL
 */
function convert_row_to_header(li_element) {
    var parent_ul = $(li_element).parent();//get parent ul
    $(parent_ul).removeClass(CMF_MAIN_ROW_ULS_SELECTOR);//remove the normal hori row class
    $(parent_ul).addClass(CMF_MAIN_ROW_ULS_FULL_SELECTOR);//add the full hori row class
    $(parent_ul).sortable( "destroy" );//make sure a sortable doesn't exist (empty row)
    $(li_element).addClass(CMF_ROW_UL_LI_FULL_SELECTOR);//add full li class
    $(li_element).css("width", "");//remove any lingering width settings
}

/**
 * Initalizes all elements matching selector into horizontal rows
 * @param {string} selector A selector that results in a list of desires UL elements
 */
function create_sortable_row(selector) {
        
        $(selector).sortable({
        connectWith: CMF_MAIN_ROW_ULS_SELECTOR_C + ", " + CMF_MAIN_UL_SELECTOR_C + ", "+CMF_GARBAGE_UL_SELECTOR_C,//initalize rows to be connected to vert ul, hor uls, garbage
        placeholder: "ui-state-highlight course_menu_placeholder",
        
        //when drag and drop starts
        start: function(e, ui) {
            ui.placeholder.html(" &nbsp; ");//add content to placeholder to fix vert. align issues
            load_sortable_targets();//load target assist images
        },

        tolerance: "pointer",
                
        //on recieving a new LI
        receive: function(event, ui) {
    
            //If its a header (SHOULD ONLY OCCUR FOR EMPTY horizontal rows)
            if(isFullCellFromMenu(ui.sender)) {
                convert_row_to_header(ui.item);//convert into a header row
            } else {
                //resize current list due to new element
                update_list_size($(this));
                
                //resize sender list due to lost element
                update_list_size($(ui.sender));  
            }
           
            //recopy element back to menu
            onRecievedFromMenu(event, ui);
            
            //no matter what this row cannot be empty anymore
            $(this).removeClass(CMF_MAIN_ROW_ULS_EMPTY_SELECTOR);
            
            //update the sender to check if they are empty
            target_row_empty_check(ui.sender);
            
            //remove target assisting images
            $(".cmf_target_image").remove();
            
            cmf_ajax_update();
        },
                
        //on the loss of an element        
        remove: function(event, ui) {
            //check if empty
            target_row_empty_check(this);
        },
                
        //when an item appears over a row
        over: function(event, ui) {
            //make sure menu is dimmed
            set_menu_dimmed(true);
            //update size due to placeholder
            update_list_size($(this));
        },
                
        //drag/drop stops
        stop: function(event, ui) {
            //undim menu
            set_menu_dimmed(false);
            
            //remove target assist images
            $(".cmf_target_image").remove();
            
            if(!ui.item.parent().hasClass("cmf_garbage"))
                cmf_ajax_update();
        },
                
        //when sort occurs
        sort: function(event, ui) {
            //update size due to placeholder
            update_list_size($(this));

        }

    });
    }

    /**
     * Loads target assist images to help users to sort
     * 
     * The images are places on the left and right of all elements. Since drag and drops is set
     * to sort when the pointer is located to a side of an object - these images provide an indication
     * of where to place the mouse for sorting to occur.
     * 
     */
    function load_sortable_targets() {
        //create the left assist image
        var imageleft = $("<img/>", {src: course_menu_strings['wwwroot'] + "/course/format/course_menu/pix/target.png", class: "cmf_target_image cmf_t_left"});
       
        //create the right assist image
        var imageright = $("<img/>", {src: course_menu_strings['wwwroot'] + "/course/format/course_menu/pix/target.png", class: "cmf_target_image cmf_t_right"});

        //for each element we are prepending a clone of the left image, and appending a clone of the right image
        $(CMF_MAIN_UL_SELECTOR_C + " .cmf_content_wrapper").each(function(index, element) {
            $(element).prepend(imageleft.clone()).append(imageright.clone());
        });
    }

    /**
     * Updates a given list to have all child elements be an equal portion of avaliable width
     * 
     * -To get around padding and margin issues, we only utilize 60% of the overall space
     * 
     * @param {object} list An ul dom element
     */
    function update_list_size(list) {
        //we don't want to change the vertical UL element sizes
        if($(list).hasClass(CMF_MAIN_UL_SELECTOR)) return;
        
        //get all children
        var children = list.children();
        
        //get number of children
        var num_children = children.length;

        //if one of the children is the placeholder, minus one to the total
        $(children).each(function(index, element) {

            if ($(element).hasClass('ui-state-highlight')) {
                //num_children -= 1;
            }

        });

        //assign equal proportion to each element based on 60% of area avaliable
        //if the only child was the placeholder, do nothing
        if (num_children > 0) {
            var percent = 60 / num_children;
            children.css('width', percent + "%");
        }
    }

/**
 * A series of commands that are always completed when recieving an element from the menu
 * -Recloning the moved item back to the menu list
 * -Making the menu visible again
 * 
 * @param {object} event The event object generated by a sortable callback
 * @param {object} ui The UI object generated be a sortable callback.
 */
function onRecievedFromMenu(event, ui) {

    //double check that the sender is actually the menu - if not, do nothing
    if (ui.sender.hasClass(CMF_MENU_ULS_SELECTOR)) {
        var parent = ui.sender;//get menu ul
        var item = ui.item.clone();//make a copy of the item that was moved
        parent.append(item);//append the clone back to the menu

        ui.item.addClass(CMF_ROW_UL_LI_SELECTOR);//add an li 
        ui.sender[0].menu_post_drag(ui.item);
    }
    
    //make sure the menu is visible
    set_menu_dimmed(false);
}

/**
 * Create and displays a confirmation dialog specific to confirming the deletion of an element
 * 
 * 
 * @param {function} callbackyes function called on confirmed
 * @param {function} callbackno function called on denied
 */
function confirm_dialog(callbackyes, callbackno) {

    var buttons = {};

    //create the yes button
    buttons[course_menu_strings['yes']] = function() {
        callbackyes();
        $(this).dialog("close");
    };

    //create the no button
    buttons[course_menu_strings['no']] = function() {
        callbackno();
        $(this).dialog("close");
    };

    
    //create and open the confirmation dialog
    $('<div></div>').appendTo('body')
            .html('<div><h6>'+course_menu_strings['confirm_delete_message']+'</h6></div>')
            .dialog({
        modal: true, title: 'message', zIndex: 10000, autoOpen: true,
        width: 'auto', resizable: false,
        buttons: buttons
    });

}

/**
 * Determines whether the given UL list is considered a full element
 * 
 * @param {object} horz_ul
 */
function cmf_is_element_full(horz_ul) {
    
    if($(horz_ul).length !== 1) {//if the list has more than one element, its not a full element
        return false;
    }
    
    
    if($(horz_ul).hasClass('course_menu_target_row_full'))//if has the full class - true
        return true;
    else
        return false;  //otherwise its not a full based list
}

/**
 * Converts the current state of the layout into a json object
 * 
 * @returns {Object}
 */
function cmf_convert_to_json() {
    
    //get all rows in the course menu layout
    var rows = $("#course_menu_target").children();//vert lis
    
    //create inital json object
    var json = new Object();
    
    //add course id
    json.course = course_menu_strings['courseid'];
    json.elements = [];
    
    //convert each row to json
    rows.each(function(position_row, element) {//each vert li
       var horz_ul = $(element).children(':first-child');//horiz ul row
       var isfull = cmf_is_element_full(horz_ul) ? 1 : 0;//check if its full
        
       var horz_li = horz_ul.children();//grab all the horiz lis
       
       horz_li.each(function(position_element, cell_element) {//for each of the horiz lis
         var horz_li_obj = cmf_convert_horz_element_to_json(cell_element, position_row, position_element, isfull);//convert element to json
         json.elements.push(horz_li_obj);//add to json array
       });
        
    });
    
    //return json object
    return json;
}

/**
* Converts a given horizontal li (layout element) into its json equiv
* 
 * @param {object} horiz_li Horizontal li that represents the element
 * @param {int} row the current row of the element
 * @param {int} order the position of the element in the row
 * @param {int} isfull whether the current element is considered full
 * @returns {object} the json representation of the element 
 * */
function cmf_convert_horz_element_to_json(horiz_li, row, order, isfull) {
    
    if($(horiz_li).attr('data_type') === 'course_menu_element_header')//if its a header - convert to a header element json
        return cmf_convert_header_to_json(horiz_li, row, order, isfull);
    else
        return cmf_convert_cm_to_json(horiz_li, row, order, isfull);  //convert to a CM element json
}

/**
* Converts a given cm horizontal li (layout element) into its json equiv
* 
 * @param {object} horiz_li Horizontal li that represents the element
 * @param {int} row the current row of the element
 * @param {int} order the position of the element in the row
 * @param {int} isfull whether the current element is considered full
 * @returns {object} the json representation of the element 
 */
function cmf_convert_cm_to_json(horiz_li, row, order, isfull) {
    var element = cmf_convert_element_to_json(horiz_li, row, order, isfull);//convert general properties into general json
    element.cmid = $(horiz_li).attr("cmid");//add cmid to json
    
    return element;
}

/**
* Converts a given cm horizontal li (layout element) into its json equiv
* 
 * @param {object} horiz_li Horizontal li that represents the element
 * @param {int} row the current row of the element
 * @param {int} order the position of the element in the row
 * @param {int} isfull whether the current element is considered full
 * @returns {object} the json representation of the element 
 */
function cmf_convert_header_to_json(horiz_li, row, order, isfull) {
    var element = cmf_convert_element_to_json(horiz_li, row, order, isfull);
    element.text = $(horiz_li).find('.cmf_header_textfield').val();
    
    //return json
    return element;
}

/**
* Generates a "standard" horizontal li (layout element) into its json equiv
* 
 * @param {object} horiz_li Horizontal li that represents the element
 * @param {int} row the current row of the element
 * @param {int} order the position of the element in the row
 * @param {int} isfull whether the current element is considered full
 * @returns {object} the json representation of the element 
 */
function cmf_convert_element_to_json(horiz_li, row, order, isfull) {
    var element = new Object();
    element.data_type = $(horiz_li).attr("data_type");//get type of element
    element.data_id = $(horiz_li).attr("data_id");//get general id of element
    
    element.position_row = row;//set row #
    element.position_order = order;//set position in row
    element.is_full = isfull;//set whether its full or not
    
    //return standard element json object
    return element;
}

/**
 * Sends an ajax request to save the current state of the course menu instance,
 * and reloads the HTML based on the ajax return
 * 
 */
function cmf_ajax_update() {
    var json_obj = cmf_convert_to_json();//get current course menu state as json
    var json_string = JSON.stringify(json_obj);//convert to json string
    
    //make ajax call
   $.ajax({
      url: course_menu_strings['ajax'],//url for ajax calls
      data: {course_menu_json:json_string, operation:"update"},//data to be sent
      beforeSend: function (data) {//destroy all the sortable abilities during ajax send
        $(CMF_MENU_ULS_SELECTOR_C).sortable( "destroy" );
        $(CMF_MAIN_UL_SELECTOR_C).sortable( "destroy" );
        $(CMF_GARBAGE_UL_SELECTOR_C).sortable( "destroy" );
        $(CMF_MAIN_ROW_ULS_SELECTOR_C).sortable( "destroy" );
        
        //set loading image
        var div_loading = $("<div>", {id:"cmf_ajax_loading", style:"text-align:center"} );
        var loading_img = $("<img>", {src:course_menu_strings['wwwroot']+"/course/format/course_menu/pix/ajax.gif", style:"margin:auto"});
        $(div_loading).append(loading_img);
        
        $(".course_menu_content_main").before(div_loading);
        $("#course_menu_content_main").fadeTo( 500, 0.4 );
        
      }
      //when ajax has completed
       }).
      done(function ( data ) {
        
        //remove all of the layout content
        $("#course_menu_content_main").remove();
        
        //load new html data
        $("#cmf_ajax_loading").after(data);
        
        //dim content
        $("#course_menu_content_main").fadeTo( 0, 0.4 );
        
        //remove loading image
        $("#cmf_ajax_loading").remove();
        
        //run all of the inits for sortables
        sortables_init();
        update_icon_visibility();
        
        //reset the current button mode (full/single mode) by clicking twice
        $(".cmf_cell_selection_button[active=1]").click();
        $(".cmf_cell_selection_button[active=1]").click();
        
        //undim content
        $("#course_menu_content_main").fadeTo( 500, 1 );
        
      });
      
  
            
  
    
}

/**
 * Sets a dialogs position to be within the current viewpoint, centered horizontally,
 * and 200 pxs from the top of the view
 * 
 * @param {string} dialog_selector The jquery selector for the dialog(s) to be positioned
 */
function set_dialog_position(dialog_selector) { 
            var dialog = $(dialog_selector);
                
            //get full width of window
            var fullwidth = $(window).width();
            
            //get dialog width
            var width = dialog.dialog( "option", "width" );
            
            //need to determine the leftover space: window width - size of dialog: then half on each side!
            var leftoffset = (fullwidth - width) / 2;
            
            //always have dialog 200 below top of viewpane
            var topoffset = 200;
            
            //set our dynamic position
            dialog.dialog('option', 'position',  [leftoffset, topoffset]);
}




/**
 * Initialize the listener for when the iframe main header submits and sends a message 
 * 
 * When the main header form in the iframe is submitted - the dialog closes
 * 
 * Based on:
 * http://davidwalsh.name/window-iframe
 */
function iframe_listener() {
// Create IE + others compatible event handler
    var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod === "attachEvent" ? "onmessage" : "message";

    // Listen to message from child window (main header form)
    //when it fires close the dialog
    eventer(messageEvent, function(e) {
        $(".cmf_header_dialog").dialog('close');
    }, false);

}





// Javascript functions for Topics course format

M.course = M.course || {};

M.course.format = M.course.format || {};

/**
 * Get sections config for this format
 *
 * The section structure is:
 * <ul class="topics">
 *  <li class="section">...</li>
 *  <li class="section">...</li>
 *   ...
 * </ul>
 *
 * @return {object} section list configuration
 */
M.course.format.get_config = function() {
    return {
        container_node: 'ul',
        container_class: 'topics',
        section_node: 'li',
        section_class: 'section'
    };
}

/**
 * Swap section
 *
 * @param {YUI} Y YUI3 instance
 * @param {string} node1 node to swap to
 * @param {string} node2 node to swap with
 * @return {NodeList} section list
 */
M.course.format.swap_sections = function(Y, node1, node2) {
    var CSS = {
        COURSECONTENT: 'course-content',
        SECTIONADDMENUS: 'section_add_menus'
    };

    var sectionlist = Y.Node.all('.' + CSS.COURSECONTENT + ' ' + M.course.format.get_section_selector(Y));
    // Swap menus.
    sectionlist.item(node1).one('.' + CSS.SECTIONADDMENUS).swap(sectionlist.item(node2).one('.' + CSS.SECTIONADDMENUS));
}

/**
 * Process sections after ajax response
 *
 * @param {YUI} Y YUI3 instance
 * @param {array} response ajax response
 * @param {string} sectionfrom first affected section
 * @param {string} sectionto last affected section
 * @return void
 */
M.course.format.process_sections = function(Y, sectionlist, response, sectionfrom, sectionto) {
    var CSS = {
        SECTIONNAME: 'sectionname'
    },
    SELECTORS = {
        SECTIONLEFTSIDE: '.left .section-handle img'
    };

    if (response.action == 'move') {
        // If moving up swap around 'sectionfrom' and 'sectionto' so the that loop operates.
        if (sectionfrom > sectionto) {
            var temp = sectionto;
            sectionto = sectionfrom;
            sectionfrom = temp;
        }

        // Update titles and move icons in all affected sections.
        var ele, str, stridx, newstr;

        for (var i = sectionfrom; i <= sectionto; i++) {
            // Update section title.
            sectionlist.item(i).one('.' + CSS.SECTIONNAME).setContent(response.sectiontitles[i]);
            // Update move icon.
            ele = sectionlist.item(i).one(SELECTORS.SECTIONLEFTSIDE);
            str = ele.getAttribute('alt');
            stridx = str.lastIndexOf(' ');
            newstr = str.substr(0, stridx + 1) + i;
            ele.setAttribute('alt', newstr);
            ele.setAttribute('title', newstr); // For FireFox as 'alt' is not refreshed.
        }
    }
}
