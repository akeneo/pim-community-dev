<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;

class AttributeRepository
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

    public function findOneByIdentifier(string $code): ?Attribute
    {
        $sql = <<<SQL
          SELECT
            a.*,
            ag.code
          FROM
            pim_catalog_attribute a
            JOIN pim_catalog_attribute_group ag ON ag.id = a.group_id
          WHERE 
            code = :code
SQL;

        $row = $this->connection->executeQuery($sql, ['code' => $code])->fetch();
        if (empty($row)) {
            return null;
        }

        $attribute = new Attribute(
            $row['id'],
            $row['code'],
            $row['attribute_type'],
            $row['backend_type'],
            $row['created'],
            $row['updated'],
            $row['is_required'],
            $row['is_unique'],
            $row['is_localizable'],
            $row['is_scopable'],
            new ArrayCollection($row['properties']), // array coll
                                // options
            $row['group_id'], // code

            $row['useable_as_grid_filter'],
            //available locales
            $row['max_characters'],
            $row['validation_rule'],
            $row['validation_regexp'],
            $row['number_min'],
            $row['number_max'],
            $row['decimals_allowed'],
            $row['negative_allowed'],
            $row['date_min'],
            $row['date_max'],
            $row['metric_family'],
            $row['default_metric_unit'],
            $row['max_file_size'],
            $row['allowed_extensions'],
            $row['minimumInputLength']
        );

        return $attribute;
    }
}

