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
    const ROOT_IDENTITY_TYPE = '(root)';

    /**
     * @var AclExtensionSelector
     */
    protected $extensionSelector;

    /**
     * Constructor
     *
     * @param AclExtensionSelector $extensionSelector
     */
    public function __construct(AclExtensionSelector $extensionSelector)
    {
        $this->extensionSelector = $extensionSelector;
    }

    /**
     * Constructs an ObjectIdentity is used for grant default permissions
     * if more appropriate permissions are not specified
     *
     * @param ObjectIdentity|string $oidOrExtensionKey Can be ObjectIdentity or string:
     *              ObjectIdentity: The object identity the root identity should be constructed for
     *              string: The ACL extension key
     * @return ObjectIdentity
     */
    public function root($oidOrExtensionKey)
    {
        if ($oidOrExtensionKey instanceof ObjectIdentity) {
            $oidOrExtensionKey = $this->extensionSelector
                ->select($oidOrExtensionKey)
                ->getExtensionKey();
        } else {
            $oidOrExtensionKey = strtolower($oidOrExtensionKey);
        }

        return new ObjectIdentity($oidOrExtensionKey, static::ROOT_IDENTITY_TYPE);
    }

    /**
     * Constructs an ObjectIdentity for the given domain object or based on the given descriptor.
     *
     * The descriptor is a string in the following format: "ExtensionKey:Class"
     *
     * Examples:
     *     get($object)
     *     get('Entity:AcmeBundle\SomeClass')
     *     get('Entity:AcmeBundle:SomeEntity')
     *     get('Action:Some Action')
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
                ->getObjectIdentity($domainObjectOrDescriptor);

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
