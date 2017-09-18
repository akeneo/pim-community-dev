<?php

namespace Pim\Component\Connector\Reader\Database;

use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Storage-agnostic product reader using the Product Query Builder
 *
 * @author    Willy MESNAGE <willy.mesnage@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DatabaseProductReaderInterface extends
    ItemReaderInterface,
    InitializableInterface,
    StepExecutionAwareInterface
{
    /**
     * Return the current product.
     *
     * @return ProductInterface
     */
    public function getCurrentProduct();

    /**
     * Is the current product valid ?
     *
     * @return bool
     */
    public function isProductValid();

    /**
     * Go to the next product.
     */
    public function nextProduct();
}
