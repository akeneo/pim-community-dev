<?php

namespace Pim\Component\Catalog\Normalizer\Standard\Product;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\ProductValue\OptionsProductValueInterface;
use Pim\Component\Catalog\ProductValue\PriceCollectionProductValueInterface;
use Pim\Component\ReferenceData\ProductValue\ReferenceDataCollectionProductValueInterface;
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
        $isCollection = $entity instanceof OptionsProductValueInterface
            || $entity instanceof PriceCollectionProductValueInterface
            || $entity instanceof ReferenceDataCollectionProductValueInterface;

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
        return $data instanceof ProductValueInterface && 'standard' === $format;
    }

    /**
     * @param ProductValueInterface $productValue
     * @param string|null           $format
     * @param array                 $context
     *
     * @return array
     */
    protected function getCollectionValue(ProductValueInterface $productValue, $format = null, array $context = [])
    {
        $attributeType = $productValue->getAttribute()->getType();
        $context['is_decimals_allowed'] = $productValue->getAttribute()->isDecimalsAllowed();

        $data = [];
        foreach ($productValue->getData() as $item) {
            if (AttributeTypes::OPTION_MULTI_SELECT === $attributeType ||
                $productValue->getAttribute()->isBackendTypeReferenceData()) {
                $data[] = $item->getCode();
            } else {
                $data[] = $this->serializer->normalize($item, $format, $context);
            }

            sort($data);
        }

        return $data;
    }

    /**
     * @param ProductValueInterface $productValue
     * @param null                  $format
     * @param array                 $context
     *
     * @return mixed
     */
    protected function getSimpleValue(ProductValueInterface $productValue, $format = null, array $context = [])
    {
        if (null === $productValue->getData()) {
            return null;
        }

        $attributeType = $productValue->getAttribute()->getType();
        $context['is_decimals_allowed'] = $productValue->getAttribute()->isDecimalsAllowed();

        // if decimals_allowed is false, we return an integer
        // if true, we return a string to avoid to loose precision (http://floating-point-gui.de)
        if (AttributeTypes::NUMBER === $attributeType && is_numeric($productValue->getData())) {
            return $productValue->getAttribute()->isDecimalsAllowed()
                ? number_format($productValue->getData(), static::DECIMAL_PRECISION, '.', '')
                : (int) $productValue->getData();
        }

        if (in_array($attributeType, [
            AttributeTypes::OPTION_SIMPLE_SELECT,
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT
        ])) {
            return $productValue->getData()->getCode();
        }

        if (in_array($attributeType, [AttributeTypes::FILE, AttributeTypes::IMAGE])) {
            return $productValue->getData()->getKey();
        }

        return $this->serializer->normalize($productValue->getData(), $format, $context);
    }
}
