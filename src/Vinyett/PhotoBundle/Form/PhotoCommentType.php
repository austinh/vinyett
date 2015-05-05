<?php

namespace Vinyett\PhotoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;


class PhotoCommentType extends AbstractType
{
    /**
     * Only input comment content for type (in html purified_textarea).
     * 
     * @access public
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', "purified_textarea");
    }
    
    
    /**
     * No CSRF be default for rest API (enable per-usage).
     * and bound to the PhotoComment
     * 
     * @access public
     * @param OptionsResolverInterface $resolver
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Vinyett\PhotoBundle\Entity\PhotoComment',
            'csrf_protection' => false,
        ));
    }
    

    /**
     * photocomment type.
     * 
     * @access public
     * @return void
     */
    public function getName()
    {
        return 'photocomment';
    }
}
