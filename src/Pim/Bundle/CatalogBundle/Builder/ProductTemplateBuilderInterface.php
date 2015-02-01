<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;

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
     * Add required value(s) that link an attribute to a product template
     *
     * @param ProductTemplateInterface $template
     * @param AttributeInterface[]     $attributes
     */
    public function addAttributes(ProductTemplateInterface $template, array $attributes);

    /**
     * Delete values that link an attribute to the product template
     *
     * @param ProductTemplateInterface $template
     * @param AttributeInterface       $attribute
     */
    public function removeAttribute(ProductTemplateInterface $template, AttributeInterface $attribute);

    /**
     * Get non eligible attributes to a product template
     *
     * TODO: move this out of template builder, a template can be used without been related to a group
     *
     * @param GroupInterface $group
     *
     * @return AttributeInterface[]
     */
    public function getNonEligibleAttributes(GroupInterface $group);
}
