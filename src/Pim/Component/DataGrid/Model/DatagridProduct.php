<?php

namespace Pim\Component\DataGrid\Model;

use Pim\Component\Catalog\Model\AbstractProduct;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * A datagrid product does not contain all the product values of the product, for performance concern.
 * Therefore, this product should not be saved to prevent any data loss.
 *
 * Its purpose is only for read usage.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataGridProduct extends AbstractProduct implements ProductInterface
{
}
