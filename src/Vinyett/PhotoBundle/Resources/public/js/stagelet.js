define(['require', 
        'frameworks/spine', 
        'frameworks/ajax',
        ], function(require) {
        
    var Stagelet;
    
    Stagelet = Spine.Model.setup("Stagelet", ["reference", "order", "removed"]);
    Stagelet.belongsTo('stage', 'photo/stage');
    
    Stagelet.extend({ 
        reduceWithPosition: function(stagelets)
        {
            var p = [];
            $.each(stagelets, function(i, v)
            {
                p.push({photo: v.reference, position: i});
            });
            return p;
        }
    });
            
    return Stagelet;
});