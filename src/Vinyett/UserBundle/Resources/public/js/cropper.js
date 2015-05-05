define(['require', 'jquery', 'frameworks/spine', 'plugins/jcrop.jquery'], function()
{
   /* 
	* Some Comment describing the controller 
	*/
    var CropperApp = Spine.Controller.sub(
    {
        el: $(".cropper_body"),
    
        elements: 
        {
            ".crop_photo": "photo" 
        },

        events: 
        {
        },

        init: function()
        {
            var that = this;
            this.photo.Jcrop({
                bgFade:     true,
                bgOpacity: .8,
                aspectRatio: 1,
                minSize: [150, 150],
                setSelect: [ 60, 70, 150, 150 ],
                onChange: that.updateCords,
                onSelect: that.updateCords
            });
        }, 
        
        updateCords: function(c)
        { 
            $('#cropper_x').val(c.x);
        	$('#cropper_y').val(c.y);
        	$('#cropper_w').val(c.w);
        	$('#cropper_h').val(c.h);
        }
    });
        
    return CropperApp;
});