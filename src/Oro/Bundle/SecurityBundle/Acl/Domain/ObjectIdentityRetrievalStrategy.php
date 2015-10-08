<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Acl\Model\ObjectIdentityRetrievalStrategyInterface;

/**
 * Strategy to be used for retrieving object identities
 */
class ObjectIdentityRetrievalStrategy implements ObjectIdentityRetrievalStrategyInterface
{
    /**
     * @var ObjectIdentityFactory
     */
    protected $objectIdentityFactory = null;

    /**
     * Constructor
     *
     * @param ObjectIdentityFactory $objectIdentityFactory
     */
    public function __construct(ObjectIdentityFactory $objectIdentityFactory)
    {
        $this->objectIdentityFactory = $objectIdentityFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectIdentity($domainObject)
    {
        try {
            return $this->objectIdentityFactory->get($domainObject);
        } catch (InvalidDomainObjectException $failed) {
            return null;
        }
    }
}
