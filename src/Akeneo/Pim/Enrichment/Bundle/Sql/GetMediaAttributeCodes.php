<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Doctrine\DBAL\Connection;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetMediaAttributeCodes
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
     * @return string[]
     */
    public function execute(): array
    {
        $sql = <<<SQL
SELECT a.code
FROM akeneo_pim.pim_catalog_attribute a 
WHERE a.attribute_type IN (:file_type, :image_type);            
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'file_type' => AttributeTypes::FILE,
                'image_type' => AttributeTypes::IMAGE,
            ]
        )->fetchAll();

        return array_map(function (array $row): string {
            return $row['code'];
        }, $rows);
    }
}
