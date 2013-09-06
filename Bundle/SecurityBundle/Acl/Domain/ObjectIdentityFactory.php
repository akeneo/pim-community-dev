<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;

/**
 * A factory class to create ACL ObjectIdentity objects
 */
class ObjectIdentityFactory
{
    /**
     * @var AclExtensionSelector
     */
    protected $extensionSelector;

    /**
     * @var ObjectIdentity
     */
    protected $root;

    /**
     * Constructor
     *
     * @param AclExtensionSelector $extensionSelector
     */
    public function __construct(AclExtensionSelector $extensionSelector)
    {
        $this->extensionSelector = $extensionSelector;
        $this->root = new ObjectIdentity('root', 'Root');
    }

    /**
     * Constructs an ObjectIdentity is used for grant default permissions
     * if more appropriate permissions are not specified
     *
     * @return ObjectIdentity
     */
    public function root()
    {
        return $this->root;
    }

    /**
     * Constructs an ObjectIdentity based on the given descriptor
     * Examples:
     *     create('Class:AcmeBundle\SomeClass')
     *     create('Entity:AcmeBundle:SomeEntity')
     *     create('Action:Some Action')
     *
     * @param mixed $domainObjectOrDescriptor An domain object or the object identity descriptor
     * @return ObjectIdentity
     * @throws InvalidDomainObjectException
     */
    public function get($domainObjectOrDescriptor)
    {
        try {
            $result = $this->extensionSelector
                ->select($domainObjectOrDescriptor)
                ->createObjectIdentity($domainObjectOrDescriptor);

            if ($result === null) {
                $objInfo = is_object($domainObjectOrDescriptor)
                    ? get_class($domainObjectOrDescriptor)
                    : (string)$domainObjectOrDescriptor;
                throw new \InvalidArgumentException(sprintf('Cannot create ObjectIdentity for: %s.', $objInfo));
            }
        } catch (\InvalidArgumentException $ex) {
            throw new InvalidDomainObjectException($ex->getMessage(), 0, $ex);
        }

        return $result;
    }
}
