<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;

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
            /**
             * @var $businessUnits ArrayCollection
             */
            $businessUnits = $user->getBusinessUnits();
            if ($businessUnits->count()) {
                if(method_exists($entity, 'setBusinessUnitOwners')) {
                    $bu = new ArrayCollection(array());
                    foreach ($businessUnits as $businessUnit) {
                        $bu->add($businessUnit);
                    }
                    $entity->setBusinessUnitOwners($bu);
                }

                if(method_exists($entity, 'setOrganizationOwners')) {
                    $organizations = new ArrayCollection(array());
                    foreach ($businessUnits as $businessUnit) {
                        $organization = $businessUnit->getOrganization();
                        if (!$organizations->contains($organization)) {
                            $organizations->add($organization);
                        }
                    }
                    $entity->setOrganizationOwners($organizations);
                }
            }
        }
    }
}
