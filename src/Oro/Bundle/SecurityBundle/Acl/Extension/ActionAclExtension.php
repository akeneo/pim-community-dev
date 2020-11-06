<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\SecurityBundle\Metadata\ActionMetadataProvider;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class ActionAclExtension extends AbstractAclExtension
{
    /**
     * @var ActionMetadataProvider
     */
    protected $actionMetadataProvider;

    /**
     * Constructor
     */
    public function __construct(ActionMetadataProvider $actionMetadataProvider)
    {
        $this->actionMetadataProvider = $actionMetadataProvider;

        $this->map = [
            'EXECUTE' => [
                ActionMaskBuilder::MASK_EXECUTE,
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

        return $id === $this->getExtensionKey()
            && $this->actionMetadataProvider->isKnownAction($type);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionKey(): string
    {
        return 'action';
    }

    /**
     * {@inheritdoc}
     */
    public function validateMask(int $mask, $object, ?string $permission = null): void
    {
        if ($mask === 0) {
            return;
        }
        if ($mask === ActionMaskBuilder::MASK_EXECUTE) {
            return;
        }

        throw $this->createInvalidAclMaskException($mask, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity($val): ObjectIdentity
    {
        $type = $id = null;
        if (is_string($val)) {
            $this->parseDescriptor($val, $type, $id);
        } elseif ($val instanceof AclAnnotation) {
            $type = $val->getId();
            $id = $val->getType();
        }

        return new ObjectIdentity($id, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskBuilder(string $permission): MaskBuilder
    {
        return new ActionMaskBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllMaskBuilders(): array
    {
        return [new ActionMaskBuilder()];
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskPattern(int $mask): string
    {
        return ActionMaskBuilder::getPatternFor($mask);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessLevel(int $mask, string $permission = null): int
    {
        return $mask === 0
            ? AccessLevel::NONE_LEVEL
            : AccessLevel::SYSTEM_LEVEL;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions(?int $mask = null, bool $setOnly = false): array
    {
        $result = [];
        if ($mask === null || $setOnly || $mask !== 0) {
            $result[] = 'EXECUTE';
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedPermissions(ObjectIdentity $oid): array
    {
        return ['EXECUTE'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultPermission(): string
    {
        return 'EXECUTE';
    }

    /**
     * {@inheritdoc}
     */
    public function getClasses(): array
    {
        return $this->actionMetadataProvider->getActions();
    }
}
