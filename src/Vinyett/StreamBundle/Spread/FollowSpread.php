<?php

namespace Vinyett\StreamBundle\Spread;

use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Spread\SpreadInterface;
use Spy\Timeline\Spread\Entry\EntryCollection;
use Spy\Timeline\Spread\Entry\Entry;
use Spy\Timeline\Spread\Entry\EntryUnaware;

class FollowSpread implements SpreadInterface
{

    CONST USER_CLASS = 'Vinyett\UserBundle\Entity\User';

    /**
     * Constructs the spread.
     * 
     * @access public
     * @param mixed $entity_manager
     * @param mixed $security_context
     * @return void
     */
    public function __construct($entity_manager, $security_context)
    {
        $this->entity_manager = $entity_manager;
        $this->security_context = $security_context;
    }
    
    /**
     * Returns the entity manager.
     * 
     * @access public
     * @return void
     */
    public function getEntityManager()
    {
        return $this->entity_manager;
    }
    
    /**
     * Returns the security context.
     * 
     * @access public
     * @return void
     */
    public function getSecurityContext()
    { 
        return $this->security_context;
    }

    /**
     * See if the spread supports this action (all spreads support all actions)
     * 
     * @access public
     * @param ActionInterface $action
     * @return void
     */
    public function supports(ActionInterface $action)
    {
        return true; //Spread all actions
    }

    /**
     * Fetch all of those following this user, then add them as unaware objects.
     * 
     * @access public
     * @param ActionInterface $action
     * @param EntryCollection $coll
     * @return void
     */
    public function process(ActionInterface $action, EntryCollection $coll)
    {
        $em = $this->getEntityManager();
        $subject = $action->getSubject();
        $qb = $em->getRepository("ConnectBundle:Follow")->createQueryBuilder("f")
                 ->where("f.following = :following")
                 ->setParameter("following", $em->getReference("UserBundle:User", $subject->getIdentifier()));
        $followers = $qb->getQuery()->getResult();
    
        foreach($followers as $follower)
        {
            $coll->add(new EntryUnaware(self::USER_CLASS, $follower->getActor()->getId()));
        }
    }
}