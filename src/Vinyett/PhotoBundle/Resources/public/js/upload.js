define(['require', 
        'json!/..'+Global.settings.rurl+'ajax/pagelet/photos?templates=upload_app,upload_photo',
        'jquery',
        'frameworks/spine', 
        'frameworks/handlebars',  
        'plugins/qtip.jquery', 
        'plugins/filedrop.jquery',
        'photo/photo'
        ], function(require, templates)
{

    var Photo = require("photo/photo");

    var UploadApp = Spine.Controller.sub(
    {
        el: $(".upload_toolbar_widget"),
    
        elements: { 
            ".preview_image": "preview",
            ".fallback_upload_field": "fallback_upload_field",
            ".uploading_header": "upload_header",
            ".global_progress": "global_progress"
        },
    
        events: { 
            "click .cancel_photo_uploads": "closeWindow",
            "click .upload_photos": "uploadPhotos",
            "click .jump_button": "openSelect"
        },
        
        upload_handles: [],
        
        init: function()
        {
            var upload_app = this;
            
            $.each(templates, function(i, template) {
                upload_app["template_"+template.name] = Handlebars.compile(template.template);
            });
            
            this.replace(this.getTemplate('upload_app')({}));
            
            this.fileDrop();
        },
        
        getTemplate: function(template_name)
        { 
            return this["template_"+template_name];
        }, 
        
        openSelect: function() 
        { 
            this.fallback_upload_field.click();
        },
        
        fileDrop: function() 
        { 
            var app = this;
        
            $('#upload_drop').filedrop({
                url: Global.settings.rurl+'rest/photo', 
                fallback_id: "fallback_upload_field",
                paramname: 'file',
                withCredentials: true,
                error: function(err, file, i, status) 
                {
                    switch(err) {
                        case 'BrowserNotSupported':
                            alert('browser does not support html5 drag and drop')
                            break;
                        case 'TooManyFiles':
                            alert("Sorry, but right now you can only upload 10 photos at once!");
                            break;
                        case 'FileTooLarge':
                            // program encountered a file whose size is greater than 'maxfilesize'
                            // FileTooLarge also has access to the file which was too large
                            // use file.name to reference the filename of the culprit file
                            break;
                        case 'FileTypeNotAllowed':
                            alert('Only JPEG, PNG, and GIF images are allowed.');
                            break;
                        default:
                            alert("Oops, look like an error occured and your photo wasn't uploaded: "+err);
                            break;
                    }
                },
                allowedfiletypes: ['image/jpeg','image/png','image/gif'],   // filetypes allowed by Content-Type.  Empty array means no restrictions
                maxfiles: 15,
                maxfilesize: 20,    // max file size in MBs
                //queuefiles: 2,
                rename: function(name) 
                { 
                    return name.slice(0, -4);
                },
                dragOver: function() 
                {
                    $(".drop_text").addClass("drop_text_hovered");
                },
                dragLeave: function() 
                {
                    $(".drop_text").removeClass("drop_text_hovered");
                },
                drop: function()
                {
                    $(".drop_text").removeClass("drop_text_hovered");
                },
                beforeSend: function(file, i, done) 
                {
                    var upload_row = new UploadRow({ app: app, file: file, done: done, reference: i });
                    app.upload_handles[i] = upload_row;
                    $(".upload_rows").append(upload_row.el);

                    app.updatePhotoCount();
                },
                uploadFinished: function(i, file, response, time) 
                { 
                    if(response) { 
                        var photo = new Photo(response);
                        photo.save({ ajax: false });
                        photo.save();
                    }
                },
                progressUpdated: function(i, file, progress) 
                {
                    app.upload_handles[i].el.find('.progress').width(progress+"%");
                },
                globalProgressUpdated: function(progress) 
                {
                    app.global_progress.width(progress+"%");
                },
                afterAll: function()
                { 
                    $('.upload_rows').hide();
                    $('.finish_text').show();
                    app.upload_header.hide();
                    $(".cancel_photo_uploads").show();
                }

            });
            
        },
        
        updatePhotoCount: function() 
        { 
            $(".photo_count").text($(".upload_row").length);
            if($(".upload_row").length == 1)
            { 
                $(".photo_plural").text("photo");
            } else { 
                $(".photo_plural").text("photos");
            }
            
            /* This is kind of weird placement, but if there are 0 photos
             * then we just remove the bar and show the drop text */
            if($(".upload_row").length == 0) 
            {
                $(".drop_text").show();
                $(".popover_footer").hide();
            } else { 
                $(".drop_text").hide();
                $(".popover_footer").show();
            }
        },
        
        closeWindow: function(e) 
        { 
            e.preventDefault();
            $(".upload_widget").qtip("hide");
        },
        
        uploadPhotos: function() 
        { 
            this.upload_header.show();
            $(".cancel_photo_uploads").hide();
            $(".popover_footer").hide();
            $.each(this.upload_handles, function(i, obj)
            { 
                obj.triggerUpload();    
            });
        }
    
    });



    var UploadRow = Spine.Controller.sub(
    {
        uploading: false,
    
        elements: 
        { 
            ".preview_image": "preview",
            ".click_to_remove_icon": "click_to_remove_icon",
            ".true_title": "title",
            ".edit_true_title": "edit_title",
            ".edit_true_title_input": "edit_title_input"
        },
        
        events: 
        { 
            "mouseenter": "showRemove",
            "mouseleave": "hideRemove",
            "click .click_to_remove_icon": "removePhotoUpload",
            "click .true_title": "editTitle",
            "keypress input[type=text]": "blurOnEnter",
            "blur input[type=text]": "commitEdit"
        },
    
        className: "upload_row",
    
        init: function() 
        { 
            if(!this.file) throw "@needs file!";
            if(!this.done) throw "@needs done excuter";
    
            var row = this;
        
            this.html(this.render());
            
            this.reader = new FileReader();
            this.reader.onload = function(e){
                row.preview.attr('src',e.target.result);
            }
            
            this.reader.readAsDataURL(this.file);
        }, 
        
        render: function(file) 
        { 
            var that = this;
            Handlebars.registerHelper('readable_bytes', function(bytes) {
              return that.getBytesWithUnit(bytes);
            });
        
            if(file) { this.file = file }
            return this.app.getTemplate('upload_photo')({ file: this.file })
        },
        
        triggerUpload: function() 
        { 
            this.uploading = true;
            this.done();
        },
        
        showRemove: function() 
        { 
            if(this.uploading == false) 
            {
                this.click_to_remove_icon.show();
            }
        },
        
        hideRemove: function() 
        { 
            this.click_to_remove_icon.hide();
        },
        
        removePhotoUpload: function(e) 
        { 
            e.preventDefault();
            this.el.remove();
            this.app.updatePhotoCount();
            this.done = function() { return true };
            this.release();
        }, 

        blurOnEnter: function(e) {
          if (e.keyCode === 13) return e.target.blur();
        },
        
        editTitle: function() 
        { 
            this.title.hide();
            this.edit_title.show();
            return this.edit_title_input.focus();
        }, 
        
        commitEdit: function()
        {
            this.title.html(this.edit_title_input.val());
            this.title.show();
            this.edit_title.hide();
        },
        
        /**
        * @function: getBytesWithUnit()
        * @purpose: Converts bytes to the most simplified unit.
        * @param: (number) bytes, the amount of bytes
        * @returns: (string)
        */
        getBytesWithUnit: function( bytes ){
        	if( isNaN( bytes ) ){ return; }
        	var units = [ ' bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB' ];
        	var amountOf2s = Math.floor( Math.log( +bytes )/Math.log(2) );
        	if( amountOf2s < 1 ){
        		amountOf2s = 0;
        	}
        	var i = Math.floor( amountOf2s / 10 );
        	bytes = +bytes / Math.pow( 2, 10*i );
         
        	// Rounds to 3 decimals places.
                if( bytes.toString().length > bytes.toFixed(3).toString().length ){
                    bytes = bytes.toFixed(3);
                }
        	return bytes + units[i];
        }  
        
    
    });


    return UploadApp;
       
});