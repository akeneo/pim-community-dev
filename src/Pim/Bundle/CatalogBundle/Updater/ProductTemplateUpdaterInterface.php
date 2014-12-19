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
     * @param array                    $products
     * @param ProductTemplateInterface $template
     *
     * @return ProductTemplateUpdaterInterface
     */
    public function update(array $products, ProductTemplateInterface $template);
}
