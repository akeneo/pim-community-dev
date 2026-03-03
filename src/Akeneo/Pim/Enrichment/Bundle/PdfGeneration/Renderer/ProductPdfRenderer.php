<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\PdfBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * PDF renderer used to render PDF for a Product
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPdfRenderer implements RendererInterface
{
    const PDF_FORMAT = 'pdf';
    const THUMBNAIL_FILTER = 'pdf_thumbnail';

    protected Environment $templating;
    protected PdfBuilderInterface $pdfBuilder;
    protected DataManager $dataManager;
    protected CacheManager $cacheManager;
    protected FilterManager $filterManager;
    protected IdentifiableObjectRepositoryInterface $attributeRepository;
    protected string $template;
    protected ?string $customFont;

    public function __construct(
        Environment $templating,
        PdfBuilderInterface $pdfBuilder,
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        string $template,
        ?string $customFont = null
    ) {
        $this->templating = $templating;
        $this->pdfBuilder = $pdfBuilder;
        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
        $this->attributeRepository = $attributeRepository;
        $this->template = $template;
        $this->customFont = $customFont;
    }

    /**
     * {@inheritdoc}
     */
    public function render($object, $format, array $context = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $imagePaths = $this->getImagePaths($object, $context['locale'], $context['scope']);

        $params = array_merge(
            $context,
            [
                'product' => $object,
                'groupedAttributes' => $this->getGroupedAttributes($object),
                'imagePaths' => $imagePaths,
                'customFont' => $this->customFont,
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
     * Return true if the attribute should be rendered
     */
    protected function canRenderAttribute(?AttributeInterface $attribute): bool
    {
        return null !== $attribute;
    }

    /**
     * Get attributes grouped by attribute group
     *
     * @return AttributeInterface[]
     */
    protected function getGroupedAttributes(ProductInterface $product): array
    {
        $groups = [];

        $attributeCodes = $product->getUsedAttributeCodes();
        if ($product->getFamily()) {
            $attributeCodes = array_unique(array_merge($attributeCodes, $product->getFamily()->getAttributeCodes()));
        }

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if ($this->canRenderAttribute($attribute)) {
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
     * @return string[]
     */
    protected function getImagePaths(ProductInterface $product, string $locale, string $scope): array
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
     * Generate media thumbnails cache used by the PDF document
     *
     * @param string[] $imagePaths
     * @param string   $filter
     */
    protected function generateThumbnailsCache(array $imagePaths, string $filter)
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
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['locale', 'scope', 'product'])
            ->setDefaults(
                [
                    'renderingDate' => new \DateTime(),
                    'filter' => static::THUMBNAIL_FILTER,
                ]
            )
            ->setDefined(['groupedAttributes', 'imagePaths', 'customFont']);
    }
}
