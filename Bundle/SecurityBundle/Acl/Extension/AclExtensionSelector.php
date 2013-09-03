<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;

/**
 * This class provides a functionality to find ACL extension
 */
class AclExtensionSelector
{
    /**
     * @var ObjectIdAccessor
     */
    protected $objectIdAccessor;

    /**
     * @var AclExtensionInterface[]
     */
    protected $extensions = array();

    /**
     * Constructor
     *
     * @param ObjectIdAccessor $objectIdAccessor
     */
    public function __construct(ObjectIdAccessor $objectIdAccessor)
    {
        $this->objectIdAccessor = $objectIdAccessor;
    }

    /**
     * Adds ACL extension
     *
     * @param AclExtensionInterface $extension
     */
    public function addAclExtension(AclExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    /**
     * Gets ACL extension responsible for work with the given domain object
     *
     * @param mixed $object A domain object, ObjectIdentity or descriptor (type:id)
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     * @return AclExtensionInterface
     */
    public function select($object)
    {
        if ($object === null) {
            return new NullAclExtension();
        }

        $type = $id = null;
        if (is_string($object)) {
            $delim = strpos($object, ':');
            if ($delim) {
                $type = trim(substr($object, $delim + 1));
                $id = strtolower(substr($object, 0, $delim));
            }
        } elseif (is_object($object)) {
            if ($object instanceof ObjectIdentityInterface) {
                $type = $object->getType();
                $id = $object->getIdentifier();
            } else {
                $type = get_class($object);
                $id = $this->objectIdAccessor->getId($object);
            }
        }

        if ($type !== null && $id !== null) {
            foreach ($this->extensions as $extension) {
                if ($extension->supports($type, $id)) {
                    return $extension;
                }
            }
        }

        throw $this->createAclExtensionNotFoundException($object, $type, $id);
    }

    /**
     * Gets all ACL extension
     *
     * @return AclExtensionInterface[]
     */
    public function all()
    {
        return $this->extensions;
    }

    /**
     * Creates an exception indicates that ACL extension was not found for the given domain object
     *
     * @param mixed $object
     * @param string $type
     * @param int|string $id
     * @return InvalidDomainObjectException
     */
    protected function createAclExtensionNotFoundException($object, $type, $id)
    {
        $objInfo = is_object($object) && !($object instanceof ObjectIdentityInterface)
            ? get_class($object)
            : (string)$object;

        return new InvalidDomainObjectException(
            sprintf('An ACL extension was not found for: %s. Type: %s. Id: %s', $objInfo, $type, (string)$id)
        );
    }
}
