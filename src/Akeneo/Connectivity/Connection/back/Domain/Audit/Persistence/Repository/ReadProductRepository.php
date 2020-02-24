<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Repository;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\ReadProducts;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ReadProductRepository
{
    public function bulkInsert(ReadProducts $readProducts): void;
}
