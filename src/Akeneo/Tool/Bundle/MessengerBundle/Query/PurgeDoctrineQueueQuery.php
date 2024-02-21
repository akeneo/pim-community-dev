<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeDoctrineQueueQuery
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $tableName, string $queueName, \DateTimeImmutable $olderThan): int
    {
        return $this->connection->createQueryBuilder()
            ->delete($tableName)
            ->where('created_at < :datetime AND queue_name = :queue')
            ->setParameter('datetime', $olderThan, Types::DATETIME_IMMUTABLE)
            ->setParameter('queue', $queueName)
            ->execute();
    }
}
