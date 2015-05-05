<?php

namespace Vinyett\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PhotoIconType extends AbstractType
{
    /**
     * For photo upload of icon.
     * 
     * @access public
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("photo", "file", array("error_bubbling" => true));
    }
    

    /**
     * Bound to PhotoIcon object
     * 
     * @access public
     * @param OptionsResolverInterface $resolver
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Vinyett\UserBundle\Object\PhotoIcon'
        ));
    }


    /**
     * photo_icon type
     * 
     * @access public
     * @return void
     */
    public function getName()
    {
        return 'photo_icon';
    }
}
