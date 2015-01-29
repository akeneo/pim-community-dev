<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;

/**
 * Update many products at a time from the product template values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductTemplateUpdaterInterface
{
    /**
     * @param ProductTemplateInterface $template
     * TODO (JJ) use FQCN or use statement
     * @param ProductInterface[]       $products
     *
     * @return ProductTemplateUpdaterInterface
     *
     * TODO (JJ) updateProducts ?
     */
    public function update(ProductTemplateInterface $template, array $products);
}
