<?php

namespace Vinyett\ConnectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FollowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder/*
            ->add('is_friend', 'choice', array(
                    'choices'   => array(true, false),
                    'empty_data' => false,
                    'required' => false
                 ))
            ->add('is_family', 'choice', array(
                    'choices'   => array(true, false),
                    'empty_data' => false,
                    'required' => false
                 ))
            ->add('is_in_photofeed', 'choice', array(
                    'choices'   => array(false, true),
                    'empty_data' => true,
                    'required' => false
                 ))*/
            ->add('following', 'entity', array(
                    'class' => 'UserBundle:User',
                    'property' => 'id',
                 ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Vinyett\ConnectBundle\Entity\Follow',
            'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return 'vinyett_connectbundle_followtype';
    }
}
