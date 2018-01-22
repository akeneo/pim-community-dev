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
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Pim\Component\Enrich\Query\AscendantCategoriesInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGridNormalizer implements NormalizerInterface
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

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var EntityWithFamilyValuesFillerInterface */
    protected $productValuesFiller;

    /** @var EntityWithFamilyVariantAttributesProvider */
    protected $attributesProvider;

    /** @var VariantNavigationNormalizer */
    protected $navigationNormalizer;

    /** @var AscendantCategoriesInterface */
    protected $ascendantCategoriesQuery;

    /** @var NormalizerInterface */
    protected $incompleteValuesNormalizer;

    /**
     * @param NormalizerInterface                       $normalizer
     * @param NormalizerInterface                       $versionNormalizer
     * @param VersionManager                            $versionManager
     * @param ImageNormalizer                           $imageNormalizer
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
     * @param ProductBuilderInterface                   $productBuilder
     * @param EntityWithFamilyValuesFillerInterface     $productValuesFiller
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     * @param VariantNavigationNormalizer               $navigationNormalizer
     * @param AscendantCategoriesInterface|null         $ascendantCategoriesQuery
     * @param NormalizerInterface                       $incompleteValuesNormalizer
     */
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
        ObjectManager $productManager,
        CompletenessManager $completenessManager,
        ChannelRepositoryInterface $channelRepository,
        CollectionFilterInterface $collectionFilter,
        NormalizerInterface $completenessCollectionNormalizer,
        UserContext $userContext,
        CompletenessCalculatorInterface $completenessCalculator,
        ProductBuilderInterface $productBuilder,
        EntityWithFamilyValuesFillerInterface $productValuesFiller,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        AscendantCategoriesInterface $ascendantCategoriesQuery,
        NormalizerInterface $incompleteValuesNormalizer
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
        $this->productManager                   = $productManager;
        $this->completenessManager              = $completenessManager;
        $this->channelRepository                = $channelRepository;
        $this->collectionFilter                 = $collectionFilter;
        $this->completenessCollectionNormalizer = $completenessCollectionNormalizer;
        $this->userContext                      = $userContext;
        $this->completenessCalculator           = $completenessCalculator;
        $this->productBuilder                   = $productBuilder;
        $this->productValuesFiller              = $productValuesFiller;
        $this->attributesProvider               = $attributesProvider;
        $this->navigationNormalizer             = $navigationNormalizer;
        $this->ascendantCategoriesQuery         = $ascendantCategoriesQuery;
        $this->incompleteValuesNormalizer       = $incompleteValuesNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $this->productValuesFiller->fillMissingValues($product);
        $normalizedProduct = $this->normalizer->normalize($product, 'standard', $context);
        $normalizedProduct['values'] = $this->localizedConverter->convertToLocalizedFormats(
            $normalizedProduct['values'],
            $context
        );

        $normalizedProduct['values'] = $this->productValueConverter->convert($normalizedProduct['values']);

        $scopeCode = $context['channel'] ?? null;

        $normalizedProduct['meta'] = [
            'form'              => $this->formProvider->getForm($product),
            'id'                => $product->getId(),
            'model_type'        => 'product',
            'completenesses'    => $this->getNormalizedCompletenesses($product),
            'image'             => $this->normalizeImage($product->getImage(), $context),
        ] + $this->getLabels($product, $scopeCode) + $this->getAssociationMeta($product);

        $normalizedProduct['meta']['ascendant_category_ids'] =
            ($product instanceof EntityWithFamilyVariantInterface)
            ? $this->ascendantCategoriesQuery->getCategoryIds($product)
            : [];

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

        $completenesses = [];

        foreach ($completenessCollection as $completeness) {
            $completenesses[] = [
                'locale'   => $completeness->getLocale()->getCode(),
                'channel'  => $completeness->getChannel()->getCode(),
                'ratio'    => $completeness->getRatio(),
                'missing'  => $completeness->getMissingCount(),
                'required' => $completeness->getRequiredCount(),
            ];
        }

        return $completenesses;
    }

    /**
     * @param ValueInterface $value
     * @param array          $context
     *
     * @return array|null
     */
    protected function normalizeImage(?ValueInterface $value, array $context = []): ?array
    {
        return $this->imageNormalizer->normalize($value);
    }
}
