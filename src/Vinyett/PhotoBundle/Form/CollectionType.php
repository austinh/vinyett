<?php

namespace Vinyett\PhotoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CollectionType extends AbstractType
{
    /**
     * Collection formtype requires decription, title, cover_photo.
     * 
     * @access public
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('description')
        ->add('title')
        ->add('cover_photo', 'entity', array(
                'class' => 'PhotoBundle:Photo',
                'property' => 'id',
            ));
    }


    /**
     * Bound to collection entity and no csrf protection by defafult (enable per-usage).
     * 
     * @access public
     * @param OptionsResolverInterface $resolver
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Vinyett\PhotoBundle\Entity\Collection',
                'csrf_protection' => false,
            ));
    }


    /**
     * Collection type.
     * 
     * @access public
     * @return void
     */
    public function getName()
    {
        return 'collection';
    }
}
