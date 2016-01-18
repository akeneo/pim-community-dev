<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface as NewProductTemplateUpdaterInterface;

/**
 * Update many products at a time from the product template values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.5, please use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface
 */
interface ProductTemplateUpdaterInterface extends NewProductTemplateUpdaterInterface
{
}
