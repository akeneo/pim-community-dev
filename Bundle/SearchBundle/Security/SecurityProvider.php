<?php

namespace Oro\Bundle\SearchBundle\Security;

use Oro\Bundle\SecurityBundle\Metadata\EntitySecurityMetadataProvider;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class SecurityProvider
{
    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var EntitySecurityMetadataProvider
     */
    protected $entitySecurityMetadataProvider;

    public function __construct(SecurityFacade $securityFacade, EntitySecurityMetadataProvider $entitySecurityMetadataProvider)
    {
        $this->securityFacade = $securityFacade;
        $this->entitySecurityMetadataProvider = $entitySecurityMetadataProvider;
    }

    /**
     * Checks whether an entity is protected.
     *
     * @param  string $entityClass
     * @return bool
     */
    public function isProtectedEntity($entityClass)
    {
        return $this->entitySecurityMetadataProvider->isProtectedEntity($entityClass);
    }

    /**
     * Checks if an access to a resource is granted to the caller
     *
     * @param  string $attribute
     * @param  string $objectString
     * @return bool
     */
    public function isGranted($attribute, $objectString)
    {
        return $this->securityFacade->isGranted($attribute, $objectString);
    }
}
