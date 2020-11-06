<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\SecurityBundle\Metadata\EntitySecurityMetadata;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class EntityAclExtension extends AbstractAclExtension
{
    /**
     * @var ObjectIdAccessor
     */
    protected $objectIdAccessor;

    /**
     * @var EntityClassResolver
     */
    protected $entityClassResolver;

    /**
     * key = Permission
     * value = The identity of a permission mask builder
     *
     * @var int[]
     */
    protected $permissionToMaskBuilderIdentity = [];

    /**
     * key = The identity of a permission mask builder
     * value = The full class name of a permission mask builder
     *
     * @var string[]
     */
    protected $maskBuilderClassNames = [];

    /**
     * Constructor
     *
     * @param ObjectIdAccessor $objectIdAccessor
     * @param EntityClassResolver $entityClassResolver
     */
    public function __construct(
        ObjectIdAccessor $objectIdAccessor,
        EntityClassResolver $entityClassResolver
    ) {
        $this->objectIdAccessor = $objectIdAccessor;
        $this->entityClassResolver = $entityClassResolver;

        $this->maskBuilderClassNames[EntityMaskBuilder::IDENTITY]
            = EntityMaskBuilder::class;

        $this->permissionToMaskBuilderIdentity['VIEW'] = EntityMaskBuilder::IDENTITY;
        $this->permissionToMaskBuilderIdentity['CREATE'] = EntityMaskBuilder::IDENTITY;
        $this->permissionToMaskBuilderIdentity['EDIT'] = EntityMaskBuilder::IDENTITY;
        $this->permissionToMaskBuilderIdentity['DELETE'] = EntityMaskBuilder::IDENTITY;
        $this->permissionToMaskBuilderIdentity['ASSIGN'] = EntityMaskBuilder::IDENTITY;
        $this->permissionToMaskBuilderIdentity['SHARE'] = EntityMaskBuilder::IDENTITY;

        $this->map = [
            'VIEW' => [
                EntityMaskBuilder::MASK_VIEW_BASIC,
                EntityMaskBuilder::MASK_VIEW_LOCAL,
                EntityMaskBuilder::MASK_VIEW_DEEP,
                EntityMaskBuilder::MASK_VIEW_GLOBAL,
                EntityMaskBuilder::MASK_VIEW_SYSTEM,
            ],
            'CREATE' => [
                EntityMaskBuilder::MASK_CREATE_BASIC,
                EntityMaskBuilder::MASK_CREATE_LOCAL,
                EntityMaskBuilder::MASK_CREATE_DEEP,
                EntityMaskBuilder::MASK_CREATE_GLOBAL,
                EntityMaskBuilder::MASK_CREATE_SYSTEM,
            ],
            'EDIT' => [
                EntityMaskBuilder::MASK_EDIT_BASIC,
                EntityMaskBuilder::MASK_EDIT_LOCAL,
                EntityMaskBuilder::MASK_EDIT_DEEP,
                EntityMaskBuilder::MASK_EDIT_GLOBAL,
                EntityMaskBuilder::MASK_EDIT_SYSTEM,
            ],
            'DELETE' => [
                EntityMaskBuilder::MASK_DELETE_BASIC,
                EntityMaskBuilder::MASK_DELETE_LOCAL,
                EntityMaskBuilder::MASK_DELETE_DEEP,
                EntityMaskBuilder::MASK_DELETE_GLOBAL,
                EntityMaskBuilder::MASK_DELETE_SYSTEM,
            ],
            'ASSIGN' => [
                EntityMaskBuilder::MASK_ASSIGN_BASIC,
                EntityMaskBuilder::MASK_ASSIGN_LOCAL,
                EntityMaskBuilder::MASK_ASSIGN_DEEP,
                EntityMaskBuilder::MASK_ASSIGN_GLOBAL,
                EntityMaskBuilder::MASK_ASSIGN_SYSTEM,
            ],
            'SHARE' => [
                EntityMaskBuilder::MASK_SHARE_BASIC,
                EntityMaskBuilder::MASK_SHARE_LOCAL,
                EntityMaskBuilder::MASK_SHARE_DEEP,
                EntityMaskBuilder::MASK_SHARE_GLOBAL,
                EntityMaskBuilder::MASK_SHARE_SYSTEM,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $type, $id): bool
    {
        if ($type === ObjectIdentityFactory::ROOT_IDENTITY_TYPE && $id === $this->getExtensionKey()) {
            return true;
        }

        if ($id === $this->getExtensionKey()) {
            $type = $this->entityClassResolver->getEntityClass(ClassUtils::getRealClass($type));
        } else {
            $type = ClassUtils::getRealClass($type);
        }

        return $this->entityClassResolver->isEntity($type);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionKey(): string
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function validateMask(int $mask, $object, ?string $permission = null): void
    {
        if (0 === $this->removeServiceBits($mask)) {
            // zero mask
            return;
        }

        $permissions = $permission === null
            ? $this->getPermissions($mask, true)
            : [$permission];

        foreach ($permissions as $permission) {
            $validMasks = $this->getValidMasks($permission, $object);
            if (($mask | $validMasks) === $validMasks) {
                $identity = $this->permissionToMaskBuilderIdentity[$permission];
                foreach ($this->permissionToMaskBuilderIdentity as $p => $i) {
                    if ($identity === $i) {
                        $this->validateMaskAccessLevel($p, $mask, $object);
                    }
                }

                return;
            }
        }

        throw $this->createInvalidAclMaskException($mask, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity($val): \Symfony\Component\Security\Acl\Domain\ObjectIdentity
    {
        if (is_string($val)) {
            return $this->fromDescriptor($val);
        } elseif ($val instanceof AclAnnotation) {
            return new ObjectIdentity(
                $val->getType(),
                $this->entityClassResolver->getEntityClass($val->getClass())
            );
        }

        return $this->fromDomainObject($val);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskBuilder(string $permission): MaskBuilder
    {
        if (empty($permission)) {
            $permission = 'VIEW';
        }

        $identity = $this->permissionToMaskBuilderIdentity[$permission];
        $maskBuilderClassName = $this->maskBuilderClassNames[$identity];

        return new $maskBuilderClassName();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllMaskBuilders(): array
    {
        $result = [];
        foreach ($this->maskBuilderClassNames as $maskBuilderClassName) {
            $result[] = new $maskBuilderClassName();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskPattern(int $mask): string
    {
        $maskBuilderClassName = $this->maskBuilderClassNames[$this->getServiceBits($mask)];

        return $maskBuilderClassName::getPatternFor($mask);
    }

    /**
     * {@inheritdoc}
     */
    public function adaptRootMask(int $rootMask, $object): int
    {
        $permissions = $this->getPermissions($rootMask, true);
        if (!empty($permissions)) {
            $identity = $this->getServiceBits($rootMask);
            foreach ($permissions as $permission) {
                $permissionMask = $this->getMaskBuilderConst($identity, 'GROUP_' . $permission);
                $mask = $rootMask & $permissionMask;
                $accessLevel = $this->getAccessLevel($mask);

                if ($identity === EntityMaskBuilder::IDENTITY
                    && ($permission === 'ASSIGN' || $permission === 'SHARE')
                ) {
                    $rootMask &= ~$this->removeServiceBits($mask);
                } elseif ($accessLevel < AccessLevel::SYSTEM_LEVEL) {
                    $rootMask &= ~$this->removeServiceBits($mask);
                    $rootMask |= $this->getMaskBuilderConst($identity, 'MASK_' . $permission . '_SYSTEM');
                }
            }
        }

        return $rootMask;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceBits(int $mask): int
    {
        return $mask & BaseEntityMaskBuilder::SERVICE_BITS;
    }

    /**
     * {@inheritdoc}
     */
    public function removeServiceBits(int $mask): int
    {
        return $mask & BaseEntityMaskBuilder::REMOVE_SERVICE_BITS;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessLevel(int $mask, string $permission = null): int
    {
        if (0 === $this->removeServiceBits($mask)) {
            return AccessLevel::NONE_LEVEL;
        }

        $identity = $this->getServiceBits($mask);
        if ($permission !== null) {
            $permissionMask = $this->getMaskBuilderConst($identity, 'GROUP_' . $permission);
            $mask &= $permissionMask;
        }

        $result = AccessLevel::NONE_LEVEL;
        foreach (AccessLevel::$allAccessLevelNames as $accessLevel) {
            if (0 !== ($mask & $this->getMaskBuilderConst($identity, 'GROUP_' . $accessLevel))) {
                $result = AccessLevel::getConst($accessLevel . '_LEVEL');
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions(?int $mask = null, bool $setOnly = false): array
    {
        if ($mask === null) {
            return array_keys($this->permissionToMaskBuilderIdentity);
        }

        $result = [];
        if (!$setOnly) {
            $identity = $this->getServiceBits($mask);
            foreach ($this->permissionToMaskBuilderIdentity as $permission => $id) {
                if ($id === $identity) {
                    $result[] = $permission;
                }
            }
        } elseif (0 !== $this->removeServiceBits($mask)) {
            $identity = $this->getServiceBits($mask);
            foreach ($this->permissionToMaskBuilderIdentity as $permission => $id) {
                if ($id === $identity && 0 !== ($mask & $this->getMaskBuilderConst($identity, 'GROUP_' . $permission))) {
                    $result[] = $permission;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedPermissions(ObjectIdentity $oid): array
    {
        if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            $result = array_keys($this->permissionToMaskBuilderIdentity);
        } else {
            $config = new EntitySecurityMetadata();
            $result = $config->getPermissions();
            if (empty($result)) {
                $result = array_keys($this->map);
            }
        }

        return $result;
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
        $accessLevel = $this->getAccessLevel($triggeredMask);
        if ($accessLevel === AccessLevel::SYSTEM_LEVEL) {
            return true;
        }
        // check whether we check permissions for a domain object
        return $object === null || !is_object($object) || $object instanceof ObjectIdentityInterface;
    }

    /**
     * Constructs an ObjectIdentity for the given domain object
     *
     * @param string $descriptor
     * @throws \InvalidArgumentException
     */
    protected function fromDescriptor(string $descriptor): \Symfony\Component\Security\Acl\Domain\ObjectIdentity
    {
        $type = $id = null;
        $this->parseDescriptor($descriptor, $type, $id);

        if ($id === $this->getExtensionKey()) {
            return new ObjectIdentity(
                $id,
                $this->entityClassResolver->getEntityClass(ClassUtils::getRealClass($type))
            );
        }

        throw new \InvalidArgumentException(
            sprintf('Unsupported object identity descriptor: %s.', $descriptor)
        );
    }

    /**
     * Constructs an ObjectIdentity for the given domain object
     *
     * @param object $domainObject
     * @throws InvalidDomainObjectException
     * @return ObjectIdentity
     */
    protected function fromDomainObject(object $domainObject)
    {
        if (!is_object($domainObject)) {
            throw new InvalidDomainObjectException('$domainObject must be an object.');
        }

        try {
            return new ObjectIdentity(
                $this->objectIdAccessor->getId($domainObject),
                ClassUtils::getRealClass($domainObject)
            );
        } catch (\InvalidArgumentException $invalid) {
            throw new InvalidDomainObjectException($invalid->getMessage(), 0, $invalid);
        }
    }

    /**
     * Checks that the given mask represents only one access level
     *
     * @param string $permission
     * @param int $mask
     * @param mixed $object
     * @throws InvalidAclMaskException
     */
    protected function validateMaskAccessLevel(string $permission, int $mask, $object): void
    {
        $identity = $this->permissionToMaskBuilderIdentity[$permission];
        if (0 !== ($mask & $this->getMaskBuilderConst($identity, 'GROUP_' . $permission))) {
            $maskAccessLevels = [];
            foreach (AccessLevel::$allAccessLevelNames as $accessLevel) {
                if (0 !== ($mask & $this->getMaskBuilderConst($identity, 'MASK_' . $permission . '_' . $accessLevel))) {
                    $maskAccessLevels[] = $accessLevel;
                }
            }
            if (count($maskAccessLevels) > 1) {
                $msg = sprintf(
                    'The %s mask must be in one access level only, but it is in %s access levels.',
                    $permission,
                    implode(', ', $maskAccessLevels)
                );
                throw $this->createInvalidAclMaskException($mask, $object, $msg);
            }
        }
    }

    /**
     * Gets all valid bitmasks for the given object
     *
     * @param string $permission
     * @param mixed $object
     */
    protected function getValidMasks(string $permission, $object): int
    {
        if ($object instanceof ObjectIdentity && $object->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
            $identity = $this->permissionToMaskBuilderIdentity[$permission];

            return
                $this->getMaskBuilderConst($identity, 'GROUP_SYSTEM')
                | $this->getMaskBuilderConst($identity, 'GROUP_GLOBAL')
                | $this->getMaskBuilderConst($identity, 'GROUP_DEEP')
                | $this->getMaskBuilderConst($identity, 'GROUP_LOCAL')
                | $this->getMaskBuilderConst($identity, 'GROUP_BASIC');
        }

        if ($this->permissionToMaskBuilderIdentity[$permission] === EntityMaskBuilder::IDENTITY) {
            return EntityMaskBuilder::GROUP_CRUD_SYSTEM;
        }

        return $this->permissionToMaskBuilderIdentity[$permission];
    }

    /**
     * Gets class name for given object
     *
     * @param $object
     */
    protected function getObjectClassName($object): string
    {
        if ($object instanceof ObjectIdentity) {
            $className = $object->getType();
        } elseif (is_string($object)) {
            $className = $id = null;
            $this->parseDescriptor($object, $className, $id);
        } else {
            $className = ClassUtils::getRealClass($object);
        }

        return $className;
    }

    /**
     * Gets the constant value defined in the given permission mask builder
     *
     * @param int $maskBuilderIdentity The permission mask builder identity
     * @param string $constName
     */
    protected function getMaskBuilderConst(int $maskBuilderIdentity, string $constName): int
    {
        $maskBuilderClassName = $this->maskBuilderClassNames[$maskBuilderIdentity];

        return $maskBuilderClassName::getConst($constName);
    }
}
