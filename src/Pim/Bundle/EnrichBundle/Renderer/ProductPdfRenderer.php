<?php

namespace Pim\Bundle\EnrichBundle\Renderer;

use Pim\Bundle\EnrichBundle\Renderer\PdfBuilder\PdfBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;

/**
 * PDF renderer used to render PDF for a Product
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPdfRenderer implements RendererInterface
{
    const IMAGE_ATTRIBUTE_TYPE = 'pim_catalog_image';

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var PdfBuilderInterface
     */
    protected $pdfBuilder;

    /**
     * @param EngineInterface     $templating
     * @param string              $template
     * @param PdfBuilderInterface $pdfBuilder
     */
    public function __construct(EngineInterface $templating, $template, PdfBuilderInterface $pdfBuilder)
    {
        $this->templating = $templating;
        $this->template   = $template;
        $this->pdfBuilder = $pdfBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function render($object, $format, array $context = [])
    {
        $params = array_merge(
            $context,
            [
                'product'           => $object,
                'groupedAttributes' => $this->getGroupedAttributes($object, $context['locale']),
                'imageAttributes'   => $this->getImagesAttributes($object, $context['locale']),
            ]
        );

        return $this->pdfBuilder->buildPdfOutput(
            $this->templating->render($this->template, $params)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $format)
    {
        return $object instanceof AbstractProduct;
    }

    /**
     * Get attributes to display
     * @param AbstractProduct $product
     * @param string          $locale
     *
     * @return AbstractAttribute[]
     */
    protected function getAttributes(AbstractProduct $product, $locale)
    {
        return $product->getAttributes();
    }

    protected function getGroupedAttributes(AbstractProduct $product, $locale)
    {
        $groups = [];

        foreach ($this->getAttributes($product, $locale) as $attribute) {
            if (!isset($groups[$attribute->getGroup()->getLabel()])) {
                $groups[$attribute->getGroup()->getLabel()] = [];
            }

            $groups[$attribute->getGroup()->getLabel()][$attribute->getCode()] = $attribute;
        }

        return $groups;
    }

    protected function getImagesAttributes(AbstractProduct $product, $locale)
    {
        $attributes = [];

        foreach ($this->getAttributes($product, $locale) as $attribute) {
            if ($attribute->getAttributeType() === static::IMAGE_ATTRIBUTE_TYPE) {
                $attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $attributes;
    }
}
