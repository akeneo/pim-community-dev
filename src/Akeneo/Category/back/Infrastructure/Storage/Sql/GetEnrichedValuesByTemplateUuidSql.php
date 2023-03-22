<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Query\GetEnrichedValuesByTemplateUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetEnrichedValuesByTemplateUuidSql implements GetEnrichedValuesByTemplateUuid
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     */
    public function byBatchesOf(TemplateUuid $templateUuid, int $batchSize): \Generator
    {
        $offset = 0;
        $query = <<<SQL
        SELECT code,
               value_collection
        FROM 
            pim_catalog_category        
        WHERE root = (
        SELECT category.id
        FROM pim_catalog_category AS category
            LEFT JOIN pim_catalog_category_tree_template AS tree_template
            ON tree_template.category_tree_id = category.id
            LEFT JOIN pim_catalog_category_template AS template
            ON template.uuid = tree_template.category_template_uuid
        WHERE category_template_uuid = :template_uuid
        )
        AND value_collection IS NOT NULL
        LIMIT :limit OFFSET :offset;
        SQL;

        while (true) {
            $rows = $this->connection->executeQuery(
                $query,
                [
                    'template_uuid' => $templateUuid->toBytes(),
                    'limit' => $batchSize,
                    'offset' => $offset,
                ],
                [
                    'template_uuid' => \PDO::PARAM_STR,
                    'limit' => \PDO::PARAM_INT,
                    'offset' => \PDO::PARAM_INT,
                ],
            )->fetchAllAssociative();

            if (empty($rows)) {
                return;
            }
            $valuesByCode = [];
            foreach ($rows as $row) {
                $code = $row['code'];
                $valueCollection = ValueCollection::fromDatabase(
                    json_decode(
                        $row['value_collection'],
                        true,
                        512,
                        JSON_THROW_ON_ERROR,
                    ),
                );
                $valuesByCode[$code] = $valueCollection;
            }

            yield $valuesByCode;
            $offset += $batchSize;
        }
    }
}
