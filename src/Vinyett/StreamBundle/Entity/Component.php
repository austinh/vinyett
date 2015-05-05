<?php

namespace Vinyett\StreamBundle\Entity;

use Spy\TimelineBundle\Entity\Component as BaseComponent;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="TimelineComponent")
 */
class Component extends BaseComponent
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}