<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RecordOwnerDataListener
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
       $this->container = $container;
    }

    /**
     * @return SecurityContextInterface
     */
    protected function getSecurityContext()
    {
        if (!$this->securityContext) {
            $this->securityContext = $this->container->get('security.context');
        }

        return $this->securityContext;
    }

    /**
     * Handle prePersist.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $user = $this->getSecurityContext()->getToken()->getUser();
        if ($user) {
            $entity = $args->getEntity();
            if(method_exists($entity, 'setUserOwner')) {
                $entity->setUserOwner($user);
            }
            if ($businessUnit = $user->getBusinessUnit()) {
                if(method_exists($entity, 'setBusinessUnitOwner')) {
                    $entity->setBusinessUnitOwner($businessUnit);
                }
                if(method_exists($entity, 'setOrganizationOwner') && $organization = $businessUnit->getOrganization()) {
                    $entity->setOrganizationOwner($organization);
                }
            }
        }
    }
}
