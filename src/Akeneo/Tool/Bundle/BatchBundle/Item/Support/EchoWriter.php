<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Item\Support;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;

/**
 * Simple ItemReaderInterface implementations that echoes
 * the receive items
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class EchoWriter implements ItemWriterInterface
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            echo $item."\n";
        }
    }
}
