<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\PdfBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PDF renderer used to render PDF for a Product
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPdfRenderer implements RendererInterface
{
    /** @var string */
    const PDF_FORMAT = 'pdf';

    const THUMBNAIL_FILTER = 'pdf_thumbnail';

    /** @var EngineInterface */
    protected $templating;

    /** @var PdfBuilderInterface */
    protected $pdfBuilder;

    /** @var DataManager */
    protected $dataManager;

    /** @var CacheManager */
    protected $cacheManager;

    /** @var FilterManager */
    protected $filterManager;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var string */
    protected $template;

    /** @var string|null */
    protected $customFont;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeOptionRepository;

    public function __construct(
        EngineInterface $templating,
        PdfBuilderInterface $pdfBuilder,
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        string $template,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        ?string $customFont = null
    ) {
        $this->templating = $templating;
        $this->pdfBuilder = $pdfBuilder;
        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
        $this->attributeRepository = $attributeRepository;
        $this->template = $template;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->customFont = $customFont;
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function render($object, $format, array $context = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $imagePaths = $this->getImagePaths($object, $context['locale'], $context['scope']);
        $optionLabels = $this->getOptionLabels($object, $context['locale'], $context['scope']);
        $params = array_merge(
            $context,
            [
                'product'           => $object,
                'groupedAttributes' => $this->getGroupedAttributes($object),
                'imagePaths'        => $imagePaths,
                'customFont'        => $this->customFont,
                'optionLabels'      => $optionLabels,
            ]
        );

        $params = $resolver->resolve($params);

        $this->generateThumbnailsCache($imagePaths, $params['filter']);

        return $this->pdfBuilder->buildPdfOutput(
            $this->templating->render($this->template, $params)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $format)
    {
        return $object instanceof ProductInterface && $format === static::PDF_FORMAT;
    }

    protected function getAttributeCodes(ProductInterface $product): array
    {
        return $product->getUsedAttributeCodes();
    }

    /**
     * get attributes grouped by attribute group
     *
     * @param ProductInterface $product
     *
     * @return AttributeInterface[]
     */
    protected function getGroupedAttributes(ProductInterface $product)
    {
        $groups = [];

        foreach ($this->getAttributeCodes($product) as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (null !== $attribute) {
                $groupLabel = $attribute->getGroup()->getLabel();
                if (!isset($groups[$groupLabel])) {
                    $groups[$groupLabel] = [];
                }

                $groups[$groupLabel][$attribute->getCode()] = $attribute;
            }
        }

        return $groups;
    }

    /**
     * Get all image paths
     *
     * @param ProductInterface $product
     * @param string           $locale
     * @param string           $scope
     *
     * @return string[]
     */
    protected function getImagePaths(ProductInterface $product, $locale, $scope)
    {
        $imagePaths = [];

        foreach ($this->getAttributeCodes($product) as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (null !== $attribute && AttributeTypes::IMAGE === $attribute->getType()) {
                $mediaValue = $product->getValue(
                    $attribute->getCode(),
                    $attribute->isLocalizable() ? $locale : null,
                    $attribute->isScopable() ? $scope : null
                );

                if (null !== $mediaValue) {
                    $media = $mediaValue->getData();
                    if (null !== $media && null !== $media->getKey()) {
                        $imagePaths[] = $media->getKey();
                    }
                }
            }
        }

        return $imagePaths;
    }

    /**
     * Get all option labels
     */
    protected function getOptionLabels(ProductInterface $product, ?string $localeCode = null, ?string $scopeCode = null): array
    {
        $options = [];

        foreach ($this->getAttributeCodes($product) as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            $locale = $attribute->isLocalizable() ? $localeCode : null;
            $scope = $attribute->isScopable() ? $scopeCode : null;

            if (null !== $attribute && AttributeTypes::OPTION_SIMPLE_SELECT === $attribute->getType()) {
                $optionValue = $product->getValue($attributeCode, $locale, $scope);
                if ($optionValue instanceof OptionValue) {
                    $optionCode = $optionValue->getData();
                    $option = $this->attributeOptionRepository->findOneByIdentifier($attributeCode . '.' . $optionCode);
                    $option->setLocale($localeCode);
                    $translation = $option->getTranslation();
                    $options[$attributeCode] = null !== $translation->getValue() ? $translation->getValue() : sprintf('[%s]', $option->getCode());
                }
            }
            if (null !== $attribute && AttributeTypes::OPTION_MULTI_SELECT === $attribute->getType()) {
                $optionValue = $product->getValue($attributeCode, $locale, $scope);
                if ($optionValue instanceof OptionsValue) {
                    $optionCodes = $optionValue->getData();
                    $labels = [];
                    foreach ($optionCodes as $optionCode) {
                        $option = $this->attributeOptionRepository->findOneByIdentifier($attributeCode.'.'.$optionCode);
                        $option->setLocale($localeCode);
                        $translation = $option->getTranslation();
                        $labels[] = null !== $translation->getValue() ? $translation->getValue() : sprintf('[%s]', $option->getCode());
                    }
                    $options[$attributeCode] = implode(', ', $labels);
                }
            }
        }

        return $options;
    }

    /**
     * Generate media thumbnails cache used by the PDF document
     *
     * @param string[] $imagePaths
     * @param string   $filter
     */
    protected function generateThumbnailsCache(array $imagePaths, $filter)
    {
        foreach ($imagePaths as $path) {
            if (!$this->cacheManager->isStored($path, $filter)) {
                $binary = $this->dataManager->find($filter, $path);
                $this->cacheManager->store(
                    $this->filterManager->applyFilter($binary, $filter),
                    $path,
                    $filter
                );
            }
        }
    }

    /**
     * Options configuration (for the option resolver)
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['locale', 'scope', 'product'])
            ->setDefaults(
                [
                    'renderingDate' => new \DateTime(),
                    'filter'        => static::THUMBNAIL_FILTER,
                ]
            )
            ->setDefined(['groupedAttributes', 'imagePaths', 'customFont', 'optionLabels'])
        ;
    }
}
