define(['require', 
        'jquery', 
        'frameworks/spine', 
        'frameworks/ajax',
        ], function(require) {
        
    var Photo;
    
    
    Photo = Spine.Model.sub();
    Photo.configure("Photo", "owner", "highlighted", "title", "description", "privacy_level", "safety_level", "license_leve", "is_searchable", "geo_has_location", "geo_latitude", "geo_longitude", "geo_zoom_level", "total_tagged_users", "total_tags", "total_comments", "total_favorites", "photo_path_square_120", "photo_path_width_200", "photo_path_width_500", "photo_path_square_50", "photo_path_width_980", "photo_path_width_full", "date_taken", "date_posted", "geo_display_name", "tags", "is_selected", "in_view", "in_batch", "batch_order", "is_favorited", "timeline", "favorites", "published");
    Photo.extend(Spine.Model.Ajax);
    Photo.extend(
    {
        url: Global.settings.rurl + "rest/photos",
        
        For: function(user)
        { 
            return this.select(function(item)
            { 
                return (item.owner.id == user?true:false);
            });
        }, 
        
        reduce: function(photos)
        {
            var p = [];
            $.each(photos, function(i, v)
            {
                p.push(v.id);
            });
            return p;
        }
    });
        
        
    return Photo;
        
        
});