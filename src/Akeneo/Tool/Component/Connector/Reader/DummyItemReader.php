<?php

namespace Akeneo\Tool\Component\Connector\Reader;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;

/**
 * Dummy step, can't be used unless you have a concrete implementation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DummyItemReader implements ItemReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return null;
    }
}
