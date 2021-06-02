<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Messenger;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait QueryBusTrait
{
    private QueryBus $queryBus;

    public function setQueryBus(QueryBus $queryBus): void
    {
        $this->queryBus = $queryBus;
    }
}
