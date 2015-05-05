<?php

namespace Vinyett\UserBundle\Form\Type;

use Symfony\Component\Security\Core\Validator\Constraint\UserPassword;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Doctrine\ORM\EntityRepository;

class PasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('currentpassword', 'password', array('label'=>'Current password', 'mapped' => false, 'constraints' => new UserPassword()));
        
        //$builder->add('password', 'password');
        
        $builder->add('password', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'The password fields must match.',
            'options' => array('attr' => array('class' => 'password-field')),
            'required' => true,
            'first_options'  => array('label' => 'New password'),
            'second_options' => array('label' => 'Repeat new password'),
        ));

    }

    public function getName()
    {
        return 'vinyett_account_password';
    }
}