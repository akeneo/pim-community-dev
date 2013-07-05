<?php

namespace Pim\Bundle\ImportExportBundle\Item\Support;

use Pim\Bundle\ImportExportBundle\Item\ItemWriterInterface;

/**
 * 
 * Simple ItemReaderInterface implementations that echoes
 * the receive items
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class EchoWriter implements ItemWriterInterface
{
    /**
     * @{inherit}
     */
    public function write(array $items)
    {
        foreach($items as $item) {
            echo $item."\n";
        }
    }
}
