<?php

namespace Oro\Bundle\OrganizationBundle\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\OrganizationBundle\Form\Type\OwnershipType;

class RecordOwnerDataListener
{
    /**
     * TODO: Refactor direct field name usage after extended entities will be implemented
     */
    const OWNER_FIELD_NAME = 'owner';

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @param ContainerInterface $container
     * @param ConfigProvider $configProvider
     */
    public function __construct(ContainerInterface $container, ConfigProvider $configProvider)
    {
        $this->container      = $container;
        $this->configProvider = $configProvider;
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
     * @throws \LogicException when getOwner method isn't implemented for entity with ownership type
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $token = $this->getSecurityContext()->getToken();
        if (!$token) {
            return;
        }
        $user = $token->getUser();
        if (!$user) {
            return;
        }
        $entity = $args->getEntity();
        if ($this->configProvider->hasConfig(get_class($entity))) {
            $config = $this->configProvider->getConfig(get_class($entity));
            $ownerType = $config->get('owner_type');
            if ($ownerType && $ownerType !== OwnershipType::OWNER_TYPE_NONE) {
                if (!method_exists($entity, 'getOwner')) {
                    throw new \LogicException(
                        sprintf('Method getOwner must be implemented for %s entity', get_class($entity))
                    );
                }
                if (!$entity->getOwner()) {
                    /**
                     * Automatically set current user as record owner
                     */
                    if (OwnershipType::OWNER_TYPE_USER == $ownerType
                        && method_exists($entity, 'setOwner')) {
                            $entity->setOwner($user);
                    }
                }
            }
        }
    }
}
