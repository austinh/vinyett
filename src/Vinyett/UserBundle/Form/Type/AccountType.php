<?php

namespace Vinyett\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Doctrine\ORM\EntityRepository;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder->add('first_name', 'text', array("label" => "First Name:", "attr" => array("placeholder" => "Johnny")));
        $builder->add('last_name', 'text', array("label" => "Last Name:", "attr" => array("placeholder" => "Appleseed")));
        
        //$builder->add('username', 'text', array("label" => "Username:", "read_only" => true, "attr" => array("disabled" => "disabled")));
        
        $builder->add('email', 'text', array("label" => "Email Address:", "attr" => array("placeholder" => "johnnyappleseed@vinyett.com")));
        
        $builder->add('blurb', 'textarea', array("label" => "Profile Blurb:"));
        
    }

    public function getName()
    {
        return 'vinyett_account';
    }
}