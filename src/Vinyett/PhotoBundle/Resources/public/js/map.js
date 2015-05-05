
/*
* Maps API key. Pwease if you're learning based on this code, don't 
* use Vinyett's key. It holds important statistical information
* and I don't want the results to be skewed.
*/
var maps_api_key = "ce572dfa2fa74351aa41d24181186a42";

/*
* Map instance
*/
var map = null;
/*
* Marker instance
*/
var marker = null;
/*
* Solution to prevent multiple maps from being fired during modal creation
*/
var maps_inited = false;

/*
* Minimap instance
*/
var minimap = null;
/*
* Minimap marker instance
*/
var minimap_marker = null;

/*
* Public drop pin style
*/
var dropPin = L.Icon.extend({ options: {
                                            shadowUrl: '/images/drop_pin_shadow.png',
                                            iconSize: new L.Point(32, 39),
                                            shadowSize: new L.Point(32, 39),
                                            iconAnchor: new L.Point(10, 39),
                                            popupAnchor: new L.Point(0, -39),
                                            iconUrl: '/images/drop_pin_pin.png' 
                                          }
                                        });

/*
* Opens the mapper modal (based on qtip2)
*
* @return null
*/

function open_mapper() 
{ 
    if($("#open_mapper").hasClass('qtip'))
    { 
        return; //Don't add the qtip
    }
        $("#open_mapper").qtip(
        {
            id: "map-modal",
            content: {
                text: $(".mapper")
            },
            position: {
                my: 'center', // ...at the center of the viewport
                at: 'center',
                target: $(window)
            },
            show: {
                event: 'click', // Show it on click...
                solo: true, // ...and hide all other tooltips...
                modal: true, // ...and make it modal
                ready: false
            },
            hide: false,
            style: {
                classes: 'ui-tooltip-map-modal'
            }, 
            events: { 
                visible: function() 
                { 
                    if(maps_inited == false) 
                    {
                        init_photo_mapper();
                    } 
                    if($("#geo_lat").val())
                    {
                        focus_map($("#geo_lat").val(), $("#geo_long").val(), $("#geo_zoom").val());
                    }
                    
                }
            }
        });

}

/*
* Turns on the mapping
*
* @return null
*/
function init_photo_mapper() 
{

    // Set up the map
    map = new L.Map('photo_mapper', { closePopupOnClick: false });
    
    var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/'+maps_api_key+'/997/256/{z}/{x}/{y}.png',
    	cloudmadeAttribution = '&copy; 2012 OpenStreetMap',
    	cloudmade = new L.TileLayer(cloudmadeUrl, {minZoom: 2, maxZoom: 18, attribution: cloudmadeAttribution});
    
    //Set the view to something AWESOME (temp)
    map.setView(new L.LatLng(41.4357, -49.564), 2).addLayer(cloudmade);
    
    
    //Init the geo finder...
    $('#geo_finder').geo_autocomplete({
    	mapheight: 75,
    	mapwidth: 75,
    	minLength: 2,
    	delay: 100,
    	select: function(_event, _ui) {
    	 //if (_ui.item.viewport) map.fitBounds( new L.LatLng(_ui.item.viewport));
    	 map.panTo( new L.LatLng(_ui.item.location.lat(), _ui.item.location.lng())  );
    	 map.setZoom(12);
    	 reset_marker();
    	}
    });
    
    /* Why isn't this an onclick event...? */
    $('#location_finder').on('click', function() {
    	map.locate({setView : true});
    });
    
    map.on('locationfound', function() { 
        reset_marker();
    });
    
    
    var markerLocation = new L.LatLng(41.4357, -49.5649);
    var droppable = new dropPin();
    
    marker = new L.Marker(markerLocation, {icon: droppable, draggable: true});
    
    map.addLayer(marker);
    //Add a popup
    marker.bindPopup('<div id="pin_location">Drag me or replace me somewhere.</div>', { closeButton: false });
    //update_marker_location();
    
    //And make it continuously popup on a drag end event
    marker.on('dragend', function(e) {
        marker.openPopup();
        update_marker_location();
    });
    
    map.on('zoomend', function() { 
        update_marker_location();
    });
    
    maps_inited = true;
        
}


/*
* Places the Pin in the center of the current map view.
*
* NOTE: This function should only be called AFTER the map has been repositioned!
*
* @return null
*/
function reset_marker() 
{ 
    map.removeLayer(marker);

    //Reposition marker...
    marker.setLatLng(map.getCenter());
    
    map.addLayer(marker); //we recycle the same marker...
    
    marker.openPopup();
    
    update_marker_location();
    
}


/*
* Updates the pin location popup with the current location
* (updates #pin_location).
*
* @return null
*/
function update_marker_location() 
{ 
    //Let's save the results we've got
    press_marker_location(); //lat, long, zoom.

    //A little note
    marker.bindPopup("Locating...", { closeButton: false }).openPopup();
    $("#temp_geo_name").val("Unable to determine location"); //Just in case...

    //We get hte location 
    var current_location = marker.getLatLng();
    var location_url = "http://nominatim.openstreetmap.org/reverse?lat="+current_location.lat+"&lon="+current_location.lng+"&zoom="+map.getZoom()+"&format=json";

    //Where are u?
    $.ajax({
        url: location_url,
        dataType: "json",
        jsonpString: "json_callback",
        error: function() 
        { 
            marker.bindPopup("Unable to determine location.", { closeButton: false }).openPopup();
            $("#temp_geo_name").val("Unable to determine location");
        },
        success: function(data)
        { 
            if(data.display_name) 
            {
                marker.bindPopup(data.display_name, { closeButton: false }).openPopup();
                $("#temp_geo_name").val(data.display_name);
            } else { 
                marker.bindPopup("Unable to determine location.", { closeButton: false }).openPopup();
                $("#temp_geo_name").val("Unable to determine location");
            }
        }
    });
}

/*
* Updates the hidden inputs to the markers current map values
*
* @return null
*/
function press_marker_location() 
{ 
    var current_location = marker.getLatLng();

    var geo_lat = current_location.lat;
    var geo_long = current_location.lng;
    var geo_zoom = map.getZoom();
    
    //update the values...
    $("#temp_geo_lat").val(geo_lat);
    $("#temp_geo_long").val(geo_long);
    $("#temp_geo_zoom").val(geo_zoom);

}

/*
* Savea the location to the server, dismisses the box, and upates the 
* layouts location placement below the image
*
* @return null
*/
function finish_location()
{ 
    $(".map_button").html("Saving...").prop('disabled', true);

    $.ajax({
        url: Global.settings.rurl+'ajax/photo/'+Global.photo.id+'/geotag',
        data: { lat: $("#temp_geo_lat").val(), lng: $("#temp_geo_long").val(), zoom: $("#temp_geo_zoom").val(), name: $("#temp_geo_name").val() },
        type: "POST",
        dataType: "json"
    }).fail(function() { 
        alert("Oh no! It looks like there was an error and we were unable to save your data. Please refresh and try again."); 
    }).success(function() { 
        $("#open_mapper").qtip('toggle');
        $(".map_button").html("Save").prop('disabled', false);
        
        if(minimap_marker == null) //Add the minimarker to the map if there is none...
        { 
            minimap_marker = new L.Marker(new L.LatLng($("#temp_geo_lat").val(), $("#temp_geo_long").val()), {clickable: false, icon: new dropPin()});
            minimap.addLayer(minimap_marker);
        }
        
        refresh_minimap($("#temp_geo_lat").val(), $("#temp_geo_long").val(), $("#temp_geo_zoom").val(), $("#temp_geo_name").val());
    });

}


/*
* Enables the minimap in the sidebar
*
* @return null
*/
function init_minimap()
{ 
    $("#minimap").show();
    
    //if there's a location
    if($("#geo_long").val())
    { 
        minimap = new L.Map('minimap', { closePopupOnClick: false, attributionControl: false, zoomControl: false, boxZoom: false, doubleClickZoom: false, scrollWheelZoom: false, touchZoom: false, dragging: true });
    
        var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/'+maps_api_key+'/997/256/{z}/{x}/{y}.png',
        	cloudmadeAttribution = '&copy; 2012 OpenStreetMap',
        	cloudmade = new L.TileLayer(cloudmadeUrl, {minZoom: 2, maxZoom: 18, attribution: cloudmadeAttribution});
        
        //Set the view to something AWESOME (temp)
        
        minimap_marker = new L.Marker(new L.LatLng($("#geo_lat").val(), $("#geo_long").val()), {clickable: false, icon: new dropPin()});
        minimap.addLayer(minimap_marker);
    
        minimap.setView(minimap_marker.getLatLng(), $("#geo_zoom").val(), true).addLayer(cloudmade);

        
    } else { 
        minimap = new L.Map('minimap', { closePopupOnClick: false, attributionControl: false, zoomControl: false, boxZoom: false, doubleClickZoom: false, scrollWheelZoom: false, touchZoom: false, dragging: true });
    
        var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/'+maps_api_key+'/997/256/{z}/{x}/{y}.png',
        	cloudmadeAttribution = '&copy; 2012 OpenStreetMap',
        	cloudmade = new L.TileLayer(cloudmadeUrl, {minZoom: 2, maxZoom: 18, attribution: cloudmadeAttribution});
        
        minimap.setView(new L.LatLng(41.4357, -49.564), 2, true).addLayer(cloudmade);
        minimap.fitWorld();
    }
    
}


/*
* Enables the minimaps on a page
*
* @return null
*/
function init_minimaps()
{ 
    $(".minimap").each(function() { 
        
        //if there's a location
        if($(this).attr("data-geo-long"))
        { 
            minimap = new L.Map($(this).attr("data-map-id"), { closePopupOnClick: false, attributionControl: false, zoomControl: false, boxZoom: false, doubleClickZoom: false, scrollWheelZoom: false, touchZoom: false, dragging: true });
        
            var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/'+maps_api_key+'/997/256/{z}/{x}/{y}.png',
            	cloudmadeAttribution = '&copy; 2012 OpenStreetMap',
            	cloudmade = new L.TileLayer(cloudmadeUrl, {minZoom: 2, maxZoom: 18, attribution: cloudmadeAttribution});
            
            //Set the view to something AWESOME (temp)
            
            minimap_marker = new L.Marker(new L.LatLng($(this).attr("data-geo-lat"), $(this).attr("data-geo-long")), {clickable: false, icon: new dropPin()});
            minimap.addLayer(minimap_marker);
        
            minimap.setView(minimap_marker.getLatLng(), $(this).attr("data-geo-zoom"), true).addLayer(cloudmade);    
        }
    });
    
}


/*
* Sets the minimap and description to the results from the map.
*
* @return null
*/
function refresh_minimap(lat, lng, zoom, name) 
{ 
    minimap.panTo(new L.LatLng(lat, lng));
    minimap.setZoom(zoom);
    
    minimap_marker.setLatLng(minimap.getCenter());
    
    $("#location_display_name").html("in "+name);
}


/*
* Sets the map to the parameters.
*
* @return null
*/
function focus_map(lat, lng, zoom) 
{ 
    map.panTo(new L.LatLng(lat, lng));
    map.setZoom(zoom);
    
    marker.setLatLng(minimap.getCenter()); 
    update_marker_location();  
}







