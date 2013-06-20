<?php

namespace Oro\Bundle\AddressBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\AddressType;

class AddressTypeToTypeTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object to a string.
     *
     * @param  AddressType|null $addressType
     * @return string
     */
    public function transform($addressType)
    {
        if (null === $addressType) {
            return "";
        }

        return $addressType->getType();
    }

    /**
     * Transforms a string to an object.
     *
     * @param string $type
     * @return int|null
     * @throws TransformationFailedException
     */
    public function reverseTransform($type)
    {
        if (!$type) {
            return null;
        }

        $addressType = $this->om
            ->getRepository('OroAddressBundle:AddressType')
            ->findOneBy(array('type' => $type));

        if (null === $addressType) {
            throw new TransformationFailedException(
                sprintf(
                    'An address type with type "%s" does not exist!',
                    $type
                )
            );
        }

        return $addressType;
    }
}
