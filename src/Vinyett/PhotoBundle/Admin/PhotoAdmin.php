<?php
namespace Vinyett\PhotoBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PhotoAdmin extends Admin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('owner')
            ->add('title', null, array('required' => false))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('owner')
            ->add('title')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('photo_path_square_120')
            ->add('owner')
            ->add('privacy_level')
            ->add('date_posted')
        ;
    }
    
    public function getTemplate($name)
    {
        switch ($name) {
            case 'list':
                return 'PhotoBundle:Admin:list.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

}