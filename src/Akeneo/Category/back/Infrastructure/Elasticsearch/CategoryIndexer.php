<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class CategoryIndexer
{
    public function __construct(
        private Client $categoryClient,
        private Connection $connection,
    ) {
    }

    public function index(string $categoryIdentifier): void
    {
        $sql = <<<SQL
SELECT category.id, category.identifier, category.code, category.updated
FROM pim_catalog_category category
WHERE category.identifier IN (:identifier)
SQL;

        $category = $this->connection->fetchAssociative(
            $sql,
            ['identifier' => $categoryIdentifier],
            ['identifier' => Connection::PARAM_STR_ARRAY]
        );

        $platform = $this->connection->getDatabasePlatform();
        $updatedAt = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPhpValue($category['updated'], $platform);

        $normalizedCategory = [
            'id' => $category['id'],
            'identifier' => $category['identifier'],
            'code' => $category['code'],
            'updated_at' => $updatedAt->format('c'),
        ];

        $this->categoryClient->index($normalizedCategory['identifier'], $normalizedCategory, refresh::disable());
    }
}
