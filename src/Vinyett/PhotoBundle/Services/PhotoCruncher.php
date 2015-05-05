<?php 

namespace Vinyett\PhotoBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

//This class contains event listeners to tie it into the entity lifecycle.
class PhotoCruncher
{
    
    public $UPLOAD_PATH_ROOT;
    
    protected $_photo;
    protected $is_processed = false;
    
    //Injected classes
    protected $image_handler;
    protected $container;
    protected $metadata_mapper;
    protected $s3;
    
    public function __construct($imageHandler, ContainerInterface $container, $metadataMapper)
    { 
        $this->image_handler = $imageHandler;
        $this->container = $container;
        $this->metadata_mapper = $metadataMapper;
        
        $this->UPLOAD_PATH_ROOT = __DIR__.'/../../../..';

        $ACCESS_KEY = '';
        $SECRET_KEY = '';

        $this->s3 = $this->container->get('aws_s3');
        $this->s3->set_region(\AmazonS3::REGION_OREGON);
        $this->s3->enable_path_style(true);
    }
    
    public function getImageHandler() 
    {
        return $this->image_handler;
    }
    
    public function getMetadataMapper() 
    { 
        return $this->metadata_mapper;
    }
    
    public function setPhoto(\Vinyett\PhotoBundle\Entity\Photo $photo) { 
        $this->_photo = $photo;
    }
    
    public function getPhoto() {
        return $this->_photo;
    }
    
    public function getUser()
    { 
        // We don't inject the security context (which would be a much better solution) because
        // Sf2 thinks that we are endlessly injecting services into each other and throws an exception due to 
        // the user context already in the listener
        //
        // This is a work around
        return $this->container->get('security.context')->getToken()->getUser();
    }
    
    public function isPhotoProccessed() {
        return $this->is_processed;
    }
    
    public function setPhotoProccessed() {
        $this->is_processed = true;
        return true;
    }
    
    
    /*
     * Builds a farm path based on the image post date.
     *
     * @return string
     */
    public function getUploadPath()
    { 
        $time = $this->getPhoto()->getDatePosted()->getTimestamp(); //This will be the stamp we use to generate the path and aave the file. This time is speical.
        $path = "farm1/".date("Y", $time)."/".date("m", $time)."/".date("d", $time)."/".date("H", $time)."/";
        
        return $path;
    }
    
    
    /*
     * Generates a (hopefully) unique name for the image based off the time and user.
     *
	 * @param Photo $photo - Photo object
     * @return string
     */    
    public function generateName($photo) 
    { 
        $name = $photo->getDatePosted()->getTimestamp()."_".substr(md5($this->getUser()->getId()), 0, 25);
        
        return $name;
    }
    
    
    /*
     * Predicts the paths for the files so they can be saved into the database 
     * before the entity is persisted.
     *
     * @param Photo photo Photo object
     * @return null
     */    
    public function preProcessPhoto($photo)
    {
        $this->setPhoto($photo);
    
        $upload_path = $this->getUploadPath(); //Just noticed how weird it is that this function pulls from class context
        $name = $this->generateName($photo); //While this one is injected.
        
        //TODO: Do something about this weird thing happening.
        
        //For archival sake, we save the original photo in it's original format.
        $photo->setPhotoPathWidthFull($upload_path.$name."_f.".$photo->getFile()->guessExtension());
        
        $photo->setPhotoPathWidth980($upload_path.$name."_z.jpg");
        $photo->setPhotoPathWidth500($upload_path.$name."_m.jpg");
        $photo->setPhotoPathWidth200($upload_path.$name."_s.jpg");
        $photo->setPhotoPathSquare120($upload_path.$name."_t.jpg");
        $photo->setPhotoPathSquare50($upload_path.$name."_i.jpg");
        
        //Set metadata
        $mde = $this->getMetadataMapper();
        $mde->storeMetadataToPhoto($photo, $mde->getExifFromImage($photo->getFile()));
    }
    
    /*
     * Takes the photo object and processed the $file attribute to save it to the server,
     * create its thumbnails, and update the Photo object. 
     * 
     * NOTE: This does not persist the object to the database.
     *
     * @param Photo photo Photo object
     * @return null
     */
    public function postProcessPhoto($photo) 
    { 
        $this->setPhoto($photo);
        
        //Run checks
        if(empty($photo)) 
        {
            throw new \Exception('No Photo object injected, please call $photo_cruncher->setPhoto() before proccessing');
        }
        
        if($photo->getFile() === null)
        { 
            throw new \Exception("No file contained in the photo object, no file to process. (Did your form update your Photo object?)");
        }


        // 50x50 Square Thumbnail-Cropped
        $th1 = $this->square_crop($photo->getFile(), 50);
        // 120x120 Square Thumbnail-Cropped
        $th2 = $this->square_crop($photo->getFile(), 120);
        // 200x200 Thumbnail
        $th3 = $this->getImageHandler()->open($photo->getFile()->getRealPath())
                    ->resize(200, 200, 0xffffff, false, false, true)
                    ->get('jpg', 85);
        // 500x500 Thumbnail
        $th4 = $this->getImageHandler()->open($photo->getFile()->getRealPath())
                    ->resize(500, 500, 0xffffff, false, false, true)
                    ->get('jpg', 85);
        // 980x980 thumbnail
        $th5 = $this->getImageHandler()->open($photo->getFile()->getRealPath())
                    ->resize(980, 980, 0xffffff, false, false, true)
                    ->get('jpg', 85);


        if($this->uploadToAmazonS3($photo, $th1, $th2, $th3, $th4, $th5))
        {   
            $th1=null;
            $th2=null;
            $th3=null;
            $th4=null;
            $th5=null;
        }
        
        return;
    }


    /**
     * Upload Files to S3 Bucket (6 images total)
     *
     * @param $photo object
     * @param $th1 string Thumbnail1 - 50x50 Square Cropped
     * @param $th2 string Thumbnail2 - 120x120 Square Cropped
     * @param $th3 string Thumbnail3 - 200x200
     * @param $th4 string Thumbnail4 - 500x500
     * @param $th5 string Thumbnail5 - 980x980
     *
     * @return boolean
     */
    public function uploadToAmazonS3($photo, $th1, $th2, $th3, $th4, $th5)
    {

        //$filesystem = $this->get('photo_store');

        $upload_path = $this->getUploadPath(false);
        $name = $this->generateName($photo);
        $file = $photo->getFile()->getPathname();

        // S3 bucket and filename
        $filename = $upload_path.$name."_f.".$photo->getFile()->guessExtension();
        $bucket = "photos.vinyett.com";

        /* Prepare to upload the file to our new S3 bucket. Add this
        request to a queue that we won't execute quite yet. */
        $this->s3->batch()->create_object($bucket, $filename, array(
            'fileUpload' => $file,
            'acl' => \AmazonS3::ACL_PUBLIC,
            'contentType' => 'image/'.$photo->getFile()->guessExtension()
        ));

        // 50
        $this->s3->batch()->create_object($bucket, $upload_path.$name."_i.jpg", array(
            'body' => $th1,
            'acl' => \AmazonS3::ACL_PUBLIC,
            'contentType' => 'image/jpeg'
        ));
        // 120
        $this->s3->batch()->create_object($bucket, $upload_path.$name."_t.jpg", array(
            'body' => $th2,
            'acl' => \AmazonS3::ACL_PUBLIC,
            'contentType' => 'image/jpeg'
        ));
        // 200
        $this->s3->batch()->create_object($bucket, $upload_path.$name."_s.jpg", array(
            'body' => $th3,
            'acl' => \AmazonS3::ACL_PUBLIC,
            'contentType' => 'image/jpeg'
        ));
        // 500
        $this->s3->batch()->create_object($bucket, $upload_path.$name."_m.jpg", array(
            'body' => $th4,
            'acl' => \AmazonS3::ACL_PUBLIC,
            'contentType' => 'image/jpeg'
        ));
        // 980
        $this->s3->batch()->create_object($bucket, $upload_path.$name."_z.jpg", array(
            'body' => $th5,
            'acl' => \AmazonS3::ACL_PUBLIC,
            'contentType' => 'image/jpeg'
        ));

        /* Execute our queue of batched requests. This may take a few seconds to a
             few minutes depending on the size of the files and how fast your upload
             speeds are. */
        $file_upload_response = $this->s3->batch()->send();

        /* Since a batch of requests will return multiple responses, let's
             make sure they ALL came back successfully using `areOK()` (singular
             responses use `isOK()`). */
        if ($file_upload_response->areOK()) {
            return true;
        } else {
            throw new \Exception('There was a problem connecting to S3 to dump images, try it again because Amazon is probably hiccuping.');
        }

    }
    
    /**
     * Crops a square image
     *
     * NOTE: It seems like getting a square crop out of gregwar is near impossible and I am entirely 
     * tired of trying. Austin, we should probably fix this eventually...
     * 
     * For now, because I am lazy, I will use this script
     * via http://www.abeautifulsite.net/blog/2009/08/cropping-an-image-to-make-square-thumbnails-in-php/
     *
     *
     * @param $src_image An image path (in this case, $file inside of a Photo object)
     * @param $thumb_size integer Thumbnail size in pixels (squared and cropped)
     * @param $jpg_quality integer Thumbnail quality (0-100)
     *
     * @return mixed
     */
    public function square_crop($source_image, $thumb_size = 64, $jpg_quality = 90) {
        
        $height = $width = $thumb_size;
     
        if( ! $image_data = getimagesize( $source_image ) )
        {
                return false;
        }

        switch( $image_data['mime'] )
        {
                case 'image/gif':
                        $get_func = 'imagecreatefromgif';
                        $suffix = ".gif";
                break;
                case 'image/jpeg';
                        $get_func = 'imagecreatefromjpeg';
                        $suffix = ".jpg";
                break;
                case 'image/png':
                        $get_func = 'imagecreatefrompng';
                        $suffix = ".png";
                break;
        }

        $img_original = call_user_func( $get_func, $source_image );
        $old_width = $image_data[0];
        $old_height = $image_data[1];
        $new_width = $width;
        $new_height = $height;
        $src_x = 0;
        $src_y = 0;
        $current_ratio = round( $old_width / $old_height, 2 );
        $desired_ratio_after = round( $width / $height, 2 );
        $desired_ratio_before = round( $height / $width, 2 );
        
        ob_start();

        $new_image = imagecreatetruecolor( $width, $height );

        /* Landscape Image */
        if( $current_ratio > $desired_ratio_after )
        {
                $new_width = $old_width * $height / $old_height;
        }

        /* Nearly square ratio image. */
        if( $current_ratio > $desired_ratio_before && $current_ratio < $desired_ratio_after )
        {
                if( $old_width > $old_height )
                {
                        $new_height = max( $width, $height );
                        $new_width = $old_width * $new_height / $old_height;
                }
                else
                {
                        $new_height = $old_height * $width / $old_width;
                }
        }

        /* Portrait sized image */
        if( $current_ratio < $desired_ratio_before  )
        {
                $new_height = $old_height * $width / $old_width;
        }

        /**
         * Find out the ratio of the original photo to it's new, thumbnail-based size
         * for both the width and the height. It's used to find out where to crop.
         */
        $width_ratio = $old_width / $new_width;
        $height_ratio = $old_height / $new_height;

        /* Calculate where to crop based on the center of the image */
        $src_x = floor( ( ( $new_width - $width ) / 2 ) * $width_ratio );
        $src_y = round( ( ( $new_height - $height ) / 2 ) * $height_ratio );

        imagecopyresampled( $new_image, $img_original, 0, 0, $src_x, $src_y, $new_width, $new_height, $old_width, $old_height );

        /**
         * Save it as a JPG File with our $destination_filename param.
         */
        $success = imagejpeg($new_image, null, 90);

        if (!$success) {
            return false;
        }

        return ob_get_clean();
    }
    
    
    
}















