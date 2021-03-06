<?php

namespace Vinyett\StreamBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * NewsStoryCacheRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NewsStoryCacheRepository extends EntityRepository
{

    /**
     * Deletes the cache for a user.
     * 
     * @access public
     * @param mixed $user
     * @return void
     */
    public function deleteForUser($user)
    { 
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
                    ->delete("StreamBundle:NewsStoryCache", "c")
                    ->where("c.user = :user")
                    ->setParameter("user", $user);
        $q = $qb->getQuery()->getResult();
        
        return true;
    }


}