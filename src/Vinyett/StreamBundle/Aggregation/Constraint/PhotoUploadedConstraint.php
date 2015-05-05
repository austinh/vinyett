<?php

namespace Vinyett\StreamBundle\Aggregation\Constraint;

use Spy\Timeline\Aggregation\AggregationConstraintInterface;
use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Aggregation\Constraint\ConstraintResolver;

/**
 * AggregationConstraintInterface
 *
 * @author Daniel Griffin <dan@contagious.nu>
 */
class PhotoUploadedConstraint implements AggregationConstraintInterface
{
    /**
     * {@inheritdoc}
     */
    public function shouldAggregate(ActionInterface $action, ConstraintResolver $ConstraintResolver) 
    {
        return $ConstraintResolver->declineAggregation();

        $verb = $action->getVerb();
        if($verb == "uploaded") 
        { 
            /* Basically, we see that it is a commenting action, if it is, we constain the 
            action based on the directComplement (which is the photo [i.e., all comments on photo 4 
            will be grouped */
            return $ConstraintResolver->acceptAggregation("subject", //The DC is the photo, and the factor we group on.
                                                            array(
                                                                "directComplement"
                                                              )
                                                          );                                          

        } 
        else 
        { 
            /* Some other verb, we don't care to constrain */
            return $ConstraintResolver->declineAggregation();
        }
    }
    
    public function getPriority()
    { 
        return 2;
    }
    
    public function getName()
    { 
        return 'PhotoUploadedConstraint';
    }
}