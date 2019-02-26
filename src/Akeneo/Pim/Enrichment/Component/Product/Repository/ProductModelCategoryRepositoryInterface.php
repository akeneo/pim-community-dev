<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Repository;

use Akeneo\Tool\Component\Classification\Repository\CategoryFilterableRepositoryInterface;
use Akeneo\Tool\Component\Classification\Repository\ItemCategoryRepositoryInterface;

/**
 * Product model category repository interface
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductModelCategoryRepositoryInterface extends
    ItemCategoryRepositoryInterface,
    CategoryFilterableRepositoryInterface
{
}
