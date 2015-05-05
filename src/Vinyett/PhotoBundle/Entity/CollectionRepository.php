<?php

namespace Vinyett\PhotoBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CollectionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CollectionRepository extends EntityRepository
{

    /**
     * Finds a photo by its ID for the rest API.
     * 
     * @access public
     * @param mixed $id
     * @return Collection
     */
    public function findForRest($id) 
    { 
        $qb = $this->createQueryBuilder("c");
        $qb->addSelect(array("cp", "p2", "p", "f"))
    
           ->innerJoin("c.cover_photo", "p2")
           ->leftJoin('c.collection_photos', 'cp')
           ->leftJoin('cp.photo', 'p')
           ->leftJoin("p.favorites", 'f')
            
           ->where("c.id = :collection")
           
           ->orderBy('cp.position')
           
           ->setParameter("collection", $id);
           
        return $qb->getQuery()->getResult();
    }


    /**
     * Fetches photos for the owner for the rest API.
     * 
     * @access public
     * @param mixed $for
     * @return array
     */
    public function findByOwnerForRest($for)
    {
        $em = $this->getEntityManager();
        
        $qb = $this->createQueryBuilder("c");
        $qb->addSelect(array("cp", "p2", "p", "f"))
    
           ->innerJoin("c.cover_photo", "p2")
           ->leftJoin('c.collection_photos', 'cp')
           ->leftJoin('cp.photo', 'p')
           ->leftJoin("p.favorites", 'f')
            
           ->where("c.owner = :owner")
           
           ->setParameter("owner", $em->getReference("UserBundle:User", $for));
           
        return $qb->getQuery()->getResult();
        
    }
    
    public function createProfileQuery($profile)
    {
        
        $em = $this->getEntityManager("c");
        
        $qb = $this->createQueryBuilder("c");
        $qb->addSelect(array("p2", "f"))
    
           ->innerJoin("c.cover_photo", "p2")
           ->leftJoin("p2.favorites", 'f')
            
           ->where("c.owner = :owner")
           
           ->orderby("c.date_updated", "DESC")
           
           ->setMaxResults(8)
           ->setParameter("owner", $profile);
           
        return $qb;
        
    }


}