<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributeCodesFromIdsQueryInterface;
use Doctrine\DBAL\Connection;

class SelectAttributeCodesFromIdsQuery implements SelectAttributeCodesFromIdsQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $attributeIds): array
    {
        $sql = <<<'SQL'
            SELECT code
            FROM pim_catalog_attribute
            WHERE id IN(:attributeIds)
SQL;
        $statement = $this->connection->executeQuery(
            $sql,
            ['attributeIds' => $attributeIds],
            ['attributeIds' => Connection::PARAM_INT_ARRAY]
        );

        return array_map(function (array $result) {
            return $result['code'];
        }, $statement->fetchAll());
    }
}
