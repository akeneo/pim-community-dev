<?php

namespace Pim\Component\Catalog\Normalizer\Standard\Product;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Value\OptionsValueInterface;
use Pim\Component\Catalog\Value\PriceCollectionValueInterface;
use Pim\Component\ReferenceData\Value\ReferenceDataCollectionValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a product value into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    const DECIMAL_PRECISION = 4;

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = [])
    {
        $isCollection = $entity instanceof OptionsValueInterface
            || $entity instanceof PriceCollectionValueInterface
            || $entity instanceof ReferenceDataCollectionValueInterface;

        $data = $isCollection ?
            $this->getCollectionValue($entity, $format, $context) : $this->getSimpleValue($entity, $format, $context);

        return [
            'locale' => $entity->getLocale(),
            'scope'  => $entity->getScope(),
            'data'   => $data,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ValueInterface && 'standard' === $format;
    }

    /**
     * @param ValueInterface $value
     * @param string|null    $format
     * @param array          $context
     *
     * @return array
     */
    protected function getCollectionValue(ValueInterface $value, $format = null, array $context = [])
    {
        $attributeType = $value->getAttribute()->getType();
        $context['is_decimals_allowed'] = $value->getAttribute()->isDecimalsAllowed();

        $data = [];
        foreach ($value->getData() as $item) {
            if (AttributeTypes::OPTION_MULTI_SELECT === $attributeType ||
                $value->getAttribute()->isBackendTypeReferenceData()) {
                $data[] = $item->getCode();
            } else {
                $data[] = $this->serializer->normalize($item, $format, $context);
            }
        }

        if (AttributeTypes::PRICE_COLLECTION === $attributeType) {
            usort($data, function ($a, $b) {
                return strnatcasecmp($a['currency'], $b['currency']);
            });
        } else {
            sort($data);
        }

        return $data;
    }

    /**
     * @param ValueInterface $value
     * @param null           $format
     * @param array          $context
     *
     * @return mixed
     */
    protected function getSimpleValue(ValueInterface $value, $format = null, array $context = [])
    {
        if (null === $value->getData()) {
            return null;
        }

        $attributeType = $value->getAttribute()->getType();
        $context['is_decimals_allowed'] = $value->getAttribute()->isDecimalsAllowed();

        // if decimals_allowed is false, we return an integer
        // if true, we return a string to avoid to loose precision (http://floating-point-gui.de)
        if (AttributeTypes::NUMBER === $attributeType && is_numeric($value->getData())) {
            return $value->getAttribute()->isDecimalsAllowed()
                ? number_format($value->getData(), static::DECIMAL_PRECISION, '.', '')
                : (int) $value->getData();
        }

        if (in_array($attributeType, [
            AttributeTypes::OPTION_SIMPLE_SELECT,
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT
        ])) {
            return $value->getData()->getCode();
        }

        if (in_array($attributeType, [AttributeTypes::FILE, AttributeTypes::IMAGE])) {
            return $value->getData()->getKey();
        }

        return $this->serializer->normalize($value->getData(), $format, $context);
    }
}
