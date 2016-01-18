<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Attribute options collection denormalizer used for following attribute types:
 * - pim_catalog_multiselect
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionsDenormalizer extends AbstractValueDenormalizer implements SerializerAwareInterface
{
    /** @var SerializerInterface */
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (empty($data)) {
            return null;
        }

        $options = new ArrayCollection();

        foreach ($data as $optionCode) {
            $option = $this->serializer->denormalize(
                $optionCode,
                AttributeTypes::OPTION_SIMPLE_SELECT,
                $format,
                $context
            );
            $options->add($option);
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
