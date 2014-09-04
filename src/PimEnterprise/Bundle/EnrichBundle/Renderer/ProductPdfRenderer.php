<?php

namespace PimEnterprise\Bundle\EnrichBundle\Renderer;

use Pim\Bundle\EnrichBundle\Renderer\PdfBuilder\PdfBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\WorkflowBundle\Helper\FilterProductValuesHelper;
use Pim\Bundle\EnrichBundle\Renderer\ProductPdfRenderer as PimProductPdfRenderer;


/**
 * PDF renderer used to render PDF for a Product
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function getAttributes(AbstractProduct $product, $locale)
    {
        $values     = $this->filterHelper->filter($product->getValues()->toArray(), $locale);
        $attributes = [];

        foreach ($values as $value) {
            $attributes[$value->getAttribute()->getCode()] = $value->getAttribute();
        }

        return $attributes;
    }
}
