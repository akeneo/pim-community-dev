<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\PdfGeneratorBundle\Renderer;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface;
use Pim\Bundle\PdfGeneratorBundle\Renderer\ProductPdfRenderer as PimProductPdfRenderer;
use PimEnterprise\Bundle\WorkflowBundle\Helper\FilterProductValuesHelper;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * PDF renderer used to render PDF for a Product
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductPdfRenderer extends PimProductPdfRenderer
{
    /** @var FilterProductValuesHelper */
    protected $filterHelper;

    /**
     * @param EngineInterface           $templating
     * @param string                    $template
     * @param PdfBuilderInterface       $pdfBuilder
     * @param FilterProductValuesHelper $filterHelper
     * @param string                    $uploadDirectory
     */
    public function __construct(
        EngineInterface $templating,
        $template,
        PdfBuilderInterface $pdfBuilder,
        FilterProductValuesHelper $filterHelper,
        $uploadDirectory
    ) {
        parent::__construct($templating, $template, $pdfBuilder, $uploadDirectory);

        $this->filterHelper = $filterHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributes(ProductInterface $product, $locale)
    {
        $values     = $this->filterHelper->filter($product->getValues()->toArray(), $locale);
        $attributes = [];

        foreach ($values as $value) {
            $attributes[$value->getAttribute()->getCode()] = $value->getAttribute();
        }

        return $attributes;
    }
}
