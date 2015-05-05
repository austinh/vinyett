define(['require', 'jquery', 'frameworks/spine'], function()
{

    var InviteApp = Spine.Controller.sub(
    {
        elements: { 
            ".send_invite_button": "send_invite_button",
            ".invite_form": "invite_form"
        },
    
        events: {
            "submit .invite_form": "send",
            "click .cancel_button": "cancel"
        },

        init: function()
        {
           var controller = this;
        
           $.ajax({
              url: Global.settings.rurl+'invite-friends',
              type: 'GET',
              dataType: 'html'
            })
            .done(function(data) { 
                controller.html(data);
                controller.openPopover();
            })
            .fail(function() { alert("Unable to load the inviter (try to reload the page?)"); });
        },
        
        openPopover: function()
        {
            $("body").qtip({
                id: "invite_View",
                content: this.el,
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
                    classes: 'ui-tooltip-popover-modal invite-modal'
                }, 
                events: { 
                    hidden: function(e, api) 
                    { 
                        $(this).qtip('destroy'); //Dispose of the tip when it's done with.
                    }
                }
            });
        },
        
        cancel: function(e) 
        { 
            e.preventDefault();
            $("body").qtip("hide");
        },
        
        send: function(e) 
        { 
            e.preventDefault();
            this.send_invite_button.html("Sending").prop("disabled", "disabled");
            
            $.ajax({
              url: Global.settings.rurl+'invites/send',
              type: 'POST',
              data: $('.invite_form').serialize(),
              dataType: 'json'
            })
            .always(function() { alert("Invites have been sent out!"); $("body").qtip("hide"); });
        }
    });
        
    return InviteApp;
});