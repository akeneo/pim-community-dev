<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException;

abstract class AbstractAclExtension implements AclExtensionInterface
{
    protected $map;

    /**
     * {@inheritdoc}
     */
    public function getMasks($permission)
    {
        return isset($this->map[$permission])
            ? $this->map[$permission]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMasks($permission)
    {
        return isset($this->map[$permission]);
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceBits($mask)
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function removeServiceBits($mask)
    {
        return $mask;
    }

    /**
     * {@inheritdoc}
     */
    public function decideIsGranting($triggeredMask, $object, TokenInterface $securityToken)
    {
        return true;
    }

    /**
     * Split the given object identity descriptor
     *
     * @param string $descriptor
     * @param string $type [output]
     * @param string $id [output]
     * @throws \InvalidArgumentException
     */
    protected function parseDescriptor($descriptor, &$type, &$id)
    {
        $delim = strpos($descriptor, ':');
        if (!$delim) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Incorrect descriptor: %s. Expected IdentifierType:Name.',
                    $descriptor
                )
            );
        }

        $type = strtolower(substr($descriptor, 0, $delim));
        $id = trim(substr($descriptor, $delim + 1));
    }

    /**
     * Builds InvalidAclMaskException object
     *
     * @param string $permission
     * @param int $mask
     * @param mixed $object
     * @param string|null $errorDescription
     * @return InvalidAclMaskException
     */
    protected function createInvalidAclMaskException($permission, $mask, $object, $errorDescription = null)
    {
        $objectDescription = is_object($object) && !($object instanceof ObjectIdentityInterface)
            ? get_class($object)
            : (string)$object;
        $msg = sprintf(
            'Invalid ACL mask "%s" for %s.',
            $this->createMaskBuilder($permission)->getPatternFor($mask),
            $objectDescription
        );
        if (!empty($errorDescription)) {
            $msg = sprintf('%s %s', $errorDescription, $msg);
        }

        return new InvalidAclMaskException($msg);
    }
}
