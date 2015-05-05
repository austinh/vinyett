<?php

namespace Vinyett\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EmailType extends AbstractType
{
    /**
     * Builds an email form with an email and password input.
     * 
     * @access public
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("email", "text", array("error_bubbling" => true));
        $builder->add("password", "password", array("always_empty" => true, "trim" => false, "error_bubbling" => true, 'property_path' => false));
    }


    /**
     * Email Type
     * 
     * @access public
     * @return void
     */
    public function getName()
    {
        return 'email';
    }
}
