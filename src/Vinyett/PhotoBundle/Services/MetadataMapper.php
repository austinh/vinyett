<?php 

namespace Vinyett\PhotoBundle\Services;

class MetadataMapper 
{ 

    public function __construct()
    { 
        //Constructor.
    }
    
    public function getExifFromImage($image)
    {
        return null;
        //First we check to see if the image is valid (and has EXIF data)
        $data = new \PelDataWindow(file_get_contents($image));
        
        if (\PelJpeg::isValid($data)) {
        
            $jpeg = new \PelJpeg();
            $jpeg->load($data);
            $exif = $jpeg->getExif();
            
            if(!$exif)
            { 
                return null;
            } else { 
                $tiff = $exif->getTiff();
                return $tiff->getIfd();
            }
        
        } else { 
            return null; //No valid data
        }
    }

    /*
     * Assigns exif result data to the photo object
     * NOTE: This does not invoke the entity manager to persist the object
     * to the database, it merely sets the values to be persisted.
     *
	 * @param Vinyett\PhotoBundle\Entity\Photo Photo Photo object
	 * @param array data - a data array from ::getExifFromImage
     * @return Photo object
     */        
    public function storeMetadataToPhoto(\Vinyett\PhotoBundle\Entity\Photo $photo, $data)
    {
        if(empty($data)) //No Data to store.
        { 
            return;
        }
    
        $pool = array(
            'DateTaken'         => date_create("@".($data->getEntry(\PelTag::DATE_TIME)?$data->getEntry(\PelTag::DATE_TIME)->getValue():time())),
            'Description'       => ($data->getEntry(\PelTag::IMAGE_DESCRIPTION)?$data->getEntry(\PelTag::IMAGE_DESCRIPTION)->getValue():null),
            'ExifCameraMake'    => ($data->getEntry(\PelTag::MAKE)?$data->getEntry(\PelTag::MAKE)->getValue():null),
            'ExifCameraModel'   => ($data->getEntry(\PelTag::MODEL)?$data->getEntry(\PelTag::MODEL)->getValue():null),
            'ExifExposure'      => ($data->getEntry(\PelTag::EXPOSURE_TIME)?$data->getEntry(\PelTag::EXPOSURE_TIME)->getValue():null),
            'ExifAperture'      => ($data->getEntry(\PelTag::APERTURE_VALUE)?$data->getEntry(\PelTag::APERTURE_VALUE)->getValue():null),
            'ExifFocalLength'   => ($data->getEntry(\PelTag::FOCAL_LENGTH)?$data->getEntry(\PelTag::FOCAL_LENGTH)->getValue():null),
            'ExifISOSpeed'      => ($data->getEntry(\PelTag::ISO_SPEED_RATINGS)?$data->getEntry(\PelTag::ISO_SPEED_RATINGS)->getValue():null),
            'ExifExposureBias'  => ($data->getEntry(\PelTag::EXPOSURE_BIAS_VALUE)?$data->getEntry(\PelTag::EXPOSURE_BIAS_VALUE)->getValue():null),
            'ExifFlash'         => ($data->getEntry(\PelTag::FLASH)?$data->getEntry(\PelTag::FLASH)->getValue():null),
            'ExifOrientation'   => ($data->getEntry(\PelTag::ORIENTATION)?$data->getEntry(\PelTag::ORIENTATION)->getValue():null),
        );
        
        if($data->getEntry(\PelTag::GPS_DEST_LATITUDE))
        { 
            $pool['GeoHasLocation'] = true;
            $pool['GeoLatitude'] = $data->getEntry(\PelTag::GPS_DEST_LATITUDE)->getValue();
            $pool['GeoLongitude'] = $data->getEntry(\PelTag::GPS_DEST_LONGITUDE)->getValue();
        }
        
        foreach($pool as $tag => $value)
        { 
            $function_name = "set".$tag;
            //Oh this here.
            $photo->$function_name($value);
        }
        
        return true;
    }

}