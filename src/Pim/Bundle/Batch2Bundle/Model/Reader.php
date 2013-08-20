<?php

namespace Pim\Bundle\Batch2Bundle\Model;

use Pim\Bundle\Batch2Bundle\EventDispatching\DispatchingService;
use Pim\Bundle\Batch2Bundle\Event\EventInterface;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Reader extends DispatchingService implements ExecutionInterface
{
    public function read(ExecutionContext $context)
    {
        $this->execute($context);
        $this->dispatchItemEvent(EventInterface::AFTER_READ, $context->getItem());
    }
}
