define(['jquery', 'frameworks/spine', 'frameworks/handlebars', 'frameworks/underscore', 'plugins/masonry.jquery', 'plugins/timeago.jquery', 'core/photobox', "photo/photo", "photo/comment"], function()
{
    var PhotoBox = require("core/photobox");
    var Photo = require("photo/photo");
    var Comment = require("photo/comment");

    /* set up some relations */
    
    //Comment.belongsTo('photo', 'photo/photo');
    Photo.hasMany('comments', 'photo/comment');

    var Templates = Spine.Class.sub();
    Templates.include(
    {
        photo_template: Handlebars.compile($("#action_handlebar").html()),
        commentbox_template: Handlebars.compile($("#commentbox_handlebar").html()),
    });
    var templates = new Templates; //Singleton pattern...

    var StreamApp = Spine.Controller.sub(
    {

        el: $(".homepage"),

        elements: { 
            ".followedby_button": "followers_tab",
            ".following_button": "following_tab",
            ".photo_stream": "photo_stream"
        },

        events: {
            "click .followedby_button": "openFollowersWindow",
            "click .following_button": "openFollowingWindow",
            "click .invite_button": "launchInviter",
            "click .photobox_link": "open"
        },

        init: function()
        {
            this.photo_stream.masonry(
            {
                itemSelector: '.activity'
            });
            
            Photo.bind("refresh", this.proxy(this.addAll));
            Photo.bind("create", this.proxy(this.addOne));
            
            Photo.fetch({ data: "type=timeline" });
        },
        
        addOne: function(item)
        {
            if(item.timeline == false) 
            { 
                return false;
            }
        
            var photo = new PhotoSlotController(
            {
                item: item,
                photo_stream: this.photo_stream
            });
            this.photo_stream.append(photo.render().el);
            
            var ps = this.photo_stream;
            this.photo_stream.imagesLoaded(function() {
                ps.masonry('reload');
            });
        },

        addAll: function()
        {
            //this.count = 0;
            //this.photo_stream.html(null);
            var psc = this;
            $.each(_.sortBy(Photo.findAllByAttribute("timeline", true), function(photo)
            {
                return photo.created_at
            }).reverse(), function(i)
            {
                psc.addOne(this)
            });
        },
        
        open: function(e) 
        {
            e.preventDefault();
            var photo_id = $(e.target).closest(".photobox_link").data("photo");
            PhotoBox.open(photo_id);
        },
        
        
        openFollowersWindow: function(e)
        { 
            e.preventDefault();
            
            if(this.followers_tab.data('qtip') == null) {
                $(this.followers_tab).qtip(
                {
                    content: {
                		text: '<div class="preloading_popover_info">Fetching...</div>',
                		ajax: {
                			url: Global.settings.rurl+'profile/'+Global.user.url+'/pagelet/followed',
                			type: 'GET'
                		}
            		},
                    position: {
                        my: 'top center',
                        // ...at the center of the viewport
                        at: 'bottom center',
                        adjust: {
                            y: 3
                        }
                    },
                    show: {
                        solo: true,
                        event: 'click',
                        ready: true //Show when ready
                    },
                    hide: {
                        event: 'unfocus'
                    },
                    style: {
                        tip: {
                            width: 18,
                            height: 8
                        },
                        width: 300,
                        classes: 'ui-tooltip-shadow ui-tooltip-organizer-popover'
                    }
                });
            }
        },
        
        openFollowingWindow: function()
        { 
            if(this.following_tab.data('qtip') == null) {
                $(this.following_tab).qtip(
                {
                    content: {
                		text: '<div class="preloading_popover_info">Fetching...</div>',
                		ajax: {
                			url: Global.settings.rurl+'profile/'+Global.user.url+'/pagelet/following',
                			type: 'GET'
                		}
                	},
                    position: {
                        my: 'top center',
                        // ...at the center of the viewport
                        at: 'bottom center',
                        adjust: {
                            y: 3
                        }
                    },
                    show: {
                        solo: true,
                        event: 'click',
                        ready: true //Show when ready
                    },
                    hide: {
                        event: 'unfocus'
                    },
                    style: {
                        tip: {
                            width: 18,
                            height: 8
                        },
                        width: 300,
                        classes: 'ui-tooltip-shadow ui-tooltip-organizer-popover'
                    }
                });
            }
        },
        
        launchInviter: function()
        {   
            //We load the controller (and it will automatically fetch the template)
            require(['user/invite'], function(invite_controller)
            {  
                $("body").qtip("hide");
                var aic = new invite_controller();
            });
        }
        

    });
    
    var PhotoSlotController = Spine.Controller.sub({ 
        
        className: 'activity',
        
        events: {
            "click .primary_photo": "openPopup",
            "click .profile_favorite_button": "toggleFavorite",
            "click .popover_comment_number": "openPopup",
            "click .ready_comment_container": "openCommentbox",
            "click .post_comment_button": "saveComment",
            "click .confirmed_remove_comment": "removeComment",
            "click .remove_comment": "confirmRemoveComment",
            "click .stream_favorite_button": "toggleFavorite",
            "click .tes_link": "test"
        },

        elements: {
            ".more": "more",
            ".favorited": "favorite",
            ".comment_adder_container": "comment_adder",
            
            ".waiting_text": "waiting_text",
            ".commentbox_interact": "commentbox_interact",
            ".comment_textbox": "comment_textbox",
            ".remove_comment": "remove_comment_link"
            
        },
        
        test: function(e) 
        {
            e.preventDefault(); 
            alert(this.item.favorites);
        },

        init: function()
        {
            if (!this.item) throw "@photo required";
            this.item.bind("change", this.proxy(this.render));
            this.item.bind("destroy", this.proxy(this.remove));
            this.item.bind("favorited", this.proxy(this.render));
        
            Comment.bind("create change", this.proxy(this.render));
        },

        render: function(item)
        {
            var comments = _.where(Comment.all(), { photo: this.item.id });
                
            this.html(this.template(this.item, comments));
            $("abbr.timeago").timeago();
            this.photo_stream.masonry('reload');
            
            return this;
        },
        
        confirmRemoveComment: function(e)
        { 
            e.preventDefault();
            $(e.target).addClass("confirmed_remove_comment").removeClass("remove_comment").text("Click again to delete");
        },
        
        removeComment: function(e) 
        { 
            e.preventDefault();
            var comment = Comment.find($(e.target).data('comment'));
            comment.destroy();
        },
        
        saveComment: function() { 
            var comment = new Comment({ owner: Global.user, photo: this.item.id, content: this.comment_textbox.val() });
            comment.save();
        },

        template: function(item, comments)
        {
            /* Register helpers */
            /* Deleteable */
            Handlebars.registerHelper('deletable', function(options, comment, fn) {
              return (_.contains(options, "DELETE") == true) ? fn(comment) : null ;
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
              return !(favorites.length > 1) ? fn() : null;
            }); 
            
            return templates.photo_template(
            {
                photo_path: Global.settings.image_farm,
                photo: item,
                comments: comments,
                user: Global.user,
                Global: Global
            });
        },
        
        openCommentbox: function() { 
            this.comment_adder.removeClass("ready_comment_container");
            
            this.waiting_text.hide();
            this.commentbox_interact.show()
            this.comment_textbox.focus();
            
            this.photo_stream.masonry('reload');
        },

        popable: function()
        {
            /*var pop = new PhotoMenuController(
            {
                photo_item_controller: this
            })
            $(this.more).qtip(
            {
                content: pop.render().el,
                position: {
                    at: "top right",
                    my: "bottom right"
                },
                style: {
                    classes: 'ui-tooltip-plain ui-tooltip-shadow follow_options_tooltip',
                    width: 170,
                    tip: {
                        width: 18,
                        height: 8,
                        offset: 10,
                        mimic: "bottom center"
                    }
                },
                show: {
                    event: 'click',
                },
                hide: {
                    event: 'unfocus'
                }
            });*/
        },

        toggleFavorite: function(e)
        {
            e.preventDefault();
            var photo = this.item;

            if (this.item.is_favorited == true)
            {
                this.item.is_favorited = false;
                this.item.total_favorites = this.item.total_favorites - 1;
            }
            else
            {
                this.item.is_favorited = true;
                this.item.total_favorites = this.item.total_favorites + 1;
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

            this.item.save(
            {
                ajax: false
            });
            this.item.trigger("favorited");
        },

        openPopup: function(e)
        {
            e.preventDefault();
            PhotoBox.open(this.item.id);
        },

        // Called after an element is destroyed
        remove: function()
        {
            this.el.remove();
        }
    });
    
    
    
    

    return StreamApp;
});