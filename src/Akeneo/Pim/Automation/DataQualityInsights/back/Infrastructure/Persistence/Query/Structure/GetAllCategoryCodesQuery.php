<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllCategoryCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllCategoryCodesQuery implements GetAllCategoryCodesQueryInterface
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
SELECT code FROM pim_catalog_category;
SQL;

        $statement = $this->connection->executeQuery($query);

        return array_map(function ($row) {
            return new CategoryCode($row['code']);
        }, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }
}
