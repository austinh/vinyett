<?php

namespace Vinyett\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use FOS\UserBundle\Entity\User as BaseUser;

use JMS\SerializerBundle\Annotation\PreSerialize;
use JMS\SerializerBundle\Annotation\ExclusionPolicy;
use JMS\SerializerBundle\Annotation\Expose;

/**
 * Vinyett\UserBundle\Entity\User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vinyett\UserBundle\Entity\UserRepository")
 *
 * @ExclusionPolicy("all")
 */
class User extends BaseUser
{

    /* Account levels */
    const USER_PRO_ACCOUNT = 1;
    
    const USER_FREE_ACCOUNT = 0;
    
    const USER_FREE_ACCOUNT_MB_AMOUNT = 250;


    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\OneToOne(targetEntity="Invitation", mappedBy="user")
     * @ORM\JoinColumn(referencedColumnName="code")
     * @Assert\NotNull(message="Your invitation code is not valid.")
     */
    protected $invitation;

    /**
     * @var string $first_name
     *
     * @ORM\Column(name="first_name", type="string", length=50, nullable=true)
     *
     * @Assert\NotBlank(groups={"Registration", "Profile"})
     * @Assert\MinLength(limit="3", groups={"Registration", "Profile"})
     * @Assert\MaxLength(limit="50", groups={"Registration", "Profile"})
     *
     * @Expose
     */
    private $first_name;

    /**
     * @var string $last_name
     *
     * @ORM\Column(name="last_name", type="string", length=50, nullable=true)
     *
     * @Assert\NotBlank(groups={"Registration", "Profile"})
     * @Assert\MaxLength(limit="50", groups={"Registration", "Profile"})
     *
     * @Expose
     */
    private $last_name;
    
    /**
     * @var string $photo_square
     *
     * @ORM\Column(name="photo_square", type="string", length=255, nullable=true)
     * @Expose
     */
    private $photo_square; 
    
    /**
     * @var string $photo_square_full
     *
     * @ORM\Column(name="photo_square_full", type="string", length=255, nullable=true)
     * @Expose
     */
    private $photo_square_full;    
    
    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\PhotoBundle\Entity\Photo")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL")
     */
    private $profile_photo;
       
    /**
     * @var integer $profile_photo_offset
     *
     * @ORM\Column(name="profile_photo_offset", type="integer")
     * @Expose
     */
    private $profile_photo_offset = 0; 
    
    /**
     * @var integer $popularity
     *
     * @ORM\Column(name="popularity", type="integer")
     */
    private $popularity = 0;

    /**
     * @var integer $photo_count
     *
     * @ORM\Column(name="photo_count", type="integer")
     * @Expose
     */
    private $photo_count = 0;

    /**
     * @var integer $comment_count
     *
     * @ORM\Column(name="comment_count", type="integer")
     * @Expose
     */
    private $comment_count = 0;
    
    /**
     * @var integer $$total_invites
     *
     * @ORM\Column(name="total_invites", type="integer")
     * @Expose
     */
    private $total_invites = 5;

    /**
     * @var integer $favorite_count
     *
     * @ORM\Column(name="favorite_count", type="integer")
     * @Expose
     */
    private $favorite_count = 0;

    /**
     * @var boolean $has_completed_intro
     *
     * @ORM\Column(name="has_completed_intro", type="boolean")
     */
    private $has_completed_intro = false;
    

    /**
     * @var datetime $password_updated_last
     *
     * @ORM\Column(name="password_updated_last", type="datetime")
     */
    private $password_updated_last;
    
    
    /**
     * @var datetime $last_stream_update
     *
     * @ORM\Column(name="last_stream_update", type="datetime")
     */
    private $last_stream_update;
    
    
    /**
     * @var integer $default_privacy_level
     *
     * @ORM\Column(name="default_privacy_level", type="integer")
     */
    private $default_privacy_level = 0;
 
        
    /**
     * @var string $blurb
     *
     * @ORM\Column(name="blrub", type="string", length=255, nullable=true)
     * @Expose
     */
    private $blurb;
    
    /**
     * @var string $url
     * @Expose
     */
    private $url;    
    
    /**
     * @var integer $uploaded_amount
     *
     * @ORM\Column(name="uploaded_amount", type="integer")
     */
    private $uploaded_amount = 0;
    
    /**
     * @var datetime $last_upload_reset
     *
     * @ORM\Column(name="last_upload_reset", type="datetime", nullable=true)
     */
    private $last_upload_reset;
    
    /**
     * @var integer $account_type
     *
     * @ORM\Column(name="account_type", type="integer")
     */
    private $account_type = self::USER_FREE_ACCOUNT;
    
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->password_updated_last = new \DateTime();
        $this->last_stream_update = new \DateTime();
        
    }   
    
         
    /**
     * @PreSerialize
     */
    public function preSerialize() { 
        $this->url = $this->getUsernameCanonical();
    }
    

    /**
     * Get a URL friendly version of username
     *
     * @return string
     */
    public function getUrlUsername() 
    {
        return strtolower($this->getUsername());
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
     * Set first_name
     *
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set popularity
     *
     * @param integer $popularity
     */
    public function setPopularity($popularity)
    {
        $this->popularity = $popularity;
    }

    /**
     * Get popularity
     *
     * @return integer 
     */
    public function getPopularity()
    {
        return $this->popularity;
    }
    
    /**
     * Increase photo_count
     *
     * @param integer $count
     */
    public function increasePhotoCount($count = 1)
    {
        $this->photo_count = $this->getPhotoCount() + 1;
    }    
    
    /**
     * Decrease photo_count
     *
     * @param integer $count
     */
    public function decreasePhotoCount($count = 1)
    {
        $this->photo_count = $this->getPhotoCount() - 1;
    }    

    /**
     * Set photo_count
     *
     * @param integer $photoCount
     */
    public function setPhotoCount($photoCount)
    {
        $this->photo_count = $photoCount;
    }

    /**
     * Get photo_count
     *
     * @return integer 
     */
    public function getPhotoCount()
    {
        return $this->photo_count;
    }

    /**
     * Set comment_count
     *
     * @param integer $commentCount
     */
    public function setCommentCount($commentCount)
    {
        $this->comment_count = $commentCount;
    }

    /**
     * Get comment_count
     *
     * @return integer 
     */
    public function getCommentCount()
    {
        return $this->comment_count;
    }
        
    /**
     * Increase comment_count
     *
     * @param integer $count
     */
    public function increaseCommentCount($count = 1)
    {
        $this->comment_count = $this->getPhotoCount() + 1;
    }    
    
    /**
     * Decrease comment_count
     *
     * @param integer $count
     */
    public function decreaseCommentCount($count = 1)
    {
        $this->comment_count = $this->getPhotoCount() - 1;
    }   

    /**
     * Set favorite_count
     *
     * @param integer $favoriteCount
     */
    public function setFavoriteCount($favoriteCount)
    {
        $this->favorite_count = $favoriteCount;
    }

    /**
     * Get favorite_count
     *
     * @return integer 
     */
    public function getFavoriteCount()
    {
        return $this->favorite_count;
    }

    /**
     * Set has_completed_intro
     *
     * @param boolean $hasCompletedIntro
     */
    public function setHasCompletedIntro($hasCompletedIntro)
    {
        $this->has_completed_intro = $hasCompletedIntro;
    }

    /**
     * Get has_completed_intro
     *
     * @return boolean 
     */
    public function getHasCompletedIntro()
    {
        return $this->has_completed_intro;
    }

    /**
     * Set photo_square
     *
     * @param string $photoSquare
     */
    public function setPhotoSquare($photoSquare)
    {
        $this->photo_square = $photoSquare;
    }

    /**
     * Get photo_square
     *
     * @return string 
     */
    public function getPhotoSquare()
    {
        return $this->photo_square;
    }

    /**
     * Set photo_square_full
     *
     * @param string $photoSquareFull
     */
    public function setPhotoSquareFull($photoSquareFull)
    {
        $this->photo_square_full = $photoSquareFull;
    }

    /**
     * Get photo_square_full
     *
     * @return string 
     */
    public function getPhotoSquareFull()
    {
        return $this->photo_square_full;
    }

    /**
     * Set profile_photo
     *
     * @param Vinyett\PhotoBundle\Entity\Photo $profilePhoto
     */
    public function setProfilePhoto(\Vinyett\PhotoBundle\Entity\Photo $profilePhoto = null)
    {
        $this->profile_photo = $profilePhoto;
    }

    /**
     * Get profile_photo
     *
     * @return Vinyett\PhotoBundle\Entity\Photo 
     */
    public function getProfilePhoto()
    {
        return $this->profile_photo;
    }

    /**
     * Set password_updated_last
     *
     * @param datetime $passwordUpdatedLast
     */
    public function setPasswordUpdatedLast($passwordUpdatedLast)
    {
        $this->password_updated_last = $passwordUpdatedLast;
    }

    /**
     * Get password_updated_last
     *
     * @return datetime 
     */
    public function getPasswordUpdatedLast()
    {
        return $this->password_updated_last;
    }

    /**
     * Set last_stream_update
     *
     * @param datetime $lastStreamUpdate
     */
    public function setLastStreamUpdate($lastStreamUpdate)
    {
        $this->last_stream_update = $lastStreamUpdate;
    }

    /**
     * Get last_stream_update
     *
     * @return datetime 
     */
    public function getLastStreamUpdate()
    {
        return $this->last_stream_update;
    }

    /**
     * Set blurb
     *
     * @param string $blurb
     * @return User
     */
    public function setBlurb($blurb)
    {
        $this->blurb = $blurb;
    
        return $this;
    }

    /**
     * Get blurb
     *
     * @return string 
     */
    public function getBlurb()
    {
        return $this->blurb;
    }

    /**
     * Set profile_photo_offset
     *
     * @param integer $profilePhotoOffset
     * @return User
     */
    public function setProfilePhotoOffset($profilePhotoOffset)
    {
        $this->profile_photo_offset = $profilePhotoOffset;
    
        return $this;
    }

    /**
     * Get profile_photo_offset
     *
     * @return integer 
     */
    public function getProfilePhotoOffset()
    {
        return $this->profile_photo_offset;
    }
    
    
    public function setInvitation(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function getInvitation()
    {
        return $this->invitation;
    }

    /**
     * Set total_invites
     *
     * @param integer $totalInvites
     * @return User
     */
    public function setTotalInvites($totalInvites)
    {
        $this->total_invites = $totalInvites;
    
        return $this;
    }

    /**
     * Get total_invites
     *
     * @return integer 
     */
    public function getTotalInvites()
    {
        return $this->total_invites;
    }

    /**
     * Set default_privacy_level
     *
     * @param integer $defaultPrivacyLevel
     * @return User
     */
    public function setDefaultPrivacyLevel($defaultPrivacyLevel)
    {
        $this->default_privacy_level = $defaultPrivacyLevel;
        return $this;
    }

    /**
     * Get default_privacy_level
     *
     * @return integer 
     */
    public function getDefaultPrivacyLevel()
    {
        return $this->default_privacy_level;
    }

    /**
     * Set uploaded_amount
     *
     * @param integer $uploadedAmount
     * @return User
     */
    public function setUploadedAmount($uploadedAmount)
    {
        $this->uploaded_amount = $uploadedAmount;
        return $this;
    }

    /**
     * Get uploaded_amount
     *
     * @return integer 
     */
    public function getUploadedAmount()
    {
        return $this->uploaded_amount;
    }

    /**
     * Set last_upload_reset
     *
     * @param datetime $lastUploadReset
     * @return User
     */
    public function setLastUploadReset($lastUploadReset)
    {
        $this->last_upload_reset = $lastUploadReset;
        return $this;
    }

    /**
     * Get last_upload_reset
     *
     * @return datetime 
     */
    public function getLastUploadReset()
    {
        return $this->last_upload_reset;
    }

    /**
     * Set account_type
     *
     * @param integer $accountType
     * @return User
     */
    public function setAccountType($accountType)
    {
        $this->account_type = $accountType;
        return $this;
    }

    /**
     * Get account_type
     *
     * @return integer 
     */
    public function getAccountType()
    {
        return $this->account_type;
    }
}