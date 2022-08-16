<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\FindCategoryByIdentifier;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindOneByIdentifier implements FindCategoryByIdentifier
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function __invoke(int $identifier): ?Category
    {
        $query = <<<SQL
SELECT cat.id, cat.code, cat.parent_id, JSON_ARRAYAGG(JSON_OBJECT(
    trans.locale,
    trans.label
    )) AS labels
FROM pim_catalog_category AS cat
JOIN pim_catalog_category_translation AS trans
ON trans.foreign_key = cat.id
AND cat.id = :id
SQL;
        $statement = $this->connection->executeQuery($query, ['id' => $identifier]);
        $row = $statement->fetchAssociative();
        if (false === $row) {
            return null;
        }

        $labelCollection = [];
        /**
         * Before:
         * [
         *      [0] => ['en_US' => 'socks'],
         *      [1] => ['fr_FR' => 'chaussettes'],
         * ]
         * After:
         * [
         *     ['en_US' => 'socks'],
         *     ['fr_FR' => 'chaussettes'],
         * ]
         */
        array_map(static function ($label) use (&$labelCollection) {
            $labelCollection[array_keys($label)[0]] = array_values($label)[0];
        }, json_decode($row['labels'], true, 512, JSON_THROW_ON_ERROR));

        return new Category(
            new CategoryId((int)$row['id']),
            new Code($row['code']),
            LabelCollection::fromArray($labelCollection),
            $row['parent_id'] ? new CategoryId((int)$row['parent_id']) : null,
        );
    }
}
