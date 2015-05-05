<?php

namespace Vinyett\CoreServicesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

class PurifiedTextareaType extends AbstractType
{
    private $purifierTransformer;
    

    /**
     * Construction requires the DataTransformerInterface.
     * 
     * @access public
     * @param DataTransformerInterface $purifierTransformer
     * @return void
     */
    public function __construct(DataTransformerInterface $purifierTransformer)
    {
        $this->purifierTransformer = $purifierTransformer;
    }


    /**
     * Just add the purified transformer as a cilent..
     * 
     * @access public
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->purifierTransformer);
    }
    

    /**
     * purified_textarea_type.
     * 
     * @access public
     * @return void
     */
    public function getName()
    {
        return 'purified_textarea';
    }
    
    
    /**
     * Inherits from formtype.
     * 
     * @access public
     * @return void
     */
    public function getParent()
    {
        return 'textarea';
    }
}