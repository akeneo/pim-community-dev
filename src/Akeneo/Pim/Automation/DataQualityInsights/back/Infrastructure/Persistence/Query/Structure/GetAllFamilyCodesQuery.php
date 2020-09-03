<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllFamilyCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Doctrine\DBAL\Connection;

final class GetAllFamilyCodesQuery implements GetAllFamilyCodesQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(): array
    {
        $query = <<<SQL
SELECT code FROM pim_catalog_family;
SQL;

        $statement = $this->connection->executeQuery($query);

        return array_map(function ($row) {
            return new FamilyCode($row['code']);
        }, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }
}
