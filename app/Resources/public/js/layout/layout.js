


/**
 * Hooks to set up the layout.
 *
 * @return 
 */
function layout_init() 
{ 
    
    window.dialog_template = Handlebars.compile($("#dialog_handlebar").html());
}

/**
 * Creates a simple dialog (with one option to continue).
 *
 * @param string title Title of dialog
 * @param string description Description for title
 *
 * @return 
 */
function throw_simple_dialog(title, description) 
{ 
    var model = { 
        title: title,
        content: description
    }

    $("body").qtip({
        id: "dialog_modal",
        content: {
            text: dialog_template(model)
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
}


/**
 * Shortcut for buttons to refer to when closing qtips attached
 * to the window (most global ones, are).
 *
 * @return null
 */
function kill_window_qtip() 
{ 
    $("body").qtip('api').hide();
}



function generate_notifications_bubble(num)
{ 
	if(num == 0) 
	{ 
		$("#notifications_status .bubble").remove();
		return;
	}
	if($("#notifications_status .bubble").length > 0) 
	{ 
		$("#notifications_status .bubble").html(num);
	} else { 
		jQuery('<div/>', {
		class: "bubble",
    text: num
		}).appendTo('#notifications_status');
	}
}

function enable_notifications_popover() 
{ 

  $(".notification_icon").qtip({
	content: {
		ajax: {
			url: Global.settings.rurl+"notifications/show"
		},
		text:'<div class="tooltip_headbar tooltip_title">Notifications</div><div class="tooltip_content tooltip_loading_content">Loading<div class="clearfix"></div></div>'
	},
	position: {
		at: "bottom center",
		my: "top center",
		resize: false
	},
	style: {
		tip: {
            width: 18,
            height: 8,
            offset: 105
        },
		width: 300,
		classes: "ui-tooltip-shadow ui-tooltip-plain ui-tooltip-notificationBubble"
	},
	show: {
		delay: 10,
		event: "mouseup"
	},
	hide: {
		fixed: true,
		event: "unfocus"
	}
}
);

}


/**
 * Builds the account popover
 *
 * @return 
 */
function enable_account_popover() 
{ 

  $(".account_icon").qtip({
	content: {
		text: $("#account_popover")
	},
	position: {
		at: "bottom center",
		my: "top center",
		resize: false
	},
	style: {
		tip: {
            width: 18,
            height: 8,
            offset: 105
        },
		width: 300,
		classes: "ui-tooltip-plain ui-tooltip-shadow follow_options_tooltip toolbar_tooltip"
	},
	show: {
		delay: 10,
		event: "mouseup"
	},
	hide: {
		fixed: true,
		event: "unfocus"
	}
}
);

}


function clickable_search() 
{ 
  $(".search").focus(function() 
  { 
    $(".search").addClass("active_search").attr("placeholder", "Start typing to search...").animate({width: 250}, 'fast');
  });
  $(".search").blur(function()
  { 
    $(".search").removeClass("active_search");
    if($(".search").attr("placeholder") == "Start typing to search...")
    { 
      $(".search").attr("placeholder", "Search Merge").animate({width: 150}, 'fast');
    }
  });
}


function toolbar_init() 
{ 
    clickable_search();
    enable_notifications_popover();
    enable_account_popover();
}