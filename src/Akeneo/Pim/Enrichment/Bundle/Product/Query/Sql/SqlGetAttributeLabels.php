<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetAttributeLabelsInterface;
use Doctrine\DBAL\Connection;

/**
 * TODO DESC
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetAttributeLabels implements GetAttributeLabelsInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getLabels(array $attributeCodes): array
    {
        $sql = <<<SQL
SELECT
   attribute.code AS code,
   trans.label AS label,
   trans.locale AS locale
FROM pim_catalog_attribute attribute
INNER JOIN pim_catalog_attribute_translation trans ON attribute.id=trans.foreign_key
WHERE attribute.code IN (:attributeCodes)
SQL;
        $rows = $this->connection->executeQuery($sql, ['attributeCodes' => $attributeCodes])->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']][$row['locale']] = $row['label'];
        }

        return $result;
    }
}
