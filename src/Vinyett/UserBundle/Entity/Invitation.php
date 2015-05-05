<?php

namespace Vinyett\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Vinyett\UserBundle\Entity\User;

/** @ORM\Entity */
class Invitation
{
/** @ORM\Id @ORM\Column(type="string", length=6) */
    protected $code;

    /** @ORM\Column(type="string", length=256) */
    protected $email;

    /**
     * When sending invitation be sure to set this value to `true`
     *
     * It can prevent invitations from being sent twice
     *
     * @ORM\Column(type="boolean")
     */
    protected $sent = false;

    /** @ORM\OneToOne(targetEntity="User", inversedBy="invitation", cascade={"persist", "merge"}) */
    protected $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Vinyett\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id")
     */
    private $sender;

    public function __construct()
    {
        // generate identifier only once, here a 6 characters length code
        $this->code = substr(md5(uniqid(rand(), true)), 0, 6);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function isSent()
    {
        return $this->sent;
    }

    public function send()
    {
        $this->sent = true;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Invitation
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Set sent
     *
     * @param boolean $sent
     * @return Invitation
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
    
        return $this;
    }

    /**
     * Get sent
     *
     * @return boolean 
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Set sender
     *
     * @param Vinyett\UserBundle\Entity\User $sender
     * @return Invitation
     */
    public function setSender(\Vinyett\UserBundle\Entity\User $sender = null)
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * Get sender
     *
     * @return Vinyett\UserBundle\Entity\User 
     */
    public function getSender()
    {
        return $this->sender;
    }
}