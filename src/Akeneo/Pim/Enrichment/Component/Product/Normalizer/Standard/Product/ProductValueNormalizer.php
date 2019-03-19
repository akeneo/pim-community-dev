<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
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

    /** @var LruArrayAttributeRepository */
    private $attributeRepository;

    public function __construct(NormalizerInterface $normalizer, LruArrayAttributeRepository $attributeRepository)
    {
        $this->normalizer = $normalizer;
        $this->attributeRepository = $attributeRepository;
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
            'scope'  => $entity->getScopeCode(),
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

    protected function getCollectionValue(ValueInterface $value, ?string $format = null, array $context = []): array
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());


        if (null === $attribute) {
            return [];
        }

        $attributeType = $attribute->getType();
        $context['is_decimals_allowed'] = $attribute->isDecimalsAllowed();

        $data = [];
        foreach ($value->getData() as $item) {
            if (AttributeTypes::OPTION_MULTI_SELECT === $attributeType ||
                in_array(
                    $attribute->getBackendType(),
                    [
                        AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION,
                        AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS,
                    ]
                )
            ) {
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

        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

        if (null === $attribute) {
            return null;
        }

        $attributeType = $attribute->getType();

        // if decimals_allowed is false, we return an integer
        // if true, we return a string to avoid to loose precision (http://floating-point-gui.de)
        if (AttributeTypes::NUMBER === $attributeType && is_numeric($value->getData())) {
            return $attribute->isDecimalsAllowed()
                ? number_format($value->getData(), static::DECIMAL_PRECISION, '.', '')
                : (int) $value->getData();
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
}
