<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class AspectAclExtension extends AbstractAclExtension
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->map = array(
            'VIEW' => array(
                AspectMaskBuilder::MASK_VIEW,
            ),
            'CREATE' => array(
                AspectMaskBuilder::MASK_CREATE,
            ),
            'EDIT' => array(
                AspectMaskBuilder::MASK_EDIT,
            ),
            'DELETE' => array(
                AspectMaskBuilder::MASK_DELETE,
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object)
    {
        if ($object instanceof ObjectIdentity) {
            return $object->getType() === 'aspect';
        }
        if (is_string($object)) {
            return $this->getSortOfDescriptor($object) === 'aspect';
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidMask($mask, $object)
    {
        $validMasks =
            AspectMaskBuilder::MASK_VIEW
            | AspectMaskBuilder::MASK_CREATE
            | AspectMaskBuilder::MASK_EDIT
            | AspectMaskBuilder::MASK_DELETE;

        return
            $mask === 0
            || ($mask | $validMasks) === $validMasks;
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectIdentity($object)
    {
        $sortOfDescriptor = $value = null;
        $this->parseDescriptor($object, $sortOfDescriptor, $value);

        return new ObjectIdentity('aspect', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function createMaskBuilder()
    {
        return new AspectMaskBuilder();
    }
}
