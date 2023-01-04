<?php

namespace Akeneo\Category\Infrastructure\EventSubscriber\Cleaner\Sql;

use Doctrine\DBAL\Connection;

final class GetAllEnrichedCategoryValuesByCategoryCodeSql implements GetAllEnrichedCategoryValuesByCategoryCode
{
    public function __construct(private readonly Connection $dbalConnection)
    {
    }

    public function execute(): array
    {
        $query = <<<SQL
            SELECT code, value_collection
            FROM pim_catalog_category category
            WHERE category.value_collection IS NOT NULL
        SQL;

        $data = $this->dbalConnection->fetchAllAssociative($query);

        $results = [];
        foreach ($data as $key => $value) {
            $results[$value['code']] = $value['value_collection'];
        }

        return $results;
    }
}
