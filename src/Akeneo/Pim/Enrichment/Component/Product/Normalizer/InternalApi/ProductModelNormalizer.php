<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\Pim\Enrichment\Component\Category\Query\AscendantCategoriesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\QuantifiedAssociationsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\ImageAsLabel;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var string[] */
    private array $supportedFormat = ['internal_api'];

    private NormalizerInterface $normalizer;
    private NormalizerInterface $versionNormalizer;
    private ImageNormalizer $imageNormalizer;
    private VersionManager $versionManager;
    private AttributeConverterInterface $localizedConverter;
    private ConverterInterface $productValueConverter;
    private FormProviderInterface $formProvider;
    private LocaleRepositoryInterface $localeRepository;
    private FillMissingValuesInterface $fillMissingProductModelValues;
    private EntityWithFamilyVariantAttributesProvider $attributesProvider;
    private VariantProductRatioInterface $variantProductRatioQuery;
    private VariantNavigationNormalizer $navigationNormalizer;
    private ImageAsLabel $imageAsLabel;
    private AscendantCategoriesInterface $ascendantCategoriesQuery;
    private UserContext $userContext;
    private NormalizerInterface $parentAssociationsNormalizer;
    private MissingAssociationAdder $missingAssociationAdder;
    private CatalogContext $catalogContext;
    private MissingRequiredAttributesCalculatorInterface $missingRequiredAttributesCalculator;
    private MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer;
    private QuantifiedAssociationsNormalizer $quantifiedAssociationsNormalizer;

    public function __construct(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        ImageNormalizer $imageNormalizer,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        FormProviderInterface $formProvider,
        LocaleRepositoryInterface $localeRepository,
        FillMissingValuesInterface $fillMissingProductModelValues,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        VariantProductRatioInterface $variantProductRatioQuery,
        ImageAsLabel $imageAsLabel,
        AscendantCategoriesInterface $ascendantCategoriesQuery,
        UserContext $userContext,
        MissingAssociationAdder $missingAssociationAdder,
        NormalizerInterface $parentAssociationsNormalizer,
        CatalogContext $catalogContext,
        MissingRequiredAttributesCalculatorInterface $missingRequiredAttributesCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer,
        QuantifiedAssociationsNormalizer $quantifiedAssociationsNormalizer
    ) {
        $this->normalizer = $normalizer;
        $this->versionNormalizer = $versionNormalizer;
        $this->versionManager = $versionManager;
        $this->imageNormalizer = $imageNormalizer;
        $this->localizedConverter = $localizedConverter;
        $this->productValueConverter = $productValueConverter;
        $this->formProvider = $formProvider;
        $this->localeRepository = $localeRepository;
        $this->fillMissingProductModelValues = $fillMissingProductModelValues;
        $this->attributesProvider = $attributesProvider;
        $this->navigationNormalizer = $navigationNormalizer;
        $this->variantProductRatioQuery = $variantProductRatioQuery;
        $this->imageAsLabel = $imageAsLabel;
        $this->ascendantCategoriesQuery = $ascendantCategoriesQuery;
        $this->userContext = $userContext;
        $this->missingAssociationAdder = $missingAssociationAdder;
        $this->parentAssociationsNormalizer = $parentAssociationsNormalizer;
        $this->catalogContext = $catalogContext;
        $this->missingRequiredAttributesCalculator = $missingRequiredAttributesCalculator;
        $this->missingRequiredAttributesNormalizer = $missingRequiredAttributesNormalizer;
        $this->quantifiedAssociationsNormalizer = $quantifiedAssociationsNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductModelInterface $productModel
     */
    public function normalize($productModel, $format = null, array $context = []): array
    {
        $this->missingAssociationAdder->addMissingAssociations($productModel);
        $normalizedProductModel = $this->normalizer->normalize($productModel, 'standard', $context);
        $normalizedProductModel = $this->fillMissingProductModelValues->fromStandardFormat($normalizedProductModel);

        $normalizedProductModel['values'] = $this->localizedConverter->convertToLocalizedFormats(
            $normalizedProductModel['values'],
            $context
        );

        $normalizedProductModel['family'] = $productModel->getFamilyVariant()->getFamily()->getCode();
        $normalizedProductModel['values'] = $this->productValueConverter->convert($normalizedProductModel['values']);
        $normalizedProductModel['quantified_associations'] = $this->formatQuantifiedAssociations($normalizedProductModel['quantified_associations']);

        $oldestLog = $this->versionManager->getOldestLogEntry($productModel);
        $newestLog = $this->versionManager->getNewestLogEntry($productModel);

        $created = null !== $oldestLog ?
            $this->versionNormalizer->normalize(
                $oldestLog,
                'internal_api',
                ['timezone' => $this->userContext->getUserTimezone()]
            ) : null;
        $updated = null !== $newestLog ?
            $this->versionNormalizer->normalize(
                $newestLog,
                'internal_api',
                ['timezone' => $this->userContext->getUserTimezone()]
            ) : null;

        $levelAttributes = [];
        foreach ($this->attributesProvider->getAttributes($productModel) as $attribute) {
            $levelAttributes[] = $attribute->getCode();
        }

        $axesAttributes = [];
        foreach ($this->attributesProvider->getAxes($productModel) as $attribute) {
            $axesAttributes[] = $attribute->getCode();
        }

        $normalizedProductModel['parent_associations'] = $this->parentAssociationsNormalizer
            ->normalize($productModel, $format, $context);

        $normalizedFamilyVariant = $this->normalizer->normalize($productModel->getFamilyVariant(), 'standard');

        $variantProductCompletenesses = $this->variantProductRatioQuery->findComplete($productModel);
        $closestImage = $this->imageAsLabel->value($productModel);

        $scopeCode = $context['channel'] ?? null;
        $requiredMissingAttributes = $this->missingRequiredAttributesNormalizer->normalize(
            $this->missingRequiredAttributesCalculator->fromEntityWithFamily($productModel)
        );

        $normalizedProductModel['meta'] = [
                'variant_product_completenesses' => $variantProductCompletenesses->values(),
                'family_variant'            => $normalizedFamilyVariant,
                'form'                      => $this->formProvider->getForm($productModel),
                'id'                        => $productModel->getId(),
                'created'                   => $created,
                'updated'                   => $updated,
                'model_type'                => 'product_model',
                'attributes_for_this_level' => $levelAttributes,
                'attributes_axes'           => $axesAttributes,
                'image'                     => $this->normalizeImage($closestImage, $this->catalogContext->getScopeCode(), $this->catalogContext->getLocaleCode()),
                'variant_navigation'        => $this->navigationNormalizer->normalize($productModel, $format, $context),
                'ascendant_category_ids'    => $this->ascendantCategoriesQuery->getCategoryIds($productModel),
                'required_missing_attributes' => $requiredMissingAttributes,
                'level'                     => $productModel->getVariationLevel(),
                'quantified_associations_for_this_level' => $this->formatQuantifiedAssociations($this->quantifiedAssociationsNormalizer->normalizeWithoutParentsAssociations($productModel, 'standard', $context)),
                'parent_quantified_associations' => $this->formatQuantifiedAssociations($this->quantifiedAssociationsNormalizer->normalizeOnlyParentsAssociations($productModel, 'standard', $context)),
            ] + $this->getLabels($productModel, $scopeCode) + $this->getAssociationMeta($productModel);

        return $normalizedProductModel;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelInterface && in_array($format, $this->supportedFormat);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string|null           $scopeCode
     *
     * @return array
     */
    private function getLabels(ProductModelInterface $productModel, string $scopeCode = null): array
    {
        $labels = [];

        foreach ($this->localeRepository->getActivatedLocaleCodes() as $localeCode) {
            $labels[$localeCode] = $productModel->getLabel($localeCode, $scopeCode);
        }

        return ['label' => $labels];
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    protected function getAssociationMeta(ProductModelInterface $productModel)
    {
        $meta = [];
        $associations = $productModel->getAssociations();

        foreach ($associations as $association) {
            $associationType = $association->getAssociationType();
            $meta[$associationType->getCode()]['groupIds'] = array_map(
                function ($group) {
                    return $group->getId();
                },
                $association->getGroups()->toArray()
            );
        }

        return ['associations' => $meta];
    }

    /**
     * @param ValueInterface|null $data
     * @param string              $localeCode
     * @param string              $channelCode
     *
     * @return array|null
     */
    private function normalizeImage(?ValueInterface $data, ?string $channelCode, ?string $localeCode = null): ?array
    {
        return $this->imageNormalizer->normalize($data, $localeCode, $channelCode);
    }

    private function formatQuantifiedAssociations(array $quantifiedAssociations): array
    {
        return array_map(static function (array $quantifiedAssociation) {
            $quantifiedAssociation['products'] = array_map(static fn (array $productLink) => array_filter(
                $productLink,
                fn (string $key): bool => in_array($key, ['uuid', 'quantity']),
                ARRAY_FILTER_USE_KEY
            ), $quantifiedAssociation['products']);

            return $quantifiedAssociation;
        }, $quantifiedAssociations);
    }
}
