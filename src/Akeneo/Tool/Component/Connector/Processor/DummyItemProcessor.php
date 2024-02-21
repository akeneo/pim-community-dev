<?php

namespace Akeneo\Tool\Component\Connector\Processor;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;

/**
 * Dummy step, can be use to do nothing until you'll have concrete implementation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DummyItemProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return null;
    }
}
