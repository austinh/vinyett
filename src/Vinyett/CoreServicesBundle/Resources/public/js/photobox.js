define(['require', 
        'json!/..'+Global.settings.rurl+'ajax/pagelet/photos?templates=photobox,photo_toolbar,photobox_sidebar,photobox_comment',
        //'json!/..'+Global.settings.rurl+'/rest/photo',
        'jquery', 
        'frameworks/spine', 
        'frameworks/handlebars',
        'frameworks/underscore',  
        'plugins/qtip.jquery', 
        'photo/photo', 
        'photo/collection', 
        'photo/comment', 
        'frameworks/moment'], function(require, templates, photo)
{

    var Photo = require("photo/photo");
    var Collection = require("photo/collection");
    var Comment = require("photo/comment");
    var Moment = require("frameworks/moment");

    Photo.hasMany('comments', 'photo/comment');

   /*
    * Photobox app 
    */
    var PhotoboxApp = Spine.Controller.sub(
    {

        init: function()
        {
            var that = this;
            
            $.each(templates, function(i, template) {
                that["template_"+template.name] = Handlebars.compile(template.template);
            });
            
            this.registerHelpers();
        },
        
        registerHelpers: function()
        {
            /* Register helpers */
            /* Deleteable, before globally migraring we've got to fix param 2 */
            Handlebars.registerHelper('deletable', function(object, fn) {
              return ((_.contains(object.options, "DELETE")) ? fn(this) : null );
            });
            Handlebars.registerHelper('editable', function(object, fn) {
              return ((_.contains(object.options, "EDIT")) ? fn(this) : null );
            });
            
            /* Favorites rendering */
            Handlebars.registerHelper('limitedeach', function(context, options) {
              var ret = "";
              var limit = 2
              
              if(limit > context.length) 
              { 
                  limit = context.length;
              }
              for(var i=0, j=2; i<limit; i++) {
                ret = ret + options.fn(context[i]);
                if(context.length == 2 && i != limit-1) { 
                    ret = ret + " and ";
                }
 
                if(context.length > 2) {
                    ret = ret + ", ";
                }
              }
              
              return ret;
            });
            
            /* Extra favorites rendering helpers */
            Handlebars.registerHelper('extraeach', function(favorites, fn) {
              return (favorites.length > 2) ? fn(favorites.length-2) : null;
            });
            Handlebars.registerHelper('morethanone', function(favorites, fn) {
              return (favorites.length > 1) ? fn() : null;
            }); 
            
            Handlebars.registerHelper('ifAnd', function(v1, v2, options) {
              if(v1 && v2) {
                return options.fn(this);
              }
              return options.inverse(this);
            });
            
            Handlebars.registerHelper('unlessAnd', function(v1, v2, options) {
              if(!v1 && !v2) {
                return options.fn(this);
              }
              return options.inverse(this);
            });
        },
        
        getTemplate: function(template_name)
        { 
            return this["template_"+template_name];
        }, 

        open: function(photo)
        {
            var that = this;
        
            if(!Photo.exists(photo))
            { 
                $("body").qtip({
                    id: "photo_loading",
                    content: '<img src="/images/ajax-loader.gif" />',
                    position: {
                        my: 'center', // ...at the center of the viewport
                        at: 'center',
                        target: $(window)
                    },
                    hide: "none",
                    show: {
                    	modal: {
                			on: true
                		}, 
                		blur: false,
                		escape: false,
                        ready: true
                    },
                    style: {
                        classes: 'ui-tooltip-popover-modal ui-waiting-popover ui-tooltip-loading'
                    }, 
                    events: { 
                        show: function(e, api)
                        { 
                            $.ajax({
                                url: Global.settings.rurl+"rest/photos/"+photo,
                                data: 'GET',
                                dataType: 'json'
                            }).done(function(data) { 
                                
                                var loaded_photo = new Photo(data);
                                loaded_photo.save({ 
                                    ajax: false
                                });
                                
                                that._open(loaded_photo);
                            });
                        },
                        hidden: function(e, api) 
                        { 
                            $(this).qtip('destroy');
                        }
                    }
            
                });
             } else { 
                 var _photo = Photo.find(photo);
                 this._open(_photo);
             }
        
        },
        
        _open: function(photo)
        {
            var that = this;
            
            this.window_controller = new WindowController({ app: this, photo: photo });
            this.window_controller.render();
            
            $("body").qtip({
                id: "photo_View",
                content: that.window_controller.el,
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
                    classes: 'ui-tooltip-popover-modal'
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
    
    
    var WindowController = Spine.Controller.sub(
    { 
        className: "photobox_window",
    
        freeze_toolbar: true,
    
        elements: 
        { 
            ".inner_photo_view": "sidebar",
            ".photo_toolbar": "toolbar",
            ".window_control": "window_controls"
        },

        events: 
        {
            "click .floating_close_button": "close",
            "mouseenter .photo_view": "showPhotoViewControls",
            "mouseleave .photo_view": "hidePhotoViewControls",
        },

        init: function()
        {
           if(!this.app) throw "@app dependency";
           if(!this.photo) throw "@photo required";
           
           this.photo.bind("destroy", this.proxy(this.close));
           
           var that = this;
           
            setTimeout(function() {
                that.freeze_toolbar = false;
                that.hidePhotoViewControls();
            }, 1000);
        },
        
        showPhotoViewControls: function() 
        {
            this.window_controls.fadeIn(250);
        },
        
        hidePhotoViewControls: function()
        { 
            if(this.freeze_toolbar == false)
                this.window_controls.fadeOut(200);
        },
        
        close: function() 
        { 
            $("body").qtip("hide");
        },
        
        render: function()
        { 
            this.html(this.template());
            
            this.sidebar_controller = new SidebarController({ app: this.app, photo: this.photo })
            this.sidebar.html(this.sidebar_controller.render().el);
            
            this.toolbar_controller = new ToolbarController({ app: this.app, photo: this.photo });
            this.toolbar.html(this.toolbar_controller.render().el);
            return this;
        }, 
        
        template: function()
        { 
            return this.app.getTemplate("photobox")({ photo: this.photo });
        }
    });
    
   /* 
	* Controller for the sidebar of the photo
	*/
    var SidebarController = Spine.Controller.sub(
    {
        className: "sidebarable",
    
        elements: 
        { 
            ".comment_list": "comments",
            ".comment_adder_container": "comment_adder",
            ".waiting_text": "waiting_text",
            ".commentbox_interact": "commentbox_interact",
            ".comment_textbox": "comment_textbox",
            ".photo_information": "photo_information",
            ".edit_dialog": "photo_editing",
            ".edit_details_form": "photo_form",
            ".timestamp": "timestamp"
        },

        events: 
        {
            "click .ready_comment_container": "openCommentbox",
            "click .post_comment_button": "saveComment",
            
            "click .stream_favorite_button": "toggleFavorite",
            
            "mouseenter .photo_information": "showEditable",
            "mouseleave .photo_information": "hideEditable",
            "click .photo_information": "editPhoto",
            "click .cancel_photo_changes": "uneditPhoto",
            "click .save_photo_changes": "saveChanges",
            "click .delete_photo": "deletePhoto"
        },

        init: function()
        {
           if(!this.app) throw "@app dependency";
           if(!this.photo) throw "@photo required";
           
           Comment.bind("create", this.proxy(this.addComment));
           
           this.photo.bind("favorited", this.proxy(this.render));
           this.photo.bind("change", this.proxy(this.render));
        },
        
        render: function()
        { 
            var that = this;
            this.html(this.template());
            
            $.each(this.timestamp, function(i, t) {
                $(t).livestamp($(t).data('livestamp'));
            });
            
            $.each(this.photo.comments().all(), function(i, comment) { 
                that.addComment(comment);
            });
            return this;
        },
        
        updateFavoriteBlock: function() 
        { 
            this.render();
        },
        
        addComment: function(comment) 
        { 
            var comment_controller = new CommentController({ app: this.app, comment: comment });
            this.comments.append(comment_controller.render().el);
        },
        
        template: function()
        { 
            return this.app.getTemplate("photobox_sidebar")({ photo: this.photo, user: Global.user });
        },
        
        uneditPhoto: function(e) 
        { 
            e.preventDefault();
            this.photo_information.show();
            this.photo_editing.hide();
        },
        
        editPhoto: function() 
        { 
            if(_.contains(this.photo.options, "EDIT"))
            { 
                this.photo_information.hide();
                this.photo_editing.show();
            }
        },
        
        saveChanges: function(e)
        { 
            e.preventDefault();
            var o = this.photo; //So we don't lost the pointer.
            $.each(this.photo_form.serializeArray(), function()
            {
                o[this.name] = this.value;
            });
            o.save();
        },
        
        deletePhoto: function(e)
        {
            e.preventDefault();
            var r = confirm("Are you sure you want to remove this photo? Removing this photo will destroy it forever!");
            if(r)
            { 
                this.photo.destroy();
            }
        },
        
        showEditable: function()
        { 
            if(_.contains(this.photo.options, "EDIT"))
            { 
                this.photo_information.addClass("photo_information_hover");
            }
        },
        
        hideEditable: function()
        { 
            this.photo_information.removeClass("photo_information_hover");
        },
              
        openCommentbox: function() { 
            this.comment_adder.removeClass("ready_comment_container");
            
            this.waiting_text.hide();
            this.commentbox_interact.show()
            this.comment_textbox.focus();
        },
                
        saveComment: function() { 
            //var comment = this.photo.comments().create({ owner: Global.user, photo: this.photo.id, content: this.comment_textbox.val() });
            var comment = new Comment({ owner: Global.user, photo: this.photo.id, content: this.comment_textbox.val() });
            comment.save();
            this.comment_textbox.val(null);
        },
        
        /* Thinking this hsould be movd into photo/photo */
        toggleFavorite: function(e)
        {
            e.preventDefault();
            var photo = this.photo;

            if (this.photo.is_favorited)
            {
                this.photo.is_favorited = false;
                this.photo.total_favorites = this.photo.total_favorites - 1;
            }
            else
            {
                this.photo.is_favorited = true;
                this.photo.total_favorites = this.photo.total_favorites + 1;
            }

            Spine.Ajax.queue(function()
            {
                return $.ajax(
                {
                    url: photo.url("favorite"),
                    dataType: "json",
                    type: "PATCH"
                });
            });

            this.photo.save(
            {
                ajax: false
            });
            this.photo.reload();
            this.photo.trigger("favorited");
        }
    });
    
   /* 
	* Handles the rendering of the toolbar
	*/
    var ToolbarController = Spine.Controller.sub(
    {
        elements: 
        { 
            ".options_button": "options_button",
            ".toolbar_menu": "toolbar_menu_template"
        },

        events: 
        {
            "click .starred": "favorite",
            "click .options": "openOptions"
        },

        init: function()
        {
           if(!this.app) throw "@app dependency";
           if(!this.photo) throw "@photo required";
           
           this.photo.bind("change", this.proxy(this.render));
           this.photo.bind("favorited", this.proxy(this.render));
        },
        
        render: function()
        { 
            this.html(this.template());
            return this;
        }, 
        
        template: function()
        { 
            return this.app.getTemplate("photo_toolbar")({ photo: this.photo });
        },
        
        favorite: function(e) 
        {
            //We'll just pass this event to the sidebar controller (which is already inited)
            this.app.window_controller.sidebar_controller.toggleFavorite(e);
        },
        
        openOptions: function(e)
        { 
            e.preventDefault();
            if(!this.options_menu) {
                this.options_menu = new OptionsMenuController({ app: this.app, toolbar: this, menu_template: this.toolbar_menu_template.html(), photo: this.photo });
                this.options_menu.render();
            }
            this.options_menu.open();
        }
    });
    
   /* 
	* Controls the popover for the Options menu
	*/
    var OptionsMenuController = Spine.Controller.sub(
    {
        elements: 
        { 
        },

        events: 
        {
            "click .edit_details_menu_item": "edit",
            "click .delete_menu_item": "delete",
            "click .download_menu_item": "download"
        },

        init: function()
        {
           if(!this.app) throw "@app dependency";
           if(!this.toolbar) throw "@toolbar dependency";
           if(!this.menu_template) throw "@menu_template expected to be injected";
           if(!this.photo) throw "@photo required";
        },
        
        render: function()
        { 
            this.html(this.template());
            return this;
        }, 
        
        template: function()
        { 
            this.menu_template = Handlebars.compile(this.menu_template);
            return this.menu_template({ photo: this.photo });
        },
        
        download: function()
        { 
            alert("download... just kidding. For now.");
        },
        
        delete: function(e)
        { 
            this.app.window_controller.sidebar_controller.deletePhoto(e);
            this.close();
        },
        
        edit: function() 
        { 
            this.app.window_controller.sidebar_controller.editPhoto();
            this.close();
        },
        
        close: function() 
        { 
            $(this.toolbar.options_button).qtip("hide");
        },
        
        open: function()
        { 
            var that = this;
            $(this.toolbar.options_button).qtip({
                content: that.el,
                position: {
                	at: "top center",
                	my: "bottom center",
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
                	ready: true
                },
                hide: {
                	event: "unfocus"
                },
                events: { 
                    show: function(e, api)
                    {
                        that.app.window_controller.freeze_toolbar = true;
                    },
                    hidden: function(e, api)
                    { 
                        that.app.window_controller.freeze_toolbar = false;
                        setTimeout(function() 
                        { 
                            that.app.window_controller.hidePhotoViewControls();
                        }, 1000);
                        $(this).qtip("destroy");
                    }
                }
            });
        }
    });
    
   /* 
	* Comment Controller, responsible for the comments in the sidebar...
	*/
    var CommentController = Spine.Controller.sub(
    {
        className: "comment",
    
        elements: 
        { 
            ".date": "timestamp"
        },

        events: 
        {
            "click .remove_comment": "confirmRemove"
        },

        init: function()
        {
           if(!this.app) throw "@app dependency";
           if(!this.comment) throw "@comment required";
           
           this.comment.bind("change", this.proxy(this.render));
           this.comment.bind("destroy", this.proxy(this.remove));
        },
        
        render: function()
        { 
            this.html(this.template());
            
            /* We do this to reduce the lag between rendering and the stamps 
               getting picked up by the browser */
            $.each(this.timestamp, function(i, t) {
                $(t).livestamp($(t).data('livestamp'));
            });
            
            return this;
        }, 
        
        template: function()
        { 
            return this.app.getTemplate("photobox_comment")({ comment: this.comment });
        }, 
        
        confirmRemove: function()
        { 
            var r = confirm("Remove your comment? This cannot be undone.");
            if(r)
            { 
                this.comment.destroy();
            }
        },
        
        remove: function()
        { 
            this.el.remove();
        }
    });
    

    return new PhotoboxApp;

});