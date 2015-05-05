define(['require', 'jquery', 'frameworks/spine', 'photo/photo', 'frameworks/handlebars', 'frameworks/moment', 'core/photobox'], function(require) {

    var Photo = require("photo/photo");
    var PhotoBox = require("core/photobox");

    var NotificationToolbarController = Spine.Controller.sub({ 
    
        elements:
        { 
            
        },
    
        events: { 
            'click .photobox_link': 'openPhotobox'
        },
    
        init: function() 
        { 
            
        },
        
        openPhotobox: function(e) 
        { 
            var photo_id = $(e.target).closest(".photobox_link").data("photo");
            PhotoBox.open(photo_id);
        }
        
    });

    return NotificationToolbarController;
});