<?php

namespace Pim\Bundle\CatalogBundle\Factory;

/**
 * Product template factory
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateFactory
{
    /** @var string */
    protected $productTemplateClass;

    /**
     * @param string $productTemplateClass
     */
    public function __construct($productTemplateClass)
    {
        $this->productTemplateClass = $productTemplateClass;
    }

    /**
     * Creates a product template
     *
     * @return Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface
     */
    public function createProductTemplate()
    {
        return new $this->productTemplateClass();
    }
}
