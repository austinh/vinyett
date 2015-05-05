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
class PhotoCommentConstraint implements AggregationConstraintInterface
{
    /**
     * {@inheritdoc}
     */
    public function shouldAggregate(ActionInterface $action, ConstraintResolver $ConstraintResolver) 
    { 
        $verb = $action->getVerb();
        if($verb == "commented on") 
        { 
            /* Basically, we see that it is a commenting action, if it is, we constain the 
            action based on the directComplement (which is the photo [i.e., all comments on photo 4 
            will be grouped */
            return $ConstraintResolver->acceptAggregation("directComplement", //The DC is the photo, and the factor we group on.
                                                            array(
                                                                "subject", //This tells the ConstraintResolver to group these components into a ComponentCollection
                                                                "indirectComplement" //The IC is the comment and just like above, they'll be looped into a ComponentCollection
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
        return 1;
    }
    
    public function getName()
    { 
        return 'PhotoCommentConstraint';
    }
}