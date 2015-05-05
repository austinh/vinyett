/*
 * @var array in_batch
 */
var in_batch;

/*
 * @var boolean $placing
 */
var placing = false;

/*
 * @var boolean $removing
 */
var removing = false;

/*
 * @var interger $content_height
 */
var content_height = 0;

/*
 * @var sessionStore $dataStore
 */
var dataStore = null;

/*
 * @var boolean $can_tab
 */
var can_tab = true;


/**
 * Hooks in the required bits to start the organizer.
 *
 * @return null
 */ 
function init_organizer() 
{ 

    //get window heights... 
    var window_height = $(window).height();
    var toolbar_height = 135;
    var organizer_photo_browser = $(".organizer_browser").height();
    
    content_height = window_height-(toolbar_height+organizer_photo_browser); //So the views can have a static height to base themselves off of.


    compile_templates();
    create_batcher();
    build_popover_menus();
}


/**
 * Adds an ID to the batch
 *
 * @param integer id ID of the photo in the batch
 *
 * @return null
 */
function add_to_batch_dispatch(id) 
{   
    in_batch.push(id);
}


/**
 * Removes an ID to the batch
 *
 * @param integer id ID of the photo in the batch
 *
 * @return null
 */
function remove_from_batch_dispatch(id) 
{   
    in_batch.splice(in_batch.indexOf(id), 1);
	
	// re-enable draggable
	$("#photo_browser li[data-photo-id="+id+"]").draggable("enable");

}


/**
 * Checks for an ID in the batch   
 *
 * @param integer id the ID to check
 *
 * @return 
 */
function is_in_batch(id) 
{ 
    if($.inArray(id, in_batch) == "-1")
    { 
        return false;
    } else { 
        return true;
    }
}


/**
 * Compiles the templates for later use.
 *
 * @return null
 */
function compile_templates() 
{ 
    window.loading_template = Handlebars.compile($("#loading_handlebar").html());
    window.details_editor_template = Handlebars.compile($("#details_editor_handlebar").html());
    window.collection_information_template = Handlebars.compile($("#collection_information_handlebar").html());
}


/**
 * Shows the loading dialog when things are happening...
 *
 * @return null
 */
function show_loading_dialog() 
{
    $("#loading_blob").qtip({ 
        content: loading_template(),
        position: {
                my: 'center', // ...at the center of the viewport
                at: 'center',
                target: $(window)
            },
            show: {
                event: 'click', // Show it on click...
                solo: true, // ...and hide all other tooltips...
                modal: true, // ...and make it modal
                ready: true
            },
            hide: false,
            style: {
                classes: 'ui-tooltip-loading-modal'
            }
    });
}


/**
 * Checks to see if the browser supports session storage
 * also loads the dataStore var if null and supported.
 *
 * @return boolean
 */
function does_support_locale_storage() 
{ 

    if(typeof(Storage)!=="undefined")
    { 
        if(dataStore === null) { 
            dataStore = window.sessionStorage;
        }
    
        return true;
    } else { 
        return false;
    }

}


/**
 * Gets the photo from cache or returns false
 *
 * @param interger id ID of the photo to check for stored infos
 *
 * @return 
 */
function photo_in_cache(id) 
{ 
    //First we check to make sure the browser supports it...
    if(!does_support_locale_storage())
    {
        return false;
    }
    
    var photo = null;
    //Now let's check...
    var photo_key = "photo_stored_structure_"+id;
    photo = dataStore.getItem(photo_key);
    
    if(photo == null) 
    { 
        photo = false;
    } else { 
        photo = JSON.parse(photo);
    }
    
    return photo;
}


/**
 * store_photo_to_cache
 *
 * @param array photo Photo array to store
 *
 * @return array (photo array)
 */
function store_photo_to_cache(photo, id) 
{ 
    if(!does_support_locale_storage())
    {
        return false;
    }
    
    var photo_key = "photo_stored_structure_"+id;
    
    try {
        dataStore.setItem(photo_key, JSON.stringify(photo, null, 2));
    }
    catch(e){
        if(e.code == 22){
            dataStore.clear(); //Wipe cache and keep going.
        }
    }
    
    return photo;
    
}


/**
 * Watches .thumble for double clicks and opens the details editor
 * to make changes to it
 *
 * @return null
 */
function watch_thumble_clicks() 
{ 
    $(".thumble").dblclick(function(){ 
        
        var photo_id = $(this).parent().attr("data-photo-id");
        
        //Throw up a popup loading modal.
        $(this).addClass("loading_details_editor");
        $(".thumble").unbind("dblclick");
        
        var cached_photo = photo_in_cache(photo_id); //First we see if we've pulled the info for this photo...
        if(!cached_photo)
        { 
            //We request the photo
            $.ajax({
                url: Global.settings.rurl+'ajax/photo/'+photo_id,
                type: "POST",
                dataType: "json"
            }).done(function (data) {
        		store_photo_to_cache(data.photo, data.photo.id); //Tricky, it returns the photo
        		var model = { 
            		photo: data.photo
        		}
        		create_edit_details_dialog(model);
        	}).fail(function() { 
                alert("Oh no! unable to fetch data, maybe you or Vinyett are offline? Try again, maybe?"); 
            });
        } else { 
            var model = { 
        		photo: cached_photo
    		}
            create_edit_details_dialog(model);
        }
                
		$(this).removeClass("loading_details_editor");
        watch_thumble_clicks();
         
    }); //dblclick
}


/**
 * Creates the edit photo window
 *
 * @param array model Model to send to the template
 *
 * @return 
 */
function create_edit_details_dialog(model) 
{ 

    $("body").qtip({
        id: "detail_edtior_modal",
        content: {
            text: details_editor_template(model)
        },
        position: {
            my: 'center', // ...at the center of the viewport
            at: 'center',
            target: $(window)
        },
        show: {
            solo: true, // ...and hide all other tooltips...
        	modal: {
    			on: true,
    			blur: false,
    			escape: true
    		}, // ...and make it modal
            ready: true //Show when ready
        },
        hide: false,
        style: {
            classes: 'ui-tooltip-detail-edtior-modal'
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
 * Creates a new window in the editor with a title. Returns
 * the ID of the element to refer to when utilizing.
 *
 * Overall, this function: 
 * 1: Creates a new tab and window
 * 2: Names the tab and links it to the window
 * 3: Gives back the identifier to refer to the window
 *
 * @param string title Title of the new window
 *
 * @return string
 */
function new_organizer_window(title, type, set_active) 
{ 
    var ts = Math.round(new Date().getTime() / 1000);
    var window_id = type+"_organizer_"+ts;
    var model;
    
    //new tab time!
    var organizer_tab = '<li id="tab_'+window_id+'" data-organizer-tab-type="custom" data-type="'+type+'">'+title+'</li>';
    //Add the tab to the organizer list...
    $("#organizer_tabs").append(organizer_tab);
        
    if(set_active == true) 
    { 
        open_tab("#tab_"+window_id, type);
    }
    
    //Create the window...
    if(type == "new_collection") { 
        model = { 
            info_title: "Create a collection",
            button_title: "Create",
            title: null,
            description: null
        }
        $("#tab_"+window_id).attr("data-unsaved", "true"); //New Set so unsaved.
        set_organizer_for_collection_edit(model);
    }

    return window_id;
}


/**
 * Sets the organizer up to be edit preloaded with a collection or for
 * a new collection
 *
 * @param array model Model for the information window
 *
 * @return null
 */
function set_organizer_for_collection_edit(model) 
{ 
    $("body").append(collection_information_template(model));
    $(".organizer_collection_information").height(content_height + 26);
    $(".organizer_batch_editor").css("padding-left", "260px");
    
    $(".draggable_cover").droppable({
        drop: function( event, ui ) {
            var cover_id = ui.helper.attr("data-photo-id");
            var cover_image = ui.helper.children("a").children("img").attr("src");
            
            $("#cover_id").val(cover_id); //yay!
            
            $(".draggable_cover .cover img").attr("src", cover_image);
        }
    });
    
    $('#collection_form').submit(function(event) {
        event.preventDefault();
    });
    
}


/**
 * Returns the collection creator back to batch editing...
 *
 * @return null
 */
function cancel_collection_to_batch() 
{ 
    if(can_tab == false) 
    { 
        //We've made some edits, ask the user if they want to leave the page...
    }
    
    //Kill the organizer window
    $(".organizer_collection_information").remove();
    $("li[data-unsaved=true]").remove();
    open_tab("#tab_batch_organize", "batch");
}


/**
 * Changes the active tab
 *
 * @param string tab_id Identifier for the current tab
 *
 * @return null;
 */
function open_tab(tab_id, toolbar) 
{ 

    $("#organizer_tabs li").removeClass("selected_organizer_tab");
    $(tab_id).addClass("selected_organizer_tab");
    $(".organizer_batch_editor").css("padding-left", 0);
    $(".toolbar_options").hide();
    $(".toolbar_options[data-toolbar-type="+toolbar+"]").show();
}


/**
 * Sets the internal batch to the order of the items in the sortable
 * and resyncs it (if they come unordered).
 *
 * @return boolean
 */
function resync_internal_batch() 
{ 
    var resync_batch = new Array();
    
    $(".batch_listing li").each(function(index) { 
        var item_id = $(this).attr("data-photo-id");
        
        resync_batch.push(item_id);
    });
    
    in_batch = resync_batch;
    
    return true;
}


/**
 * Saves a collection, if nulled ID, then it creates a new one.
 *
 * @param mixed id ID of the collection, can be null
 *
 * @return null
 */
function save_collection() 
{ 
    $("#collection_form button[type=submit]").text("Saving...").prop("disabled", true);

    resync_internal_batch();

    var collection_title = $("#collection_title").val();
    var collection_desc = $("#collection_description").val();
    var collection_id = $("input#collection_id").val();
        
    var postData = $("#collection_form").serialize();
    
    $.ajax({
        url: Global.settings.rurl+'organizer/ajax/collection/sync',
        data: (postData+"&photos="+in_batch),
        type: "POST",
        dataType: "json"
    }).done(function (data) {
		throw_simple_dialog("Photo collection created", "Your collection was created.");
		$("#collection_form button[type=submit]").text("Update").prop("disabled", false);
		$("input#collection_id").val(data.collection.id);
		$(".toolbar_options").hide(); //Reset toolbars
		$(".toolbar_options[data-toolbar-type=collection]").show();
	}).fail(function() { 
        alert("Oh no! It looks like there was an error and nothing happened."); 
        $("#collection_form button[type=submit]").text("Save").prop("disabled", false);
    });
}


/**
 * Sets up the batch editor to make a new set.
 *
 * @return null
 */
function create_new_set() 
{ 
    if(in_batch.length == 0) 
    {
        throw_simple_dialog("No photos in batch", "You must have at least one photo in the batch to work with Collections.");
    } 
    else 
    { 
        new_organizer_window("Untitled Collection", "new_collection", true);
    }        
}


/**
 * Builds the toolbar menus (in the entire app) that are
 * contained in popovers
 *
 * @return 
 */
function build_popover_menus() 
{
    $('.toolbar_options li img.more').each(function(index) {
        var menu_id = $(this).attr('data-menu-id');
        $(this).qtip({
            content: $("#"+menu_id),
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
            show: { 
                event: 'click'
            },
        	hide: {
        		event: 'unfocus'
        	}
        });
    });
}


/**
 * Makes photos interactive!
 *
 * @return 
 */
function make_photos_interactive() 
{ 
    $("#photo_browser li").draggable({
        snap: false,
        revert: 'invalid',
        helper: 'clone',
        appendTo: "body",
        cursor: 'move',
        opacity: 0.5,
        connectToSortable: "ul.batch_listing",
        start: function(e, ui){
            placing = true;
        }, 
        stop: function(e, ui){
            placing = false;
        }
    });
    
    watch_thumble_clicks();

}


/**
 * Resizes the batcher to the height of the window minus the elements
 * rendered in the layout (headers, footers)
 *
 * @return null
 */
function create_batcher() 
{ 
    //get window heights...
    $( ".batch_listing li" ).disableSelection();
    
    var batch_space = content_height; //Set the batcher to the content space height.

    $(".batch_listing").css("height", batch_space+"px");
    $('.batch_listing').sortable({
        forcePlaceholderSize: true,
        forceHelperSize: true,
        placeholder: 'photo_ghost',
        helper: 'clone',
        accept: '#photo_browser li',
        receive: function(e, ui) 
        { 
            if(!is_in_batch(ui.item.attr("data-photo-id"))) 
            { 
                add_to_batch_dispatch(ui.item.attr("data-photo-id"));
                watch_thumble_clicks();
				$(ui.item).draggable("disable");
            } else { 
                //Remove from batch
                $(this).sortable("cancel");
            }
        },
        sort: function(event, ui) {
            if(placing == false) 
            {
                $(".destory_drop").fadeIn();
            }
        },
        stop: function(event, ui) { 
            if(removing == false)
            {
                $(".destory_drop").fadeOut();
            } else { 
                setTimeout('$(".destory_drop").fadeOut();', 800);
            }
        }
    }).disableSelection();
    
    //Also sets up the destroy drop
    $('body').append($('<img>', { 
        src : "/images/n2mine.gif", 
        alt : "Gone now!",
        id: "explostion_removed",
        style: "position:absolute; display:none; pointer-events:none;"
    }));
    
    $(".destory_drop").droppable({
        over: function(e, ui){ 
            removing = true;
        },
        out: function(e,u) { 
            removing = false;
        },
        drop: function(e, ui){
            var removed_id = ui.draggable.attr("data-photo-id");
            
            remove_from_batch_dispatch(removed_id);
        
            ui.draggable.remove();
            ui.helper.remove();
            
            var dropped_at = ui.offset;
            $("#explostion_removed").show().css({ 'top': dropped_at.top, 'left': dropped_at.left }).attr("src", "/images/n2mine.gif");
        }       
    });

}


function photo_browser_itemLoadCallback(carousel, state)
{
    if (carousel.prevFirst != null) {
        // Remove the last visible items to keep the list small
        for (var i = carousel.prevFirst; i <= carousel.prevLast; i++) {
            // jCarousel takes care not to remove visible items
            //carousel.remove(i);
        }
    }

    var per_page = carousel.last - carousel.first + 1;
    var currPage = 0;
    var f,l;
    var cr = carousel;

    for (var i = carousel.first; i <= carousel.last; i++) {
        var page = Math.ceil(i / per_page);

        if (currPage != page) {
            currPage = page;

            f = ((page - 1) * per_page) + 1;
            l = f + per_page - 1;

            f = f < carousel.first ? carousel.first : f;
            l = l > carousel.last ? carousel.last : l;

            if (carousel.has(f, l)) {
                continue;
            }

            photo_browser_makeRequest(carousel, f, l, per_page, page);
        }
        
    }
};

function photo_browser_makeRequest(carousel, first, last, per_page, page)
{
    carousel.lock();
    
    $.ajax({ 
        url: Global.settings.rurl+'organize/ajax/photos',
        dataType: 'json',
        type: "POST",
        data: { 'per_page': per_page, "page": page }
    }).done(function(data) {
        photo_browser_itemAddCallback(carousel, first, last, data, page);
    }).fail(function(error, textStatus) { 
        alert(textStatus);
    });
};

function photo_browser_itemAddCallback(carousel, first, last, data, page)
{
    // Unlock
    carousel.unlock();

    // Set size
    carousel.size(data.photos.total);

    var photos = data.photos.photo;
    var per_page = carousel.last - carousel.first + 1;
    
    if(last > data.photos.total) 
    { 
        last = data.photos.total;
    }

    for (var i = first; i <= last; i++) {
        var pos = i - 1;
        var idx = Math.round(((pos / per_page) - Math.floor(pos / per_page)) * per_page);

        carousel.add(i, photo_browser_getItemHTML(photos[idx]));  
		$(carousel.get(i)).attr('data-photo-id', photos[idx].id);
    }
    make_photos_interactive();

};

/**
 * Global item html creation helper.
 */
function photo_browser_getItemHTML(photo)
{

    var absolute_url_path = "http://photos.vinyett.com/";

    return '<a href="#" class="thumble" onclick="return false"><img src="'+absolute_url_path+photo.photo_path_square_120+'" width="75" height="75" class="browser_photo" alt="'+photo.title+'" /></a>';
};