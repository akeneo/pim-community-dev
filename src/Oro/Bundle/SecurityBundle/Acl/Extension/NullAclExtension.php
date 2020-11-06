<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * This class implements "Null object" design pattern for AclExtensionInterface
 */
final class NullAclExtension implements AclExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $type, $id): bool
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionKey(): string
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function validateMask(int $mask, $object, ?string $permission = null): void
    {
        throw new InvalidAclMaskException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity($val): ObjectIdentity
    {
        throw new InvalidDomainObjectException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskBuilder(string $permission): MaskBuilder
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getAllMaskBuilders(): array
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskPattern(int $mask): string
    {
        return 'NullAclExtension: ' . $mask;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasks(string $permission): array
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMasks(string $permission): bool
    {
        return false;
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
    public function getAccessLevel(int $mask, string $permission = null): int
    {
        return AccessLevel::UNKNOWN;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions(?int $mask = null, bool $setOnly = false): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedPermissions(ObjectIdentity $oid): array
    {
        return [];
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
    public function getClasses(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function decideIsGranting(int $triggeredMask, $object, TokenInterface $securityToken): bool
    {
        return true;
    }
}
