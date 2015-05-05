define(['require', 
        'jquery', 
        'frameworks/spine', 
        'frameworks/ajax',
        ], function(require) {
        
    var Friend;
    
    
    Friend = Spine.Model.sub();
    Friend.configure("Friend", "following", "is_friend", "is_family", "is_in_photofeed", "created_at", "is_phantom");
    Friend.extend(Spine.Model.Ajax);
    Friend.extend(
    {
        url: Global.settings.rurl + "rest/friends",
        });

    Friend.include({
        toJSON: function(objects){
            var data = this.attributes();
            data.following = data.following.id;
            return data;
          }
    });
        
        
    return Friend;
        
        
});