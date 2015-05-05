<?php
namespace Vinyett\BlogBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PostAdmin extends Admin
{
    protected $security_context;

    public function setSecurityContext($sc)
    {
        $this->security_context = $sc;
    }

    public function getSecurityContext()
    {
        return $this->security_context;
    }

    public function getNewInstance()
    {
        $user = $this->getSecurityContext()->getToken()->getUser();

        $instance = parent::getNewInstance();
        $instance->setOwner($user);
        $instance->setCreatedAt(new \DateTime());

        return $instance;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title')
            ->add('slug', null, array('required' => false))
            ->add('body')
            ->add('created_at')
            ->add('is_public', null, array('required' => false))
            ->add('is_front_page', null, array('required' => false))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('owner')
            ->add('title')
            ->add('slug')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('owner')
            ->add('comment_count')
            ->add('is_public')
            ->add('is_front_page')
            ->add('created_at')
        ;
    }

    public function getTemplate($name)
    {
        switch ($name) {
            case 'list':
                //return 'PhotoBundle:Admin:list.html.twig';
                //break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

}