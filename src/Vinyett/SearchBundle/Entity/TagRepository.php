<?php

namespace Vinyett\SearchBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query;

class TagRepository extends EntityRepository

{

    public function getTagsQueryBuilder($taggableType)
    {
        return $this->createQueryBuilder('tag')
            ->join('tag.tagging', 'tagging')
            ->where('tagging.resourceType = :resourceType')
            ->setParameter('resourceType', $taggableType)
        ;
    }

}