<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a product value to store it as mongodb_json
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var SerializerInterface */
    protected $serializer;

    /**
     * {@inheritdoc}
     *
     * @param array $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $valueKey = $this->getFieldValue($object);
        $normalized = null;
        if ($object->getData() instanceof Collection) {
            $normalized = $this->normalizeCollection($object->getData(), $format, $context);
        } elseif ($object->getData() !== null) {
            if (AttributeTypes::BACKEND_TYPE_DECIMAL === $object->getAttribute()->getBackendType()) {
                $normalized = $this->normalizeDecimal($object->getData(), $format, $context);
            } else {
                $normalized = $this->serializer->normalize($object->getData(), $format, $context);
            }
        }

        return ($normalized === null) ? $normalized : [$valueKey => $normalized];
    }

    /**
     * Normalize a collection attribute value
     *
     * @param Collection $collection
     * @param string     $format
     * @param array      $context
     *
     * @return array|null
     */
    protected function normalizeCollection(Collection $collection, $format, $context)
    {
        $normalized = [];
        foreach ($collection as $item) {
            $data = $this->serializer->normalize($item, $format, $context);
            if ($data !== null) {
                if ($item instanceof ProductPriceInterface) {
                    $normalized[$item->getCurrency()] = $data;
                } else {
                    $normalized[] = $data;
                }
            }
        }

        return (count($normalized) > 0) ? $normalized : null;
    }

    /**
     * Normalize a decimal attribute value
     *
     * @param mixed  $data
     * @param string $format
     * @param array  $context
     *
     * @return mixed|null
     */
    protected function normalizeDecimal($data, $format, $context)
    {
        if (false === is_numeric($data)) {
            return $this->serializer->normalize($data, $format, $context);
        }

        return floatval($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ValueInterface && 'mongodb_json' === $format;
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
     * @param ValueInterface $value
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
