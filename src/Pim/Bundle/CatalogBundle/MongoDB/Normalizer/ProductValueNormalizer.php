<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;

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
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $valueKey = $this->getFieldValue($object);
        $normalized = null;
        if ($object->getData() instanceof Collection) {
            $normalized = $this->normalizeCollection($object->getData(), $format, $context);
        } elseif ($object->getData() !== null) {
            $normalized = $this->serializer->normalize($object->getData(), $format, $context);
        }

        return ($normalized === null) ? $normalized : [$valueKey => $normalized];
    }

    /**
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
                if ($item instanceOf ProductPrice) {
                    $normalized[$item->getCurrency()] = $data;
                } else {
                    $normalized[] = $data;
                }
            }
        }

        return (count($normalized) > 0) ? $normalized : null;
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
