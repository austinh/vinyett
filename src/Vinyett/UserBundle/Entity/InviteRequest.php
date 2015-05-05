<?php

namespace Vinyett\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Vinyett\UserBundle\Entity\InviteRequest
 *
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields="email", message="This email is already in line for an invitation!");
 * @ORM\Entity(repositoryClass="Vinyett\UserBundle\Entity\InviteRequestRepository")
 */
class InviteRequest
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $email
     *
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     * @Assert\Length(
     *      min = "4",
     *      max = "100",
     *      minMessage = "Invalid email addresss",
     *      maxMessage = "Your email address cannot be longer than than {{ limit }} characters length"
     * )
     * @ORM\Column(name="email", type="string", length=100, unique=true)
     */
    private $email;

    /**
     * @var boolean $invited
     *
     * @ORM\Column(name="invited", type="boolean")
     */
    private $invited = false;

    /**
     * @var boolean $accepted_invite
     *
     * @ORM\Column(name="accepted_invite", type="boolean")
     */
    private $accepted_invite = false;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    /**
     * @var datetime $updated_at
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated_at;


    public function __construct()
    { 
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }
    

    /**
     * Sets the current time for the updated_at field when updated.
     *  
     * @ORM\PreUpdate
     */
    public function onUpdate() 
    { 
        $this->updated_at = new \DateTime();
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
     * Set email_address
     *
     * @param string $emailAddress
     * @return InviteRequest
     */
    public function setEmail($emailAddress)
    {
        $this->email = $emailAddress;
        return $this;
    }

    /**
     * Get email_address
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set invited
     *
     * @param boolean $invited
     * @return InviteRequest
     */
    public function setInvited($invited)
    {
        $this->invited = $invited;
        return $this;
    }

    /**
     * Get invited
     *
     * @return boolean 
     */
    public function getInvited()
    {
        return $this->invited;
    }

    /**
     * Set accepted_invite
     *
     * @param boolean $acceptedInvite
     * @return InviteRequest
     */
    public function setAcceptedInvite($acceptedInvite)
    {
        $this->accepted_invite = $acceptedInvite;
        return $this;
    }

    /**
     * Get accepted_invite
     *
     * @return boolean 
     */
    public function getAcceptedInvite()
    {
        return $this->accepted_invite;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     * @return InviteRequest
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        return $this;
    }

    /**
     * Get created_at
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     * @return InviteRequest
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
        return $this;
    }

    /**
     * Get updated_at
     *
     * @return datetime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
}