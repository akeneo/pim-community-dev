<?php

namespace Pim\Component\Catalog\Updater;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;

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
     * @param ProductInterface[]       $products
     *
     * @return ProductTemplateUpdaterInterface
     */
    public function update(ProductTemplateInterface $template, array $products);
}
