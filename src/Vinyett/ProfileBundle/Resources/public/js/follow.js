function follow_button_init(are_friends) 
{ 
    if(are_friends == true)
    {
        enable_popover();
    } else { 
        watch_follow_bubble();
    }
}

function watch_follow_bubble() 
{

    $("#follow_button").click(function() {
    
        $("#follow_button").html('Loading...');
        
        $.ajax({
          type: "POST",
          dataType: "json",
          url: Global.settings.rurl+"ajax/follow/add/"+Global.profile.id
        }).done(function(r) {
            $("#follow_button").html('<img src="/images/list_check.png" /> Following');
            $("#follow_connection_id").val(r.follow_data.follow_id);
            enable_popover();
        }).fail(function() { 
            alert("Hmmm... seems like there was an error and we couldn't follow this user. Maybe refresh and try again?"); 
        });
    });

}

function enable_popover() 
{ 
  $("#follow_button").qtip({
        content: $('.follow_popover'),
        position:
        {
            at: 'bottom center',
            my: 'top center'
        },
        style:
        {
            classes: 'ui-tooltip-plain ui-tooltip-shadow follow_options_tooltip',
        	tip: {
    			corner: true,
    			width: 16, 
    			height: 8
    		}
        },
    	hide: {
    		event: 'unfocus'
    	}
    });
    
    enable_popover_options();
}

function save_popover_change(detail, value) 
{ 
    var connection_id = $("#follow_connection_id").val();
    
    $.ajax({
      type: "POST",
      url: Global.settings.rurl+"ajax/follow/"+connection_id+"/edit/"+detail+"/"+value
    }).done(function() {
    }).fail(function() { alert("There was an error and your follow detail wasn't updated... Please refresh and try again."); });
}

function enable_popover_options() {

    /* Could probably rewrite this to be reusable, but copy and paste is cool */
    $("#friend_selector").click(function() { 
        if($("#friend_selector").attr("data-is-checked") == "true") { 
            $("#friend_selector").addClass("unselected").html("...is a friend?");
            $("#friend_selector img.check").remove();
            $("#friend_selector").attr("data-is-checked", "false");
            save_popover_change("friend", 0);
        } else { 
        
            $("#friend_selector").html('Friend <img src="/images/list_check.png" class="check" />').removeClass("unselected");
            $("#friend_selector").attr("data-is-checked", "true");
            save_popover_change("friend", 1);
        }
    });

    $("#family_selector").click(function() { 
        if($("#family_selector").attr("data-is-checked") == "true") { 
            $("#family_selector").addClass("unselected").html("...is family?");
            $("#family_selector img.check").remove();
            $("#family_selector").attr("data-is-checked", "false");
            save_popover_change("family", 0);
        } else { 
        
            $("#family_selector").html('Family <img src="/images/list_check.png" class="check" />').removeClass("unselected");
            $("#family_selector").attr("data-is-checked", "true");
            save_popover_change("family", 1);
        }
    });
    
    $("#photofeed_selector").click(function() { 
        if($("#photofeed_selector").attr("data-is-checked") == "true") { 
            $("#photofeed_selector").addClass("unselected").html("Hidden from photofeed");
            $("#photofeed_selector img.check").remove();
            $("#photofeed_selector").attr("data-is-checked", "false");
            save_popover_change("photofeed", 0);
        } else { 
            $("#photofeed_selector").html('Shown in photofeed <img src="/images/list_check.png" class="check" />').removeClass("unselected");
            $("#photofeed_selector").attr("data-is-checked", "true");
            save_popover_change("photofeed", 1);
        }
    });

}