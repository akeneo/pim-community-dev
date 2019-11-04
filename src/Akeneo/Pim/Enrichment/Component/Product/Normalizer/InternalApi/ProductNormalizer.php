<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\Pim\Enrichment\Component\Category\Query\AscendantCategoriesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var string[] */
    protected $supportedFormat = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var VersionManager */
    protected $versionManager;

    /** @var ImageNormalizer */
    protected $imageNormalizer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var StructureVersionProviderInterface */
    protected $structureVersionProvider;

    /** @var FormProviderInterface */
    protected $formProvider;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /** @var ConverterInterface */
    protected $productValueConverter;

    /** @var ProductCompletenessWithMissingAttributeCodesCollectionNormalizer */
    protected $completenessCollectionNormalizer;

    /** @var UserContext */
    protected $userContext;

    /** @var FillMissingValuesInterface */
    protected $fillMissingProductValues;

    /** @var EntityWithFamilyVariantAttributesProvider */
    protected $attributesProvider;

    /** @var VariantNavigationNormalizer */
    protected $navigationNormalizer;

    /** @var AscendantCategoriesInterface */
    protected $ascendantCategoriesQuery;

    /** @var NormalizerInterface */
    private $parentAssociationsNormalizer;

    /** @var MissingAssociationAdder */
    private $missingAssociationAdder;

    /** @var CatalogContext */
    protected $catalogContext;

    /** @var CompletenessCalculator */
    private $completenessCalculator;

    /** @var MissingRequiredAttributesNormalizerInterface */
    private $missingRequiredAttributesNormalizer;

    public function __construct(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        StructureVersionProviderInterface $structureVersionProvider,
        FormProviderInterface $formProvider,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        ProductCompletenessWithMissingAttributeCodesCollectionNormalizer $completenessCollectionNormalizer,
        UserContext $userContext,
        FillMissingValuesInterface $fillMissingProductValues,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        AscendantCategoriesInterface $ascendantCategoriesQuery,
        MissingAssociationAdder $missingAssociationAdder,
        NormalizerInterface $parentAssociationsNormalizer,
        CatalogContext $catalogContext,
        CompletenessCalculator $completenessCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer
    ) {
        $this->normalizer                       = $normalizer;
        $this->versionNormalizer                = $versionNormalizer;
        $this->versionManager                   = $versionManager;
        $this->imageNormalizer                  = $imageNormalizer;
        $this->localeRepository                 = $localeRepository;
        $this->structureVersionProvider         = $structureVersionProvider;
        $this->formProvider                     = $formProvider;
        $this->localizedConverter               = $localizedConverter;
        $this->productValueConverter            = $productValueConverter;
        $this->completenessCollectionNormalizer = $completenessCollectionNormalizer;
        $this->userContext                      = $userContext;
        $this->fillMissingProductValues         = $fillMissingProductValues;
        $this->attributesProvider               = $attributesProvider;
        $this->navigationNormalizer             = $navigationNormalizer;
        $this->ascendantCategoriesQuery         = $ascendantCategoriesQuery;
        $this->parentAssociationsNormalizer     = $parentAssociationsNormalizer;
        $this->missingAssociationAdder          = $missingAssociationAdder;
        $this->catalogContext                   = $catalogContext;
        $this->completenessCalculator           = $completenessCalculator;
        $this->missingRequiredAttributesNormalizer = $missingRequiredAttributesNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductInterface $product
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $this->missingAssociationAdder->addMissingAssociations($product);
        $normalizedProduct = $this->normalizer->normalize($product, 'standard', $context);
        $normalizedProduct = $this->fillMissingProductValues->fromStandardFormat($normalizedProduct);
        $normalizedProduct['values'] = $this->localizedConverter->convertToLocalizedFormats(
            $normalizedProduct['values'],
            $context
        );

        $normalizedProduct['values'] = $this->productValueConverter->convert($normalizedProduct['values']);

        $oldestLog = $this->versionManager->getOldestLogEntry($product);
        $newestLog = $this->versionManager->getNewestLogEntry($product);

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

        $scopeCode = $context['channel'] ?? null;
        $normalizedProduct['parent_associations'] = $this->parentAssociationsNormalizer->normalize($product, $format, $context);
        $completenesses = $this->getCompletenesses($product);

        $normalizedProduct['meta'] = [
            'form'              => $this->formProvider->getForm($product),
            'id'                => $product->getId(),
            'created'           => $created,
            'updated'           => $updated,
            'model_type'        => 'product',
            'structure_version' => $this->structureVersionProvider->getStructureVersion(),
            'completenesses'    => $this->completenessCollectionNormalizer->normalize($completenesses),
            'required_missing_attributes' => $this->missingRequiredAttributesNormalizer->normalize($completenesses),
            'image'             => $this->normalizeImage($product->getImage(), $this->catalogContext->getLocaleCode()),
        ] + $this->getLabels($product, $scopeCode) + $this->getAssociationMeta($product);

        $normalizedProduct['meta']['ascendant_category_ids'] = $product->isVariant() ?
            $this->ascendantCategoriesQuery->getCategoryIds($product) : [];

        $normalizedProduct['meta'] += $this->getMetaForVariantProduct($product, $format, $context);

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormat);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param ProductInterface $product
     * @param string|null      $scopeCode
     *
     * @return array
     */
    protected function getLabels(ProductInterface $product, string $scopeCode = null)
    {
        $labels = [];

        foreach ($this->localeRepository->getActivatedLocaleCodes() as $localeCode) {
            $labels[$localeCode] = $product->getLabel($localeCode, $scopeCode);
        }

        return ['label' => $labels];
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getAssociationMeta(ProductInterface $product)
    {
        $meta = [];
        $associations = $product->getAssociations();

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
     * In order to get the missing attribute codes, we have to calculate the completeness on the fly.
     * It can look greedy at first, but it's ok as it is only done for one product at a time in the PEF.
     *
     * It allows to not persist the missing attributes in database, which was time costly and maintenance costly
     * (error during concurrent insertion, EAV table format, etc). The performance benefit when saving a product
     * outweighs the cost when calculating the missing attributes here.
     *
     * @param ProductInterface $product
     *
     * @return ProductCompletenessWithMissingAttributeCodesCollection
     */
    protected function getCompletenesses(ProductInterface $product): ProductCompletenessWithMissingAttributeCodesCollection
    {
        $completenessCollection = $this->completenessCalculator->fromProductIdentifier($product->getIdentifier());
        if (null === $completenessCollection) {
            $completenessCollection = new ProductCompletenessWithMissingAttributeCodesCollection($product->getId(), []);
        }

        return $completenessCollection;
    }

    /**
     * @param ValueInterface $value
     * @param string         $localeCode
     *
     * @return array|null
     */
    protected function normalizeImage(?ValueInterface $value, ?string $localeCode = null): ?array
    {
        return $this->imageNormalizer->normalize($value, $localeCode);
    }

    /**
     * @param ProductInterface $product
     * @param string           $format
     * @param array            $context
     *
     * @return array
     */
    protected function getMetaForVariantProduct(
        ProductInterface $product,
        string $format,
        $context = []
    ): array {
        $meta = [
            'variant_navigation'        => [],
            'attributes_for_this_level' => [],
            'attributes_axes'           => [],
            'parent_attributes'         => [],
            'family_variant'            => null,
            'level'                     => null,
        ];

        if (!$product instanceof ProductInterface || !$product->isVariant()) {
            return $meta;
        }

        $meta['variant_navigation'] = $this->navigationNormalizer->normalize($product, $format, $context);
        $meta['family_variant'] = $this->normalizer->normalize($product->getFamilyVariant(), 'standard');
        $meta['level'] = $product->getVariationLevel();

        foreach ($this->attributesProvider->getAttributes($product) as $attribute) {
            $meta['attributes_for_this_level'][] = $attribute->getCode();
        }

        foreach ($this->attributesProvider->getAxes($product) as $attribute) {
            $meta['attributes_axes'][] = $attribute->getCode();
        }

        foreach ($this->attributesProvider->getAxes($product->getParent()) as $attribute) {
            $meta['attributes_axes'][] = $attribute->getCode();
        }

        foreach ($this->attributesProvider->getAttributes($product->getParent()) as $attribute) {
            $meta['parent_attributes'][] = $attribute->getCode();
        }

        return $meta;
    }
}
