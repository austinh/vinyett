
/*
 * @var boolean has_edited_name
 */
var has_edited_name = false;


/**
 * Runs the functions needed to start up the accounts page's 
 * cool effects.
 *
 * @return null
 */
function account_init() 
{ 
    $(".account form:not(#photo_icon_form)").submit(function () { return false; }); //Kill all forms from submitting by default.
    register_account_templates();

}


/**
 * Registers the handlebar templates...
 *
 * @return null 
 */
function register_account_templates() 
{ 
    window.confirmation_template = Handlebars.compile($("#confirmation_handlebar").html());
}


/**
 * Watches a row for clicking!
 *
 * @param string name Name of the editable row
 * @param string url A URL to submit
 *
 * @return null
 */
function account_watch_row(name) 
{ 
    $("#edit_"+name+"_button.edit_done_link").click(function() {
        $("#"+name+"_row").attr("data-open", true);
        
        $("#"+name+"_row .short_example").hide();
        $("#"+name+"_row .edit_options").show();
        
        $("#"+name+"_row").addClass("selected_option_row");
        $("#"+name+"_row").find(".edit_done_link").hide();
        
        watch_for_changes(name);
        
        $(this).unbind("click");
    });
}


/**
 * Watches the inputs in a form for changes and updates
 * the global variable responsible for knowing that change
 *
 * @param string name Name of the editable row
 *
 * @return 
 */
function watch_for_changes(name) 
{ 

    $("#"+name+"_form input").change(function() 
    { 
        eval("has_edited_" + name + " = true;"); //Ugh.
    })

}


/**
 * Reverts a row to its minimized state.
 *
 * @param string name Name of the row to minimize
 *
 * @return null
 */
function revert_row(name) 
{ 
    $("#"+name+"_row").attr("data-open", false);

    $("#"+name+"_row").removeClass("selected_option_row");
    $("#"+name+"_row").find(".edit_done_link").show();
        
    $("#"+name+"_row .short_example").show();
    $("#"+name+"_row .edit_options").hide();
    
    account_watch_row(name);
    
}


/**
 * Checks to see if it's OK to revert the field and
 * if not, asks the user to confirm the loss of dataz
 *
 * @return null
 */
function revert_name_changes() 
{ 
    if(has_edited_name == false) 
    { 
        do_revert_name_changes();
        return;
    }

    //Build a quick modal for the confirmation
    
    var model = { 
        title: "Discard your changes?",
        content: "You can either discard your name changes or continue editing them...",
        buttons: { 
            submit: { 
                onclick: "kill_window_qtip();",
                text: "Continue"
            },
            cancel: { 
                onclick: "do_revert_name_changes(); kill_window_qtip();",
                text: "Discard"
            }
        }
    };
    
    $("body").qtip({
        id: "confirmation_modal",
        content: {
            text: confirmation_template(model)
        },
        position: {
            my: 'center', // ...at the center of the viewport
            at: 'center',
            target: $(window)
        },
        show: {
            solo: true, // ...and hide all other tooltips...
        	modal: {
    			on: true
    		}, // ...and make it modal
            ready: true //Show when ready
        },
        hide: "unfocus",
        style: {
            classes: 'ui-tooltip-dialog-modal',
            width: 402
        }, 
        events: { 
            hide: function(e, api) 
            { 
                $(this).qtip('destroy'); //Dispose of the tip when it's done with.
            }
        }

    });
    
    return;
}


/**
 * Reverts the name fields to their defaults, then calls
 * to revert the form row to its minimized state
 *
 * @return null
 */
function do_revert_name_changes()
{ 
    $("#name_form #form_first_name").val(Global.user.first_name);
    $("#name_form #form_last_name").val(Global.user.last_name);
    $("#name_form #form_password").val("");
    
    has_edited_name = false; //Turn this off...
    
    revert_row("name");
    
    return;
}


/**
 * Reverts the name form buttons to an editable state
 *
 * @return null
 */
function do_revert_name_form_state()
{ 
    $("#name_form button[type=cancel]").show();
    $("#name_form input").prop("disabled", false);
    $("#name_form input[type=password]").val(null);
    $("#name_form button[type=submit]").prop("disabled", false).html("Save");
    
    return;
}


/**
 * Saves name changes...
 *
 * @return 
 */
function watch_save_name_changes() 
{ 

$("#name_form").submit(function()
{
    var form_data = $(this).serialize();
    
    if(has_edited_name == false) { 
        do_revert_name_changes();
    }

    $("#name_form button[type=cancel]").hide();
    $("#name_form input").prop("disabled", true);
    $("#name_form button[type=submit]").prop("disabled", true).html("Saving...");
    
    $.ajax({
        url: Global.settings.rurl+'ajax/account/update/name',
        data: form_data,
        type: "POST",
        dataType: "json"
    }).done(function (data) {
		if(data.error) 
		{ 
		    var error_content = data.error.content.errors.join("<br />");
    		throw_simple_dialog(data.error.title, error_content);
    		do_revert_name_form_state();
    		return;
		}
		//Name was saved and we have a new name!
		Global.user.first_name = data.user.name.first;
		Global.user.last_name = data.user.name.last;
		
		$("#name_example").html(data.user.name.display);
		
		do_revert_name_form_state();
		do_revert_name_changes();
		
	}).fail(function() { 
    	throw_simple_dialog("Unable to update your name", "Oh no! It looks like there was an error and nothing happened. Please try again.");
    	
    	do_revert_name_form_state();
    	
    	return; 
    });    
});  
    
}






