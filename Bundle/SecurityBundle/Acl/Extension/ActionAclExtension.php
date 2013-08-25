<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

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
    public function supportsObject($object)
    {
        if ($object instanceof ObjectIdentity) {
            return $object->getType() === 'action';
        }
        if (is_string($object)) {
            return $this->getSortOfDescriptor($object) === 'action';
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidMask($mask, $object)
    {
        return
            $mask === 0
            || $mask === ActionMaskBuilder::MASK_EXECUTE;
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectIdentity($object)
    {
        $sortOfDescriptor = $value = null;
        $this->parseDescriptor($object, $sortOfDescriptor, $value);

        return new ObjectIdentity('action', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function createMaskBuilder()
    {
        return new ActionMaskBuilder();
    }
}
