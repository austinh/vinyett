<?php

namespace Vinyett\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class InviteRequestType extends AbstractType
{
    /**
     * Email for invite form.
     * 
     * @access public
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("email", "text");
    }
    
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Vinyett\UserBundle\Entity\InviteRequest',
        ));
    }

    /**
     * invite_require_type.
     * 
     * @access public
     * @return void
     */
    public function getName()
    {
        return 'invite_request';
    }
}

