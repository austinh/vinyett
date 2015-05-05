<?php
 
namespace Vinyett\UserBundle\Listener;
 
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
 
/**
 * Custom login listener.
 */
class UploadUserResetListener
{
	/** @var \Symfony\Component\Security\Core\SecurityContext */
	private $securityContext;
	
	/** @var \Doctrine\ORM\EntityManager */
	private $em;
	
	/**
	 * Constructor
	 * 
	 * @param SecurityContext $securityContext
	 * @param Doctrine        $doctrine
	 */
	public function __construct(SecurityContext $securityContext, Doctrine $doctrine)
	{
		$this->securityContext = $securityContext;
		$this->em = $doctrine->getEntityManager();
	}
	
	/**
	 * Check to reset the usage counter.
	 * 
	 * @param InteractiveLoginEvent $event
	 */
	public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
	{
		if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY') || $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			$user = $event->getAuthenticationToken()->getUser();
			$current_time = new \DateTime();
			
			if($current_time->getTimestamp() - $user->getLastUploadReset()->getTimestamp() > 2592000)
			{ 
    			//Reset the counter if it's been more than 30 days
    			$user->setUploadedAmount(0);
    			$user->setLastUploadReset($current_time);
    			
    			$this->em->flush();
			}
		}
	}
}