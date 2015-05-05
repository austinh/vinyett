define(['require', 
        'jquery', 
        'frameworks/spine', 
        'frameworks/ajax',
        ], function(require) {
        
    var Collection;

    Collection = Spine.Model.sub();
    Collection.configure("Collection", "total_photos", "total_comments", "description", "title", "cover_photo", "photos", "date_created", "date_updated");
    Collection.extend(Spine.Model.Ajax);
    Collection.extend(
    {
        url: Global.settings.rurl + "rest/collections",
    });
    Collection.include(
    {
        toJSON: function(objects)
        {
            var data = this.attributes();
            data.cover_photo = data.cover_photo.id;
            return data;
        }
    });
    
    
    return Collection;
});