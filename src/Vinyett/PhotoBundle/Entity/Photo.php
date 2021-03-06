<?php

namespace Vinyett\PhotoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use \DateTime;

use Vinyett\NotificationBundle\Entity\NotificationObjectIdentity;

use JMS\SerializerBundle\Annotation\ExclusionPolicy;
use JMS\SerializerBundle\Annotation\Expose;
use JMS\SerializerBundle\Annotation\PreSerialize;

use DoctrineExtensions\Taggable\Taggable;
use Doctrine\Common\Collections\ArrayCollection;

use \HTMLPurifier_Config;
use \HTMLPurifier;

/**
 * Vinyett\PhotoBundle\Entity\Photo
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\PhotoBundle\Entity\PhotoRepository")
 * @ExclusionPolicy("all")
 */
class Photo implements Taggable, NotificationObjectIdentity
{

    const absolute_url_path = "http://photos.vinyett.com/";

    /**
     * @Assert\File(maxSize="25000000")
     */
    protected $file;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     * @Expose
     */
    private $owner;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     * @Assert\MaxLength(100)
     * @Expose
     */
    private $title;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Expose
     */
    private $description;

    /**
     * @var integer $privacy_level
     *
     * @ORM\Column(name="privacy_level", type="integer")
     * @Expose
     */
    private $privacy_level = 1; //0=public, 1=private, 2=friends, 3=family, 4=friends&family. Before the photo is actually set up we keep it private

    /**
     * @var integer $safety_level
     *
     * @ORM\Column(name="safety_level", type="integer")
     * @Expose
     */
    private $safety_level = 0; //Zero is OK.

    /**
     * @var integer $license_level
     *
     * @ORM\Column(name="license_level", type="integer")
     * @Expose
     */
    private $license_level = 0; //Zero is All Rights Reserved

    /**
     * @var boolean $is_searchable
     *
     * @ORM\Column(name="is_searchable", type="boolean")
     * @Expose
     */
    private $is_searchable = true;
    
    /**
     * @var boolean $highlighted
     *
     * @ORM\Column(name="highlighted", type="boolean")
     * @Expose
     */
    private $highlighted = false;

    /**
     * @var integer $comment_level
     *
     * @ORM\Column(name="comment_level", type="integer")
     * @Expose
     */
    private $comment_level = 0;
    
    /**
     * @var integer $published
     *
     * @ORM\Column(name="published", type="boolean")
     * @Expose
     */
    private $published = false;
    
    /**
     * @var boolean $geo_show_location
     *
     * @ORM\Column(name="geo_show_location", type="boolean")
     * @Expose
     */
    private $geo_show_location = true;
    
    /**
     * @var boolean $geo_has_location
     *
     * @ORM\Column(name="geo_has_location", type="boolean")
     * @Expose
     */
    private $geo_has_location = false;

    /**
     * @var float $geo_latitude
     *
     * @ORM\Column(name="geo_latitude", type="float", nullable=true)
     * @Expose
     */
    private $geo_latitude;

    /**
     * @var float $geo_longitude
     *
     * @ORM\Column(name="geo_longitude", type="float", nullable=true)
     * @Expose
     */
    private $geo_longitude;

    /**
     * @var integer $geo_zoom_level
     *
     * @ORM\Column(name="geo_zoom_level", type="integer", nullable=true)
     * @Expose
     */
    private $geo_zoom_level;
    
    /**
     * @var integer $geo_display_name
     *
     * @ORM\Column(name="geo_display_name", type="string", length=255, nullable=true)
     * @Expose
     */
    private $geo_display_name;

    /**
     * @var integer $geo_view_level
     *
     * @ORM\Column(name="geo_view_level", type="integer")
     * @Expose
     */
    private $geo_view_level = 0;

    /**
     * @var integer $total_tagged_users
     *
     * @ORM\Column(name="total_tagged_users", type="integer")
     * @Expose
     */
    private $total_tagged_users = 0;

    /**
     * @var integer $total_tags
     *
     * @ORM\Column(name="total_tags", type="integer")
     */
    private $total_tags = 0;

    /**
     * @var integer $total_comments
     *
     * @ORM\Column(name="total_comments", type="integer")
     * @Expose
     */
    private $total_comments = 0;

    /**
     * @var integer $total_favorites
     *
     * @ORM\Column(name="total_favorites", type="integer")
     * @Expose
     */
    private $total_favorites = 0;

    /**
     * @var integer $total_shares
     *
     * @ORM\Column(name="total_shares", type="integer")
     * @Expose
     */
    private $total_shares = 0;
    
    /**
     * @var string $photo_path_square_50
     *
     * @ORM\Column(name="photo_path_square_50", type="string", length=255, nullable=true)
     * @Expose
     */
    private $photo_path_square_50;

    /**
     * @var string $photo_path_square_120
     *
     * @ORM\Column(name="photo_path_square_120", type="string", length=255, nullable=true)
     * @Expose
     */
    private $photo_path_square_120;

    /**
     * @var string $photo_path_width_200
     *
     * @ORM\Column(name="photo_path_width_200", type="string", length=255, nullable=true)
     * @Expose
     */
    private $photo_path_width_200;

    /**
     * @var string $photo_path_width_500
     *
     * @ORM\Column(name="photo_path_width_500", type="string", length=255, nullable=true)
     * @Expose
     */
    private $photo_path_width_500;

    /**
     * @var string $photo_path_width_980
     *
     * @ORM\Column(name="photo_path_width_980", type="string", length=255, nullable=true)
     * @Expose
     */
    private $photo_path_width_980;

    /**
     * @var string $photo_path_width_full
     *
     * @ORM\Column(name="photo_path_width_full", type="string", length=255, nullable=true)
     * @Expose
     */
    private $photo_path_width_full;

    /**
     * @var datetime $date_taken
     *
     * @ORM\Column(name="date_taken", type="datetime")
     * @Assert\DateTime()
     * @Expose
     */
    private $date_taken;

    /**
     * @var datetime $date_posted
     *
     * @ORM\Column(name="date_posted", type="datetime")
     * @Assert\DateTime()
     * @Expose
     */
    private $date_posted;

    /**
     * @var datetime $date_last_update
     *
     * @ORM\Column(name="date_last_update", type="datetime")
     * @Assert\DateTime()
     */
    private $date_last_update;

    /**
     * @var string $exif_camera_make
     *
     * @ORM\Column(name="exif_camera_make", type="string", length=255, nullable=true)
     * @Expose
     */
    private $exif_camera_make;

    /**
     * @var string $exif_camera_model
     *
     * @ORM\Column(name="exif_camera_model", type="string", length=255, nullable=true)
     * @Expose
     */
    private $exif_camera_model;

    /**
     * @var string $exif_exposure
     *
     * @ORM\Column(name="exif_exposure", type="string", length=255, nullable=true)
     * @Expose
     */
    private $exif_exposure;

    /**
     * @var string $exif_aperture
     *
     * @ORM\Column(name="exif_aperture", type="string", length=255, nullable=true)
     * @Expose
     */
    private $exif_aperture;

    /**
     * @var string $exif_focal_length
     *
     * @ORM\Column(name="exif_focal_length", type="string", length=255, nullable=true)
     * @Expose
     */
    private $exif_focal_length;

    /**
     * @var string $exif_ISO_speed
     *
     * @ORM\Column(name="exif_ISO_speed", type="string", length=255, nullable=true)
     * @Expose
     */
    private $exif_ISO_speed;

    /**
     * @var string $exif_exposure_bias
     *
     * @ORM\Column(name="exif_exposure_bias", type="string", length=255, nullable=true)
     * @Expose
     */
    private $exif_exposure_bias;

    /**
     * @var string $exif_flash
     *
     * @ORM\Column(name="exif_flash", type="string", length=255, nullable=true)
     * @Expose
     */
    private $exif_flash;

    /**
     * @var string $exif_orientation
     *
     * @ORM\Column(name="exif_orientation", type="string", length=255, nullable=true)
     * @Expose
     */
    private $exif_orientation;
    
    /**
     * @ORM\OneToMany(targetEntity="PersonTag", mappedBy="photo", cascade={"all"})
     */
    private $person_tags;

    /**
     * @ORM\OneToMany(targetEntity="CollectionPhoto", mappedBy="photo", cascade={"remove"})
     */
    private $collections;
    
    /**
     * @ORM\OneToMany(targetEntity="\Vinyett\PhotoBundle\Entity\PhotoComment", mappedBy="photo", cascade={"remove"})
     * @Expose
     */
    private $comments;
    
    /**
     * @ORM\OneToMany(targetEntity="\Vinyett\PhotoBundle\Entity\Favorite", mappedBy="photo", cascade={"remove"})
     * @Expose
     */
    private $favorites;
    
    /**
     * Tag storage
     * @Expose
     */    
    protected $tags;
    
    /**
     * var $user 
     * @Expose
     */
    private $user;
    
    /**
     * @var boolean $is_favorited
     *
     * @Expose
     */
    private $is_favorited;   
    
    /**
     * @var boolean $timeline Exists to distingush photos loaded in the feed from those outside (until Timeline is implemented)
     *
     * @Expose
     */
    private $timeline = false;   
    
    /**
     * @Expose
     */
    protected $options = array();


    public function __construct() 
    { 
        $this->setDateTaken(new DateTime());
        $this->setDatePosted(new DateTime());
        $this->setDateLastUpdate(new DateTime());
        
        $this->collections = new \Doctrine\Common\Collections\ArrayCollection();
    }

    
    public function toAjaxArray()
    { 
        return get_object_vars($this);
    }
    
    /**
     * Satisfies the NotificationObjectIdentity Inferface.
     * 
     * @access public
     * @return void
     */
    public function getObjectType() 
    { 
        return "photo";
    }
    
        
    /**
     * @PreSerialize
     */
    public function preSerialize() { 
        $this->user = $this->getOwner()->getId();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = stripslashes(strip_tags($title));
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        //Clean this text
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        
        $this->description = stripslashes($purifier->purify($description));
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set privacy_level
     *
     * @param integer $privacyLevel
     */
    public function setPrivacyLevel($privacyLevel)
    {
        $this->privacy_level = $privacyLevel;
    }

    /**
     * Get privacy_level
     *
     * @return integer 
     */
    public function getPrivacyLevel()
    {
        return $this->privacy_level;
    }
 
    /**
     * Get string version of privacy_level
     *
     * @return string 
     */   
    public function getPrivacyString()
    {
        if($this->privacy_level == 0) { 
            return "PUBLIC";
        } elseif($this->privacy_level == 1) {
            return "PRIVATE";
        }
    }

    /**
     * Set safety_level
     *
     * @param integer $safetyLevel
     */
    public function setSafetyLevel($safetyLevel)
    {
        $this->safety_level = $safetyLevel;
    }

    /**
     * Get safety_level
     *
     * @return integer 
     */
    public function getSafetyLevel()
    {
        return $this->safety_level;
    }

    /**
     * Set license_level
     *
     * @param integer $licenseLevel
     */
    public function setLicenseLevel($licenseLevel)
    {
        $this->license_level = $licenseLevel;
    }

    /**
     * Get license_level
     *
     * @return integer 
     */
    public function getLicenseLevel()
    {
        return $this->license_level;
    }

    /**
     * Set is_searchable
     *
     * @param boolean $isSearchable
     */
    public function setIsSearchable($isSearchable)
    {
        $this->is_searchable = $isSearchable;
    }

    /**
     * Get is_searchable
     *
     * @return boolean 
     */
    public function getIsSearchable()
    {
        return $this->is_searchable;
    }

    /**
     * Set comment_level
     *
     * @param integer $commentLevel
     */
    public function setCommentLevel($commentLevel)
    {
        $this->comment_level = $commentLevel;
    }

    /**
     * Get comment_level
     *
     * @return integer 
     */
    public function getCommentLevel()
    {
        return $this->comment_level;
    }

    /**
     * Set geo_has_location
     *
     * @param boolean $geoHasLocation
     */
    public function setGeoHasLocation($geoHasLocation)
    {
        $this->geo_has_location = $geoHasLocation;
    }

    /**
     * Get geo_has_location
     *
     * @return boolean 
     */
    public function getGeoHasLocation()
    {
        return $this->geo_has_location;
    }

    /**
     * Set geo_latitude
     *
     * @param float $geoLatitude
     */
    public function setGeoLatitude($geoLatitude)
    {
        $this->geo_latitude = $geoLatitude;
    }

    /**
     * Get geo_latitude
     *
     * @return float 
     */
    public function getGeoLatitude()
    {
        return $this->geo_latitude;
    }

    /**
     * Set geo_longitude
     *
     * @param float $geoLongitude
     */
    public function setGeoLongitude($geoLongitude)
    {
        $this->geo_longitude = $geoLongitude;
    }

    /**
     * Get geo_longitude
     *
     * @return float 
     */
    public function getGeoLongitude()
    {
        return $this->geo_longitude;
    }

    /**
     * Set geo_zoom_level
     *
     * @param integer $geoZoomLevel
     */
    public function setGeoZoomLevel($geoZoomLevel)
    {
        $this->geo_zoom_level = $geoZoomLevel;
    }

    /**
     * Get geo_zoom_level
     *
     * @return integer 
     */
    public function getGeoZoomLevel()
    {
        return $this->geo_zoom_level;
    }

    /**
     * Set geo_view_level
     *
     * @param integer $geoViewLevel
     */
    public function setGeoViewLevel($geoViewLevel)
    {
        $this->geo_view_level = $geoViewLevel;
    }

    /**
     * Get geo_view_level
     *
     * @return integer 
     */
    public function getGeoViewLevel()
    {
        return $this->geo_view_level;
    }

    /**
     * Set total_tagged_users
     *
     * @param integer $totalTaggedUsers
     */
    public function setTotalTaggedUsers($totalTaggedUsers)
    {
        $this->total_tagged_users = $totalTaggedUsers;
    }

    /**
     * Get total_tagged_users
     *
     * @return integer 
     */
    public function getTotalTaggedUsers()
    {
        return $this->total_tagged_users;
    }

    /**
     * Set total_tags
     *
     * @param integer $totalTags
     */
    public function setTotalTags($totalTags)
    {
        $this->total_tags = $totalTags;
    }

    /**
     * Get total_tags
     *
     * @return integer 
     */
    public function getTotalTags()
    {
        return $this->total_tags;
    }

    /**
     * Set total_comments
     *
     * @param integer $totalComments
     */
    public function setTotalComments($totalComments)
    {
        $this->total_comments = $totalComments;
    }

    /**
     * Get total_comments
     *
     * @return integer 
     */
    public function getTotalComments()
    {
        return $this->total_comments;
    }

    /**
     * Set total_favorites
     *
     * @param integer $totalFavorites
     */
    public function setTotalFavorites($totalFavorites)
    {
        $this->total_favorites = $totalFavorites;
    }

    /**
     * Get total_favorites
     *
     * @return integer 
     */
    public function getTotalFavorites()
    {
        return $this->total_favorites;
    }

    /**
     * Set total_shares
     *
     * @param integer $totalShares
     */
    public function setTotalShares($totalShares)
    {
        $this->total_shares = $totalShares;
    }

    /**
     * Get total_shares
     *
     * @return integer 
     */
    public function getTotalShares()
    {
        return $this->total_shares;
    }

    /**
     * Set photo_path_square_120
     *
     * @param string $photoPathSquare120
     */
    public function setPhotoPathSquare120($photoPathSquare120)
    {
        $this->photo_path_square_120 = $photoPathSquare120;
    }

    /**
     * Get photo_path_square_120
     *
     * @return string 
     */
    public function getPhotoPathSquare120()
    {
        return self::absolute_url_path.$this->photo_path_square_120;
    }

    /**
     * Set photo_path_width_200
     *
     * @param string $photoPathWidth200
     */
    public function setPhotoPathWidth200($photoPathWidth200)
    {
        $this->photo_path_width_200 = $photoPathWidth200;
    }

    /**
     * Get photo_path_width_200
     *
     * @return string 
     */
    public function getPhotoPathWidth200()
    {
        return self::absolute_url_path.$this->photo_path_width_200;
    }

    /**
     * Set photo_path_width_500
     *
     * @param string $photoPathWidth500
     */
    public function setPhotoPathWidth500($photoPathWidth500)
    {
        $this->photo_path_width_500 = $photoPathWidth500;
    }

    /**
     * Get photo_path_width_500
     *
     * @return string 
     */
    public function getPhotoPathWidth500()
    {
        return self::absolute_url_path.$this->photo_path_width_500;
    }
    
    /**
     * Set photo_path_width_980
     *
     * @param string $photoPathWidth980
     */
    public function setPhotoPathWidth980($photoPathWidth980)
    {
        $this->photo_path_width_980 = $photoPathWidth980;
    }

    /**
     * Get photo_path_width_980
     *
     * @return string 
     */
    public function getPhotoPathWidth980()
    {
        return self::absolute_url_path.$this->photo_path_width_980;
    }

    /**
     * Set photo_path_width_full
     *
     * @param string $photoPathWidthFull
     */
    public function setPhotoPathWidthFull($photoPathWidthFull)
    {
        $this->photo_path_width_full = $photoPathWidthFull;
    }

    /**
     * Get photo_path_width_full
     *
     * @return string 
     */
    public function getPhotoPathWidthFull()
    {
        return self::absolute_url_path.$this->photo_path_width_full;
    }

    /**
     * Set date_taken
     *
     * @param datetime $dateTaken
     */
    public function setDateTaken($dateTaken)
    {
        $this->date_taken = $dateTaken;
    }

    /**
     * Get date_taken
     *
     * @return datetime 
     */
    public function getDateTaken()
    {
        return $this->date_taken;
    }

    /**
     * Set date_posted
     *
     * @param datetime $datePosted
     */
    public function setDatePosted($datePosted)
    {
        $this->date_posted = $datePosted;
    }

    /**
     * Get date_posted
     *
     * @return datetime 
     */
    public function getDatePosted()
    {
        return $this->date_posted;
    }

    /**
     * Set date_last_update
     *
     * @param datetime $dateLastUpdate
     */
    public function setDateLastUpdate($dateLastUpdate)
    {
        $this->date_last_update = $dateLastUpdate;
    }

    /**
     * Get date_last_update
     *
     * @return datetime 
     */
    public function getDateLastUpdate()
    {
        return $this->date_last_update;
    }

    /**
     * Set exif_camera_make
     *
     * @param string $exifCameraMake
     */
    public function setExifCameraMake($exifCameraMake)
    {
        $this->exif_camera_make = $exifCameraMake;
    }

    /**
     * Get exif_camera_make
     *
     * @return string 
     */
    public function getExifCameraMake()
    {
        return $this->exif_camera_make;
    }

    /**
     * Set exif_camera_model
     *
     * @param string $exifCameraModel
     */
    public function setExifCameraModel($exifCameraModel)
    {
        $this->exif_camera_model = $exifCameraModel;
    }

    /**
     * Get exif_camera_model
     *
     * @return string 
     */
    public function getExifCameraModel()
    {
        return $this->exif_camera_model;
    }

    /**
     * Set exif_exposure
     *
     * @param string $exifExposure
     */
    public function setExifExposure($exifExposure)
    {
        $this->exif_exposure = $exifExposure;
    }

    /**
     * Get exif_exposure
     *
     * @return string 
     */
    public function getExifExposure()
    {
        return $this->exif_exposure;
    }

    /**
     * Set exif_aperture
     *
     * @param string $exifAperture
     */
    public function setExifAperture($exifAperture)
    {
        $this->exif_aperture = $exifAperture;
    }

    /**
     * Get exif_aperture
     *
     * @return string 
     */
    public function getExifAperture()
    {
        return $this->exif_aperture;
    }

    /**
     * Set exif_focal_length
     *
     * @param string $exifFocalLength
     */
    public function setExifFocalLength($exifFocalLength)
    {
        $this->exif_focal_length = $exifFocalLength;
    }

    /**
     * Get exif_focal_length
     *
     * @return string 
     */
    public function getExifFocalLength()
    {
        return $this->exif_focal_length;
    }

    /**
     * Set exif_ISO_speed
     *
     * @param string $exifISOSpeed
     */
    public function setExifISOSpeed($exifISOSpeed)
    {
        $this->exif_ISO_speed = $exifISOSpeed;
    }

    /**
     * Get exif_ISO_speed
     *
     * @return string 
     */
    public function getExifISOSpeed()
    {
        return $this->exif_ISO_speed;
    }

    /**
     * Set exif_exposure_bias
     *
     * @param string $exifExposureBias
     */
    public function setExifExposureBias($exifExposureBias)
    {
        $this->exif_exposure_bias = $exifExposureBias;
    }

    /**
     * Get exif_exposure_bias
     *
     * @return string 
     */
    public function getExifExposureBias()
    {
        return $this->exif_exposure_bias;
    }

    /**
     * Set exif_flash
     *
     * @param string $exifFlash
     */
    public function setExifFlash($exifFlash)
    {
        $this->exif_flash = $exifFlash;
    }

    /**
     * Get exif_flash
     *
     * @return string 
     */
    public function getExifFlash()
    {
        return $this->exif_flash;
    }
    
    
    /**
     * Set is_favorited
     *
     * @param string $exifFlash
     */
    public function setIsFavorited($is_favorited)
    {
        $this->is_favorited = $is_favorited;
    }

    /**
     * Get is_favorited
     *
     * @return string 
     */
    public function getIsFavorited()
    {
        return $this->is_favorited;
    }

    /**
     * Set exif_orientation
     *
     * @param string $exifOrientation
     */
    public function setExifOrientation($exifOrientation)
    {
        $this->exif_orientation = $exifOrientation;
    }

    /**
     * Get exif_orientation
     *
     * @return string 
     */
    public function getExifOrientation()
    {
        return $this->exif_orientation;
    }

    /**
     * Add person_tags
     *
     * @param Vinyett\PhotoBundle\Entity\PersonTag $personTags
     */
    public function addPersonTag(\Vinyett\PhotoBundle\Entity\PersonTag $personTags)
    {
        $this->person_tags[] = $personTags;
    }

    /**
     * Get person_tags
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPersonTags()
    {
        return $this->person_tags;
    }

    /**
     * Set owner
     *
     * @param Vinyett\UserBundle\Entity\User $owner
     */
    public function setOwner(\Vinyett\UserBundle\Entity\User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner
     *
     * @return Vinyett\UserBundle\Entity\User 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Add collections
     *
     * @param Vinyett\PhotoBundle\Entity\Collection $collections
     */
    public function addCollection(\Vinyett\PhotoBundle\Entity\Collection $collections)
    {
        $this->collections[] = $collections;
    }

    /**
     * Get collections
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCollections()
    {
        return $this->collections;
    }
    
    
    public function getFile() 
    {
        return $this->file;
    }
    
    public function setFile($file)
    {
        $this->file = $file;
    }
    

    /**
     * Set photo_path_square_50
     *
     * @param string $photoPathSquare50
     */
    public function setPhotoPathSquare50($photoPathSquare50)
    {
        $this->photo_path_square_50 = $photoPathSquare50;
    }

    /**
     * Get photo_path_square_50
     *
     * @return string 
     */
    public function getPhotoPathSquare50()
    {
        return self::absolute_url_path.$this->photo_path_square_50;
    }
    

    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }

    public function getTaggableType()
    {
        return 'photo_tag';
    }

    public function getTaggableId()
    {
        return $this->getId();
    }



    /**
     * Set geo_display_name
     *
     * @param string $geoDisplayName
     */
    public function setGeoDisplayName($geoDisplayName)
    {
        $this->geo_display_name = $geoDisplayName;
    }

    /**
     * Get geo_display_name
     *
     * @return string 
     */
    public function getGeoDisplayName()
    {
        return $this->geo_display_name;
    }

    /**
     * Add comments
     *
     * @param Vinyett\PhotoBundle\Entity\PhotoComment $comments
     */
    public function addPhotoComment(\Vinyett\PhotoBundle\Entity\PhotoComment $comments)
    {
        $this->comments[] = $comments;
    }

    /**
     * Get comments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set geo_show_location
     *
     * @param boolean $geoShowLocation
     * @return Photo
     */
    public function setGeoShowLocation($geoShowLocation)
    {
        $this->geo_show_location = $geoShowLocation;
        return $this;
    }

    /**
     * Get geo_show_location
     *
     * @return boolean 
     */
    public function getGeoShowLocation()
    {
        return $this->geo_show_location;
    }
    
    public function setTags($tags)
    {
        $this->tags = $tags;
    }


    /**
     * Add collections
     *
     * @param Vinyett\PhotoBundle\Entity\CollectionPhoto $collections
     * @return Photo
     */
    public function addCollectionPhoto(\Vinyett\PhotoBundle\Entity\CollectionPhoto $collections)
    {
        $this->collections[] = $collections;
        return $this;
    }

    /**
     * Set highlighted
     *
     * @param boolean $highlighted
     * @return Photo
     */
    public function setHighlighted($highlighted)
    {
        $this->highlighted = $highlighted;
        return $this;
    }

    /**
     * Get highlighted
     *
     * @return boolean 
     */
    public function getHighlighted()
    {
        return $this->highlighted;
    }

    /**
     * Remove person_tags
     *
     * @param \Vinyett\PhotoBundle\Entity\PersonTag $personTags
     */
    public function removePersonTag(\Vinyett\PhotoBundle\Entity\PersonTag $personTags)
    {
        $this->person_tags->removeElement($personTags);
    }

    /**
     * Remove collections
     *
     * @param \Vinyett\PhotoBundle\Entity\CollectionPhoto $collections
     */
    public function removeCollection(\Vinyett\PhotoBundle\Entity\CollectionPhoto $collections)
    {
        $this->collections->removeElement($collections);
    }

    /**
     * Add comments
     *
     * @param \Vinyett\PhotoBundle\Entity\PhotoComment $comments
     * @return Photo
     */
    public function addComment(\Vinyett\PhotoBundle\Entity\PhotoComment $comments)
    {
        $this->comments[] = $comments;
    
        return $this;
    }

    /**
     * Remove comments
     *
     * @param \Vinyett\PhotoBundle\Entity\PhotoComment $comments
     */
    public function removeComment(\Vinyett\PhotoBundle\Entity\PhotoComment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Add favorites
     *
     * @param \Vinyett\PhotoBundle\Entity\Favorite $favorites
     * @return Photo
     */
    public function addFavorite(\Vinyett\PhotoBundle\Entity\Favorite $favorites)
    {
        $this->favorites[] = $favorites;
    
        return $this;
    }

    /**
     * Remove favorites
     *
     * @param \Vinyett\PhotoBundle\Entity\Favorite $favorites
     */
    public function removeFavorite(\Vinyett\PhotoBundle\Entity\Favorite $favorites)
    {
        $this->favorites->removeElement($favorites);
    }

    /**
     * Get favorites
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFavorites()
    {
        return $this->favorites;
    }
    
    public function removeFile() 
    { 
        unset($this->file);
    }  
      
    /**
     * Set Options
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Get Options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     * Set Timeline
     *
     * @param bool $from_timeline
     */
    public function setTimeline($from_timeline)
    { 
        $this->timeline = $from_timeline;
    }

    /**
     * Get Timeline
     *
     * @return bool
     */
    public function getTimeline()
    { 
        return $this->timeline;
    }
    
    /**
     * Set published
     *
     * @param boolean $published
     * @return Photo
     */
    public function setPublished($published)
    {
        $this->published = $published;
        return $this;
    }

    /**
     * Get published
     *
     * @return boolean 
     */
    public function getPublished()
    {
        return $this->published;
    }
}