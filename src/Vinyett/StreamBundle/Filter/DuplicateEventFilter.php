<?php
namespace Vinyett\StreamBundle\Filter;

use Spy\Timeline\Filter\FilterInterface;
use Spy\Timeline\Model\TimelineInterface;

class DuplicateEventFilter implements FilterInterface
{
    public function filter($collection)
    {
        return $collection;
    }
    
    protected function buildHashFromResult($action)
    {
    }

    public function getPriority()
    {
        return 10;
    }
}