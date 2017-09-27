<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;

/**
 * Load a product model from its identifier.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelLoaderProcessor extends AbstractProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return $this->findObject($this->repository, $item);
    }
}
