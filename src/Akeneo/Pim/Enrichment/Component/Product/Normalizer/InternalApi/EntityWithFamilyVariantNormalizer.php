<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer\AxisValueLabelsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\ImageAsLabel;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for entities with family variant, such as VariantProducts and ProductModels.
 * It only returns some properties of these entities, helpful for some display on the front side.
 *
 * To fully normalize a Product or a ProductModel, please use either
 * {@see \Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductNormalizer} or
 * {@see \Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductModelNormalizer}
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithFamilyVariantNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var string[] */
    private $supportedFormat = ['internal_api'];

    /** @var ImageNormalizer */
    private $imageNormalizer;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /** @var ProductCompletenessWithMissingAttributeCodesCollectionNormalizer */
    private $completenessCollectionNormalizer;

    /** @var VariantProductRatioInterface */
    private $variantProductRatioQuery;

    /** @var ImageAsLabel */
    private $imageAsLabel;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeOptionRepository;

    /** @var AxisValueLabelsNormalizer[] */
    private $normalizers;

    /** @var CatalogContext */
    private $catalogContext;

    /** @var CompletenessCalculator */
    private $completenessCalculator;

    public function __construct(
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        ProductCompletenessWithMissingAttributeCodesCollectionNormalizer $completenessCollectionNormalizer,
        VariantProductRatioInterface $variantProductRatioQuery,
        ImageAsLabel $imageAsLabel,
        CatalogContext $catalogContext,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        CompletenessCalculator $completenessCalculator,
        AxisValueLabelsNormalizer ...$normalizers
    ) {
        $this->imageNormalizer                  = $imageNormalizer;
        $this->localeRepository                 = $localeRepository;
        $this->attributesProvider               = $attributesProvider;
        $this->completenessCollectionNormalizer = $completenessCollectionNormalizer;
        $this->variantProductRatioQuery         = $variantProductRatioQuery;
        $this->imageAsLabel                     = $imageAsLabel;
        $this->catalogContext                   = $catalogContext;
        $this->attributeOptionRepository        = $attributeOptionRepository;
        $this->completenessCalculator           = $completenessCalculator;
        $this->normalizers = $normalizers;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = []): array
    {
        if (!$entity instanceof ProductModelInterface && !$entity instanceof ProductInterface) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" or "%s" expected, "%s" received',
                ProductModelInterface::class,
                ProductInterface::class,
                get_class($entity)
            ));
        }

        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();

        $labels = [];
        foreach ($localeCodes as $localeCode) {
            $labels[$localeCode] = $entity->getLabel($localeCode);
        }

        $identifier = $entity instanceof ProductModelInterface ? $entity->getCode() : $entity->getIdentifier();

        if ($entity instanceof ProductModelInterface) {
            $image = $this->imageAsLabel->value($entity);
        } else {
            $image = $entity->getImage();
        }

        return [
            'id'                 => $entity->getId(),
            'identifier'         => $identifier,
            'axes_values_labels' => $this->getAxesValuesLabelsForLocales($entity, $localeCodes),
            'labels'             => $labels,
            'order'              => $this->getOrder($entity),
            'image'              => $this->normalizeImage($image, $this->catalogContext->getLocaleCode()),
            'model_type'         => $entity instanceof ProductModelInterface ? 'product_model' : 'product',
            'completeness'       => $this->getCompletenessDependingOnEntity($entity)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof EntityWithFamilyVariantInterface && in_array($format, $this->supportedFormat);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param ValueInterface $data
     * @param string         $localeCode
     *
     * @return array|null
     */
    private function normalizeImage(?ValueInterface $data, ?string $localeCode = null): ?array
    {
        return $this->imageNormalizer->normalize($data, $localeCode);
    }

    /**
     * Get axes values labels for the $entity on the given $localeCodes for all axes values.
     * For example:
     * [
     *      'fr_FR' => 'Jaune, XL',
     *      'en_US' => 'Yellow, XL',
     * ]
     *
     * @param EntityWithFamilyVariantInterface $entity
     * @param array                            $localeCodes
     *
     * @throws \LogicException
     *
     * @return array
     */
    private function getAxesValuesLabelsForLocales(EntityWithFamilyVariantInterface $entity, array $localeCodes): array
    {
        $axesValuesLabels = [];

        foreach ($localeCodes as $localeCode) {
            $valuesForLocale = [];

            foreach ($this->attributesProvider->getAxes($entity) as $axisAttribute) {
                $value = $entity->getValue($axisAttribute->getCode());

                $normalizedValue = (string) $value;

                $attributeNormalizer = $this->getAttributeLabelsNormalizer($axisAttribute);
                if ($attributeNormalizer instanceof AxisValueLabelsNormalizer) {
                    $normalizedValue = $attributeNormalizer->normalize($value, $localeCode);
                }

                $valuesForLocale[] = $normalizedValue;
            }

            $axesValuesLabels[$localeCode] = implode(', ', $valuesForLocale);
        }

        return $axesValuesLabels;
    }

    private function getAttributeLabelsNormalizer(AttributeInterface $attribute): ?AxisValueLabelsNormalizer
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supports($attribute->getType())) {
                return $normalizer;
            }
        }

        return null;
    }

    /**
     * Get completeness of the given $entity, whether it's a ProductModel or a VariantProduct.
     *
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return array
     */
    private function getCompletenessDependingOnEntity(EntityWithFamilyVariantInterface $entity): array
    {
        if ($entity instanceof ProductModelInterface) {
            return $this->variantProductRatioQuery->findComplete($entity)->values();
        }

        if ($entity instanceof ProductInterface && $entity->isVariant()) {
            $completenessCollection = $this->completenessCalculator->fromProductIdentifier($entity->getIdentifier());
            if (null === $completenessCollection) {
                $completenessCollection = new ProductCompletenessWithMissingAttributeCodesCollection($entity->getId(), []);
            }

            return $this->completenessCollectionNormalizer->normalize($completenessCollection);
        }

        return [];
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
                $option = $this->attributeOptionRepository->findOneByIdentifier($value->getAttributeCode().'.'.$optionCode);
                $orderArray[] = $option->getSortOrder();
                $orderArray[] = $option->getCode();
            } elseif (AttributeTypes::METRIC === $axisAttribute->getType()) {
                $data = $value->getData();
                $orderArray[] = $data->getUnit();
                $orderArray[] = floatval($data->getData());
            } elseif (AttributeTypes::BOOLEAN === $axisAttribute->getType()) {
                $orderArray[] = (true === $value->getData() ? '1' : '0');
            } else {
                $orderArray[] = (string) $value;
            }
        }

        return $orderArray;
    }
}
