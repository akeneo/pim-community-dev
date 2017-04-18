<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;

/**
 * Product template builder, allows to create new product template and update them
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductTemplateBuilderInterface
{
    /**
     * Creates a product template
     *
     * @return ProductTemplateInterface
     */
    public function createProductTemplate();

    /**
     * Adds required, translated value(s), that links an attribute to a product template
     *
     * @param ProductTemplateInterface $template
     * @param AttributeInterface[]     $attributes
     */
    public function addAttributes(ProductTemplateInterface $template, array $attributes);

    /**
     * Deletes values that link an attribute to the product template
     *
     * @param ProductTemplateInterface $template
     * @param AttributeInterface       $attribute
     */
    public function removeAttribute(ProductTemplateInterface $template, AttributeInterface $attribute);
}
