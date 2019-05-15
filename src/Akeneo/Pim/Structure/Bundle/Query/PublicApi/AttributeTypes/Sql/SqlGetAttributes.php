<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeTypes\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Doctrine\DBAL\Connection;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlGetAttributes implements GetAttributes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forCodes(array $attributeCodes): array
    {
        if (empty($attributeCodes)) {
            return [];
        }

        $query = <<<SQL
        SELECT code, attribute_type, properties
        FROM pim_catalog_attribute
        WHERE code IN (:attributeCodes)
SQL;

        $rawResults = $this->connection->executeQuery(
            $query,
            ['attributeCodes' => $attributeCodes],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        return array_map(function (array $attribute): Attribute {
            return new Attribute(
                $attribute['code'],
                $attribute['attribute_type'],
                unserialize($attribute['properties'])
            );
        }, $rawResults);
    }
}
