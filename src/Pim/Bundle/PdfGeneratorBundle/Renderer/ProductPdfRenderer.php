<?php

namespace Pim\Bundle\PdfGeneratorBundle\Renderer;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
    const IMAGE_ATTRIBUTE_TYPE = 'pim_catalog_image';

    /** @var string */
    const PDF_FORMAT = 'pdf';

    /** @var EngineInterface */
    protected $templating;

    /** @var string */
    protected $template;

    /** @var PdfBuilderInterface */
    protected $pdfBuilder;

    /**
     * @param EngineInterface     $templating
     * @param string              $template
     * @param PdfBuilderInterface $pdfBuilder
     * @param string              $uploadDirectory
     * @param ContainerInterface  $customFont
     */
    public function __construct(
        EngineInterface $templating,
        $template,
        PdfBuilderInterface $pdfBuilder,
        $uploadDirectory,
        $customFont = null
    ) {
        $this->templating      = $templating;
        $this->template        = $template;
        $this->pdfBuilder      = $pdfBuilder;
        $this->uploadDirectory = $uploadDirectory;
        $this->customFont      = $customFont;
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
                'imageAttributes'   => $this->getImageAttributes($object, $context['locale']),
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
     *
     * @return AttributeInterface[]
     */
    protected function getImageAttributes(ProductInterface $product, $locale)
    {
        $attributes = [];

        foreach ($this->getAttributes($product, $locale) as $attribute) {
            if ($attribute->getAttributeType() === static::IMAGE_ATTRIBUTE_TYPE) {
                $attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Options configuration (for the option resolver)
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
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
