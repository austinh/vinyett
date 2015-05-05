<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;


date_default_timezone_set('America/Los_Angeles'); #because LA Rocks

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle(), //Temporarily
            
            // Bundled Bundles
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            
            //External bundles
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new FPN\TagBundle\FPNTagBundle(),
            new Gregwar\ImageBundle\GregwarImageBundle(),
            new Cybernox\AmazonWebServicesBundle\CybernoxAmazonWebServicesBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
            new Hearsay\RequireJSBundle\HearsayRequireJSBundle(),
            new WhiteOctober\SwiftMailerDBBundle\WhiteOctoberSwiftMailerDBBundle(),
            new Spy\TimelineBundle\SpyTimelineBundle(),
            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            //new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\jQueryBundle\SonatajQueryBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),

            // Vinyett Bundles
            new Vinyett\StaticBundle\StaticBundle(),
            new Vinyett\CoreServicesBundle\CoreServicesBundle(),
            new Vinyett\NotificationBundle\NotificationBundle(),

            new Vinyett\StreamBundle\StreamBundle(),
            new Vinyett\PhotoBundle\PhotoBundle(),
            new Vinyett\SearchBundle\SearchBundle(),
            
            new Vinyett\UserBundle\UserBundle(),
            new Vinyett\ProfileBundle\ProfileBundle(),
            new Vinyett\ConnectBundle\ConnectBundle(),
            new Vinyett\RestBundle\RestBundle(),
            
            new Vinyett\TestBundle\TestBundle(),
            new Vinyett\BlogBundle\BlogBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            //$bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
