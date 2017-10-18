<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormat = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var VersionManager */
    protected $versionManager;

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

    /** @var ObjectManager */
    protected $productManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /** @var NormalizerInterface */
    protected $completenessCollectionNormalizer;

    /** @var UserContext */
    protected $userContext;

    /** @var CompletenessCalculatorInterface */
    private $completenessCalculator;

    /** @var FileNormalizer */
    protected $fileNormalizer;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var EntityWithFamilyValuesFillerInterface */
    protected $productValuesFiller;

    /** @var EntityWithFamilyVariantAttributesProvider */
    protected $attributesProvider;

    /** @var VariantNavigationNormalizer */
    protected $navigationNormalizer;

    /**
     * @param NormalizerInterface                       $normalizer
     * @param NormalizerInterface                       $versionNormalizer
     * @param VersionManager                            $versionManager
     * @param LocaleRepositoryInterface                 $localeRepository
     * @param StructureVersionProviderInterface         $structureVersionProvider
     * @param FormProviderInterface                     $formProvider
     * @param AttributeConverterInterface               $localizedConverter
     * @param ConverterInterface                        $productValueConverter
     * @param ObjectManager                             $productManager
     * @param CompletenessManager                       $completenessManager
     * @param ChannelRepositoryInterface                $channelRepository
     * @param CollectionFilterInterface                 $collectionFilter
     * @param NormalizerInterface                       $completenessCollectionNormalizer
     * @param UserContext                               $userContext
     * @param CompletenessCalculatorInterface           $completenessCalculator
     * @param FileNormalizer                            $fileNormalizer
     * @param ProductBuilderInterface                   $productBuilder
     * @param EntityWithFamilyValuesFillerInterface     $productValuesFiller
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     * @param VariantNavigationNormalizer               $navigationNormalizer
     */
    public function __construct(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        LocaleRepositoryInterface $localeRepository,
        StructureVersionProviderInterface $structureVersionProvider,
        FormProviderInterface $formProvider,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        ObjectManager $productManager,
        CompletenessManager $completenessManager,
        ChannelRepositoryInterface $channelRepository,
        CollectionFilterInterface $collectionFilter,
        NormalizerInterface $completenessCollectionNormalizer,
        UserContext $userContext,
        CompletenessCalculatorInterface $completenessCalculator,
        FileNormalizer $fileNormalizer,
        ProductBuilderInterface $productBuilder,
        EntityWithFamilyValuesFillerInterface $productValuesFiller,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer
    ) {
        $this->normalizer                       = $normalizer;
        $this->versionNormalizer                = $versionNormalizer;
        $this->versionManager                   = $versionManager;
        $this->localeRepository                 = $localeRepository;
        $this->structureVersionProvider         = $structureVersionProvider;
        $this->formProvider                     = $formProvider;
        $this->localizedConverter               = $localizedConverter;
        $this->productValueConverter            = $productValueConverter;
        $this->productManager                   = $productManager;
        $this->completenessManager              = $completenessManager;
        $this->channelRepository                = $channelRepository;
        $this->collectionFilter                 = $collectionFilter;
        $this->completenessCollectionNormalizer = $completenessCollectionNormalizer;
        $this->userContext                      = $userContext;
        $this->completenessCalculator           = $completenessCalculator;
        $this->fileNormalizer                   = $fileNormalizer;
        $this->productBuilder                   = $productBuilder;
        $this->productValuesFiller              = $productValuesFiller;
        $this->attributesProvider               = $attributesProvider;
        $this->navigationNormalizer             = $navigationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $this->productBuilder->addMissingAssociations($product);
        $this->productValuesFiller->fillMissingValues($product);
        $normalizedProduct = $this->normalizer->normalize($product, 'standard', $context);
        $normalizedProduct['values'] = $this->localizedConverter->convertToLocalizedFormats(
            $normalizedProduct['values'],
            $context
        );

        $normalizedProduct['values'] = $this->productValueConverter->convert($normalizedProduct['values']);

        $oldestLog = $this->versionManager->getOldestLogEntry($product);
        $newestLog = $this->versionManager->getNewestLogEntry($product);

        $created = null !== $oldestLog ? $this->versionNormalizer->normalize($oldestLog, 'internal_api') : null;
        $updated = null !== $newestLog ? $this->versionNormalizer->normalize($newestLog, 'internal_api') : null;

        $normalizedProduct['meta'] = [
            'form'                => $this->formProvider->getForm($product),
            'id'                  => $product->getId(),
            'created'             => $created,
            'updated'             => $updated,
            'model_type'          => 'product',
            'structure_version'   => $this->structureVersionProvider->getStructureVersion(),
            'completenesses'      => $this->getNormalizedCompletenesses($product),
            'image'               => $this->normalizeImage($product->getImage(), $format, $context),
            'locked_category_ids' => $this->lockedCategoryIds($product),
        ] + $this->getLabels($product) + $this->getAssociationMeta($product);

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

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getLabels(ProductInterface $product)
    {
        $labels = [];

        foreach ($this->localeRepository->getActivatedLocaleCodes() as $localeCode) {
            $labels[$localeCode] = $product->getLabel($localeCode);
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
     * Get Product Completeness and normalize it
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getNormalizedCompletenesses(ProductInterface $product)
    {
        $completenessCollection = $product->getCompletenesses();
        if ($completenessCollection->isEmpty()) {
            $newCompletenesses = $this->completenessCalculator->calculate($product);
            foreach ($newCompletenesses as $completeness) {
                $completenessCollection->add($completeness);
            }
        }
        return $this->completenessCollectionNormalizer->normalize($completenessCollection, 'internal_api');
    }

    /**
     * @param ValueInterface $data
     * @param string         $format
     * @param array          $context
     *
     * @return array|null
     */
    protected function normalizeImage(?ValueInterface $data, $format, $context = [])
    {
        if (null === $data || null === $data->getData()) {
            return null;
        }

        return $this->fileNormalizer->normalize($data->getData(), $format, $context);
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
            'family_variant'            => null
        ];

        if (!$product instanceof VariantProductInterface) {
            return $meta;
        }

        $meta['variant_navigation'] = $this->navigationNormalizer->normalize($product, $format, $context);
        $meta['family_variant'] = $this->normalizer->normalize($product->getFamilyVariant(), 'standard');

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

    /**
     * Returns the category ids inherited from parent product models
     *
     * @param ProductInterface $product
     *
     * @return integer[]
     */
    private function lockedCategoryIds(ProductInterface $product): array
    {
        $result = [];

        $parent = $product->getParent();
        if (null !== $parent) {
            foreach ($parent->getCategories() as $category) {
                $result[] = $category->getId();
            }

            $root = $parent->getParent();
            if (null !== $root) {
                foreach ($root->getCategories() as $category) {
                    $result[] = $category->getId();
                }
            }
        }

        return array_unique($result);
    }
}
