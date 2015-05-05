define(['require', 'jquery', 'frameworks/spine', 'frameworks/handlebars', 'plugins/qtip.jquery', "plugins/color", 'frameworks/moment', 'plugins/typeahead', 'notification/toolbar'], function(require) {

    var notiification_toolbar = require('notification/toolbar');
    
    var Templates = Spine.Class.sub();
    Templates.include(
    {
        dialog_template: Handlebars.compile($("#dialog_handlebar").html())
    });
    
    var ToolbarController = Spine.Controller.sub({ 
    
        el: $("#main_header"),
        
        init: function() 
        { 
            if(!this.templates) throw "@Templates required.";

            $(document).on('click', 'a.trigger_upload', this.proxy(this.loadUploadWidget));

            this.setupSearch();
        }, 
        
        setupSearch: function() 
        { 
            var that = this;

            $(document).on('click', 'a.search_click_through', function() {
                //alert("test");
                var $this = $(this), query = that.search.val();
                $this.attr('href', $this.attr('href').replace('%QUERY', query));
            });
        
            var source = $("#search_person_template").html(),
                search_template = Handlebars.compile(source);
        
            this.search.typeahead([
            {
                name: 'following',
                template: search_template,
                limit: 5,
                prefetch: {
                    url: Global.settings.rurl+"rest/friends",
                    ttl: 100,
                    filter: function(data)
                    { 
                        var datams = new Array();
                        $.each(data, function(i, f) { 
                            var datam = new Object;
                            var friend = f.following;
                            datam.value = friend.username;
                            datam.tokens = [friend.username, friend.first_name, friend.last_name, friend.email];
                            datam.profileImageUrl = friend.photo_square;
                            datam.url = Global.settings.rurl+"photos/"+friend.url;
                            
                            datams.push(datam);
                        });
                        return datams;
                    },
                },
                footer: '<div class="search_footer"><a class="search_click_through" href="'+Global.settings.rurl+'users/search?term=%QUERY">Search for more users...</a></div>'
            }
            ])
            .on("typeahead:selected", function($e, user) { 
                    window.location = user.url;
            });
        },
        
        events: { 
            "click .explore_more": "openExploreMenu",
            "click .user_more": "openUserMenu",
            "click .notification_menu": "openNotificationWindow",
            "click .search": "activateSearch",
            "click .upload_widget" : "loadUploadWidget",
            "click .feedback_widget": "openFeedbackWidget",
            "focus .search": "focusSearch",
            "blur .search": "blurSearch",
        },
        
        elements: { 
            ".user_more": "user_menu_button",
            ".explore_more": "explore_menu_button",
            ".notification_menu": "notification_menu_button",
            ".upload_widget": "upload_button",
            ".search": "search"
        },
        
        focusSearch: function() 
        { 
            this.search.animate({ width: '275px', backgroundColor: "#ffffff" });
        },
                
        blurSearch: function() 
        { 
            if(this.search.val() == "") {
                this.search.animate({ width: '150px', backgroundColor: "#3f3f3f" });
            }
        },
        
        openNotificationWindow: function() 
        { 
            if(!$(this.notification_menu_button).data("qtip")) {
                
                $(this.notification_menu_button).qtip({
                    content: $(".notification_menu_popover").html(),
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
                        width: 400,
                    	classes: "ui-tooltip-plain ui-tooltip-shadow follow_options_tooltip toolbar_tooltip notification_tooltip"
                    },
                    show: {
                    	event: "mouseup",
                    	ready: true,
                    },
                    hide: {
                    	fixed: true,
                    	event: "unfocus"
                    },
                    events: {
                		render: function(event, api) {
                			
                			$.ajax({
                    			url: Global.settings.rurl+'notifications/show',
                    			dataType: 'html'
                			}).done(function(data) { 
                    			var c = new notiification_toolbar({ el: data });
                    			$(".notify_list").html(c.el);
                    			$('.notification_center .bubble').hide();
                			});
                			
                		}
                	}
                });
            }
        }, 
        
        openExploreMenu: function()
        { 
            if(!$(this.explore_menu_button).data("qtip")) {
                $(this.explore_menu_button).qtip({
                    content: "What can you find?",
                    position: {
                    	at: "bottom center",
                    	my: "top center",
                    	resize: false
                    },
                    style: {
                    	tip: {
                            width: 18,
                            height: 8,
                            offset: 50
                        },
                    	width: 200,
                    	classes: "ui-tooltip-plain ui-tooltip-shadow follow_options_tooltip toolbar_tooltip"
                    },
                    show: {
                    	event: "mouseup",
                    	ready: true,
                    },
                    hide: {
                    	fixed: true,
                    	event: "unfocus"
                    }
                });
            }
        }, 
        
        openUserMenu: function() 
        {
            if(!$(this.user_menu_button).data("qtip")) {
                $(this.user_menu_button).qtip({
                    content: $(".user_menu_popover").html(),
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
                    	event: "mouseup",
                    	ready: true,
                    },
                    hide: {
                    	fixed: true,
                    	event: "unfocus"
                    }
                });
            }
            
        },
        
        loadUploadWidget: function(e) 
        {
            e.preventDefault();
            if(!this.upload_button.data("qtip")) {
                this.upload_button.qtip(
                {
                    id: "upload_widget_popover",
                    content: '<div class="upload_toolbar_widget"><div class="uploader_loading"><img src="/images/ajax-upload-load.gif" /></div></div>',
                    position: {
                        my: 'top left',
                        // ...at the center of the viewport
                        at: 'bottom left',
                        adjust: {
                            y: 3
                        }
                    },
                    show: {
                        solo: true,
                        ready: true,
                        event: 'click'
                    },
                    hide: {
                        event: 'none'
                    },
                    style: {
                        tip: {
                            width: 18,
                            height: 8,
                            corner: 'top center'
                        },
                        width: 300,
                        classes: 'ui-tooltip-shadow ui-tooltip-organizer-popover toolbox-tooltip upload_widget_tooltip'
                    },
                    events: {
                        render: function(e, api) 
                        { 
                            require(["photo/upload"], function(UploadApp) 
                            { 
                                var uploader_app = new UploadApp({});
                            });
                        }
                    }
                });
            }
        },
        
        openFeedbackWidget: function() 
        { 
            showClassicWidget();
        },
        
        activateSearch: function() 
        { 
            
        }
        
    });

    var VinyettApp = Spine.Controller.sub({
    
        el: $("body"),
    
        init: function()
        {
            var that = this;

            this.bootstrap();
        
            if(this.getUser)
                this.toolbar = new ToolbarController({templates: this.templates, user: this.getUser()});
        
            this.setTimeFormats;
        },
        
        bootstrap: function() {    
            this.user = Global.user;
            this.templates = this.precompileTemplates();
        },
        
        precompileTemplates: function() 
        {  
            return new Templates;
        },
        
        getUser: function() { 
            return this.user;
        },
        
        setTimeFormats: function() 
        { 
            //$("abbr.timeago").timeago(); Replace with moment...
        }, 
        
        throw_simple_dialog: function(title, description) 
        {
            $("body").qtip({
                id: "dialog_modal",
                content: {
                    text: this.templates.dialog_template({ 
                        title: title,
                        content: description
                    })
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
        }, 
        
        kill_window_dialog: function() 
        { 
            $("body").qtip('api').hide();
        }, 


      
    });    
    
    
    
    
    return VinyettApp;
 
});