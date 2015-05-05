define(['require', 'jquery', 'frameworks/spine', 'frameworks/ajax', ], function(require)
{

    var PhotoComment;


    PhotoComment = Spine.Model.sub();
    PhotoComment.configure("PhotoComment", "owner", "photo", "content", "options", "created_at");
    PhotoComment.extend(Spine.Model.Ajax);
    PhotoComment.extend(
    {
    });
    PhotoComment.include(
    {
        save: function() 
        { 
            PhotoComment.extend({ url: Global.settings.rurl + "rest/photos/"+this.photo+"/comments" });
            this.constructor.__super__.save.apply(this, arguments);
        },
        
        destroy: function() 
        { 
            PhotoComment.extend({ url: Global.settings.rurl + "rest/photos/"+this.photo+"/comments" });
            this.constructor.__super__.destroy.apply(this, arguments);
        },
        
        validate: function()
        {
            if (!this.content) return "Comment  is required!";
        },
        
        toJSON: function(objects)
        {
            var data = this.attributes();
            //data.owner = data.cover_photo.id;
            return data;
        }
    });

    return PhotoComment;
});