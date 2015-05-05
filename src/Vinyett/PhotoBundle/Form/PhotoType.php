<?php

namespace Vinyett\PhotoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Symfony\Component\Validator\Constraints\Type;

class PhotoType extends AbstractType
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
        $builder
            ->add('title')
            ->add('description')
            ->add('privacy_level')
            ->add('safety_level')
            ->add('license_level')
            ->add("file", "file", array("attr" => array(
                                           "accept" => "image/*"
                                       )))
            ->add('highlighted', 'choice', array("choices" => array(1,0)))  
            ->add('is_searchable', 'choice', array("choices" => array(1,0)))
            ->add('comment_level')
            ->add("geo_has_location")
            ->add('geo_latitude')
            ->add('geo_longitude')
            ->add('geo_zoom_level')
            ->add('geo_display_name')
            ->add('date_taken', 'datetime', array("input" => "datetime", 'widget' => 'single_text'))
            ->add('date_posted', 'datetime', array("input" => "datetime", 'widget' => 'single_text'))
            ->add('exif_camera_make')
            ->add('exif_camera_model')
            ->add('exif_exposure')
            ->add('exif_aperture')
            ->add('exif_focal_length')
            ->add('exif_ISO_speed')
            ->add('exif_exposure_bias')
            ->add('exif_flash')
            ->add('exif_orientation')
            ->add('published', 'choice', array("choices" => array(1,0)));
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
        return 'photo';
    }
}
