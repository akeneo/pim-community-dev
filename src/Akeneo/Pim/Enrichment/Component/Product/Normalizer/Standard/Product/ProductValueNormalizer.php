<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product value into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    const DECIMAL_PRECISION = 4;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(NormalizerInterface $normalizer, GetAttributes $getAttributes)
    {
        $this->normalizer = $normalizer;
        $this->getAttributes = $getAttributes;
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
            'locale' => $entity->getLocaleCode(),
            'scope' => $entity->getScopeCode(),
            'data' => $data,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ValueInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    protected function getCollectionValue(ValueInterface $value, ?string $format = null, array $context = []): array
    {
        $attribute = $this->getAttributes->forCode($value->getAttributeCode());
        if (null === $attribute) {
            return [];
        }

        $attributeType = $attribute->type();
        $context['is_decimals_allowed'] = $attribute->isDecimalsAllowed();

        $data = [];
        foreach ($value->getData() as $item) {
            if (AttributeTypes::OPTION_MULTI_SELECT === $attributeType ||
                isset($attribute->properties()['reference_data_name'])) {
                $data[] = $item;
            } else {
                $data[] = $this->normalizer->normalize($item, $format, $context);
            }
        }

        return $data;
    }

    protected function getSimpleValue(ValueInterface $value, ?string $format = null, array $context = [])
    {
        if (null === $value->getData()) {
            return null;
        }

        $attribute = $this->getAttributes->forCode($value->getAttributeCode());

        if (null === $attribute) {
            return [];
        }

        $attributeType = $attribute->type();

        // if decimals_allowed is false, we return an integer
        // if true, we return a string to avoid to loose precision (http://floating-point-gui.de)
        if (AttributeTypes::NUMBER === $attributeType && is_numeric($value->getData())) {
            return $this->formatNumber($value, $attribute);
        }

        if ($value instanceof ScalarValue) {
            return $value->getData();
        }

        if ($attributeType === AttributeTypes::OPTION_SIMPLE_SELECT ||
            $attributeType === AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT
        ) {
            return $value->getData();
        }

        if ($attributeType === AttributeTypes::FILE ||
            $attributeType === AttributeTypes::IMAGE
        ) {
            return $value->getData()->getKey();
        }

        $context['is_decimals_allowed'] = $attribute->isDecimalsAllowed();

        return $this->normalizer->normalize($value->getData(), $format, $context);
    }

    /**
     * Cut all the trailing zeros after comma if decimals are more than 4, or add trailing zeros to go to 4 digits.
     *
     * Expected return for number value:
     *   15.00        => 15.0000
     *   15.50        => 15.5000
     *   15.500       => 15.5000
     *   15.50787600  => 15.507876
     *   15.000000    => 15.0000
     *
     * @param ValueInterface $value
     * @param Attribute      $attribute
     *
     * @return string|int
     */
    private function formatNumber(ValueInterface $value, Attribute $attribute)
    {
        if (!$attribute->isDecimalsAllowed()) {
            return (int) $value->getData();
        }

        $data = $value->getData();
        $integer = substr($data, 0, strpos($data, '.') + 1);
        $decimals = substr($data, strpos($data, '.') + 1);

        if (strlen($decimals) <= static::DECIMAL_PRECISION) {
            return number_format($data, static::DECIMAL_PRECISION, '.', '');
        }

        return sprintf(
            '%s%s%s',
            $integer,
            substr($decimals, 0, static::DECIMAL_PRECISION),
            rtrim(substr($decimals, static::DECIMAL_PRECISION), '0')
        );
    }
}
