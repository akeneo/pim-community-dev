<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Normalize a product value to store it as mongodb_json
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    const NORM_ITEM_KEY   = 'normKey';
    const NORM_ITEM_VALUE = 'normValue';

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $data = null;
        $valueKey = $this->getFieldValue($object);
        if ($object->getData() instanceof Collection) {
            $data[$valueKey] = [];
            foreach ($object->getData() as $item) {
                $normalizedItem = $this->serializer->normalize($item, $format, $context);
                $data[$valueKey][$normalizedItem[self::NORM_ITEM_KEY]] = $normalizedItem[self::NORM_ITEM_VALUE];
            }

        } else {
            if ($object->getData() !== null) {
                $normalizedValue = $this->serializer->normalize($object->getData(), $format, $context);
                if (is_array($normalizedValue) &&
                    ([self::NORM_ITEM_KEY, self::NORM_ITEM_VALUE] === array_keys($normalizedValue))) {
                    $normalizedValue = $normalizedValue[self::NORM_ITEM_VALUE];
                }
                $data[$valueKey] = $this->serializer->normalize($object->getData(), $format, $context);
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && 'mongodb_json' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Normalize the field name for values
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    protected function getFieldValue($value)
    {
        $suffix = '';

        if ($value->getAttribute()->isLocalizable()) {
            $suffix = sprintf('-%s', $value->getLocale());
        }
        if ($value->getAttribute()->isScopable()) {
            $suffix .= sprintf('-%s', $value->getScope());
        }

        return $value->getAttribute()->getCode() . $suffix;
    }
}
