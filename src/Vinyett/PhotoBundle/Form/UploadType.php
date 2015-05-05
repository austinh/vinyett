<?php

namespace Vinyett\PhotoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Vinyett\RestBundle\Form\EventListener\IgnoreNonSubmittedFieldSubscriber;

class UploadType extends AbstractType
{
    /**
     * Photo type.
     * 
     * @access public
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    
        $subscriber = new IgnoreNonSubmittedFieldSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);
    
        $builder
            ->add('title')
            ->add('description')
            ->add('privacy_level')
            ->add('safety_level')
            ->add('license_level')
            ->add("file", "file", array("attr" => array(
                                           "accept" => "image/*"
                                       )))
            //->add('highlighted', 'checkbox', array("value" => true))
            ->add('is_searchable')
            ->add('comment_level')
            ->add('date_taken', 'datetime', array("input" => "datetime", 'widget' => 'single_text'))
            ->add('date_posted', 'datetime', array("input" => "datetime", 'widget' => 'single_text'));
        }
    
    /**
     * No CSRF protection by default (for Rest API), enable per0-usage basis.
     * 
     * @access public
     * @param OptionsResolverInterface $resolver
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Vinyett\PhotoBundle\Entity\Photo',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'upload';
    }
}
