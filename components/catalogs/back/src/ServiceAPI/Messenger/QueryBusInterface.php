<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Messenger;

use Akeneo\Catalogs\ServiceAPI\Query\QueryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface QueryBusInterface
{
    /**
     * @template R
     * @param QueryInterface<R> $query
     * @return R
     */
    public function execute(QueryInterface $query): mixed;
}
