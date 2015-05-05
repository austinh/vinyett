<?php

namespace Vinyett\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NameType extends AbstractType
{
    /**
     * Name change form.
     * 
     * @access public
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("first_name", "text", array("error_bubbling" => true));
        $builder->add("last_name", "text", array("error_bubbling" => true));
        $builder->add("password", "password", array("always_empty" => true, "trim" => false, "error_bubbling" => true, 'property_path' => false));
    }

    /**
     * Name type.
     * 
     * @access public
     * @return void
     */
    public function getName()
    {
        return 'name';
    }
}
