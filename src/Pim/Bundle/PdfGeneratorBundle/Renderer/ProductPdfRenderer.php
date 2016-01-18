<?php

namespace Pim\Bundle\PdfGeneratorBundle\Renderer;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface;
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

    /** @var EngineInterface */
    protected $templating;

    /** @var string */
    protected $template;

    /** @var PdfBuilderInterface */
    protected $pdfBuilder;

    /** @var DataManager */
    protected $dataManager;

    /** @var CacheManager */
    protected $cacheManager;

    /** @var FilterManager */
    protected $filterManager;

    /**
     * @param EngineInterface     $templating
     * @param PdfBuilderInterface $pdfBuilder
     * @param DataManager         $dataManager
     * @param CacheManager        $cacheManager
     * @param FilterManager       $filterManager
     * @param string              $template
     * @param string              $uploadDirectory
     * @param string|null         $customFont
     */
    public function __construct(
        EngineInterface $templating,
        PdfBuilderInterface $pdfBuilder,
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        $template,
        $uploadDirectory,
        $customFont = null
    ) {
        $this->templating      = $templating;
        $this->template        = $template;
        $this->pdfBuilder      = $pdfBuilder;
        $this->uploadDirectory = $uploadDirectory;
        $this->customFont      = $customFont;
        $this->dataManager     = $dataManager;
        $this->cacheManager    = $cacheManager;
        $this->filterManager   = $filterManager;
    }

    /**
     * {@inheritdoc}
     */
    public function render($object, $format, array $context = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $params = array_merge(
            $context,
            [
                'product'           => $object,
                'groupedAttributes' => $this->getGroupedAttributes($object, $context['locale']),
                'imageAttributes'   => $this->getImageAttributes($object, $context['locale'], $context['scope']),
                'customFont'        => $this->customFont
            ]
        );

        $resolver->resolve($params);

        $params['uploadDir'] = $this->uploadDirectory . DIRECTORY_SEPARATOR;

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

    /**
     * Get attributes to display
     *
     * @param ProductInterface $product
     * @param string           $locale
     *
     * @return AttributeInterface[]
     */
    protected function getAttributes(ProductInterface $product, $locale)
    {
        return $product->getAttributes();
    }

    /**
     * get attributes grouped by attribute group
     *
     * @param ProductInterface $product
     * @param string           $locale
     *
     * @return AttributeGroup[]
     */
    protected function getGroupedAttributes(ProductInterface $product, $locale)
    {
        $groups = [];

        foreach ($this->getAttributes($product, $locale) as $attribute) {
            $groupLabel = $attribute->getGroup()->getLabel();
            if (!isset($groups[$groupLabel])) {
                $groups[$groupLabel] = [];
            }

            $groups[$groupLabel][$attribute->getCode()] = $attribute;
        }

        return $groups;
    }

    /**
     * Get all image attributes
     *
     * @param ProductInterface $product
     * @param string           $locale
     * @param string           $scope
     *
     * @return AttributeInterface[]
     */
    protected function getImageAttributes(ProductInterface $product, $locale, $scope)
    {
        $attributes = [];

        foreach ($this->getAttributes($product, $locale) as $attribute) {
            if (AttributeTypes::IMAGE === $attribute->getAttributeType()) {
                $attributes[$attribute->getCode()] = $attribute;
            }
        }

        $this->generateThumbnailsCache($product, $attributes, $locale, $scope);

        return $attributes;
    }

    /**
     * Generate media thumbnails cache used by the PDF document
     *
     * @param ProductInterface     $product
     * @param AttributeInterface[] $imageAttributes
     * @param string               $locale
     * @param string               $scope
     */
    protected function generateThumbnailsCache(ProductInterface $product, array $imageAttributes, $locale, $scope)
    {
        foreach ($imageAttributes as $attribute) {
            $media = $product->getValue($attribute->getCode(), $locale, $scope)->getMedia();
            if (null !== $media && null !== $media->getKey()) {
                $path   = $media->getKey();
                $filter = 'thumbnail';
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
    }

    /**
     * Options configuration (for the option resolver)
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'scope', 'product']);
        $resolver->setDefaults(
            [
                'groupedAttributes' => [],
                'imageAttributes'   => [],
                'renderingDate'     => new \DateTime(),
                'customFont'        => null
            ]
        );
    }
}
