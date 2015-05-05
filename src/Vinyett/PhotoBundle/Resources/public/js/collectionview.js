define(['require', 
        'jquery', 
        'frameworks/spine', 
        'frameworks/handlebars', 
        'photo/photo', 
        'photo/collection', 
        'core/photobox'], 
function()
{
    var PhotoBox = require("core/photobox");
    
    var Templates = Spine.Class.sub();
    Templates.include(
    {
        //photo_template: Handlebars.compile($("#photo_handlebar").html()),
    });

   /* 
	* Controller for the overall view of the collections page.
	*/
    var CollectionApp = Spine.Controller.sub(
    {
        el: $(".collection_view"),
    
        elements: 
        {
        },

        events: 
        {
            "click .collection_photo": "openBox"
        },

        init: function()
        {
        },
        
        openBox: function(e)
        { 
            var photo_id = $(e.currentTarget).data("photo");
            PhotoBox.open(photo_id);
        }
    });
    return CollectionApp;
});