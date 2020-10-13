<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer\AxisValueLabelsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\ImageAsLabel;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * "Light" version of the EntityWithFamilyVariantNormalizer, it only returns the needed information
 * for the "variant navigation" drop down in a product model edit form:
 * - only the current locale
 * - for completeness: only the ratio for the current locale and channel
 *
 * This normalizer exists for performance reasons, as normalizing a completeness collection in
 * internal_api format is very costly, especially if there are a lot of channels and locales in the catalog
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LightEntityWithFamilyVariantNormalizer implements NormalizerInterface
{
    /** @var ImageNormalizer */
    private $imageNormalizer;

    /** @var ImageAsLabel */
    private $imageAsLabel;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeOptionRepository;

    /** @var GetProductCompletenessRatio */
    private $getCompletenessRatio;

    /** @var VariantProductRatioInterface */
    private $variantProductRatioQuery;

    /** @var iterable */
    private $axisLabelsNormalizers;

    public function __construct(
        ImageNormalizer $imageNormalizer,
        ImageAsLabel $imageAsLabel,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        GetProductCompletenessRatio $getCompletenessRatio,
        VariantProductRatioInterface $variantProductRatioQuery,
        iterable $axisLabelsNormalizers
    ) {
        $this->imageNormalizer = $imageNormalizer;
        $this->imageAsLabel = $imageAsLabel;
        $this->attributesProvider = $attributesProvider;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->getCompletenessRatio = $getCompletenessRatio;
        $this->variantProductRatioQuery = $variantProductRatioQuery;
        $this->axisLabelsNormalizers = $axisLabelsNormalizers;
    }

    public function normalize($entity, $format = null, array $context = []): array
    {
        $channelCode = $context['channel'] ?? null;
        $localeCode = $context['locale'] ?? null;

        if (!is_string($channelCode) || !is_string($localeCode)) {
            throw new \LogicException('channel and locale have to be defined in the $context argument');
        }

        if ($entity instanceof ProductModelInterface) {
            $image = $this->imageAsLabel->value($entity);
            $completeness = $this->getProductModelCompleteness($entity, $channelCode, $localeCode);
        } else {
            $image = $entity->getImage();
            $completeness = $this->getProductCompleteness($entity, $channelCode, $localeCode);
        }

        return [
            'id' => $entity->getId(),
            'identifier' => $this->getIdentifier($entity),
            'labels' => [$localeCode => $entity->getLabel($localeCode, $channelCode)],
            'axes_values_labels' => $this->getAxesLabel($entity, $localeCode),
            'order' => $this->getOrder($entity),
            'image' => $this->imageNormalizer->normalize($image),
            'model_type' => $entity instanceof ProductModelInterface ? 'product_model' : 'product',
            'completeness' => $completeness,
        ];
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return 'internal_api' === $format && $data instanceof EntityWithFamilyVariantInterface;
    }

    /**
     * Get axes label for the $entity on the given $localeCode for all axes values.
     */
    private function getAxesLabel(EntityWithFamilyVariantInterface $entity, string $localeCode): array
    {
        $valuesForLocale = [];
        foreach ($this->attributesProvider->getAxes($entity) as $axisAttribute) {
            $value = $entity->getValue($axisAttribute->getCode());
            $normalizedValue = (string)$value;

            $attributeNormalizer = $this->getAttributeLabelsNormalizer($axisAttribute);
            if ($attributeNormalizer instanceof AxisValueLabelsNormalizer) {
                $normalizedValue = $attributeNormalizer->normalize($value, $localeCode);
            }

            $valuesForLocale[] = $normalizedValue;
        }

        return [$localeCode => implode(', ', $valuesForLocale)];
    }

    private function getAttributeLabelsNormalizer(AttributeInterface $attribute): ?AxisValueLabelsNormalizer
    {
        foreach ($this->axisLabelsNormalizers as $normalizer) {
            if ($normalizer->supports($attribute->getType())) {
                return $normalizer;
            }
        }

        return null;
    }

    /**
     * Generate an array for the given $entity to represent its order among all its axes values.
     *
     * For example, if its axes values are "Blue, 10 CENTIMETER" and Blue is an option with a sort order equals to 4,
     * it will return [4, "blue", "CENTIMETER", 10].
     *
     * It allows to sort on front-end to respect sort orders of attribute options.
     *
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return array
     */
    private function getOrder(EntityWithFamilyVariantInterface $entity): array
    {
        $orderArray = [];

        foreach ($this->attributesProvider->getAxes($entity) as $axisAttribute) {
            $value = $entity->getValue($axisAttribute->getCode());

            if (AttributeTypes::OPTION_SIMPLE_SELECT === $axisAttribute->getType()) {
                $optionCode = $value->getData();
                $option = $this->attributeOptionRepository->findOneByIdentifier(
                    $value->getAttributeCode() . '.' . $optionCode
                );
                $orderArray[] = $option->getSortOrder();
                $orderArray[] = $option->getCode();
            } elseif (AttributeTypes::METRIC === $axisAttribute->getType()) {
                $data = $value->getData();
                $orderArray[] = $data->getUnit();
                $orderArray[] = floatval($data->getData());
            } elseif (AttributeTypes::BOOLEAN === $axisAttribute->getType()) {
                $orderArray[] = (true === $value->getData() ? '1' : '0');
            } else {
                $orderArray[] = (string)$value;
            }
        }

        return $orderArray;
    }

    private function getIdentifier(EntityWithFamilyVariantInterface $entity): string
    {
        if (!$entity instanceof ProductInterface || !$entity instanceof ProductModelInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Entity must be a product or a product model, instance of \'%s\' given',
                get_class($entity)
            ));
        }

        return $entity instanceof ProductModelInterface ? $entity->getCode() : $entity->getIdentifier();
    }

    private function getProductModelCompleteness(ProductModelInterface $entity, string $channelCode, string $localeCode): array
    {
        $completeness = $this->variantProductRatioQuery->findComplete($entity)->value($channelCode, $localeCode);

        return [
            'completenesses' => [
                $channelCode => [
                    $localeCode => $completeness['complete'],
                ],
            ],
            'total' => $completeness['total'],
        ];
    }

    private function getProductCompleteness(ProductInterface $entity, string $channelCode, string $localeCode): array
    {
        return [
            [
                'channel' => $channelCode,
                'locales' => [
                    $localeCode => [
                        'completeness' => [
                            'ratio' => $this->getCompletenessRatio->forChannelCodeAndLocaleCode(
                                $entity->getId(),
                                $channelCode,
                                $localeCode
                            ),
                        ],
                    ],
                ],
            ],
        ];
    }
}
