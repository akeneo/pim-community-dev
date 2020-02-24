<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\ReadProducts;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Repository\ReadProductRepository;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class DbalReadProductRepository implements ReadProductRepository
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function bulkInsert(ReadProducts $readProducts): void
    {
        foreach ($readProducts->productIds() as $productId) {
            $this->dbalConnection->insert('akeneo_connectivity_connection_audit_read_product', [
                'connection_code' => $readProducts->connectionCode(),
                'product_id' => $productId,
                'event_datetime' => $readProducts->eventDatetime()
            ], [
                'connection_code' => Types::STRING,
                'product_id' => Types::INTEGER,
                'event_datetime' => Types::DATETIME_IMMUTABLE,
            ]);
        }
    }
}
