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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FranklinAttributeCreatedRepository implements FranklinAttributeCreatedRepositoryInterface
{
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function save(FranklinAttributeCreated $franklinAttributeCreated): void
    {
        $sqlQuery = <<<'SQL'
INSERT INTO pimee_franklin_insights_attribute_created
(attribute_code, attribute_type)
VALUES (:attribute_code, :attribute_type)
SQL;

        $bindParams = [
            'attribute_code' => (string) $franklinAttributeCreated->getAttributeCode(),
            'attribute_type' => (string) $franklinAttributeCreated->getAttributeType(),
        ];

        $this->dbalConnection->executeUpdate($sqlQuery, $bindParams);
    }

    public function count(): int
    {
        $sql = <<<'SQL'
            SELECT COUNT(attribute_created.attribute_code)
            FROM pimee_franklin_insights_attribute_created as attribute_created
SQL;

        $stmt = $this->dbalConnection->executeQuery($sql);

        return (int) $stmt->fetchColumn();
    }
}
