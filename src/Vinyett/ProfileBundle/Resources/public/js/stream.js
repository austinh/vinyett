define(['require', 'jquery', 'frameworks/spine', 'frameworks/underscore', 'photo/photo', 'photo/collection', 'friendships/friend', 'frameworks/handlebars', 'plugins/masonry.jquery', 'plugins/waypoints.jquery', 'core/photobox'], function(require)
{
    var Photo = require("photo/photo");
    var Collection = require("photo/collection");
    var PhotoBox = require("core/photobox");
    var Friend = require("friendships/friend");

    var ProfileUser = Global.profile;

    var Templates = Spine.Class.sub();
    Templates.include(
    {
        photo_template: Handlebars.compile($("#photo_handlebar").html()),
        photo_menu: Handlebars.compile($("#photo_menu_handlebar").html()),
        follow_menu: Handlebars.compile($("#follow_popover_handlebar").html()),
        follow_button: Handlebars.compile($("#follow_button_handlebar").html()),
        collection_item: Handlebars.compile($("#collection_item_handlebar").html()),
        collection_toolbar: Handlebars.compile($("#collection_template_handlebar").html()),
        admin_menu: Handlebars.compile($("#admin_popover_handlebar").html())

    });

    var templates = new Templates; //Singleton pattern...
    var ProfileApp = Spine.Controller.sub(
    {
        el: $(".profile"),

        events: {
            "click .photostream_tab": "openPhotostream",
            "click .collectionstream_tab": "openCollections",
            "click .profile_tab": "openProfile",
            "click .profile_edit_button": "editProfileBannerMenu",
            "click .followers_tab": "openFollowersWindow",
            "click .following_tab": "openFollowingWindow",
            "click .edit_blrub": "editBlurb",
            "click .change_profile_button": "editProfilePhoto",
            "mouseenter .profile_header": "showOptions",
            "mouseleave .profile_header": "hideOptions",
            "mouseenter .profile_image": "showProfileEdit",
            "mouseleave .profile_image": "hideProfileEdit"
        },

        elements: {
            "ul.navigation": "tabbar",
            "ul.navigation > li": "tabbar_items",
            ".view_grid": "view_grid",
            ".collection_grid": "collection_grid",
            ".photo_grid": "photo_grid",
            ".profile_edit_button": "profile_edit_button",
            ".followers_tab": "followers_tab",
            ".following_tab": "following_tab",
            ".edit_blrub": "blurbPopup",
            ".blurb_about": "blurb_about",
            ".profile_banner_button": "profile_banner_button",
            ".edit_photo": "edit_photo_button",
            ".change_profile_button": "photo_profile_edit_button"
        },
        
        profile_banner_button_sticky: false,
        profile_picture_button_sticky: false,
        
        showOptions: function()
        { 
            this.profile_banner_button.show();
        },
        
        hideOptions: function()
        { 
            if(!this.profile_picture_button_sticky)
                this.profile_banner_button.hide();
        },
        
        showProfileEdit: function() 
        { 
            this.edit_photo_button.show();
        },
        
        hideProfileEdit: function()
        { 
            if(!this.profile_picture_button_sticky)
                this.edit_photo_button.hide();
        },
        
        openProfile: function(e)
        { 
            e.preventDefault();
            this.profilecontroller.active();
        },

        openPhotostream: function(e)
        {
            e.preventDefault();
            this.photostream.active();
        },

        openCollections: function(e)
        {
            e.preventDefault();
            this.collectionstream.active();
        },

        init: function()
        {
            //Preload information...
            if(Global.user.id == Global.profile.id)
            {
                Photo.fetch();
            } else { 
                Photo.fetch({ data: "for="+Global.profile.id });
            }
            Collection.fetch({ data: "for="+Global.profile.id });
        
            //Set up views
            this.photostream = new PhotostreamController(
            {
                app: this
            });
            this.collectionstream = new CollectionController(
            {
                app: this
            });
            this.profilecontroller = new ProfileController(
            {
                app: this
            });
            this.mapscontroller = new MapsController(
            {
                app: this
            });
            this.likedcontroller = new LikedController(
            {
                app: this
            });

            //Add them to the manager
            new Spine.Manager(this.photostream, this.collectionstream, this.profilecontroller);


            //The photostream is active
            this.photostream.active();

            this.assignProfileButtonMenus();
        },

        addPhoto: function(photo)
        {
            var new_photo = new Photo(photo);
            new_photo.save(
            {
                ajax: false
            });
        },

        assignProfileButtonMenus: function()
        {
            if ($("#follow_button").length > 0)
            {
                this.followButtonController = new StreamFollowButtonController(
                {
                    el: $("#follow_button")
                });
            }
            
            if ($("#admin_button").length > 0)
            {
                this.adminButtonController = new StreamAdminButtonController();
            }
        },

        pressTabButton: function(child_controller)
        {
            this.tabbar_items.removeClass("pressed");
            $("." + child_controller.tab_class_name).addClass("pressed");
        },
        
        editProfileBannerMenu: function() 
        { 
            var that = this;
            if(this.profile_edit_button.data('qtip') == null) {
                var banner_controller = new ProfileBannerController({ app: this });
                this.profile_edit_button.qtip(
                {
                    content: banner_controller.el,
                    position: {
                        at: "top center",
                        my: "bottom center"
                    },
                    style: {
                        classes: 'ui-tooltip-plain ui-tooltip-shadow follow_options_tooltip',
                        width: 170,
                        tip: {
                            width: 18,
                            height: 8
                        }
                    },
                    show: {
                        event: 'click',
                        ready: true
                    },
                    hide: {
                        event: 'unfocus'
                    },
                    events: {
                        show: function()
                        { 
                            that.profile_banner_button_sticky = true;
                        },
                        hidden: function(e, api)
                        {
                            $(this).qtip('destroy'); //Dispose of the tip when it's done with.
                            that.profile_banner_button_sticky = false;
                        }
                    }
                });
            }
        },
        
        openFollowersWindow: function()
        { 
            if(this.followers_tab.data('qtip') == null) {
                $(this.followers_tab).qtip(
                {
                    content: {
                		text: '<div class="preloading_popover_info">Fetching...</div>',
                		ajax: {
                			url: Global.settings.rurl+'profile/'+Global.profile.url+'/pagelet/followed',
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
                			url: Global.settings.rurl+'profile/'+Global.profile.url+'/pagelet/following',
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
        
        editBlurb: function(e)
        { 
            e.preventDefault();
            this.blurb_editor = new BlurbEditorController({ app: this });
            this.blurb_editor.render().open();
        }, 
        
        editProfilePhoto: function(e)
        { 
            var ppm = new ProfilePhotoMenuController({ app: this });
            ppm.render().open();
        }

    });
    
    var ProfilePhotoMenuController = Spine.Controller.sub(
    {
        events: {
            "click .remove": "remove",
            "click .upload": "upload",
            "click .adjust": "adjust",
            "change .photo_upload_input": "doUpload"
        },
        
        elements: { 
            ".photo_upload_form": "form", 
            ".photo_upload_input": "input"
        },

        init: function()
        {
            if (!this.app) throw "@app depdency";
        },

        render: function()
        {
            this.html($(".profile_photo_menu").html());
            return this;
        },

        remove: function()
        {
            window.location= Global.settings.rurl+"account/update/photo/remove";
        }, 
        
        upload: function() 
        { 
            console.log("Oh you want to upload a new image, huh?");
            this.input.trigger("click");
        }, 
        
        doUpload: function()
        { 
            this.form.trigger("submit");
        },
        
        adjust: function() 
        { 
            
        },
        
        open: function() 
        { 
            var that = this;
            this.app.photo_profile_edit_button.qtip({ 
                content: that.el,
                position: {
                    at: "right center",
                    my: "left center"
                },
                style: {
                    classes: 'ui-tooltip-plain ui-tooltip-shadow follow_options_tooltip',
                    width: 170,
                    tip: {
                        width: 18,
                        height: 8
                    }
                },
                show: {
                    event: 'click',
                    ready: true
                },
                hide: {
                    event: 'unfocus'
                },
                events: {
                    show: function()
                    { 
                        /*that.profile_banner_button_sticky = true;*/
                    },
                    hidden: function(e, api)
                    {
                        $(this).qtip('destroy'); //Dispose of the tip when it's done with.
                        //that.profile_banner_button_sticky = false;
                    }
                }
            });
        }
    });
    
    var BlurbEditorController = Spine.Controller.sub(
    { 
        elements: 
        { 
            ".counter": "counter",
            ".blurblet": "blurblet",
            ".save_button": "save_button",
            ".cancel_button": "cancel_button"
        },

        events: 
        {
            "keyup .blurblet": "updateCounter",
            "click .cancel_button": "close",
            "click .save_button": "save"
        },

        init: function()
        {
           if(!this.app) throw "@app dependency";
        },
        
        render: function()
        { 
            this.html($("#profile_edit_menu").html());
            this.blurblet.val(Global.user.blurb);
            return this;
        }, 
        
        updateCounter: function()
        { 
            if(this.blurblet.val().length > 160) {
                this.blurblet.val(this.blurblet.val().substr(0, 160));
            }
            this.counter.text((160-this.blurblet.val().length)+" characters left");
        },
        
        save: function() 
        { 
            var blurb = this.blurblet.val();
            var that = this; 
            
            $.ajax({ 
                url: Global.settings.rurl+"profile/update/blurb",
                data: "blurb="+blurb,
                type: "POST", 
                beforeSend: function() 
                { 
                    that.blurblet.prop("disabled", true);
                    that.save_button.html("Saving...").prop("disabled", true);
                    that.cancel_button.prop("disabled", true);
                }
            }).done(function() { 
                that.app.blurb_about.html(blurb);
                Global.user.blurb = blurb; //Have to manually manage this one.
                that.close();
            });
        },
        
        close: function()
        { 
            this.app.blurbPopup.qtip("hide");
        },
        
        open: function()
        { 
            var that = this;
            this.app.blurbPopup.qtip(
            {
                content: that.el,
                position: {
                    at: 'bottom center',
                    my: 'top center'
                },
                style: {
                    classes: 'ui-tooltip-plain ui-tooltip-shadow follow_options_tooltip edit_dialog_tooltip',
                    tip: {
                        corner: true,
                        width: 16,
                        height: 8
                    }
                },
                show: {
                    ready: true,
                    event: "click"
                },
                hide: {
                    event: 'none'
                },
                events: { 
                    hidden: function()
                    { 
                        $(this).qtip("destory");
                    }
                }
            });
        }
    });
    
    var ProfileBannerController = Spine.Controller.sub(
    {
         
         el:  $('.profile_banner_menu'),
         
         init: function() 
         { 
         },
         
         events: { 
             "click .reposition": "adjustBanner",
             "click .remove": "removeBanner",
             "click .save_reposition": "saveReposition",
             "click .cancel_reposition": "cancelReposition"
         },
         
         removeBanner: function() 
         { 
             $(".profile_banner").attr("src", "/images/linen_photoless_profile.png");
             $.post(Global.settings.rurl+"profile/update/banner", { photo: "none" });
             $(".profile_edit_button").qtip("hide");
         },
         
         adjustBanner: function() 
         { 
            $(".profile_edit_button").qtip("hide");
            $(".profile_edit_button").hide();
         
            require(['frameworks/jquery.ui',], function() {
                $(".profile_banner").addClass("is_repositioning");
                $(".profile_banner").draggable({ 
                drag: function(event, ui) {
                    if(ui.position.top>0) { ui.position.top = 0; }
                    var maxtop = ui.helper.parent().height()-ui.helper.height();
                    if(ui.position.top<maxtop) { ui.position.top = maxtop; }
                    if(ui.position.left>0) { ui.position.left = 0; }
                    var maxleft = ui.helper.parent().width()-ui.helper.width();
                    if(ui.position.left<maxleft) { ui.position.left = maxleft; }
                  }
                });
                
                $(".reposition_options").fadeIn();
                if(!this.c)
                { 
                    this.c = new ProfileBannerController({el: $(".reposition_options") });
                }
            });
         },
         
         stopBannerMove: function() 
         {
             $(".profile_banner").removeClass("is_repositioning");
             $(".reposition_options").hide();
             $(".profile_edit_button").show();
             $(".profile_banner").draggable("destroy");
         },
         
         saveReposition: function() 
         { 
             var offset_top = $(".profile_banner").position().top;
             this.stopBannerMove();
             
             Global.profile.profile_photo_offset = offset_top; //For future usage...
             
             $.post(Global.settings.rurl+"profile/update/banner/position", { offset: offset_top });
             
         },
         
         cancelReposition: function() 
         { 
            $(".profile_banner").css("top", Global.profile.profile_photo_offset);
             this.stopBannerMove();
         }
    });
    
    var ProfileController = Spine.Controller.sub(
    { 
    
        tab_class_name: "profile_tab",
        
        el: $(".profile_grid"),
        
        init: function()
        {
            if (!this.app) throw "@ProfileApp dependency required.";
        },

        activate: function()
        {
            this.app.pressTabButton(this);
            this.el.addClass("active");
            return this;
        },

        deactivate: function()
        {
            this.el.removeClass("active");
            return this;
        },
    });
    
    var MapsController = Spine.Controller.sub(
    { 
    
        tab_class_name: "maps_tab",
        
        el: $(".maps_grid"),

        activate: function()
        {
            this.app.pressTabButton(this);
            this.el.addClass("active");
            return this;
        },

        deactivate: function()
        {
            this.el.removeClass("active");
            return this;
        },
    });
    
    var LikedController = Spine.Controller.sub(
    { 
    
        tab_class_name: "liked_tab",
        
        el: $(".liked_grid"),

        activate: function()
        {
            this.app.pressTabButton(this);
            this.el.addClass("active");
            return this;
        },

        deactivate: function()
        {
            this.el.removeClass("active");
            return this;
        },
    });

    var CollectionController = Spine.Controller.sub(
    {

        tab_class_name: "collectionstream_tab",

        default_view: "list", //list, thumbnails
        
        el: $(".collection_grid"),

        elements: { 
            ".collection_toolbar": "toolbar",
            ".collection_stream": "stream"
        },
        
        events: { 
            "click .view_switch": "switchView"
        },

        init: function()
        {
            if (!this.app) throw "@ProfileApp dependency required.";
            Collection.bind("refresh", this.proxy(this.addAll));
            Collection.bind("create",  this.proxy(this.addOne));
            
            Collection.bind("refresh change", this.proxy(this.renderToolbar));
        },
       
        addOne: function(item){
            var collection = new CollectionItem({item: item, collection_controller: this });
            this.append(collection.render());
        },
       
        addAll: function(){
            if(Collection.count() == 0) { 
                $(".empty_following_collection").show();
            }
            Collection.each(this.proxy(this.addOne));
        },
        
        switchView: function(e) 
        { 
            e.preventDefault();
            if(this.default_view == "grid") { this.default_view = "list"; } else { this.default_view = "grid"; }
            this.renderToolbar();
            Collection.trigger("redraw");
        },
        
        renderToolbar: function() 
        { 
            var model = { options: { list: (this.default_view=="grid"?false:true) }, total_collections: Collection.count() }
            this.toolbar.html(this.toolbar_template(model));
        },
        
        toolbar_template: function(model) 
        { 
            return templates.collection_toolbar(model);
        },

        activate: function()
        {
            if(!this.has_been_loaded)
            {
                /*Collection.fetch({ data: "for="+Global.profile.id });*/
                this.has_been_loaded = true;
            }
            
            this.app.pressTabButton(this);
            this.el.addClass("active");
            return this;
        },

        deactivate: function()
        {
            this.el.removeClass("active");
            return this;
        }

    });

    var CollectionItem = Spine.Controller.sub(
    {
        attr: { 
            "class" : "collection_window"
        },
    
        init: function()
        {
            if (!this.item) throw "@item required";
            if (!this.collection_controller) throw "@collection_controller required."
            this.item.bind("update", this.proxy(this.render));
            this.item.bind("destroy", this.proxy(this.remove));
            Collection.bind("redraw", this.proxy(this.render));
        },
            
        render: function(item){
        if (item) this.item = item;
        
          var model = { root_path: Global.settings.rurl, collection: this.item, list: (this.collection_controller.default_view=="grid"?true:false) };
        
          this.html(this.template(model));
          return this;
        },
        
        template: function(item){
          return templates.collection_item(item);
        },
        
        remove: function(){
          this.el.remove();
        }

    });

    var StreamFollowPopoverController = Spine.Controller.sub(
    {
        events: {
            "click .friend_selector": "toggleFriends",
            "click .family_selector": "toggleFamily",
            "click .photofeed_selector": "togglePhotofeed",
            "click .unfollow": "doUnfollow"
        },

        init: function()
        {
            if (!this.friend) throw "@Required follow context";
            if (!this.button) throw "@Required button controller";
            this.friend.bind("refresh change", this.proxy(this.render));
        },

        template: function(friend)
        {
            return templates.follow_menu(friend);
        },

        render: function(friend)
        {
            if (friend) this.friend = friend;

            this.html(this.template(this.friend));
            return this;
        },

        toggleFriends: function()
        {
            this.friend.is_friend = !this.friend.is_friend;
            this.friend.save();
        },

        toggleFamily: function()
        {
            this.friend.is_family = !this.friend.is_family;
            this.friend.save();
        },

        togglePhotofeed: function()
        {
            this.friend.is_in_photofeed = !this.friend.is_in_photofeed;
            this.friend.save();
        },

        doUnfollow: function()
        {
            this.friend.destroy();
        }

    });

    var StreamFollowButtonController = Spine.Controller.sub(
    {
        el: $("#follow_button"),

        events: {
            "click": "follow"
        },

        init: function()
        {
            Friend.bind("create", this.proxy(this.renderButtonContext));

            if (Global.profile.relationship)
            {
                this.use(Global.profile.relationship);
            }

            this.renderButtonContext(this.friend);
        },

        use: function(friend)
        {
            this.friend = new Friend(friend);
            this.friend.save(
            {
                ajax: false
            }); //This record already exists so we lie and create it in the browser but not server.
            this._bindFollow();
        },

        new: function(friend)
        {
            this.friend = friend
            this.friend.save(); //This record already exists so we lie and create it in the browser but not server.
            this._bindFollow(true);
        },

        _bindFollow: function(show)
        {
            this.friend.bind("destroy", this.proxy(this.removeFriend));
            this.cuePopover(this.friend, show);
        },

        removeFriend: function()
        {
            $("#follow_button").qtip("destroy");
            this.friend = null;
            this.renderButtonContext(null);
        },

        renderButtonContext: function(friend)
        {
            if(friend) {
                this.html(templates.follow_button(
                {
                    follow: friend
                }));
            }
        },

        cuePopover: function(friend, show)
        {
            if (!show) show = false;

            if (friend)
            {
                var menu_popover = new StreamFollowPopoverController(
                {
                    friend: friend,
                    button: this
                });

                $("#follow_button").qtip(
                {
                    content: menu_popover.render().el,
                    position: {
                        at: 'bottom center',
                        my: 'top center'
                    },
                    style: {
                        classes: 'ui-tooltip-plain ui-tooltip-shadow follow_options_tooltip',
                        tip: {
                            corner: true,
                            width: 16,
                            height: 8
                        }
                    },
                    show: {
                        ready: show
                    },
                    hide: {
                        event: 'unfocus'
                    }
                });
            }
        },

        follow: function()
        {
            if (this.friend) return; //nothing if we already follow!
            var friend = new Friend();
            friend.following = Global.profile;
            this.new(friend);
        }
    });
    
    var StreamAdminButtonController = Spine.Controller.sub(
    {
        el: $("#admin_button"),

        init: function()
        {
            this.cuePopover();
        },
        
        cuePopover: function()
        {   
            this.stream_admin_window_controller = new StreamAdminPopoverController({ profile: ProfileUser });
            $("#admin_button").qtip(
            {
                content: this.stream_admin_window_controller.render().el,
                position: {
                    at: 'bottom center',
                    my: 'top center'
                },
                style: {
                    classes: 'ui-tooltip-plain ui-tooltip-shadow follow_options_tooltip',
                    tip: {
                        corner: true,
                        width: 16,
                        height: 8
                    }
                },
                show: "click",
                hide: {
                    event: 'unfocus'
                }
            });
        }
    });
    
    var StreamAdminPopoverController = Spine.Controller.sub(
    {
        events: {
            "click .become_selector": "becomeUser",
            "click .reset_usage_selector": "resetUsage"
        },

        init: function()
        {
            if (!this.profile) throw "@Required profile ";
        },

        template: function(profile)
        {
            return templates.admin_menu({ user: profile });
        },

        render: function(profile)
        {
            if (profile) this.profile = profile;

            this.html(this.template(this.profile));
            return this;
        }, 
        
        becomeUser: function() 
        { 
            window.location = document.URL+'?_switch_user='+ProfileUser.username;
        }, 
        
        resetUsage: function() 
        { 
            $.ajax({ 
                url: Global.settings.rurl+"profile/"+ProfileUser.username+"/admin/resetUsage",
                type: "POST"
            }).done(function() { 
                alert("User's usage was reset"); 
            }).fail(function() { 
                alert("An error occured and the usage wasn't reset");
            });
        }

    });

    var PhotostreamController = Spine.Controller.sub(
    {
        tab_class_name: "photostream_tab",

        count: 0,

        pending: false,
        
        paging: false,
        
        elements: 
        { 
            ".empty_following_photos": "empty_photos"
        },

        el: $(".photo_grid"),

        init: function()
        {
            if (!this.app) throw "@ProfileApp dependency required.";

            Photo.bind("refresh", this.proxy(this.addAll));
            Photo.bind("create", this.proxy(this.addOne));
        },

        activate: function()
        {
            this.app.pressTabButton(this);
            this.el.addClass("active");
            return this;
        },

        deactivate: function()
        {
            this.el.removeClass("active");
            return this;
        },

        watchScroll: function()
        {
            var psc = this;
            $('.photo_grid').waypoint(function(event, direction)
            {
                if (direction === 'down' && psc.isActive())
                {
                    psc.paging = true;
                    Photo.fetch(
                    {
                        data: {
                            offset: Photo.count()
                        },
                        processData: true
                    });
                }
            }, {
                offset: 'bottom-in-view',
                onlyOnScroll: true // middle of the page
            });
        },

        addOne: function(item)
        {
            this.count++;
            if (this.count == 2)
            {
                this.append($("#profile_component_collections").html());
            }
            if (this.count == 4)
            {
                this.append($("#profile_component_follow").html());
            }

            if (this.count == 9)
            {
                this.watchScroll();
            }
            var photo = new PhotoItemController(
            {
                item: item
            });
            this.empty_photos.hide();
            this.append(photo.render());
            $.waypoints("refresh");
        },

        addAll: function()
        {
            this.count = 0;
            if(this.paging==true)
            {
                this.el.html(null);
            }
            var psc = this;
            $.each(_.sortBy(Photo.For(Global.profile.id), function(photo)
            {
                return photo.created_at
            }).reverse(), function(i)
            {
                psc.addOne(this);
            });

            //var new_photo = new Photo(photo);
            //new_photo.save({ ajax: false });
        }
    });

    var PhotoMenuController = Spine.Controller.sub(
    {
        events: {
            "click .highlight_post": "highlightPhoto",
            "click .set_photo_banner": "bannerizePhoto"
        },

        init: function()
        {
            if (!this.photo_item_controller) throw "@Required photo controller";
        },

        render: function()
        {
            this.html(templates.photo_menu(
            {
                photo: this.photo_item_controller.item
            }));
            return this;
        },

        highlightPhoto: function()
        {
            var photo = this.photo_item_controller.item;
            photo.highlighted = !photo.highlighted;
            photo.save();
        }, 
        
        bannerizePhoto: function() 
        { 
            $('body,html').animate({
				scrollTop: 0
			}, 800);
			var photo = this.photo_item_controller.item;
			$.post(Global.settings.rurl+"profile/update/banner", { photo: photo.id });
			$(".profile_banner").attr("src", "http://photos.vinyett.com/"+photo.photo_path_width_980);
        }
    });

    var PhotoItemController = Spine.Controller.sub(
    {

        events: {
            "click .photo_wrap": "openPopup",
            "click .profile_favorite_button": "toggleFavorite",
            "click .popover_comment_number": "openPopup"
        },

        elements: {
            ".more": "more",
            ".favorited": "favorite"
        },

        init: function()
        {
            if (!this.item) throw "@photo required";
            this.item.bind("update", this.proxy(this.render));
            this.item.bind("destroy", this.proxy(this.remove));
        },

        render: function(item)
        {
            if (item) this.item = item;

            this.html(this.template(this.item));

            this.popable();

            return this;
        },

        // Use a template, in this case via jQuery.tmpl.js
        template: function(item)
        {
            return templates.photo_template(
            {
                photo_path: Global.settings.image_farm,
                photo: item
            });
        },

        popable: function()
        {
            var pop = new PhotoMenuController(
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
            });
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


    return ProfileApp;
});