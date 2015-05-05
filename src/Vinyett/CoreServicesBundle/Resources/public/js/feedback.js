define(['require', 
        'json!/..'+Global.settings.rurl+'ajax/pagelet/core?templates=feedback_app',
        'frameworks/spine', 
        'frameworks/handlebars',
        'plugins/qtip.jquery'], function(require, templates)
{

    /*
    Whoops this is overkill...
    
    var Feedback = Spine.Model.sub();
    Feedback.configure("Feedback", "content", "created_at");
    Feedback.extend(Spine.Model.Ajax);
    Feedback.extend(
    {
        url: Global.settings.rurl + "rest/feedback"
    });
    */

   /*
    * Feedback app 
    */
    var FeedbackApp = Spine.Controller.sub(
    {

        init: function()
        {
            var that = this;
            
            $.each(templates, function(i, template) {
                that["template_"+template.name] = Handlebars.compile(template.template);
            });
            
            this.html(this.getTemplate("feedback_app")());
        },
        
        getTemplate: function(template_name)
        { 
            return this["template_"+template_name];
        }, 
        
        open: function(photo)
        {
            var that = this;
            
            //this.window_controller = new WindowController({ app: this, photo: photo });
            
            $("body").qtip({
                id: "feedback_app",
                content: that.el,
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
                    classes: 'ui-tooltip-popover-feedback',
                    width: 500
                }, 
                events: { 
                
                    show: function(e, api)
                    { 
                        $('.column').height($(this).height());
                        $("body").addClass("noscroll");
                    },
                    
                    hide: function(e,api)
                    { 
                        $("body").removeClass("noscroll");
                    },
                
                    hidden: function(e, api) 
                    { 
                        $(this).qtip('destroy'); //Dispose of the tip when it's done with.
                    }
                }
        
            });
        }
    });

    return new FeedbackApp;

});