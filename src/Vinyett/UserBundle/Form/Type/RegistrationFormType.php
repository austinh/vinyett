<?php

namespace Vinyett\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class RegistrationFormType extends BaseRegistrationFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder->add('first_name', 'text', array("label" => "First Name:", "attr" => array("placeholder" => "Johnny")));
        $builder->add('last_name', 'text', array("label" => "Last Name:", "attr" => array("placeholder" => "Appleseed")));
        
        $builder->add('invitation', 'invitation_type', array("label" => "Invitation Code:", "attr" => array("placeholder" => "******")));
    }

    public function getName()
    {
        return 'vinyett_registration';
    }
}