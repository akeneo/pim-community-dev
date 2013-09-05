<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Metadata\ActionMetadataProvider;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;

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

        $this->map = array(
            'EXECUTE' => array(
                ActionMaskBuilder::MASK_EXECUTE,
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type, $id)
    {
        if ($type === ObjectIdentityFactory::ROOT_IDENTITY_TYPE && $id === $this->getExtensionKey()) {
            return true;
        }

        return $id === $this->getExtensionKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionKey()
    {
        return 'action';
    }

    /**
     * {@inheritdoc}
     */
    public function validateMask($mask, $object, $permission = null)
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
    public function getObjectIdentity($object)
    {
        $type = $id = null;
        $this->parseDescriptor($object, $type, $id);

        return new ObjectIdentity($id, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskBuilder($permission)
    {
        return new ActionMaskBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllMaskBuilders()
    {
        return array(new ActionMaskBuilder());
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskPattern($mask)
    {
        return ActionMaskBuilder::getPatternFor($mask);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessLevel($mask, $permission = null)
    {
        return $mask === 0
            ? AccessLevel::NONE_LEVEL
            : AccessLevel::SYSTEM_LEVEL;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions($mask = null, $setOnly = false)
    {
        $result = array();
        if ($mask === null || $setOnly || $mask !== 0) {
            $result[] = 'EXECUTE';
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedPermissions(ObjectIdentity $oid)
    {
        return array('EXECUTE');
    }

    /**
     * {@inheritdoc}
     */
    public function getClasses()
    {
        return $this->actionMetadataProvider->getActions();
    }
}
