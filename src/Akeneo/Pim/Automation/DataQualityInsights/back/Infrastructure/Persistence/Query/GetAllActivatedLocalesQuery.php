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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAllActivatedLocalesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Doctrine\DBAL\Connection;

final class GetAllActivatedLocalesQuery implements GetAllActivatedLocalesQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(): LocaleCollection
    {
        $query = <<<SQL
SELECT code FROM pim_catalog_locale WHERE is_activated = 1;
SQL;

        $statement = $this->connection->executeQuery($query);

        return new LocaleCollection(array_map(function ($row) {
            return new LocaleCode($row['code']);
        }, $statement->fetchAll(\PDO::FETCH_ASSOC)));
    }
}
