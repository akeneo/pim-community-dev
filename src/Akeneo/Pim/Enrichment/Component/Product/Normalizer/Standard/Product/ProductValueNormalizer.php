<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product value into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface
{
    const DECIMAL_PRECISION = 4;

    /** @var NormalizerInterface */
    private $normalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = []): array
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
    public function supportsNormalization($data, $format = null): bool
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
    protected function getCollectionValue(ValueInterface $value, ?string $format = null, array $context = []): array
    {
        $attributeType = $value->getAttribute()->getType();
        $context['is_decimals_allowed'] = $value->getAttribute()->isDecimalsAllowed();

        $data = [];
        foreach ($value->getData() as $item) {
            if (AttributeTypes::OPTION_MULTI_SELECT === $attributeType ||
                $value->getAttribute()->isBackendTypeReferenceData()) {
                $data[] = $item->getCode();
            } else {
                $data[] = $this->normalizer->normalize($item, $format, $context);
            }
        }

        return $data;
    }

    /**
     * @param ValueInterface $value
     * @param null|string    $format
     * @param array          $context
     *
     * @return mixed
     */
    protected function getSimpleValue(ValueInterface $value, ?string $format = null, array $context = [])
    {
        if (null === $value->getData()) {
            return null;
        }

        $attributeType = $value->getAttribute()->getType();

        // if decimals_allowed is false, we return an integer
        // if true, we return a string to avoid to loose precision (http://floating-point-gui.de)
        if (AttributeTypes::NUMBER === $attributeType && is_numeric($value->getData())) {
            return $value->getAttribute()->isDecimalsAllowed()
                ? number_format($value->getData(), static::DECIMAL_PRECISION, '.', '')
                : (int) $value->getData();
        }

        if ($value instanceof ScalarValue) {
            return $value->getData();
        }

        if ($attributeType === AttributeTypes::OPTION_SIMPLE_SELECT ||
            $attributeType === AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT
        ) {
            return $value->getData()->getCode();
        }

        if ($attributeType === AttributeTypes::FILE ||
            $attributeType === AttributeTypes::IMAGE
        ) {
            return $value->getData()->getKey();
        }

        $context['is_decimals_allowed'] = $value->getAttribute()->isDecimalsAllowed();

        return $this->normalizer->normalize($value->getData(), $format, $context);
    }
}
