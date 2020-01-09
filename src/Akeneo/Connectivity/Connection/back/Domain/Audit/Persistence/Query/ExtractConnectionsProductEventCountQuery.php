<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ExtractConnectionsProductEventCountQuery
{
    public function extractCreatedProductsByConnection(string $date): array;

    public function extractAllCreatedProducts(string $date): array;

    public function extractUpdatedProductsByConnection(string $date): array;

    public function extractAllUpdatedProducts(string $date): array;
}
