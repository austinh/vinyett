<?php

namespace Vinyett\PhotoBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PhotoCommentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PhotoCommentRepository extends EntityRepository
{
    /**
     * Finds a collection of comments for multiple photos for
     * the rest API..
     * 
     * @access public
     * @param mixed $photo
     * @return void
     */
    public function findAllByPhotosForRest($photos) 
    {   
        $qb = $this->createQueryBuilder("c");
        $qb->addSelect(array("p", "p2"))
            
           ->where("c.photo IN (:photos)")
           
           ->leftJoin('c.owner', 'p')
           ->leftJoin('p.profile_photo', 'p2')
           
           ->orderBy('c.created_at')
           
           ->setParameter("photos", $photos);
           
        return $qb->getQuery()->getResult();
        
    }

    /**
     * Finds a set of comments for a single photo.
     * 
     * @access public
     * @param mixed $photo
     * @return void
     */
    public function findByPhotoForRest($photo) 
    {   
        $qb = $this->createQueryBuilder("c");
        $qb->addSelect(array("p", "p2"))
            
           ->where("c.photo = :photo")
           
           ->leftJoin('c.owner', 'p')
           ->leftJoin('p.profile_photo', 'p2')
           
           ->orderBy('c.created_at')
           
           ->setParameter("photo", $photo);
           
        return $qb->getQuery()->getResult();
        
    }
}