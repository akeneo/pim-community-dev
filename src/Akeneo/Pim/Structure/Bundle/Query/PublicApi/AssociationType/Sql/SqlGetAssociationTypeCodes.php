<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AssociationType\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AssociationType\GetAssociationTypeCodes;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetAssociationTypeCodes implements GetAssociationTypeCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(): \Iterator
    {
        $pageSize = 1000;
        $offset = 0;
        $sql = 'SELECT code FROM pim_catalog_association_type LIMIT :limit OFFSET :offset';

        while (true) {
            $associationTypeCodes = $this->connection->executeQuery(
                $sql,
                ['offset' => $offset, 'limit' => $pageSize],
                ['offset' => \PDO::PARAM_INT, 'limit' => \PDO::PARAM_INT]
            )->fetchAll(FetchMode::COLUMN);

            foreach ($associationTypeCodes as $associationTypeCode) {
                yield $associationTypeCode;
            }

            if (count($associationTypeCodes) < $pageSize || 0 === count($associationTypeCodes)) {
                break;
            }

            $offset += $pageSize;
        }
    }
}
