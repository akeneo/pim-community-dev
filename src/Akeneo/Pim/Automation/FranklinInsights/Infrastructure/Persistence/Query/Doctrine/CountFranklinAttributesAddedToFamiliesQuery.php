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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Query\CountFranklinAttributesAddedToFamiliesQueryInterface;
use Doctrine\DBAL\Connection;

final class CountFranklinAttributesAddedToFamiliesQuery implements CountFranklinAttributesAddedToFamiliesQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(): int
    {
        $sql = <<<'SQL'
            SELECT COUNT(attribute_added_to_family.attribute_code)
            FROM pimee_franklin_insights_attribute_added_to_family as attribute_added_to_family
SQL;

        $stmt = $this->connection->executeQuery($sql);

        return (int)$stmt->fetchColumn();
    }
}
