<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

abstract class AbstractAclExtension implements AclExtensionInterface
{
    protected $map;

    /**
     * {@inheritdoc}
     */
    public function getMasks(string $permission): array
    {
        return isset($this->map[$permission])
            ? $this->map[$permission]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMasks(string $permission): bool
    {
        return isset($this->map[$permission]);
    }

    /**
     * {@inheritdoc}
     */
    public function adaptRootMask(int $rootMask, $object): int
    {
        return $rootMask;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceBits(int $mask): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function removeServiceBits(int $mask): int
    {
        return $mask;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultPermission(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function decideIsGranting(int $triggeredMask, $object, TokenInterface $securityToken): bool
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
    protected function parseDescriptor(string $descriptor, string &$type, string &$id)
    {
        $delim = strpos($descriptor, ':');
        if (!$delim) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Incorrect descriptor: %s. Expected "ExtensionKey:Class".',
                    $descriptor
                )
            );
        }

        $id = strtolower(substr($descriptor, 0, $delim));
        $type = ltrim(substr($descriptor, $delim + 1), ' ');
    }

    /**
     * Builds InvalidAclMaskException object
     *
     * @param int $mask
     * @param mixed $object
     * @param string|null $errorDescription
     */
    protected function createInvalidAclMaskException(int $mask, $object, ?string $errorDescription = null): InvalidAclMaskException
    {
        $objectDescription = is_object($object) && !($object instanceof ObjectIdentityInterface)
            ? get_class($object)
            : (string)$object;
        $msg = sprintf(
            'Invalid ACL mask "%s" for %s.',
            $this->getMaskPattern($mask),
            $objectDescription
        );
        if (!empty($errorDescription)) {
            $msg = sprintf('%s %s', $errorDescription, $msg);
        }

        return new InvalidAclMaskException($msg);
    }
}
