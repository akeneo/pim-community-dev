<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class ActionAclExtension extends AbstractAclExtension
{
    /**
     * Constructor
     */
    public function __construct()
    {
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
        return $type === $this->getRootType();
    }

    /**
     * {@inheritdoc}
     */
    public function getRootType()
    {
        return 'action';
    }

    /**
     * {@inheritdoc}
     */
    public function validateMask($permission, $mask, $object)
    {
        if ($mask === 0) {
            return;
        }
        if ($mask === ActionMaskBuilder::MASK_EXECUTE) {
            return;
        }

        throw $this->createInvalidAclMaskException($permission, $mask, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectIdentity($object)
    {
        $type = $id = null;
        $this->parseDescriptor($object, $type, $id);

        return new ObjectIdentity($id, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function createMaskBuilder($permission)
    {
        return new ActionMaskBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessLevel($mask)
    {
        return AccessLevel::SYSTEM_LEVEL;
    }
}
