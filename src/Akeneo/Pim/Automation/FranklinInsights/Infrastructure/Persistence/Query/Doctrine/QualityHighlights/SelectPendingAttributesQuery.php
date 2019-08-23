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

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingAttributesQueryInterface;
use Doctrine\DBAL\Connection;

class SelectPendingAttributesQuery implements SelectPendingAttributesQueryInterface
{
    //TODO: move the constants elsewhere, they will be needed to insert data

    public CONST ACTION_ATTRIBUTE_UPDATED = 1;

    public CONST ACTION_ATTRIBUTE_DELETED = 2;

    public const ENTITY_TYPE_ATTRIBUTE = 1;

    public const STATUS_UNLOCKED = 0;

    public const STATUS_LOCKED = 1;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getUpdatedAttributeIds(): array
    {
        $sql = <<<'SQL'
            SELECT entity_id
            FROM pimee_franklin_insights_pending_items AS pending_items
            WHERE `action` = :action
            AND entity_type = :entity_type
            AND status = :status
            ORDER BY `date` ASC
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'action' => self::ACTION_ATTRIBUTE_UPDATED,
                'entity_type' => self::ENTITY_TYPE_ATTRIBUTE,
                'status' => self::STATUS_UNLOCKED,
            ],
            [
                'action' => \PDO::PARAM_INT,
                'entity_type' => \PDO::PARAM_INT,
                'status' => \PDO::PARAM_INT,
            ]
        );

        $results = $statement->fetchAll();

        return array_map(function (array $result) {
            return (int) $result['entity_id'];
        }, $results);
    }
}
