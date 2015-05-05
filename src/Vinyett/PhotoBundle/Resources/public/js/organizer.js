define(['require', 
        'jquery', 
        'frameworks/spine', 
        'frameworks/ajax', 
        'frameworks/route',
        'frameworks/jquery.ui',
        'frameworks/handlebars',
        'frameworks/underscore',
        'photo/photo',
        'photo/stage',
        'photo/stagelet',
        'photo/collection', 
        'plugins/qtip.jquery',
        'plugins/jcarousel.jquery',
        'plugins/jquery.mousewheel'
        ], function(require) {

    var Photo = require("photo/photo");
    var Collection = require("photo/collection");
    var Stage = require("photo/stage");
    var Stagelet = require("photo/stagelet");
    
    Photo.extend(
    { 
        Selected: function()
        {
            return this.select(function(item)
            {
                return item.is_selected;
            });
        },
        Unselected: function()
        {
            return this.select(function(item)
            {
                return !item.selected;
            });
        }
    });
    
    Collection.extend(
    {
        ReplacePhotos: function(collection, photos)
        {
            $.ajax(
            {
                url: collection.url("photos"),
                type: "PUT",
                data: {
                    photos: photos
                }
            });
        },

        RecentCollections: function()
        {
            var collections = Collection.all();
            collections.sort(function(a, b)
            {
                return b.date_updated - a.date_updated
            });
            return collections;
        }
    });
    
    
    Collection.include(
    {
        saveWithBatchOrder: function(stagelets)
        {
            this.cached_photos = Stagelet.reduceWithPosition(stagelets);
            var p = [];
            $.each(this.cached_photos, function(i, s)
            { 
                p.push(s.photo);
            });
            this.photos = p;
            this.bind("ajaxSuccess", function(data, status, xhr)
            {
                //We are required to CHEAT in order to bind the correct 
                //ID to the object because Spine doesn't fill this
                //information in until after all of the events clear
                this.id = data.id;
                this.save(
                {
                    ajax: false
                });
                Collection.ReplacePhotos(this, this.cached_photos);
            });
            this.save();
            this.photos = p;
        }
    });
    
    var Templates = Spine.Class.sub();
    Templates.extend(
    {
        dialog: Handlebars.compile($("#organizer_dialog_handlebar").html()),
        photo_template: Handlebars.compile($("#photo_handlebar").html()),
        batch_photo_template: Handlebars.compile($("#batch_photo_handlebar").html()),
        batch_stage_template: Handlebars.compile($("#batch_stage_handlebar").html()),
        navigator_toolbar_template: Handlebars.compile($("#navigator_toolbar_handlebar").html()),
        toolbox_template: Handlebars.compile($("#toolbox_handlebar").html()),
        toolbox_collection_list_item: Handlebars.compile($("#toolbox_collection_list_item_handlebar").html()),
        collection_stage: Handlebars.compile($("#collection_stage_handlebar").html()),
        batch_stage_privacy_window: Handlebars.compile($("#batch_stage_privacy_window_handlebar").html()),
        batch_stage_move_window: Handlebars.compile($("#batch_stage_move_window_handlebar").html()),
        batch_stage_edit_window: Handlebars.compile($("#batch_stage_edit_window_handlebar").html()),
        batch_stage_edit_row: Handlebars.compile($("#batch_stage_edit_row_handlebar").html()),
        toolbar_photo_import_item: Handlebars.compile($("#toolbar_photo_import_item_handlebar").html()),
        photo_pubisher: Handlebars.compile($("#photo_publisher_handlebar").html()),
        photo_publisher_unpublished: Handlebars.compile($("#photo_publisher_unpublished_handlebar").html()),
        detail_view: Handlebars.compile($("#detail_view_handlebar").html())
    });


    /* 
     * A very simple modal, can be extended for more than 2 button usage.
     * 
     * But more than likely that functionality will appear eventually.
     */ 
    var Modal = Spine.Controller.sub(
    { 
       
       events: 
       { 
           "click button": "buttonClicked"
       },
       
       init: function() 
       { 
            this.modal = { title: "Untitled", content: "A snippet to help make this a lot less bad as it seems!" };
            this.modal.buttons = [];
            this.callbacks = [];
            return this;       
       },
       
       withTitle: function(title) 
       { 
           this.modal.title = title;
           return this;
       },
       
       withDescription: function(description)
       { 
           this.modal.content = description;
           return this;
       },
       
       withCancelButton: function(button_title, attributes) { 
           if(!attributes) attributes = [];
           return this.withCallbackButton(button_title, [ { name: "class", value: "button" }, { name: "type", value: "cancel"} ].concat(attributes), this.close);
       },
       
       withCallbackButton: function(button_title, attributes, callback) {
           this.callbacks.push(callback); 
           this.modal.buttons.push({ title: button_title, attributes: attributes });
           return this;
       },   
       
       setReference: function(reference)
       { 
           this.reference = reference;
           return this;
       },
       
       show: function() 
       { 
           var that = this;
           this.can();
           
           $("body").qtip({
                id: "dialog_modal",
                content: function() { return that.render().el; },
                position: {
                    my: 'center',
                    at: 'center',
                    target: $(window)
                },
                show: {
                	modal: {
            			on: true
            		},
                    ready: true //Show when ready
                },
                hide: "unfocus",
                style: {
                    classes: 'ui-tooltip-dialog-modal',
                    width: 402
                }, 
                events: { 
                    show: function()
                    {
                        closable_toolox = false;
                    },
                    hidden: function(e, api) 
                    { 
                        $(this).qtip('destroy');
                        closable_toolox = true;
                    }
                }
            });
       },
       
       render: function() { 
           this.html(this.template(this.modal));
           return this;
       },
    
       template: function(modal) { 
           return  Templates.dialog({ modal: modal });
       },
       
       buttonClicked: function(e) 
       {
           var call_to_cb = $(e.target).index("");
           this.callbacks[call_to_cb](this.close, this.reference);
       },
       
       close: function() 
       { 
           $("body").qtip('hide');
       },
       
       can: function() 
       { 
           
       }
       
    });

    var closable_toolox = true;

    /* 
     * Overall app controller. Responsible for overseeing much of the organizers 
     * low level functions and making sure higher level tasks are carried out
     * but other classes and controllers. 
     */ 
    var OrganizerApp = Spine.Controller.sub(
    {
        el: $("#organizer"),
        
        elements:
        { 
            ".organizer_stage_wrap": "organizer_stage",
            "#organizer_toolbox": "toolbox_el"
        },
    
       /*
        * You should avoid placing global events unless they're absolutely neccessary 
        */
        events: 
        { 
            "click .organizer_toolbox_button": "openTray"
        },
    
        init: function()
        {
            var that = this;
        
            //set up organizer app controllers
            this.toolbox = new ToolboxViewController({ app: this });
            this.photonav = new PhotoNavigatorController({ app: this });
        
            //Set up controllers for manager
            this.batch = new BatchController({ app: this });
            
            this.manager = new Spine.Manager(this.batch);
            
            Collection.fetch();
            
            this.setOrganizerWindowHeights();
            $(window).resize(function() {
                console.log("resizing window...");
                that.setOrganizerWindowHeights();
            });
            
            this.routes({
              "/": function(params){
                  this.batch.active();
              },
              "/collection/:id": function(params){
                var collection = Collection.find(params.id);
                var collection_controller = new CollectionController({ app: this, collection: collection });
                this.manager.add(collection_controller);
                collection_controller.active();
              }
            });
            
            Photo.bind("refresh create", this.proxy(this.publishBalloon));
            
            this.bind("show_toolbox", this.proxy(this.openTray));
            this.bind("hide_toolbox", this.proxy(this.closeTray));
        }, 
        
        custom_callback: function() 
        { 
            alert("custom callback");
        },
        
        setOrganizerWindowHeights: function(calculate) 
        { 
            this.organizer_plain_height = $(window).height() - $(".photo_navigator").height() - 64;
            $(".organizer_stage_platform").height(this.organizer_plain_height);
            $(".organizer_drawer").height(this.organizer_plain_height - 42);
            $(".stage_window").height(this.organizer_plain_height-42);
        },
        
        closeTray: function() 
        { 
            closable_toolox = true;
            this.toolbox_el.qtip("hide");
        },
        
        openTray: function()
        { 
            var that = this;
            if(!this.toolbox_el.data("qtip"))
            {
                this.toolbox_el.qtip(
                {
                    id: "toolbox_popover",
                    content: this.toolbox.el,
                    position: {
                		target: [18, 85]
                	},
                    show: {
                        solo: true,
                        ready: true,
                        event: 'none'
                    },
                    hide: {
                        event: 'unfocus'
                    },
                    style: {
                        tip: {
                            width: 18,
                            height: 8,
                            corner: 'top center'
                        },
                        width: 250,
                        classes: 'ui-tooltip-shadow ui-tooltip-organizer-popover toolbox-tooltip'
                    },
                    events: {
                		hide: function(event, api) {
                		    if(closable_toolox == false)
                		    { 
                    			event.preventDefault();
                            }
                		}
                	}
                });
            } else { 
                this.toolbox_el.qtip("show");
            }
        },
        
        publishBalloon: function()
        { 
            var unpublished = Photo.findAllByAttribute("published", false);
            if(unpublished.length > 0)
            {
                var m = new Modal();
                    m.withTitle("You have photos waiting to be reviewed!")
                     .withDescription("You have a few photos haven't been reviewed, you can go do that if you'd like.")
                     .withCancelButton("I'll do it later")
                     .withCallbackButton("Open the publisher", 
                                        [ { name: "class", value: "button" }, { name: "type", value: "submit"} ], 
                                        this.openPublisher)
                     .setReference(this)
                     .show();
             }
        },
        
        openPublisher: function(close, reference) { 
            close();
            
            var publisher = new PhotoPublishController({ app: reference });
            publisher.show();   
        }
    });
    
    /* 
     * Toolbox view controller... responsible for handling tasks associated
     * with the toolbox or outsourcing those tasks to the correct place.
     */ 
    var ToolboxViewController = Spine.Controller.sub(
    {
        init: function()
        {
            if(!this.app) { throw "@app depencency"; }
            
            this.render();
            
            Collection.bind("refresh", this.proxy(this.addAll));
            Collection.bind("create", this.proxy(this.addOne));
        },

        events: {
            "click .new_collection": "openNewCollection",
            "click .batch_button": "openBatchOrganizer"
        },

        elements: {
            ".collections_group": "collections_group",
            ".pending_imports": "import_el"
        },

        addOne: function(item)
        {
            console.log("Adding collection");
            var collectionitem = new ToolbarCollectionItem({ app: this.app, attributes: { "data-collection": item.id }, collection: item });
            this.collections_group.append(collectionitem.render().el);//append(collection.render().el); //<--
        },

        addAll: function()
        {
            Collection.each(this.proxy(this.addOne));
            Spine.Route.setup();
        },
        
        openBatchOrganizer: function()
        { 
            this.app.trigger("hide_toolbox");
            this.app.batch.active();
        },
        
        openNewCollection: function()
        { 
            this.app.trigger("hide_toolbox");
            
            var collection = new Collection({ title: "Untitled", description: "", photos: [] });
            
            var collection_controller = new CollectionController({ app: this.app, collection: collection });
            this.app.manager.add(collection_controller);
            collection_controller.active();
        },

        render: function()
        {
            var photoImportItem = new ToolbarPhotoImportItem({ app: this.app });
        
            this.html(this.template());
            this.import_el.html(photoImportItem.render().el);
            return this;
        },

        template: function(collections)
        {
            return Templates.toolbox_template();
        }

    });
    
    /* 
     * Controls the "pending review" row
     */ 
    var ToolbarPhotoImportItem = Spine.Controller.sub(
    { 
        events: 
        {
            "click": "open"
        },
    
        init: function()
        {
            if (!this.app) throw "@app dependency";
            
            Photo.bind("refresh change create destroy", this.proxy(this.render));
        },
        
        render: function()
        { 
            var unpublished = Photo.findAllByAttribute("published", false);
            this.html(this.template(unpublished.length));
            return this;
        },
        
        template: function(total) 
        { 
            total = (total==0)?false:total;
            return Templates.toolbar_photo_import_item({ total: total });
        },
        
        open: function()
        {
            this.publisher = new PhotoPublishController({ app: this.app });
            this.publisher.show();   
            this.app.trigger("hide_toolbox");
        }
    });
    
    /* 
     * Detail View for editing photos
     */ 
    var DetailViewController = Spine.Controller.sub(
    {

        events: {
            "click .save_button": "save",
            "click .cancel_button": "dismiss",
            "click ul.detail_tabs li": "showPanel",
            "click .delete_photo_button": "askDelete"
        },

        elements: {
            "#detail_form": "form",
            ".grouping .panel": "panels",
            "ul.detail_tabs li": "tabs",
            ".taken_field": "taken_field",
            ".posted_field": "posted_field",
            ".subprivacy_level": "subprivacy_level",
            ".privacy_level": "privacy_level",
            ".publish_checkbox": "publish_checkbox"
        },

        init: function()
        {
            if (!this.photo) throw "@photo required";
            this.photo.bind("update", this.proxy(this.render));
        },

        render: function(photo)
        {
            if (photo)
            {
                this.photo = photo;
            }

            this.html(this.template(this.photo));
            return this;
        },

        template: function(photo)
        {
            photo.everyone_checked = (photo.privacy_level==0);
            photo.private_checked = (photo.privacy_level==1);
            
            var modal = { photo: photo };
            
            return Templates.detail_view(photo);
        },
        
        show: function()
        { 
            var that = this;
        
            $("body").qtip(
            {
                id: "detail_edtior_modal",
                content: function() { return that.el; },
                position: {
                    my: 'center',
                    at: 'center',
                    target: $(window)
                },
                show: {
                    modal: {
                        on: true,
                        blur: false,
                        escape: true
                    },
                    ready: true
                },
                hide: false,
                style: {
                    classes: 'ui-tooltip-detail-edtior-modal'
                },
                events: {
                    hidden: function(e, api)
                    {
                        $(this).qtip('destroy'); //Dispose of the tip when it's done with.
                    }
                }

            });
            
        },
        
        save: function()
        {
            var o = this.photo; //So we don't lost the pointer.
            $.each(this.form.serializeArray(), function()
            {
            
                o[this.name] = this.value;

            });
            
            if(this.publish_checkbox.is(":checked"))
            {
                this.photo.published = true;
            } else { 
                this.photo.published = false;
            }
            
            o.save();

            this.dismiss();
        },
        
        askDelete: function(e)
        { 
            e.preventDefault();
            
            var m = new Modal();
                m.withTitle("Are you sure you want delete this photo?")
                 .withDescription("Once you delete this photo, you won't be able to get it back and it will be removed from the entire site.")
                 .withCancelButton("Don't Delete")
                 .withCallbackButton("Delete it", 
                                    [ { name: "class", value: "button" }, { name: "type", value: "submit"} ], 
                                    this.delete)
                 .setReference(this)
                 .show();
        },

        delete: function(close, reference)
        { 
            reference.photo.destroy();
            close();
        },

        dismiss: function()
        {
            $("body").qtip("hide");
        },
        
        showPanel: function(e)
        {
            this.panels.hide();
            this.tabs.removeClass("selected");
            $(".panel-" + $(e.target).addClass("selected").attr("data-panel")).show();
        }

    });
    
    /* 
     * Controllers  the collection items in the toolbox
     */ 
    var ToolbarCollectionItem = Spine.Controller.sub(
    {

        tag: "li",

        className: "collection_button",

        elements: { 
        },

        // Delegate the click event to a local handler
        events: {
            "click": "open",
            "click .quick_delete": "deleteConfirm"
        },

        // Bind events to the record
        init: function()
        {
            if (!this.collection) throw "@collection required";
            if (!this.app) throw "@app dependency";
            
            this.collection.bind("update", this.proxy(this.render));
            this.collection.bind("destroy", this.proxy(this.remove));
        },

        render: function(collection)
        {
            if (collection) this.collection = collection;    
            
            this.html(this.template(this.collection));
            return this;
        },
        
        deleteConfirm: function()
        { 
            var m = new Modal;
                m.withTitle("Delete this collection?")
                 .withDescription("Are you sure that you want to delete this collection? This is something you can't undo!")
                 .withCancelButton("Keep it")
                 .withCallbackButton("Delete it", 
                                    [ { name: "class", value: "button" }, { name: "type", value: "submit"} ], 
                                    this.doDelete)
                 .setReference(this)
                 .show();
        },
        
        doDelete: function(close, reference) 
        { 
            reference.collection.destroy();
            close();
        },

        // Use a template, in this case via jQuery.tmpl.js
        template: function(item)
        {
            return Templates.toolbox_collection_list_item({ collection: item });
        },

        // Called after an element is destroyed
        remove: function()
        {
            this.el.remove();
        },
        
        open: function(e) 
        { 
            this.app.trigger("hide_toolbox");
        
            if($(e.target).hasClass("uc")) // a little hack...
            {
                return;
            }
            
            var collection = new CollectionController({ app: this.app, collection: this.collection });
            this.app.manager.add(collection);
            collection.active();
        }
    });
    
    /* 
     * StageHelper
     *
     * Responsible for setting up the stage for a given stage controller
     */ 
    var StageHelper = Spine.Class.sub();
    //StageHelper.extend();
    StageHelper.include(Spine.Events);
    StageHelper.include(
    {
        
        init: function(el, stage) 
        { 
            this.stage = stage;
            this.$el = el;
            
            if(!this.$el) { throw "An el is required!"; }
            if(!this.stage) { throw "StageHelper expects a stage model element."; }
            
            this.setSortable();
            
            this.bind("stagelet_added", this.proxy(this._refresh));
        },
        
        setSortable: function() 
        { 
            var that = this;
            this.$el.sortable(
            {
                forcePlaceholderSize: true,
                forceHelperSize: true,
                placeholder: 'photo_ghost',
                helper: 'clone',
                accept: 'ul.listed_photos li',
                tolerance: 'pointer',
                start: function(e, ui)
                {
                    if (ui.item.data("staged"))
                    {
                        $(".dumpster_zone").fadeIn();
                    }
                    else
                    {
                        var photo = Photo.find(ui.item.find(".organizer_sortable_photo").first().attr("data-photo"));
                        photo.is_selected = true;
                        photo.save(
                        {
                            ajax: false
                        })
                    }
                },
                stop: function(e, ui)
                {
                    if (!ui.item.data("staged"))
                    {
                        /* We remove the actual dropped item */
                        ui.item.remove();
                        
                        $.each(Photo.findAllByAttribute("is_selected", true), function(i, photo)
                        { 
                            var existing_stagelet = that.stage.stagelets().select(function(s)
                            {
                                return (s.reference == photo.id && !s.removed);
                            });
                        
                            if (existing_stagelet.length > 0)
                            {
                                /* we don't drop this because it's already in this stage's set of stagelets */
                                that.trigger("stagelet_rejected");
                                console.log("Rejected!");
                            }
                            else
                            {
                                var stagelet = that.stage.stagelets().create({ reference: photo.id });
                                
                                photo.is_selected = false;
                                photo.save({ajax: false});
                                
                                console.log("Stagelet created!");
                                $el = $('<li class="ui-draggable">'+Templates.photo_template(photo)+'</li>').data('staged', true); // Get the item HTML ui.item.clone();   
                                that.trigger("stagelet_added", stagelet, $el);
                            } 
                        });
                    }
                    
                    $(".dumpster_zone").fadeOut();
                    
                },
                update: function(e, ui)
                {
                    Stagelet.trigger("reposition");
                },
                remove: function(e, ui)
                {
                    Stagelet.trigger("reposition");
                }
            }).disableSelection();
        }, 
        
        _refresh: function() 
        { 
            this.$el.sortable("refresh");
        }
    });
    
    /* 
     * All StageItemControllers should inherit from this
     */
    var StageItemController = Spine.Controller.sub(
    { 
        
        init: function()
        { 
            if(!this.el) { throw "An existing element is required"; }
            if(!this.stagelet) { throw "StageItem expecting a stagelet"; }
        
            Stagelet.bind("reposition", this.proxy(this._findPlacement));
            this.stagelet.bind("destroy", this.proxy(this._remove));
            this.stagelet.stage().bind("stagelet_removed", this.proxy(this._findPlacement));
        },
        
        _findPlacement: function() 
        { 
            var placement = this.el.index()+1;
            this.stagelet.order = placement;
            this.stagelet.save();
        },
        
        _remove: function() 
        { 
            this.el.remove();
            this.release();
        }
    });
    
    /* 
     * Controls the view for the batch items, which are photos.
     */
    var BatchStageItemController = StageItemController.sub(
    { 
        
        events: 
        { 
            "dblclick": "editDetails",
        },
        
        init: function() 
        {   
            this.constructor.__super__.init.apply(this, arguments);
            
            this.photo = Photo.find(this.stagelet.reference);
            this.photo.bind("destroy", this.proxy(this.remove));
        }, 
        
        editDetails: function()
        { 
            new DetailViewController({ photo: this.photo }).render().show();
        },
        
        remove: function()
        { 
            this.el.remove();
        }
        
    });
    
    /* 
     * Stage controllers should inherit from this to gain basic 
     * methods to hanle the stage
     */
    var StageController = Spine.Controller.sub(
    {
        activate: function()
        {
            this.el.removeClass("deactive");
            this.stage.active = true;
            this.stage.save();
            return this;
        },

        deactivate: function()
        {
            this.el.addClass("deactive");
            this.stage.active = false;
            this.stage.save();
            return this;
        }
    });
    
    /* 
     * Batch controller. Responsible for maintaining the batch view, applying 
     * tasks to photos in this batch, and remembering which photos are being currently
     * worked with 
     */
    var BatchController = StageController.sub(
    { 
        elements: 
        {
            "#batch_stage": "batch_stage",
            ".button_privacy": "privacy_button",
            ".button_move": "move_button",
            ".button_edit": "edit_button",
            ".batch_stage_empty": "batch_stage_empty_tag"
        },
        
        events: 
        {
            "click .button_clear": "clearBatch",
            "click .button_privacy": "openBatchPrivacyEditor",
            "click .button_move": "openBatchMoveEditor",
            "click .button_edit": "openEditor"
        },

        className: "organizer_stage_top organizer_stage_wrap", 

        init: function()
        {
            if(!this.app) { throw "@app required!"; }
            
            this.stage = new Stage({ name: "batch_stage" });
            this.stage.save();
            
            /* Add the batch editor and attach the helper */
            this.app.el.prepend(this.render().el);
            this.stage_helper = new StageHelper(this.batch_stage, this.stage);
            
            this.stage_helper.bind("stagelet_added", this.proxy(this.photoAdded));
            //this.stage_helper.bind("stagelet_rejected", this.proxy(this.photoAdded));
        },
        
        photoAdded: function(stagelet, element) 
        { 
            var psic = new BatchStageItemController({ el: element, stagelet: stagelet });
            this.batch_stage_empty_tag.fadeOut();
            /* we need to add a photo */
            this.batch_stage.append(psic.el);
        }, 
        
        batchHasPhotos: function() 
        { 
            var stagelets = this.stage.stagelets().select(function(s) { return !s.removed; });
            return (stagelets.length > 0);
        },
        
        clearBatch: function() 
        { 
            var stagelets = this.stage.stagelets().select(function(s) { 
                return !s.removed;
            });
            $.each(stagelets, function(i, s) 
            { 
                s.removed = true;
                s.save();
                Stagelet.destroy(s.id);
            });
        },  
        
        openBatchMoveEditor: function() 
        { 
            if(!this.batchHasPhotos())
            { 
                var m = new Modal();
                m.withTitle("Nothing to move!")
                 .withDescription("There isn't any photos to move into a collection, yet. Try adding some to the batch, first.")
                 .withCancelButton("Whoops, I'll add something.")
                 .show();
                 
                 return;
            }
            
            this.move_controller = new BatchMoveEditor({ batch_controller: this });
            this.move_button.qtip(
            {
                id: "batch_move_edit_popover",
                content: this.move_controller.render().el,
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
                    ready: true //Show when ready
                },
                hide: {
                    event: 'unfocus'
                },
                style: {
                    tip: {
                        width: 18,
                        height: 8,
                        offset: 120
                    },
                    width: 310,
                    classes: 'ui-tooltip-shadow ui-tooltip-organizer-popover'
                },
                events: {
                    hidden: function(e, api)
                    {
                        $(this).qtip('destroy'); //Dispose of the tip when it's done with.
                    }
                }
            });
            
        },
        
        openBatchPrivacyEditor: function() 
        { 
            if(!this.batchHasPhotos())
            { 
                var m = new Modal();
                m.withTitle("Nothing to change!")
                 .withDescription("Doesn't look like there are any photos in your batch to change the privacy on!")
                 .withCancelButton("Whoops, I'll add something.")
                 .show();
                 
                 return;
            }
            
            this.privacy_controller = new BatchPrivacyEditor({ batch_controller: this });
            this.privacy_button.qtip(
            {
                id: "batch_privacy_edit_popover",
                content: this.privacy_controller.render().el,
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
                    ready: true //Show when ready
                },
                hide: {
                    event: 'unfocus'
                },
                style: {
                    tip: {
                        width: 18,
                        height: 8,
                        offset: 120
                    },
                    width: 310,
                    classes: 'ui-tooltip-shadow ui-tooltip-organizer-popover'
                },
                events: {
                    hidden: function(e, api)
                    {
                        $(this).qtip('destroy'); //Dispose of the tip when it's done with.
                    }
                }
            });
        },
        
        openEditor: function() 
        { 
            if(!this.batchHasPhotos())
            { 
                var m = new Modal();
                m.withTitle("Nothing to change!")
                 .withDescription("Looks like there aren't any photos in the batch to make changes to!")
                 .withCancelButton("Whoops, I'll add something.")
                 .show();
                 
                 return;
            }
        
            var stagelets = this.stage.stagelets().select(function(s) { 
                return !s.removed;
            });
            
            if(stagelets.length == 1) 
            { 
                new DetailViewController({ photo: stagelets[0] }).render().show();
            } else if(stagelets.length > 1) 
            { 
                var photos = [];
                $.each(stagelets, function(i, s) { 
                    photos.push(Photo.find(s.reference));
                });
                new BatchPhotoDetailEditor({ app: this.app, photos: photos }).render().show();
            }
        },
        
        render: function()
        { 
            this.html(this.template());
            return this;
        },

        template: function() 
        { 
            return Templates.batch_stage_template();
        }
    });
    
    
    /* 
     * Collection controller. Responsible for maintaining the collection view
     */
    var CollectionController = StageController.sub(
    { 
        elements: 
        {
            ".collection_stage": "collection_stage",
            ".cover_photo_field": "cover_photo_field",
            ".photo_cover_holder": "photo_holder",
            ".collection_title": "collection_title",
            ".collection_desc": "collection_desc",
            ".photo_cover_holder": "photo_cover_dropzone"
        },
        
        events: {
            "click .cancel_new_set": "dismiss",
            "click .create_new": "save"
        },

        className: "organizer_stage_top organizer_stage_wrap", 
        
        changes: true,

        init: function()
        {
            if(!this.app) { throw "@app required!"; }
            if(!this.collection) { throw "@collection required!"; }
            
            this.stage = new Stage({ name: "collection_stage" });
            this.stage.save();
            
            this.app.el.prepend(this.render().el);
            this.app.setOrganizerWindowHeights(false);
            this.stage_helper = new StageHelper(this.collection_stage, this.stage);
            
            this.stage_helper.bind("stagelet_added", this.proxy(this.photoAdded));
            
            this.collection.bind("destroy", this.proxy(this.dismiss));
            this.collection.bind("change", this.proxy(this.render));
            
            this.importPhotos();
            
            this.droppableCover();
            if(Collection.exists(this.collection.id)) {
                this.navigate("/collection", this.collection.id);
            }
        },
        
        importPhotos: function() 
        {
            var that = this;
            $.each(this.collection.photos, function(i, p)
            { 
                var photo = Photo.find(p);
                var stagelet = that.stage.stagelets().create({ reference: photo.id });
                $el = $('<li class="ui-draggable">'+Templates.photo_template(photo)+'</li>').data('staged', true);
                that.photoAdded(stagelet, $el);
            });
        },
        
        droppableCover: function()
        { 
            this.photo_cover_dropzone.droppable(
            {
                accept: ".organizer_stage li",
                tolerance: "touch",
                greedy: true,
                //Fixes an overlaying issue..
                drop: this.setCover
            });
        },
        
        setCover: function(event, ui)
        {
            var photo = Photo.find($(ui.draggable).find("div").first().attr("data-photo"));
            $(".photo_cover_holder").html('<img src="http://photos.vinyett.com/' + photo.photo_path_square_120 + '" class="collection_image" />');
            $(".cover_photo_field").val(photo.id);
        },
        
        photoAdded: function(stagelet, element) 
        { 
            var psic = new BatchStageItemController({ el: element, stagelet: stagelet });
            /* we need to add a photo */
            this.collection_stage.append(psic.el);
        },   
        
        dismiss: function()
        { 
            this.prompt_to_close = false; 
            this.app.batch.active();
        },
        
        deactivate: function() 
        { 
            this.constructor.__super__.deactivate.apply(this);
            this.el.remove(); //Remove collections that are unused.
        },
        
        render: function()
        { 
            this.html(this.template());
            return this;
        },

        template: function() 
        { 
            return Templates.collection_stage({ collection: this.collection });
        },
        
        _save: function() 
        { 
            var that = this;
        
            this.collection.title = $(this.collection_title).val();
            this.collection.description = $(this.collection_desc).val();
            if ($(this.cover_photo_field).val())
            {
                this.collection.cover_photo = Photo.find($(this.cover_photo_field).val());
            }
            
            var stagelets = Stagelet.reduceWithPosition(this.stage.stagelets().select(function(s) { 
                return !s.removed;
            }));
            //this.collection.save();
            
            var p = [];
            $.each(stagelets, function(i, s)
            {
                p.push(s.photo);
            });
            this.collection.photos = p;
            /*this.collection.bind("save", function(collection){ 
                v(xhr.responseHtml);
                Collection.ReplacePhotos(that.collection, stagelets);
                that.collection.unbind("ajaxSuccess");
            });*/
            
            this.collection.save();

        },
        
        save: function() 
        { 
            if (!$(this.cover_photo_field).val())
            {
                var m = new Modal;
                m.withTitle("Unable to save collection")
                 .withDescription("At the moment all collections are required to have a cover.")
                 .withCancelButton("Ok, I'll add one")
                 .show();
                
                return;
            }

            this._save();
            this.changes = false;
            this.dismiss();
        }
    });
    
    /* 
     * Sets up a move-photos editor dialog for the batch controller
     */
    var BatchMoveEditor = Spine.Controller.sub(
    {
        elements: {
            
        },

        events: {
            "click .collection_block": "add",
            "click .new_button": "newCollection"
        },

        init: function()
        {
            if(!this.batch_controller) { throw "Batch Controller dependency!"; }
        },
        
        render: function()
        {
            var items = this.batch_controller.stage.stagelets().select(function(s) { return !s.removed; });
            this.html(Templates.batch_stage_move_window({ collections: Collection.all(), batch_num: items.length }));
            return this;
        }, 
        
        newCollection: function() 
        { 
            var collection = new Collection({ title: "Untitled", description: "", photos: [] });
            var items = this.batch_controller.stage.stagelets().select(function(s) { return !s.removed; });
            
            $.each(items, function(i, s) { 
                collection.photos.push(s.reference);
            });
            
            var collection_controller = new CollectionController({ app: this.batch_controller.app, collection: collection });
            this.batch_controller.app.manager.add(collection_controller);
            collection_controller.active();
            
            $('.qtip.ui-tooltip').qtip('hide');
        },
        
        add: function(e) 
        { 
            var collection = Collection.find($(e.currentTarget).data("collection"));
            var items = this.batch_controller.stage.stagelets().select(function(s) { return !s.removed; });
            
            $.each(items, function(i, s) { 
                collection.photos.push(s.reference);
            });
            
            collection.save();
            $('.qtip.ui-tooltip').qtip('hide');
        }

    });
    
    /* 
     * Sets up a privacy editor dialog for the batch controller
     */
    var BatchPrivacyEditor = Spine.Controller.sub(
    {
        elements: {
            ".save_button": "save_button"
        },

        events: {
            'click .save_button': 'saving'
        },

        init: function()
        {
            if(!this.batch_controller) { throw "Batch Controller dependency!"; }
        },
        
        render: function()
        {
            var items = this.batch_controller.stage.stagelets().select(function(s) { return !s.removed; });
            this.html(Templates.batch_stage_privacy_window({ batch_num: items.length }));
            return this;
        },
        
        saving: function() 
        { 
            this.save_button.text("Saving...").attr("disabled", true);
            this.save();
        },

        save: function()
        {
            $.each(this.batch_controller.stage.stagelets().select(function(s) { return !s.removed; }), function(i, s)
            { 
                var photo = Photo.find(s.reference);
                photo.privacy_level = $("#privacy_batch_edit .subprivacy_level:checked").val();
                if ($('#privacy_batch_edit #is_searchable').is(":checked"))
                {
                    photo.is_searchable = true;
                } else {
                    photo.is_searchable = false;
                }
                photo.save();
            });
            
            var m = new Modal();
                m.withTitle("Privacy was changed!")
                 .withDescription("Okay, we changed the privacy for all of the photos in the batch!")
                 .withCancelButton("Great!")
                 .show();
        }

    });
    
    /* 
     * Batch details editor!
     */
    var BatchPhotoDetailEditor = Spine.Controller.sub(
    {
        controllers: [],
    
        elements: 
        { 
            ".save_changes": "save_button",
            ".photo_editing": "photo_editor_field"
        },

        events: 
        {
            "click .save_changes": "saveAll",
            "click .delete_all": "deleteAll",
            "click .dismiss_button": "dismiss",
        },

        init: function()
        {
           if(!this.app) throw "@app dependency";
           if(!this.photos) throw "@photos required";
        },
        
        render: function()
        { 
            var that = this;
            
            this.html(this.template());
            $.each(this.photos, function(i, photo) { 
                var item = new BatchPhotoDetailItem({ photo: photo });
                that.controllers.push(item);
                that.photo_editor_field.append(item.render().el);
            });
            
            return this;
        }, 
        
        template: function()
        { 
            return Templates.batch_stage_edit_window({ photos: this.photos });
        },
        
        deleteAll: function()
        {   
            $.each(this.controllers, function(i, c) {
                console.log("destroying!");
                c.deletePhoto();
            });
            
            
            this.dismiss();
        },
        
        saveAll: function() 
        { 
            this.save_button.text("Saving...");
            this.save_button.prop("disabled", true);
        
            $.each(this.controllers, function(i, c) {
                c.save();
            });
            
            this.dismiss();
        },
        
        dismiss: function()
        { 
            $("body").qtip("hide");
        },
        
        show: function()
        { 
           var that = this;
           
           $("body").qtip({
                id: "publisher_modal",
                content: that.el,
                position: {
                    my: 'center',
                    at: 'center',
                    target: $(window)
                },
                show: {
                	modal: {
            			on: true,
            			blur: false
            		},
                    ready: true //Show when ready
                },
                hide: "none",
                style: {
                    classes: 'publisher-modal ui-tooltip-dialog-modal'
                },
                events: { 
                    hidden: function(e, api) 
                    { 
                        $(this).qtip('destroy');
                    }
                }
            });   
        } 
    });
    
   /* 
	* Controls the individual rows when editing multiple photos' 
	* details
	*/
    var BatchPhotoDetailItem = Spine.Controller.sub(
    {
        className: "editing_row",
    
        elements: 
        { 
            ".photo_form": "form"
        },

        events: 
        {
        },

        init: function()
        {
           if(!this.photo) throw "@photo required";
        },
        
        render: function()
        { 
            this.html(this.template());
            return this;
        }, 
        
        template: function()
        { 
            return Templates.batch_stage_edit_row({ photo: this.photo });
        },
        
        save: function()
        {
            var o = this.photo; //So we don't lost the pointer.
            $.each(this.form.serializeArray(), function()
            {
                o[this.name] = this.value;

            });
            o.save();
        },
        
        deletePhoto: function()
        { 
            this.photo.destroy();
            this.el.remove();
        }
    });

    /* 
     * Photo Navigator Toolbar Controller
     */ 
    var PhotoNavigatorToolbarController = Spine.Controller.sub(
    {
        
        events: 
        { 
            "click .add_all_link": "selectAll",
            "click .remove_all_link": "deselectAll"
        },
        
        init: function() 
        { 
            if(!this.photo_navigator) { throw "@dependent on photo navigator"; }
        
            Photo.bind("create change refresh", this.proxy(this.render));
        },
        
        render: function() 
        { 
            this.html(this.template);
            
            /* Set up controls */
            $('.prev').jcarouselControl({
                target: '-=8'
            });
            $('.next').jcarouselControl({
                target: '+=8'
            });
            
            return this;
        },
        
        template: function() 
        { 
            var selected = Photo.findAllByAttribute("is_selected", true);
            var viewed = _.filter(Photo.all(), function(photo) { return !photo.hidden_view });
            
            return Templates.navigator_toolbar_template({ selected_photos: selected.length, total_photos: viewed.length });
        },
        
        selectAll: function() 
        { 
            return this.photo_navigator.selectAll();
        },
        
        deselectAll: function() 
        { 
            return this.photo_navigator.deselectAll();
        }
        
    });
    
    /* 
     * Photo Navigator Controller which maintains the navigation bar that sits at the
     * bottom of most views in the organizer. Maintaining the global state of photos 
     * howeverm is oursourced to PhotoProvider 
     */ 
    var PhotoNavigatorController = Spine.Controller.sub(
    { 
              
        is_setup: false,  
        post_setup: false,
        
        el: $(".photo_navigator"),
        
        elements: 
        { 
            ".listed_photos": "organizer_reel",
            ".organizer_reel": "inner_real",
            ".reel_options_bar": "toolbar",
            ".dumpster_zone": "dumpster"
        },
                
        init: function()
        {
            if (!this.app) throw "@OrganizerApp dependency required.";
            
            Photo.bind("change", this.proxy(this.reload));
            Photo.bind("create", this.proxy(this.addOne));
            Photo.bind("refresh", this.proxy(this.addAll));
                    
            this.setup();
            this.setupToolbar();
            
            Photo.fetch();
        }, 
        
        setupToolbar: function() { 
            var toolbar = new PhotoNavigatorToolbarController({"photo_navigator": this });
            this.toolbar.html(toolbar.render().el);
        },
        
        setup: function() 
        { 
            var that = this;
            this.is_setup = true;
            this.inner_real.jcarousel({'vertical': false, animate: 'fast' });

            this.inner_real.on('animateend.jcarousel', function(event, carousel) {
                var last       = that.inner_real.jcarousel('last');
                var lastIndex  = that.inner_real.jcarousel('items').index(last);
                var total      = that.inner_real.jcarousel('items').size();
            
                if (lastIndex == (total - 1)) {
                    that.post_setup = true;
                    Photo.fetch({ data: "offset="+Photo.count() });
                }
            });
    
            this.dumpster.droppable(
            {
                drop: function(ev, ui)
                {        
                    var photo = Photo.find(ui.draggable.find(".organizer_sortable_photo").first().attr("data-photo"));
                    var stage = Stage.findByAttribute("active", true);
                    var stagelet = stage.stagelets().findByAttribute("reference", photo.id);
                    stagelet.removed = true;
                    stagelet.save();
                    
                    
                    Stagelet.destroy(stagelet.id);
                    
                    console.log("stagelet removed");
                    stage.trigger("stagelet_removed");
                }
            });
    
        },
        
        reload: function() 
        { 
            this.inner_real.jcarousel("reload");
        },
        
        photoin: function(photo) 
        { 
            var existing = [];
            $(".organizer_sortable_photo").each(function(i) 
            { 
                existing.push($(this).data('photo'));
            });
            return _.contains(existing, photo.id);
        },
        
        addOne: function(photo)
        {
            var view = new NavigatorPhotoItem(
            {
                item: photo
            });
            
            this.organizer_reel.append(view.render().el);
            this.inner_real.jcarousel('reload');
        },

        addAll: function()
        {   
            if(this.post_setup == false) 
            { 
                this.organizer_reel.html(null);
            }
            
            var that = this;
            var to_add = _.filter(Photo.all(), function(photo) { 
                return !that.photoin(photo)
            });
            
            $.each(_.sortBy(to_add, function(photo) { return photo.date_posted }).reverse(), function(i, photo)
            {
                that.addOne(photo);
            });
        },
  
        /* 
         * About selectAll and deselectAll
         * 
         * These functions are pretty slow... so we're probably going to need to add
         * hidden_view and is_selected as parameters soon, not just variables to use 
         * Spine's native model functions instead of _'s
         */
         
        deselectAll: function() 
        { 
            $.each(Photo.select(function(p) { return p.is_selected }), function(i, photo) { 
                photo.is_selected = false;
                photo.save({ ajax: false });
            });
        },
        
        selectAll: function() {
            $.each(Photo.all(), function(i, photo) { 
                photo.is_selected = true;
                photo.save({ ajax: false });
            });
        }
    });
    
    /* 
     * Photo Navigator Controller which maintains the navigation bar that sits at the
     * bottom of most views in the organizer. Maintaining the global state of photos 
     * howeverm is oursourced to PhotoProvider 
     */ 
    var NavigatorPhotoItem = Spine.Controller.sub(
    {
        //Local templates
        events: {
            "dblclick": "edit",
            "click": "select"
        },

        tag: "li",

        init: function()
        {
            if (!this.item) throw "@photo required";
            this.item.bind("update", this.proxy(this.render));
            this.item.bind("destroy", this.proxy(this.remove));
        },

        render: function(item)
        {
            if (item)
            {
                this.item = item;
            }

            this.html(this.template(this.item));
            
            $(this.el).draggable(
            {
                snap: false,
                revert: 'invalid',
                appendTo: "#organizer",
                cursor: 'move',
                opacity: 0.5,
                connectToSortable: ".stage_window",
                zIndex: 10000,
                helper: "clone",
                containment: "body",
                start: function(e, ui)
                {
                    if (Photo.Selected().length > 1)
                    {
                        //Do something to the cursor.
                    }
                }

            });
            
            return this;
        },

        template: function(items)
        {
            return Templates.photo_template(items);
        },

        remove: function()
        {
            this.el.remove();
        },

        select: function()
        {
            this.item.is_selected = !this.item.is_selected;
            this.item.save({ ajax: false });
        },

        edit: function()
        {
            new DetailViewController({ photo: this.item }).render().show();
        }
    });
    
    /* 
     * Controller for photo items inside of the publisher
     */ 
    var UnpublishedPhotoController = Spine.Controller.sub(
    {         
        events: 
        { 
            "click .title_spot": "changeTitle",
            "blur .title_change": "commitTitle",
            'keyup .title_change': 'finishTitleEditOnEnter',
            
            "click .description_spot": "changeDescription",
            "blur .description_change": "commitDescription",
            'keyup .description_change': 'finishDescriptionEditOnEnter'
        },
        
        elements: 
        {
            ".title_change": "title_input",
            ".title_spot": "title_spot",
            ".title_edit": "title_edit_div",
            
            ".description_change": "description_input",
            ".description_spot": "description_spot",
            ".description_edit": "description_edit_div"
        },
    
        className: "unpublished_block",
     
        publishing: true,
     
        init: function() 
        { 
            if(!this.publisher) throw "@PhotoPublisher dependency";
            if(!this.photo) throw "@photo required";
            
            //this.photo.bind("update", this.proxy(this.render()));
            //this.photo.bind("destroy", this.proxy(this.remove()));
        }, 
        
        render: function(photo)
        {
            if (photo) this.photo = photo;    
            
            this.html(this.template(this.photo));
            return this;
        },
        
        template: function(photo)
        { 
            return Templates.photo_publisher_unpublished({ photo: photo, publishing: this.publishing });
        },
        
        remove: function()
        { 
            this.el.remove();
        },
        
        commitTitle: function() 
        {
            //this.photo.updateAttribute("title", this.title_input.val());
            this.photo.title = this.title_input.val();
            this.render();
        },
        
        changeTitle: function()
        { 
            this.title_spot.hide();
            this.title_edit_div.show();
            this.title_input.val(this.photo.title);
            this.title_input.focus();
        },
        
        finishTitleEditOnEnter: function(e) {
          if (e.which === 13) {
            return this.commitTitle();
          }
        },
        
        commitDescription: function() 
        {
            //this.photo.updateAttribute("title", this.title_input.val());
            this.photo.description = this.description_input.val();
            this.render();
        },
        
        changeDescription: function()
        { 
            this.description_spot.hide();
            this.description_edit_div.show();
            this.description_input.val(this.photo.description);
            this.description_input.focus();
        },
        
        finishDescriptionEditOnEnter: function(e) {
          if (e.which === 13) {
            return this.commitDescription();
          }
        },
        
        publish: function(batch)
        { 
            if(this.publishing)
            { 
                if(batch)
                { 
                    var stagelet = this.app.batch.stage.stagelets().create({ reference: this.photo.id });
                    $el = $('<li class="ui-draggable">'+Templates.photo_template(this.photo)+'</li>').data('staged', true);
                    this.app.batch.photoAdded(stagelet, $el);
                }
                
                this.photo.published = true;
                this.photo.save();
            }
        }
        
    });
    
    /* 
     * Controls the general photo publishing UI.
     */ 
    var PhotoPublishController = Spine.Controller.sub(
    { 
        
        unpublished_photos: [],
        
        elements: 
        { 
            ".publishing": "unpublished_field",
            ".batch_open": "batch_published_photos"
        },
        
        events: 
        { 
            "click .do_publishing": "publishAll",
            "click .cancel_publishing": "cancel"
        },
    
        init: function() 
        { 
            if(!this.app) throw "@app dependency";
            this.render();
        }, 
        
        render: function() 
        { 
            var that = this;
        
            this.html(this.template());
            
            var unpublished = Photo.findAllByAttribute("published", false);
            $.each(unpublished, function(i, photo) { 
                var unpublishedphotocontroller = new UnpublishedPhotoController({ photo: photo, app: that.app, publisher: that });
                that.unpublished_photos.push(unpublishedphotocontroller);
                that.unpublished_field.append(unpublishedphotocontroller.render().el);
            });
            
            return this;
        },
        
        template: function() 
        { 
            return Templates.photo_pubisher();
        },
        
        publishAll: function()
        { 
            if(this.batch_published_photos.is(":checked"))
            { 
                var batch = true;
            } else { 
                var batch = false;
            }
        
            this.app.batch.clearBatch();
        
            $.each(this.unpublished_photos, function(i, p) { 
                p.publish(batch);
            });
            this.cancel();
        },
        
        cancel: function()
        { 
            $("body").qtip("hide");
        },
        
        show: function() 
        {
           var that = this;
           
           $("body").qtip({
                id: "publisher_modal",
                content: that.el,
                position: {
                    my: 'center',
                    at: 'center',
                    target: $(window)
                },
                show: {
                	modal: {
            			on: true,
            			blur: false,
            			escape: false
            		},
                    ready: true //Show when ready
                },
                hide: "none",
                style: {
                    classes: 'publisher-modal ui-tooltip-dialog-modal'
                },
                events: { 
                    hidden: function(e, api) 
                    { 
                        $(this).qtip('destroy');
                    }
                }
            });        
        }
    });
    
    return OrganizerApp;

});