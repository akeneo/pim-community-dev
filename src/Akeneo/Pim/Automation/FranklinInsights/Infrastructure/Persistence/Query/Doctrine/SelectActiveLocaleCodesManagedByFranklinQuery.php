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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\LocaleCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectActiveLocaleCodesManagedByFranklinQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SelectActiveLocaleCodesManagedByFranklinQuery implements SelectActiveLocaleCodesManagedByFranklinQueryInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return LocaleCode[]
     */
    public function execute(): array
    {
        $sqlQuery = <<<'SQL'
select code from pim_catalog_locale 
where is_activated = true and code like "en_%"
SQL;

        $stmt = $this->connection->executeQuery($sqlQuery);
        $result = $stmt->fetchAll();

        return array_map(function ($row) {
            return new LocaleCode($row['code']);
        }, $result);
    }
}
