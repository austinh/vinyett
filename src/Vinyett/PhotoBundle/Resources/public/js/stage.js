define(['require', 
        'frameworks/spine', 
        'frameworks/ajax',
        ], function(require) {
        
    var Stage;
    
    Stage = Spine.Model.setup("Stage", ["name", "active"]);
    Stage.hasMany('stagelets', 'photo/stagelet');
            
    return Stage;
        
        
});