<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Item\Support;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;

/**
 * No operation processor that does not change anthing in the item
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class NoopProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return $item;
    }
}
