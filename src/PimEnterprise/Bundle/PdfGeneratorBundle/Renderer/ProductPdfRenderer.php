<?php

namespace PimEnterprise\Bundle\PdfGeneratorBundle\Renderer;

use Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\WorkflowBundle\Helper\FilterProductValuesHelper;
use Pim\Bundle\PdfGeneratorBundle\Renderer\ProductPdfRenderer as PimProductPdfRenderer;

/**
 * PDF renderer used to render PDF for a Product
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductPdfRenderer extends PimProductPdfRenderer
{
    /**
     * @var FilterProductValuesHelper
     */
    protected $filterHelper;

    /**
     * @param EngineInterface     $templating
     * @param string              $template
     * @param PdfBuilderInterface $pdfBuilder
     */
    public function __construct(
        EngineInterface $templating,
        $template,
        PdfBuilderInterface $pdfBuilder,
        FilterProductValuesHelper $filterHelper
    ) {
        parent::__construct($templating, $template, $pdfBuilder);

        $this->filterHelper = $filterHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributes(AbstractProduct $product, $locale)
    {
        $values     = $this->filterHelper->filter($product->getValues()->toArray(), $locale);
        $attributes = [];

        foreach ($values as $value) {
            $attributes[$value->getAttribute()->getCode()] = $value->getAttribute();
        }

        return $attributes;
    }
}
